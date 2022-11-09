<?php
  class SendMessageHandlerClass {
    public function __construct($chatId, $responce) {
      $method = 'sendMessage';
      $options = [
        'chat_id' => $chatId,
        'text' => $responce['text'],
      ];
      // Установить клавиатуру если она указана
      isset($responce['keyboard']) && $options['reply_markup'] = json_encode([
        'keyboard' => $responce['keyboard']
      ]);
      //
      if (isset($responce['content']) === true) {
        unset($options['text']);
        $options['caption'] = $responce['text'];

        $type = $responce['content']['type'];
        $method = 'send'.mb_strtoupper($type);
        $options[$type] = $responce['content']['url'];
      }
      new Request($method, $options);
    }
  }
?>