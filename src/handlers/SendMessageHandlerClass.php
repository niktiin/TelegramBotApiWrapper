<?php
  class SendMessageHandlerClass {
    public function __construct($chatId, $responce) {
      $options = [
        'chat_id' => $chatId,
        'text' => $responce['text'],
      ];
      isset($responce['keyboard']) && $options['reply_markup'] = json_encode([
        'keyboard' => $responce['keyboard']
      ]);
      new Request('sendMessage', $options);
    }
  }
?>