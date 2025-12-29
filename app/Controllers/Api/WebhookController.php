<?php
/**
 * API Webhook Controller
 * Handles Facebook webhook verification and incoming events
 */

require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Database/Connection.php';
require_once APP_PATH . '/Integrations/Facebook/WebhookHandler.php';
require_once APP_PATH . '/Integrations/Facebook/GraphAPI.php';

class WebhookController extends Controller {
    
    /**
     * Handle webhook (both GET for verification and POST for events)
     */
    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $this->verify();
        } else {
            $this->receive();
        }
    }
    
    /**
     * Verify webhook (Facebook verification handshake)
     */
    private function verify(): void {
        $mode = $this->query('hub_mode');
        $token = $this->query('hub_verify_token');
        $challenge = $this->query('hub_challenge');
        
        $verifyToken = defined('WEBHOOK_VERIFY_TOKEN') ? WEBHOOK_VERIFY_TOKEN : 'messv2_verify_token';
        
        if ($mode === 'subscribe' && $token === $verifyToken) {
            // Verification successful
            http_response_code(200);
            echo $challenge;
            exit;
        }
        
        // Verification failed
        http_response_code(403);
        echo 'Verification failed';
        exit;
    }
    
    /**
     * Receive incoming webhook events
     */
    private function receive(): void {
        // Get raw payload
        $payload = file_get_contents('php://input');
        
        // Log webhook for debugging
        if (DEBUG_MODE) {
            debug_log("Webhook received: " . $payload, 'webhook.log');
        }
        
        // Verify signature if app secret is configured
        if (defined('FACEBOOK_APP_SECRET') && FACEBOOK_APP_SECRET) {
            $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
            
            if (!FacebookGraphAPI::verifyWebhookSignature($payload, $signature, FACEBOOK_APP_SECRET)) {
                debug_log("Invalid webhook signature", 'webhook.log');
                $this->json(['error' => 'Invalid signature'], 403);
                return;
            }
        }
        
        // Parse payload
        $data = json_decode($payload, true);
        
        if (!$data) {
            $this->json(['error' => 'Invalid JSON'], 400);
            return;
        }
        
        // Process webhook
        try {
            $handler = new WebhookHandler();
            $result = $handler->handle($data);
            
            if (DEBUG_MODE) {
                debug_log("Webhook processed: " . json_encode($result), 'webhook.log');
            }
            
            $this->json($result);
            
        } catch (Exception $e) {
            debug_log("Webhook error: " . $e->getMessage(), 'webhook.log');
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
