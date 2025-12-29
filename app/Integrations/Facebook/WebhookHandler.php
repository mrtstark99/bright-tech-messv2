<?php
/**
 * Facebook Webhook Handler
 * Processes incoming webhook events from Facebook
 */

require_once APP_PATH . '/Integrations/Facebook/GraphAPI.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';

class WebhookHandler {
    
    private PageRepository $pageRepo;
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    
    public function __construct() {
        $this->pageRepo = new PageRepository();
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
    }
    
    /**
     * Handle incoming webhook event
     */
    public function handle(array $payload): array {
        $results = [];
        
        if ($payload['object'] !== 'page') {
            return ['error' => 'Not a page event'];
        }
        
        foreach ($payload['entry'] ?? [] as $entry) {
            $pageId = $entry['id'];
            
            foreach ($entry['messaging'] ?? [] as $event) {
                $result = $this->processEvent($pageId, $event);
                $results[] = $result;
            }
        }
        
        return ['success' => true, 'processed' => count($results), 'results' => $results];
    }
    
    /**
     * Process individual messaging event
     */
    private function processEvent(string $pageId, array $event): array {
        $senderId = $event['sender']['id'] ?? null;
        $recipientId = $event['recipient']['id'] ?? null;
        
        if (!$senderId || !$recipientId) {
            return ['error' => 'Missing sender or recipient'];
        }
        
        // Determine if message is from user or page
        $isFromUser = ($senderId !== $pageId);
        
        if (isset($event['message'])) {
            return $this->handleMessage($pageId, $event, $isFromUser);
        }
        
        if (isset($event['postback'])) {
            return $this->handlePostback($pageId, $event);
        }
        
        if (isset($event['delivery'])) {
            return $this->handleDelivery($event);
        }
        
        if (isset($event['read'])) {
            return $this->handleRead($event);
        }
        
        return ['type' => 'unknown', 'event' => $event];
    }
    
    /**
     * Handle incoming message
     */
    private function handleMessage(string $pageId, array $event, bool $isFromUser): array {
        $senderId = $event['sender']['id'];
        $message = $event['message'];
        $messageId = $message['mid'] ?? null;
        $text = $message['text'] ?? '';
        $attachments = $message['attachments'] ?? [];
        $timestamp = $event['timestamp'] ?? time() * 1000;
        
        // Check if message already exists (avoid duplicates)
        if ($messageId && $this->msgRepo->findByFbMessageId($messageId)) {
            return ['type' => 'duplicate', 'mid' => $messageId];
        }
        
        // Get or create conversation
        $userId = $isFromUser ? $senderId : $event['recipient']['id'];
        $conversation = $this->convRepo->findByPageAndUser($pageId, $userId);
        
        if (!$conversation) {
            // Try to get user info from Facebook
            $userName = $this->fetchUserName($userId, $pageId);
            
            $convId = $this->convRepo->upsert($pageId, $userId, [
                'user_name' => $userName,
                'last_message' => $text,
                'last_message_at' => date('Y-m-d H:i:s', $timestamp / 1000),
            ]);
        } else {
            $convId = $conversation['id'];
            
            // Update last message
            $this->convRepo->updateLastMessage($convId, $text, $isFromUser);
        }
        
        // Save message - use server time for consistent ordering
        $msgId = $this->msgRepo->create([
            'conversation_id' => $convId,
            'sender_type' => $isFromUser ? 'user' : 'admin',
            'sender_id' => $senderId,
            'content' => $text,
            'message_type' => !empty($attachments) ? 'attachment' : 'text',
            'attachments' => json_encode($attachments),
            'fb_message_id' => $messageId,
            'status' => 'delivered',
            'created_at' => date('Y-m-d H:i:s'),  // Use server time
        ]);
        
        // Trigger AI Auto-Reply if message is from user
        $aiResult = null;
        if ($isFromUser && !empty($text)) {
            $aiResult = $this->triggerAiAutoReply($convId, $text);
        }
        
        return [
            'type' => 'message',
            'from_user' => $isFromUser,
            'conversation_id' => $convId,
            'message_id' => $msgId,
            'text' => $text,
            'ai_auto_reply' => $aiResult,
        ];
    }
    
    /**
     * Trigger AI Auto-Reply for incoming user message
     */
    private function triggerAiAutoReply(int $conversationId, string $userMessage): ?array {
        try {
            require_once APP_PATH . '/Services/AiAutoReplyService.php';
            $aiService = new AiAutoReplyService();
            return $aiService->processAutoReply($conversationId, $userMessage);
        } catch (Exception $e) {
            debug_log("AI Auto-Reply error: " . $e->getMessage(), 'ai.log');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Handle postback (button click)
     */
    private function handlePostback(string $pageId, array $event): array {
        $payload = $event['postback']['payload'] ?? '';
        $title = $event['postback']['title'] ?? '';
        
        // Log postback as a message
        $userId = $event['sender']['id'];
        $conversation = $this->convRepo->findByPageAndUser($pageId, $userId);
        
        if ($conversation) {
            $this->msgRepo->create([
                'conversation_id' => $conversation['id'],
                'sender_type' => 'user',
                'sender_id' => $userId,
                'content' => "[Postback] {$title}: {$payload}",
                'message_type' => 'postback',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        return ['type' => 'postback', 'payload' => $payload, 'title' => $title];
    }
    
    /**
     * Handle delivery receipt
     */
    private function handleDelivery(array $event): array {
        $mids = $event['delivery']['mids'] ?? [];
        
        foreach ($mids as $mid) {
            $msg = $this->msgRepo->findByFbMessageId($mid);
            if ($msg) {
                $this->msgRepo->updateStatus($msg['id'], 'delivered');
            }
        }
        
        return ['type' => 'delivery', 'mids' => $mids];
    }
    
    /**
     * Handle read receipt
     */
    private function handleRead(array $event): array {
        $watermark = $event['read']['watermark'] ?? 0;
        
        return ['type' => 'read', 'watermark' => $watermark];
    }
    
    /**
     * Fetch user name from Facebook
     */
    private function fetchUserName(string $userId, string $pageId): string {
        $page = $this->pageRepo->findByPageId($pageId);
        
        if (!$page || empty($page['access_token'])) {
            return 'Người dùng';
        }
        
        $result = FacebookGraphAPI::getUserProfile($userId, $page['access_token']);
        
        if ($result['success'] && isset($result['data']['name'])) {
            return $result['data']['name'];
        }
        
        return 'Người dùng';
    }
}
