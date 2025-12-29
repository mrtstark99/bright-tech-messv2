<?php
/**
 * Facebook Graph API Helper
 * Handles communication with Facebook Messenger API
 */

class FacebookGraphAPI {
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com/';
    
    /**
     * Send message via Facebook Messenger
     */
    public static function sendMessage(string $recipientId, string $message, string $accessToken): array {
        $url = self::BASE_URL . self::API_VERSION . '/me/messages';
        
        $data = [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message],
            'messaging_type' => 'RESPONSE'
        ];
        
        return self::makeRequest($url, $data, $accessToken);
    }
    
    /**
     * Send message with quick replies
     */
    public static function sendMessageWithQuickReplies(
        string $recipientId, 
        string $message, 
        array $quickReplies,
        string $accessToken
    ): array {
        $url = self::BASE_URL . self::API_VERSION . '/me/messages';
        
        $data = [
            'recipient' => ['id' => $recipientId],
            'message' => [
                'text' => $message,
                'quick_replies' => array_map(function($reply) {
                    return [
                        'content_type' => 'text',
                        'title' => $reply['title'],
                        'payload' => $reply['payload'] ?? $reply['title']
                    ];
                }, $quickReplies)
            ],
            'messaging_type' => 'RESPONSE'
        ];
        
        return self::makeRequest($url, $data, $accessToken);
    }
    
    /**
     * Get user profile information
     */
    public static function getUserProfile(string $userId, string $accessToken): array {
        $url = self::BASE_URL . self::API_VERSION . '/' . $userId;
        $fields = 'id,name,first_name,last_name,profile_pic';
        
        return self::makeGetRequest($url . '?fields=' . $fields . '&access_token=' . $accessToken);
    }
    
    /**
     * Get page information
     */
    public static function getPageInfo(string $pageId, string $accessToken): array {
        $url = self::BASE_URL . self::API_VERSION . '/' . $pageId;
        $fields = 'id,name,picture';
        
        return self::makeGetRequest($url . '?fields=' . $fields . '&access_token=' . $accessToken);
    }
    
    /**
     * Get conversations for a page
     */
    public static function getConversations(string $pageId, string $accessToken, int $limit = 50): array {
        $url = self::BASE_URL . self::API_VERSION . '/' . $pageId . '/conversations';
        $fields = 'participants,messages.limit(1){message,from,created_time}';
        
        return self::makeGetRequest($url . '?fields=' . $fields . '&limit=' . $limit . '&access_token=' . $accessToken);
    }
    
    /**
     * Get messages from a conversation
     */
    public static function getMessages(string $conversationId, string $accessToken, int $limit = 50): array {
        $url = self::BASE_URL . self::API_VERSION . '/' . $conversationId . '/messages';
        $fields = 'message,from,created_time,attachments';
        
        return self::makeGetRequest($url . '?fields=' . $fields . '&limit=' . $limit . '&access_token=' . $accessToken);
    }
    
    /**
     * Exchange short-lived token for long-lived token
     */
    public static function getLongLivedToken(
        string $shortLivedToken,
        string $appId,
        string $appSecret
    ): array {
        $url = self::BASE_URL . 'oauth/access_token';
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $shortLivedToken
        ];
        
        return self::makeGetRequest($url . '?' . http_build_query($params));
    }
    
    /**
     * Get page access tokens for user
     */
    public static function getPageTokens(string $userAccessToken): array {
        $url = self::BASE_URL . self::API_VERSION . '/me/accounts';
        $fields = 'id,name,access_token,picture';
        
        return self::makeGetRequest($url . '?fields=' . $fields . '&access_token=' . $userAccessToken);
    }
    
    /**
     * Subscribe page to webhooks
     */
    public static function subscribePageToWebhooks(string $pageId, string $accessToken): array {
        $url = self::BASE_URL . self::API_VERSION . '/' . $pageId . '/subscribed_apps';
        
        $data = [
            'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins,message_deliveries,message_reads'
        ];
        
        return self::makeRequest($url, $data, $accessToken);
    }
    
    /**
     * Make POST request
     */
    private static function makeRequest(string $url, array $data, string $accessToken): array {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?access_token=' . $accessToken,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400 || isset($result['error'])) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown error',
                'code' => $result['error']['code'] ?? $httpCode
            ];
        }
        
        return ['success' => true, 'data' => $result];
    }
    
    /**
     * Make GET request
     */
    private static function makeGetRequest(string $url): array {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400 || isset($result['error'])) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown error',
                'code' => $result['error']['code'] ?? $httpCode
            ];
        }
        
        return ['success' => true, 'data' => $result];
    }
    
    /**
     * Verify webhook signature
     */
    public static function verifyWebhookSignature(string $payload, string $signature, string $appSecret): bool {
        if (empty($signature) || empty($appSecret)) {
            return false;
        }
        
        $expectedHash = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
        return hash_equals($expectedHash, $signature);
    }
}
