<p align="center">
  <img src="./content/assets/logo.webp" />
</p>

<h1 align="center">Обертка для Telegram Bot API</h1>
<p align="center"><code>Упрощает и ускоряет запуск телеграм бота на PHP</code></p>

<hr style="height: 1px;">

<div style="margin-top: 4em;">
  <h2>Описание</h2>
Приложение позволяет создавать сценарии и цепочки событий не используя <code>callback_data</code>.
Все промежуточные данные сохраняются в базу данных и по необходимости используются.
В основе <code>php + mysql</code>.
Базовые настройки задаются по средством <code>JSON</code> файла.
</div>

<div style="margin-top: 4em;">
  <h2>Содержание:</h2>
  <ol>
    <li><a href="#">Основной класс <code>HandlerClass</code></a></li>
    <li><a href="#">Классы помощники :<code>RequestClass, MessageClass</code></a></li>
    <li><a href="#">Основные обработчики <code>SendMessageHandlerClass, SendContentHandlerClass, ...</code></a></li>
    <li><a href="#">Файл <code>pass.json</code></a></li>
    <li><a href="#">Файл <code>configs.json</code></a></li>
  </ol>
</div>

<div style="margin-top: 4em;">
  <h3>1. Основной класс <code>HandlerClass</code></h3>

```php
  class HandlerClass {...}
```

Основные задачи:

  <ol>
  <li> Получить данные о сообщении с сервера Telegram</li>
  <li> Проверить доступность и подключится к базе данных</li>
  <li> Создать таблицу в базе данных если она не существует</li>
  <li> Запросить следующее вхождение из базы данных и выполнить соответствующие действия</li>
  <li> Если данные о следующем вхождении в базе отсутсвуют, обработать сообщение и выполнить соответсвующие действия</li>
  <li> Если обработчик для сообщения не указан, выполнить исключение</li>
  </ol>
</div>

<div style="margin-top: 4em;">
  <h3>2. Классы помошники: </h3>
  <h4><code>RequestClass</code></h4>

<div style="margin-bottom: 4em">

```php
  class RequestClass {...}
```

Основные задачи:

<ol>
<li> Отправка запроса на сервер Telegram по средством <code>Curl</code></li>
</ol>

</div>

<div style="margin-bottom: 4em">
  <h4><code>MessageClass</code></h4>

```php
  class MessageClass {...}
```

Основные задачи:

<ol>
<li> Преобраховать полученные даннные в свойства обьекта, для последующего использования</li>
</ol>
</div>

<div style="margin-top: 4em;">
  <h3>3. Основные обработчики: </h3>
  <h4>Принцип работы:</h4>
  <p>Каждое отправленное сообщение боту в конечном счете обрабатвается обработчиками. Задается метод Telegram bot API, и параметры подробнее — <a href="https://core.telegram.org/bots/api#available-methods">[available-methods]</a>. Чтобы создать обработчик достаточно поместить его в папку <code>handlers</code>, а на входе в метод <code>__construct</code> указать параметры: <code>$chatId;
  $responce;
  $message;</code>

  </p>

```php
  class ExampleHandlerClass {
    /**
     * @param Array $responce Установленные данные для ответа
     */
    public function __construct($chatId, $responce, $message) {
    $method = 'sendMessage';
    $options = [
      'chat_id' => $chatId,
      'text' => $responce['text'],
    ];

    // Дополнительые вычесления при необходимости

    new Request($method, $options);
  }
```

<h4>Доступные обработчики из коробки:</h4>
<ol>
<li>
<code>SendMessageHandlerClass</code>
— Подходит для отправки сообщений без файлов и дополнительного контента. Доступные настройки:

```json
  "/tap" : { // Текст на который срабатывает данный маршрут
      "responce": {
        "text": "You tap one times...", // Сообщение ответа
        "keyboard": [[{"text": "tap"}]] // Клавиатура, опционально
      },
      "nextEntryName": "twoTapEntry", // Следущее вхождение, опционально
      "forEval": "new SendMessageHandlerClass($chatId, $responce);" // Вызов обработчика
    },
```

</li>
<li>
<code>SendContentHandlerClass</code>
— Подходит для отправки сообщений без файлов и дополнительного контента. Доступные настройки:

```json
  "/tap" : { // Текст на который срабатывает данный маршрут
      "responce": {
        "text": "You tap one times...", // Сообщение ответа
        "keyboard": [[{"text": "tap"}]] // Клавиатура, опционально
      },
      "content": {
          "type": "photo", // Тип контента (Photo, Audio, Video)
          "url": "https://copyfx4.com/wp-content/uploads/2022/07/file-127.jpg" // Ссылка на контент
        }
      "nextEntryName": "twoTapEntry", // Следущее вхождение, опционально
      "forEval": "new SendMessageHandlerClass($chatId, $responce);" // Вызов обработчика
    },
```

</li>
</ol>
</div>

<div style="margin-top: 4em;">
  <h3>Файл <code>pass.json</code></h3>

```json
{
  "botApiKey": "", // Токен бота
  "database": {
    "host": "", // Адрес сервера для работы базы данных
    "username": "", // Логин от базы данных
    "password": "" // Пароль от базы данных
  }
}
```

</div>

<div style="margin-top: 4em;">
  <h3>Файл <code>configs.json</code></h3>

```json
{
  "globalRoutes": {
    ...
  },
  "nextEntries": {
    ...
  },
  "database": {
    "name": "", // Название базы данных
    "tableNameForNextEntry": "" // Таблица для работы вхождений, если отсутствует, буден создана автоматически.
  }
}
```

</div>
