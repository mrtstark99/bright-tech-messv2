<?php
/**
 * Summarize Controller
 * Generates AI-powered customer portrait summary using OpenAI
 */

require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';

class SummarizeController extends Controller {
    
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    
    public function __construct() {
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
    }
    
    /**
     * Generate AI summary for a conversation
     */
    public function generate(): void {
        $conversationId = $this->input('conversation_id');
        
        if (!$conversationId) {
            $this->json(['error' => 'Missing conversation_id'], 400);
            return;
        }
        
        // Check if OpenAI is configured (from .env constants)
        $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        $model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-3.5-turbo';
        
        if (empty($apiKey)) {
            $this->json(['error' => 'OpenAI API Key chưa được cấu hình trong .env'], 400);
            return;
        }
        
        // Get conversation
        $conv = $this->convRepo->find((int) $conversationId);
        if (!$conv) {
            $this->json(['error' => 'Conversation not found'], 404);
            return;
        }
        
        // Get last N messages (default 10)
        $limit = 10;
        $messages = $this->msgRepo->getLatest((int) $conversationId, $limit);
        
        if (empty($messages)) {
            $this->json(['error' => 'Không có tin nhắn để phân tích'], 400);
            return;
        }
        
        // Format messages for prompt
        $transcript = "";
        foreach (array_reverse($messages) as $msg) {
            $role = ($msg['sender_type'] === 'user') ? 'Khách hàng' : 'Nhân viên';
            $text = $msg['content'] ?? '';
            $transcript .= "{$role}: {$text}\n";
        }
        
        $currentSummary = $conv['summary'] ?? 'Chưa có';
        $userName = $conv['user_name'] ?? 'Khách hàng';
        
        // Build Prompt
        $systemPrompt = "Bạn là chuyên gia CRM. Hãy tạo 'Chân dung khách hàng' ngắn gọn và hữu ích.";
        $userPrompt = "
Tên khách hàng: {$userName}

Tóm tắt hiện tại:
{$currentSummary}

Lịch sử hội thoại gần đây ({$limit} tin nhắn):
{$transcript}

Nhiệm vụ:
Cập nhật bản tóm tắt chân dung khách hàng dựa trên các tương tác gần đây.
Tập trung vào:
- Nhu cầu và sở thích của khách hàng
- Vấn đề đã giải quyết hoặc đang chờ xử lý
- Cảm xúc (Tích cực/Trung bình/Tiêu cực)
- Gợi ý hành động tiếp theo

Giữ tóm tắt dưới 150 từ. Trả về CHỈ nội dung tóm tắt bằng tiếng Việt, sử dụng Markdown format:
- **bold** cho điểm quan trọng
- *italic* cho ghi chú
- - danh sách bullet cho các điểm
";

        // Call OpenAI API
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->json(['error' => 'Lỗi kết nối: ' . $error], 500);
            return;
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $apiError = $responseData['error']['message'] ?? 'Unknown API Error';
            $this->json(['error' => 'OpenAI Error: ' . $apiError], $httpCode);
            return;
        }
        
        // Extract summary
        $newSummary = $responseData['choices'][0]['message']['content'] ?? '';
        
        if (empty($newSummary)) {
            $this->json(['error' => 'Không thể tạo tóm tắt từ response'], 500);
            return;
        }
        
        // Save to database
        $this->convRepo->updateSummary((int) $conversationId, $newSummary);
        
        $this->json([
            'success' => true,
            'summary' => $newSummary
        ]);
    }
}
