<?php

  $files = glob('src/handlers/*.php');
  foreach ($files as $file) {
      require_once($file);
  }

  class Handler {
    private $configs, $pass, $databaseConection, $tableNameForNextEntry;
    public $message;

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
      $contents = json_decode(
        file_get_contents("php://input"),
        true
      );
      $this->message = new MessageClass($contents);
    }

    /**
     * Обработать исключение, если
     * обработчик не нашёл подходящий маршрут
     */
    private function handleExceptionTextMessage() {
      // выпонить заданный код
      $responce = $this->configs['exceptionTextMessage']['responce'];
      $evalString = $this->configs['exceptionTextMessage']['forEval'];
      $this->evalHandler($evalString, $responce);
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
      $this->databaseConection->select_db($databaseName);
      // Создать таблицу для вхождений
      $this->tableNameForNextEntry = $this->configs['database']['tableNameForNextEntry'];
      $this->createTableForIfNotExists($this->tableNameForNextEntry);
    }

    /**
     * Создать таблицу для вхождений если она не существует
     */
    private function createTableForIfNotExists($tableName) {
      $query = "CREATE TABLE IF NOT EXISTS `$tableName` ( `index` INT NOT NULL AUTO_INCREMENT , `chatId` VARCHAR(16) NOT NULL , `nextEntryData` JSON NOT NULL , PRIMARY KEY (`index`), UNIQUE `UNIQUE` (`chatId`)) ENGINE = InnoDB";
      $this->databaseConection->query($query);
    }

    /**
     * Добавить вхождение в базу данных
     * Перезаписать если повторяется
     */
    private function pushNextEntryToDatabase($nextEntryName) {
      $tableNameForNextEntry = $this->tableNameForNextEntry;
      $chatId = $this->message->chatId;
      $nextEntry = json_encode($this->configs['nextEntries'][$nextEntryName]);
      $query = "INSERT INTO `$tableNameForNextEntry`(`chatId`, `nextEntryData`) VALUES ('$chatId', '$nextEntry') ON DUPLICATE KEY UPDATE `nextEntryData` = '$nextEntry'";
      $queryResult = $this->databaseConection->query($query);
    }

    /**
     * Выполнить код
     */
    private function evalHandler($evalString, $responce) {
      $message = $this->message;
      $chatId = $this->message->chatId;
      eval($evalString);
    }
    /**
     * Основной блок приложения
     * Найти обьект маршрута
     * Выполнить код
     */
    public function catchTextMessage() {
      // Получить ключи валидных маршрутов
      $availableRoute = [];
      foreach ($this->configs['globalRoutes'] as $key => $value) {
        array_push($availableRoute, $key);
      }
      $text = $this->message->text;
      $routeIndex = array_search($text, $availableRoute);
      $routeIndex === false && $this->handleExceptionTextMessage();
      // Найти обьект маршрута
      $routeKey = array_keys($this->configs['globalRoutes'])[array_search($text, $availableRoute)];
      $nextEntryName = $this->configs['globalRoutes'][$routeKey]['nextEntryName'];
      isset($nextEntryName) && $this->pushNextEntryToDatabase($nextEntryName);
      // выпонить заданный код
      $responce = $this->configs['globalRoutes'][$routeKey]['responce'];
      $evalString = $this->configs['globalRoutes'][$routeKey]['forEval'];
      $this->evalHandler($evalString, $responce, $this->message);
    }
    /**
     * Найти вхождение
     * Выполнить код вхождения
     * Удалить вхождение
     */
    public function checkNextEntry() {
      $tableNameForNextEntry = $this->tableNameForNextEntry;
      $chatId = $this->message->chatId;
      $query = "SELECT * FROM `$tableNameForNextEntry` WHERE `chatId` = '$chatId'";
      $queryResult = $this->databaseConection->query($query);
      if ($queryResult->num_rows < 1) return false;
      $nextEntry = json_decode($queryResult->fetch_assoc()['nextEntryData'], true);
      // Выполнить код вхождения
      $responce = $nextEntry['responce'];
      $evalString = $nextEntry['forEval'];
      $this->evalHandler($evalString, $responce, $this->message);
      // Удалить вхождение из базы данных
      $query = "DELETE FROM `tablefornextentry` WHERE `chatId` = '$chatId'";
      $this->databaseConection->query($query);
      return true;
    }
  }
?>