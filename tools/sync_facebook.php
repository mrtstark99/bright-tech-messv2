<?php
/**
 * Facebook Sync Tool
 * Fetches real conversations and messages from Facebook API
 * 
 * Usage: php tools/sync_facebook.php [--clear]
 */

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/PageRepository.php';
require_once __DIR__ . '/../app/Repositories/ConversationRepository.php';
require_once __DIR__ . '/../app/Repositories/MessageRepository.php';
require_once __DIR__ . '/../app/Integrations/Facebook/GraphAPI.php';

echo "=================================\n";
echo "MessV2 Facebook Sync Tool\n";
echo "=================================\n\n";

// Check for --clear flag
$clearData = in_array('--clear', $argv ?? []);

$pageRepo = new PageRepository();
$convRepo = new ConversationRepository();
$msgRepo = new MessageRepository();

if ($clearData) {
    echo "🗑️  Clearing existing data...\n";
    
    // Clear messages first (foreign key)
    Connection::exec("DELETE FROM messages");
    echo "   ✓ Messages cleared\n";
    
    // Clear conversations
    Connection::exec("DELETE FROM conversations");
    echo "   ✓ Conversations cleared\n";
    
    // Reset auto-increment
    Connection::exec("DELETE FROM sqlite_sequence WHERE name IN ('messages', 'conversations')");
    echo "   ✓ IDs reset\n\n";
}

// Get all active pages with tokens
$pages = $pageRepo->getActive();

if (empty($pages)) {
    echo "❌ No pages found. Please add pages in Settings first.\n";
    exit(1);
}

echo "📄 Found " . count($pages) . " active page(s)\n\n";

$totalConversations = 0;
$totalMessages = 0;

foreach ($pages as $page) {
    $pageId = $page['page_id'];
    $pageName = $page['name'];
    $accessToken = $page['access_token'] ?? null;
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📘 {$pageName} ({$pageId})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    if (empty($accessToken)) {
        echo "   ⚠️  No access token, skipping...\n\n";
        continue;
    }
    
    // Fetch conversations from Facebook
    echo "   📥 Fetching conversations...\n";
    $result = FacebookGraphAPI::getConversations($pageId, $accessToken, 50);
    
    if (!$result['success']) {
        echo "   ❌ Error: " . ($result['error'] ?? 'Unknown error') . "\n\n";
        continue;
    }
    
    $conversations = $result['data']['data'] ?? [];
    echo "   ✓ Found " . count($conversations) . " conversation(s)\n";
    
    foreach ($conversations as $conv) {
        $convId = $conv['id'];
        $participants = $conv['participants']['data'] ?? [];
        $lastMessage = $conv['messages']['data'][0] ?? null;
        
        // Find the user (not the page)
        $user = null;
        foreach ($participants as $p) {
            if ($p['id'] !== $pageId) {
                $user = $p;
                break;
            }
        }
        
        if (!$user) continue;
        
        $userId = $user['id'];
        $userName = $user['name'] ?? 'Người dùng';
        
        // Try to get user profile picture
        $avatar = null;
        $profileResult = FacebookGraphAPI::getUserProfile($userId, $accessToken);
        if ($profileResult['success'] && isset($profileResult['data']['profile_pic'])) {
            $avatar = $profileResult['data']['profile_pic'];
        }
        
        // Upsert conversation
        $localConvId = $convRepo->upsert($pageId, $userId, [
            'user_name' => $userName,
            'avatar' => $avatar,
            'last_message' => $lastMessage['message'] ?? '',
            'last_message_at' => $lastMessage ? date('Y-m-d H:i:s', strtotime($lastMessage['created_time'])) : date('Y-m-d H:i:s'),
        ]);
        
        echo "   → {$userName}\n";
        $totalConversations++;
        
        // Fetch messages for this conversation
        $messagesResult = FacebookGraphAPI::getMessages($convId, $accessToken, 25);
        
        if ($messagesResult['success']) {
            $messages = $messagesResult['data']['data'] ?? [];
            
            foreach ($messages as $msg) {
                $msgId = $msg['id'];
                $msgContent = $msg['message'] ?? '';
                $msgFrom = $msg['from']['id'] ?? '';
                $msgTime = $msg['created_time'] ?? 'now';
                
                // Determine sender type
                $senderType = ($msgFrom === $pageId) ? 'admin' : 'user';
                
                // Check if message already exists
                $existing = $msgRepo->findByFbMessageId($msgId);
                if ($existing) continue;
                
                // Create message
                $msgRepo->create([
                    'conversation_id' => $localConvId,
                    'sender_type' => $senderType,
                    'sender_id' => $msgFrom,
                    'content' => $msgContent,
                    'fb_message_id' => $msgId,
                    'status' => 'delivered',
                    'created_at' => date('Y-m-d H:i:s', strtotime($msgTime)),
                ]);
                
                $totalMessages++;
            }
            
            // Update unread count (simplified: mark all as read after sync)
            $convRepo->markAsRead($localConvId);
        }
    }
    
    echo "\n";
}

echo "=================================\n";
echo "✅ Sync completed!\n";
echo "   Conversations: {$totalConversations}\n";
echo "   Messages: {$totalMessages}\n";
echo "=================================\n";
