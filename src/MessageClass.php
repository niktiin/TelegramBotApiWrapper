<?php
  class MessageClass {
    private $contents;
    public $message, $chatId, $text;
    public function __construct($contents) {
      $this->message = $contents["message"];
      $this->chatId = $this->message["chat"]["id"];
      $this->text = $this->message["text"];
    }
  }
?>