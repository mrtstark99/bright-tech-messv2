<?php
/**
 * MessV2 - Front Controller
 * Entry point for all requests with complete routing
 */

// Load configuration
require_once __DIR__ . '/app/Config/config.php';

// Load Router
require_once __DIR__ . '/app/Core/Router.php';

// Initialize Router
$router = new Router('/messv2');

// ===== Web Routes =====
$router->get('/', 'DashboardController', 'index');
$router->get('/inbox', 'InboxController', 'index');
$router->get('/inbox/{conversationId}', 'InboxController', 'show');
$router->get('/pages', 'PageController', 'index');
$router->get('/posts', 'PostController', 'index');
$router->get('/automation', 'AutomationController', 'index');
$router->get('/tools', 'ToolController', 'index');
$router->get('/settings', 'SettingController', 'index');

// ===== API Routes =====

// Messages
$router->post('/api/messages/send', 'Api\\MessageController', 'send');
$router->get('/api/messages/{conversationId}', 'Api\\MessageController', 'list');
$router->post('/api/messages/ai-mode', 'Api\\MessageController', 'toggleAiMode');
$router->post('/api/messages/ai-timer', 'Api\\MessageController', 'setAiTimer');
$router->post('/api/messages/notes', 'Api\\MessageController', 'updateNotes');
$router->post('/api/messages/tags', 'Api\\MessageController', 'updateTags');
$router->post('/api/messages/read', 'Api\\MessageController', 'markRead');
$router->post('/api/messages/border-level', 'Api\\MessageController', 'updateBorderLevel');
$router->post('/api/messages/summary', 'Api\\MessageController', 'updateSummary');
$router->post('/api/summarize', 'Api\\SummarizeController', 'generate');
$router->get('/api/search', 'Api\\MessageController', 'search');

// Webhook
$router->get('/api/webhook', 'Api\\WebhookController', 'handle');
$router->post('/api/webhook', 'Api\\WebhookController', 'handle');

// Settings API
$router->post('/api/settings/page-tokens', 'SettingController', 'getPageTokens');
$router->post('/api/settings/subscribe-page', 'SettingController', 'subscribePage');
$router->post('/api/settings/toggle-page', 'SettingController', 'togglePage');
$router->post('/api/settings/delete-page', 'SettingController', 'deletePage');
$router->post('/api/settings/save', 'SettingController', 'saveSettings');
$router->get('/api/settings/test-webhook', 'SettingController', 'testWebhook');

// Dispatch
$router->dispatch();
