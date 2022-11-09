<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

# REQUIRE FILE
require_once('src/RequestClass.php');
require_once('src/HandlerClass.php');

# SET CONFIG PATH
$GLOBALS['pathToProtectionData'] = 'content/pass.json';
$GLOBALS['pathToConfigsFileName'] = 'content/configs.json';

try {
    // App life cycle
    $handler = new Handler;
    $handler->catchTextMessage();
} catch (Exception $e) {
    # HANDLE EXCEPTION, THIS EXAMPLE PRINTED EXCEPTION TO CHAT
    $text = $e->getMessage();
    new Request('sendMessage', [
        'chat_id' => '1050337296',
        'text' => '<code>' . $text . '</code>',
        'parse_mode' => 'HTML'
    ]);
    die();
} catch (Error $e) {
    # HANDLE ERROR, THIS EXAMPLE PRINTED ERROR TO CHAT
    $text = json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    new Request('sendMessage', [
        'chat_id' => $handler->chatId,
        'text' => '<code>' . $text . '</code>',
        'parse_mode' => 'HTML'
    ]);
    die();
}
