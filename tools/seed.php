<?php
/**
 * Database Seeder
 * CLI tool to seed database with sample data
 * 
 * Usage: php tools/seed.php
 */

// Load configuration
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/PageRepository.php';
require_once __DIR__ . '/../app/Repositories/ConversationRepository.php';
require_once __DIR__ . '/../app/Repositories/MessageRepository.php';

echo "=================================\n";
echo "MessV2 Database Seeder\n";
echo "=================================\n\n";

try {
    $pageRepo = new PageRepository();
    $convRepo = new ConversationRepository();
    $msgRepo = new MessageRepository();
    
    // Seed Pages
    echo "📄 Seeding Pages...\n";
    $pages = [
        ['page_id' => '123456789', 'name' => 'Shop ABC', 'is_active' => 1],
        ['page_id' => '987654321', 'name' => 'Tech Store', 'is_active' => 1],
        ['page_id' => '555666777', 'name' => 'Fashion Hub', 'is_active' => 1],
    ];
    
    foreach ($pages as $page) {
        $pageRepo->upsert($page['page_id'], $page);
        echo "   ✓ {$page['name']}\n";
    }
    
    // Seed Conversations
    echo "\n💬 Seeding Conversations...\n";
    $conversations = [
        ['page_id' => '123456789', 'user_id' => 'u001', 'user_name' => 'Nguyễn Văn A', 'last_message' => 'Cho mình hỏi sản phẩm này còn hàng không ạ?', 'unread_count' => 1],
        ['page_id' => '123456789', 'user_id' => 'u002', 'user_name' => 'Trần Thị B', 'last_message' => 'Cảm ơn shop, mình đã nhận được hàng rồi', 'unread_count' => 0],
        ['page_id' => '987654321', 'user_id' => 'u003', 'user_name' => 'Lê Văn C', 'last_message' => 'Ship về Hà Nội bao lâu vậy shop?', 'unread_count' => 3],
        ['page_id' => '987654321', 'user_id' => 'u004', 'user_name' => 'Phạm Thị D', 'last_message' => 'Sản phẩm này có bảo hành không ạ?', 'unread_count' => 0],
        ['page_id' => '555666777', 'user_id' => 'u005', 'user_name' => 'Hoàng Văn E', 'last_message' => 'Cho mình xem thêm mẫu khác', 'unread_count' => 2],
    ];
    
    foreach ($conversations as $conv) {
        $conv['last_message_at'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' minutes'));
        $convId = $convRepo->upsert($conv['page_id'], $conv['user_id'], $conv);
        echo "   ✓ {$conv['user_name']} (ID: {$convId})\n";
        
        // Add sample messages
        $msgRepo->addMessage($convId, 'user', $conv['last_message'], $conv['user_id']);
        
        // Add a response
        if ($conv['unread_count'] == 0) {
            $msgRepo->addMessage($convId, 'ai', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ hỗ trợ bạn ngay.', null);
        }
    }
    
    echo "\n✅ Seeding completed!\n";
    
    // Show stats
    echo "\n📊 Database Stats:\n";
    echo "   - Pages: " . $pageRepo->count() . "\n";
    echo "   - Conversations: " . $convRepo->count() . "\n";
    echo "   - Messages: " . $msgRepo->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
