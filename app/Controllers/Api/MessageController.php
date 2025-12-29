<?php
/**
 * API Message Controller
 * Handles sending and fetching messages via API
 */

require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';
require_once APP_PATH . '/Integrations/Facebook/GraphAPI.php';

class MessageController extends Controller {
    
    private PageRepository $pageRepo;
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    
    public function __construct() {
        $this->pageRepo = new PageRepository();
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
    }
    
    /**
     * Send a message
     */
    public function send(): void {
        $conversationId = $this->input('conversation_id');
        $message = $this->input('message');
        
        if (!$conversationId || !$message) {
            $this->json(['error' => 'Missing conversation_id or message'], 400);
            return;
        }
        
        // Get conversation
        $conversation = $this->convRepo->find((int) $conversationId);
        
        if (!$conversation) {
            $this->json(['error' => 'Conversation not found'], 404);
            return;
        }
        
        // Get page access token
        $page = $this->pageRepo->findByPageId($conversation['page_id']);
        $accessToken = $page['access_token'] ?? null;
        
        // Check if n8n is enabled (hybrid mode)
        $n8nEnabled = defined('N8N_ENABLED') && N8N_ENABLED === 'true';
        $n8nWebhookUrl = defined('N8N_WEBHOOK_URL') ? N8N_WEBHOOK_URL : '';
        
        if ($n8nEnabled && !empty($n8nWebhookUrl)) {
            // === n8n MODE: Send via n8n webhook ===
            $webhookResult = $this->sendToN8nWebhook($n8nWebhookUrl, [
                'type' => 'manual_reply',
                'page_id' => $conversation['page_id'],
                'user_id' => $conversation['user_id'],
                'user_name' => $conversation['user_name'] ?? 'Khách hàng',
                'message' => $message,
                'conversation_id' => $conversationId,
                'timestamp' => time() * 1000
            ]);
            
            $this->json([
                'success' => $webhookResult['success'],
                'mode' => 'n8n',
                'status' => 'pending',
                'message' => 'Message sent to n8n for processing',
                'n8n_response' => $webhookResult
            ]);
            return;
        }
        
        // === DIRECT MODE: Send directly via Facebook API ===
        
        // Save message locally first
        $msgId = $this->msgRepo->addMessage(
            (int) $conversationId,
            'admin',
            $message
        );
        
        // Update conversation
        $this->convRepo->updateLastMessage((int) $conversationId, $message, false);
        
        // Try to send via Facebook if we have token
        $fbResult = null;
        if ($accessToken) {
            $fbResult = FacebookGraphAPI::sendMessage(
                $conversation['user_id'],
                $message,
                $accessToken
            );
            
            if ($fbResult['success'] && isset($fbResult['data']['message_id'])) {
                $this->msgRepo->updateFbMessageId($msgId, $fbResult['data']['message_id']);
            }
        }
        
        $this->json([
            'success' => true,
            'mode' => 'direct',
            'message_id' => $msgId,
            'facebook_sent' => $fbResult['success'] ?? false,
            'facebook_error' => $fbResult['error'] ?? null,
        ]);
    }
    
    /**
     * Send message to n8n webhook
     */
    private function sendToN8nWebhook(string $url, array $data): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
    
    /**
     * List messages for a conversation
     */
    public function list(string $conversationId): void {
        $limit = (int) $this->query('limit', 50);
        $after = $this->query('after'); // Timestamp for pagination
        
        $messages = $after 
            ? $this->msgRepo->getAfter((int) $conversationId, $after)
            : $this->msgRepo->getLatest((int) $conversationId, $limit);
        
        $this->json([
            'success' => true,
            'messages' => $messages,
            'count' => count($messages),
        ]);
    }
    
    /**
     * Toggle AI mode for conversation
     */
    public function toggleAiMode(): void {
        $conversationId = $this->input('conversation_id');
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        $result = $this->convRepo->toggleAiMode((int) $conversationId);
        $conversation = $this->convRepo->find((int) $conversationId);
        
        $this->json([
            'success' => $result,
            'ai_mode' => $conversation['ai_mode'] ?? false,
        ]);
    }
    
    /**
     * Set AI auto-enable timer
     */
    public function setAiTimer(): void {
        $conversationId = $this->input('conversation_id');
        $minutes = (int) $this->input('minutes', 0);
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        if ($minutes <= 0) {
            $this->json(['error' => 'Invalid timer value'], 400);
            return;
        }
        
        // Calculate enable_at timestamp
        $enableAt = date('Y-m-d H:i:s', time() + ($minutes * 60));
        $result = $this->convRepo->setAiTimer((int) $conversationId, $enableAt);
        
        $this->json([
            'success' => $result,
            'enable_at' => $enableAt,
            'minutes' => $minutes,
        ]);
    }
    
    /**
     * Update customer notes
     */
    public function updateNotes(): void {
        $conversationId = $this->input('conversation_id');
        $notes = $this->input('notes', '');
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        $result = $this->convRepo->updateNotes((int) $conversationId, $notes);
        
        $this->json(['success' => $result]);
    }
    
    /**
     * Update customer tags
     */
    public function updateTags(): void {
        $conversationId = $this->input('conversation_id');
        $tags = $this->input('tags', []);
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        $result = $this->convRepo->updateTags((int) $conversationId, $tags);
        
        $this->json(['success' => $result]);
    }
    
    /**
     * Mark conversation as read
     */
    public function markRead(): void {
        $conversationId = $this->input('conversation_id');
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        $result = $this->convRepo->markAsRead((int) $conversationId);
        
        $this->json(['success' => $result]);
    }
    
    /**
     * Search conversations
     */
    public function search(): void {
        $query = $this->query('q', '');
        $limit = (int) $this->query('limit', 20);
        
        if (strlen($query) < 2) {
            $this->json(['error' => 'Query too short'], 400);
            return;
        }
        
        $results = $this->convRepo->search($query, $limit);
        
        $this->json([
            'success' => true,
            'conversations' => $results,
            'count' => count($results),
        ]);
    }
    
    /**
     * Update border level (VIP tier) for a conversation
     */
    public function updateBorderLevel(): void {
        $conversationId = $this->input('conversation_id');
        $borderLevel = (int) $this->input('border_level', 0);
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        // Validate border level (0-5)
        if ($borderLevel < 0 || $borderLevel > 5) {
            $this->json(['error' => 'Invalid border level (0-5)'], 400);
            return;
        }
        
        $result = $this->convRepo->updateBorderLevel((int) $conversationId, $borderLevel);
        
        $this->json([
            'success' => $result,
            'border_level' => $borderLevel,
        ]);
    }
    
    /**
     * Update conversation summary
     */
    public function updateSummary(): void {
        $conversationId = $this->input('conversation_id');
        $summary = $this->input('summary', '');
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        $result = $this->convRepo->updateSummary((int) $conversationId, $summary);
        
        $this->json([
            'success' => $result,
        ]);
    }
}
