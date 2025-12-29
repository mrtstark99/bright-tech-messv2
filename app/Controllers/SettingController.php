<?php
/**
 * Settings Controller
 * Manages application settings, Facebook tokens, and integrations
 */
require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Repositories/PageRepository.php';
require_once APP_PATH . '/Repositories/SettingRepository.php';
require_once APP_PATH . '/Integrations/Facebook/GraphAPI.php';

class SettingController extends Controller {
    
    private PageRepository $pageRepo;
    private SettingRepository $settingRepo;
    
    public function __construct() {
        $this->pageRepo = new PageRepository();
        $this->settingRepo = new SettingRepository();
    }
    
    public function index(): void {
        // Get all settings
        $settings = $this->settingRepo->getAllAsArray();
        
        // Get pages with tokens
        $pages = $this->pageRepo->getAllWithStats();
        
        // Facebook App config from env
        $facebookConfig = [
            'app_id' => getenv('FACEBOOK_APP_ID') ?: '',
            'app_secret' => !empty(getenv('FACEBOOK_APP_SECRET')) ? '••••••••' : '',
            'webhook_token' => getenv('WEBHOOK_VERIFY_TOKEN') ?: 'messv2_verify_token',
            'webhook_url' => APP_URL . '/api/webhook',
        ];
        
        // AI config
        $aiConfig = [
            'enabled' => getenv('AI_ENABLED') === 'true',
            'provider' => getenv('AI_PROVIDER') ?: 'openai',
            'model' => getenv('OPENAI_MODEL') ?: 'gpt-3.5-turbo',
            'has_key' => !empty(getenv('OPENAI_API_KEY')),
        ];
        
        // n8n config
        $n8nConfig = [
            'enabled' => getenv('N8N_ENABLED') === 'true',
            'webhook_url' => getenv('N8N_WEBHOOK_URL') ?: '',
        ];
        
        $this->view('layouts.app', [
            'title' => 'Cài đặt',
            '_viewPage' => 'pages.settings',
            'activeNav' => 'settings',
            'settings' => $settings,
            'pages' => $pages,
            'facebookConfig' => $facebookConfig,
            'aiConfig' => $aiConfig,
            'n8nConfig' => $n8nConfig,
        ]);
    }
    
    /**
     * Save general settings
     */
    public function saveSettings(): void {
        $settings = $this->input();
        
        foreach ($settings as $key => $value) {
            $this->settingRepo->set($key, $value);
        }
        
        $this->json(['success' => true]);
    }
    
    /**
     * Get page tokens from Facebook using User Access Token
     */
    public function getPageTokens(): void {
        $userAccessToken = $this->input('user_access_token');
        
        if (empty($userAccessToken)) {
            $this->json(['error' => 'Missing user access token'], 400);
            return;
        }
        
        // Get page tokens from Facebook
        $result = FacebookGraphAPI::getPageTokens($userAccessToken);
        
        if (!$result['success']) {
            $this->json([
                'error' => $result['error'] ?? 'Failed to get page tokens',
            ], 400);
            return;
        }
        
        $pages = $result['data']['data'] ?? [];
        $savedCount = 0;
        
        foreach ($pages as $page) {
            $pageId = $page['id'];
            $pageName = $page['name'];
            $accessToken = $page['access_token'];
            $avatar = $page['picture']['data']['url'] ?? null;
            
            // Upsert page with token
            $this->pageRepo->upsert($pageId, [
                'name' => $pageName,
                'avatar' => $avatar,
                'access_token' => $accessToken,
                'is_active' => 1,
            ]);
            
            $savedCount++;
        }
        
        $this->json([
            'success' => true,
            'pages_count' => $savedCount,
            'pages' => array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'picture' => $p['picture']['data']['url'] ?? null,
                ];
            }, $pages),
        ]);
    }
    
    /**
     * Subscribe page to webhooks
     */
    public function subscribePage(): void {
        $pageId = $this->input('page_id');
        
        if (empty($pageId)) {
            $this->json(['error' => 'Missing page_id'], 400);
            return;
        }
        
        $page = $this->pageRepo->findByPageId($pageId);
        
        if (!$page || empty($page['access_token'])) {
            $this->json(['error' => 'Page not found or no access token'], 404);
            return;
        }
        
        $result = FacebookGraphAPI::subscribePageToWebhooks($pageId, $page['access_token']);
        
        if (!$result['success']) {
            $this->json([
                'error' => $result['error'] ?? 'Failed to subscribe',
            ], 400);
            return;
        }
        
        $this->json(['success' => true, 'subscribed' => true]);
    }
    
    /**
     * Toggle page active status
     */
    public function togglePage(): void {
        $pageId = $this->input('page_id');
        
        if (empty($pageId)) {
            $this->json(['error' => 'Missing page_id'], 400);
            return;
        }
        
        $page = $this->pageRepo->findByPageId($pageId);
        
        if (!$page) {
            $this->json(['error' => 'Page not found'], 404);
            return;
        }
        
        $result = $this->pageRepo->toggleActive($page['id']);
        
        $this->json(['success' => $result]);
    }
    
    /**
     * Delete page
     */
    public function deletePage(): void {
        $pageId = $this->input('page_id');
        
        if (empty($pageId)) {
            $this->json(['error' => 'Missing page_id'], 400);
            return;
        }
        
        $page = $this->pageRepo->findByPageId($pageId);
        
        if (!$page) {
            $this->json(['error' => 'Page not found'], 404);
            return;
        }
        
        $result = $this->pageRepo->delete($page['id']);
        
        $this->json(['success' => $result]);
    }
    
    /**
     * Test webhook connection
     */
    public function testWebhook(): void {
        $this->json([
            'success' => true,
            'webhook_url' => APP_URL . '/api/webhook',
            'verify_token' => getenv('WEBHOOK_VERIFY_TOKEN') ?: 'messv2_verify_token',
            'message' => 'Use these values in Facebook Developer Console',
        ]);
    }
}
