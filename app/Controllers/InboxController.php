<?php
/**
 * Inbox Controller - Messages & Conversations
 * Handles chat interface with real data
 */
require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';

class InboxController extends Controller {
    
    private PageRepository $pageRepo;
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    
    public function __construct() {
        $this->pageRepo = new PageRepository();
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
    }
    
    public function index(): void {
        // Get conversations with details
        $conversations = $this->convRepo->getAllWithDetails(50);
        
        // Get first conversation's messages if available
        $activeConversation = null;
        $messages = [];
        
        if (!empty($conversations)) {
            $activeConversation = $conversations[0];
            $messages = $this->msgRepo->getByConversation($activeConversation['id'], 50);
        }
        
        // Get stats for sidebar
        $stats = $this->convRepo->getStats();
        
        // Get pages for filter
        $pages = $this->pageRepo->getActive();
        
        $this->view('layouts.app', [
            'title' => 'Inbox',
            '_viewPage' => 'pages.inbox',
            'activeNav' => 'inbox',
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'stats' => $stats,
            'pages' => $pages,
        ]);
    }
    
    public function show(string $conversationId): void {
        // Get all conversations
        $conversations = $this->convRepo->getAllWithDetails(50);
        
        // Get specific conversation
        $activeConversation = $this->convRepo->find((int) $conversationId);
        
        if (!$activeConversation) {
            $this->redirect('/inbox');
            return;
        }
        
        // Get messages for this conversation
        $messages = $this->msgRepo->getByConversation((int) $conversationId, 50);
        
        // Mark as read
        $this->convRepo->markAsRead((int) $conversationId);
        
        // Get stats and pages
        $stats = $this->convRepo->getStats();
        $pages = $this->pageRepo->getActive();
        
        $this->view('layouts.app', [
            'title' => 'Inbox',
            '_viewPage' => 'pages.inbox',
            'activeNav' => 'inbox',
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'stats' => $stats,
            'pages' => $pages,
            'conversationId' => $conversationId,
        ]);
    }
}
