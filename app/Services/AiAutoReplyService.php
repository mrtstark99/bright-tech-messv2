<?php
/**
 * AI Auto Reply Service
 * Generates and sends AI responses when AI mode is enabled for a conversation
 */

require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Integrations/Facebook/GraphAPI.php';

class AiAutoReplyService {
    
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    private PageRepository $pageRepo;
    
    public function __construct() {
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
        $this->pageRepo = new PageRepository();
    }
    
    /**
     * Process AI auto-reply for a conversation
     * 
     * @param int $conversationId
     * @param string $userMessage The message from user that triggered this
     * @return array Result of auto-reply attempt
     */
    public function processAutoReply(int $conversationId, string $userMessage): array {
        debug_log("=== AI Auto-Reply Start === ConvID: {$conversationId}, Msg: " . substr($userMessage, 0, 50), 'ai.log');
        
        // Get conversation
        $conversation = $this->convRepo->find($conversationId);
        
        if (!$conversation) {
            debug_log("AI Auto-Reply: Conversation not found", 'ai.log');
            return ['success' => false, 'error' => 'Conversation not found'];
        }
        
        debug_log("AI Auto-Reply: Conversation found, ai_mode=" . ($conversation['ai_mode'] ?? 'NULL'), 'ai.log');
        
        // Check if AI mode is enabled
        if (!$conversation['ai_mode']) {
            debug_log("AI Auto-Reply: SKIPPED - AI mode is OFF for this conversation", 'ai.log');
            return ['success' => false, 'skipped' => true, 'reason' => 'AI mode is OFF'];
        }
        
        // Check if AI is globally enabled
        $aiEnabled = $this->isAiEnabled();
        debug_log("AI Auto-Reply: Global AI_ENABLED = " . ($aiEnabled ? 'true' : 'false'), 'ai.log');
        
        if (!$aiEnabled) {
            debug_log("AI Auto-Reply: SKIPPED - AI is disabled globally", 'ai.log');
            return ['success' => false, 'skipped' => true, 'reason' => 'AI is disabled globally'];
        }
        
        // Get OpenAI config
        $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        $model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-3.5-turbo';
        
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }
        
        // Get conversation history for context
        $messages = $this->msgRepo->getLatest($conversationId, 10);
        
        // Build chat history for OpenAI
        $chatHistory = $this->buildChatHistory($messages, $conversation);
        
        // Add current user message
        $chatHistory[] = ['role' => 'user', 'content' => $userMessage];
        
        // Generate AI response
        $aiResponse = $this->callOpenAI($apiKey, $model, $chatHistory, $conversation);
        
        if (!$aiResponse['success']) {
            return $aiResponse;
        }
        
        $replyText = $aiResponse['content'];
        
        // Send reply via Facebook
        $page = $this->pageRepo->findByPageId($conversation['page_id']);
        $accessToken = $page['access_token'] ?? null;
        
        if (!$accessToken) {
            // Save locally only
            $msgId = $this->msgRepo->addMessage($conversationId, 'admin', $replyText);
            $this->convRepo->updateLastMessage($conversationId, $replyText, false);
            
            return [
                'success' => true,
                'message_id' => $msgId,
                'reply' => $replyText,
                'facebook_sent' => false,
                'reason' => 'No access token'
            ];
        }
        
        // Send to Facebook
        $fbResult = FacebookGraphAPI::sendMessage(
            $conversation['user_id'],
            $replyText,
            $accessToken
        );
        
        // Save message locally
        $msgId = $this->msgRepo->addMessage($conversationId, 'admin', $replyText);
        $this->convRepo->updateLastMessage($conversationId, $replyText, false);
        
        if ($fbResult['success'] && isset($fbResult['data']['message_id'])) {
            $this->msgRepo->updateFbMessageId($msgId, $fbResult['data']['message_id']);
        }
        
        debug_log("AI Auto-Reply sent: conversation={$conversationId}, reply=" . substr($replyText, 0, 50) . "...", 'ai.log');
        
        return [
            'success' => true,
            'message_id' => $msgId,
            'reply' => $replyText,
            'facebook_sent' => $fbResult['success'] ?? false,
            'facebook_error' => $fbResult['error'] ?? null,
        ];
    }
    
    /**
     * Check if AI is globally enabled
     */
    private function isAiEnabled(): bool {
        return defined('AI_ENABLED') && 
               (AI_ENABLED === true || AI_ENABLED === 'true' || AI_ENABLED === '1');
    }
    
    /**
     * Build chat history for OpenAI from message history
     */
    private function buildChatHistory(array $messages, array $conversation): array {
        $history = [];
        
        // System prompt
        $userName = $conversation['user_name'] ?? 'Khách hàng';
        $history[] = [
            'role' => 'system',
            'content' => "Bạn là nhân viên chăm sóc khách hàng thân thiện và chuyên nghiệp. 
Tên khách hàng: {$userName}
Quy tắc:
- Trả lời ngắn gọn, lịch sự, thân thiện
- Sử dụng tiếng Việt
- Không dùng emoji quá nhiều
- Nếu không biết, hãy hỏi lại hoặc hứa sẽ kiểm tra và phản hồi
- Gọi khách hàng bằng tên nếu có"
        ];
        
        // getLatest already returns in chronological order (oldest first)
        foreach ($messages as $msg) {
            $role = $msg['sender_type'] === 'user' ? 'user' : 'assistant';
            $history[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }
        
        return $history;
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $apiKey, string $model, array $messages, array $conversation): array {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => 300,
            'temperature' => 0.7
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            debug_log("OpenAI curl error: {$error}", 'ai.log');
            return ['success' => false, 'error' => 'Connection error: ' . $error];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $apiError = $data['error']['message'] ?? 'Unknown API error';
            debug_log("OpenAI API error ({$httpCode}): {$apiError}", 'ai.log');
            return ['success' => false, 'error' => 'OpenAI error: ' . $apiError];
        }
        
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        if (empty($content)) {
            return ['success' => false, 'error' => 'Empty response from AI'];
        }
        
        return ['success' => true, 'content' => $content];
    }
}
