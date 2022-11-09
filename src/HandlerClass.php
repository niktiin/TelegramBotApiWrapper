<?php
  require_once('handlers/SendMessageHandlerClass.php');
  require_once('handlers/DebuggingHandlerClass.php');
  class Handler {
    private $configs, $pass, $contents, $message, $text, $databaseConection;
    public $chatId;
    public function __construct() {
      $this->configs = json_decode(file_get_contents($GLOBALS['pathToConfigsFileName']), true);
      $this->pass = json_decode(file_get_contents($GLOBALS['pathToProtectionData']), true);
      $this->getContents();
      $this->connectDatabase();
    }
    /**
     * Заполнить свойства обьекта данными
     * из ответа telegram bot api
     */
    private function getContents() {
      $this->contents = json_decode(
        file_get_contents("php://input"),
        true
      );
      $this->message = $this->contents["message"];
      $this->chatId = $this->message["chat"]["id"];
      $this->text = $this->message["text"];
    }
    /**
     * Обработать исключение, если
     * обработчик не нашёл подходящий маршрут
     */
    private function handleExceptionTextMessage() {
      $options = [
        'chat_id' => $this->chatId,
        'text' => $this->configs['globalExceptionRoute']['responce']['text'],
      ];
      new Request('sendMessage', $options);
      die();
    }
    /**
     * Подключится к серверу
     * Проверить доступность базы данных
     * Проверить существует ли таблица, если нет создать её
     */
    private function connectDatabase() {
      // Подключиться к серверу
      ['host' => $host, 'username' => $username, 'password' => $password] = $this->pass['database'];
      $databaseName = $this->configs['database']['name'];
      $this->databaseConection = new mysqli($host, $username, $password);
      if ($this->databaseConection->connect_error) {
        throw new Exception("Ошибка подключения: данные для авторизации не валидны");
      }
      // Проверить существует ли база данных, если нет вызвать исключение
      $checkDatabaseIsExistsResult = $this->databaseConection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName'");
      if ($checkDatabaseIsExistsResult->num_rows < 1) {
        throw new Exception("Ошибка подключения: База данных не установлена!");
      }
      // Создать таблицу для вхождений
      $tableNameForNextEntry = $this->configs['database']['tableNameForNextEntry'];
      $this->createTableForIfNotExists($tableNameForNextEntry);
    }
    /**
     * Создать таблицу для вхождений если она не существует
     */
    private function createTableForIfNotExists($tableName) {
      $databaseName = $this->configs['database']['name'];
      $query = "CREATE TABLE IF NOT EXISTS `$databaseName`.`$tableName` ( `index` INT NOT NULL AUTO_INCREMENT , `chatId` VARCHAR(16) NOT NULL , `nextEntryData` JSON NOT NULL , PRIMARY KEY (`index`)) ENGINE = InnoDB";
      $this->databaseConection->query($query);
    }
    public function catchTextMessage() {
      // Получить ключи валидных маршрутов
      $availableRoute = [];
      foreach ($this->configs['globalRoutes'] as $key => $value) {
        array_push($availableRoute, $key);
      }
      $routeIndex = array_search($this->text, $availableRoute);
      $routeIndex === false && $this->handleExceptionTextMessage();
      // Найти обьект маршрута и выпонить заданный код
      $routeKey = array_keys($this->configs['globalRoutes'])[array_search($this->text, $availableRoute)];
      $chatId = $this->chatId;
      $responce = $this->configs['globalRoutes'][$routeKey]['responce'];
      $handler = $this;
      eval($this->configs['globalRoutes'][$routeKey]['forEval']);
    }
  }
?>