<?php
class DebuggingHandlerClass {
  public function __construct($chatId, $databaseConection) {
    $options = [
      'chat_id' => $chatId,
      'text' => $responceText,
    ];
    new Request('sendMessage', $options);
  }
}

?>