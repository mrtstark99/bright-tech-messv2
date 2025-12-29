<?php
/**
 * Dashboard Controller
 * Displays overview stats and recent activity
 */
require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Repositories/ConversationRepository.php';
require_once APP_PATH . '/Repositories/MessageRepository.php';

class DashboardController extends Controller {
    
    private PageRepository $pageRepo;
    private ConversationRepository $convRepo;
    private MessageRepository $msgRepo;
    
    public function __construct() {
        $this->pageRepo = new PageRepository();
        $this->convRepo = new ConversationRepository();
        $this->msgRepo = new MessageRepository();
    }
    
    public function index(): void {
        // Get stats
        $convStats = $this->convRepo->getStats();
        $msgStats = $this->msgRepo->getStats();
        
        // Get recent conversations
        $recentConversations = $this->convRepo->getAllWithDetails(5);
        
        // Get pages count
        $pages = $this->pageRepo->getActive();
        
        $this->view('layouts.app', [
            'title' => 'Dashboard',
            '_viewPage' => 'pages.dashboard',
            'activeNav' => 'dashboard',
            'stats' => [
                'totalMessages' => $msgStats['total'] ?? 0,
                'unreadCount' => $convStats['total_unread'] ?? 0,
                'aiResponses' => $msgStats['from_ai'] ?? 0,
                'pageCount' => count($pages),
                'activePages' => count($pages),
            ],
            'recentConversations' => $recentConversations,
            'pages' => $pages,
        ]);
    }
}
