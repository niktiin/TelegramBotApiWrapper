<?php
class Request {
    private $options;
    private $url;
    private $method;
    private $botApiKey;
    public function __construct(String $method, Array $options) {
        $this->method = $method;
        $this->options = $options;
        $this->botApiKey = json_decode(file_get_contents($GLOBALS['pathToProtectionData']), true)['botApiKey'];
        return $this->curl();
    }
    private function curl() {
        $this->url = 'https://api.telegram.org/bot' . $this->botApiKey . '/' . $this->method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->options);
        $responce = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != '200') {
            throw new Exception('{"text": "Telegram Bot API return server error!", "httpcode": ' . $httpcode . ', "responce": "' . $responce . '"}');
        }
        curl_close($ch);
        return $responce;
    }
}
?>