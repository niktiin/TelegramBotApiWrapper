<?php
  class SendMessageHandlerClass {
    public function __construct($chatId, $responce) {
      $options = [
        'chat_id' => $chatId,
        'text' => $responce['text'],
      ];
      new Request('sendMessage', $options);
    }
  }
?>