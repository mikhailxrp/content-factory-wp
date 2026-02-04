# Content Factory UI

WordPress плагин для управления контент-фабрикой через n8n.

## Возможности

- Управление нишей и целевой аудиторией
- Генерация смыслов
- Создание тем и редактирование структуры
- Генерация статей и создание черновиков в WordPress
- Генерация и публикация постов в Telegram
- Логирование всех операций

## Установка

1. Скопировать папку `content-factory-ui` в `wp-content/plugins/`
2. Активировать плагин в админке WordPress
3. Настроить подключение к n8n в разделе "Content Factory → Настройки"

## Настройка endpoints в n8n

Плагин ожидает, что в n8n настроены webhook endpoints:

### Служебные

- `/webhook/test` — тест соединения

### Смыслы

- `POST /webhook-test/generate-senses` — генерация смыслов из контекста
- `GET /webhook/senses/run-ids` — получить список всех run_id запусков генерации
- `GET /webhook/senses/list?run_id={run_id}` — получить список смыслов по конкретному run_id
- `GET /webhook/senses/{id}` — получить один смысл

### Темы

- `GET /webhook-test/topics/list?run_id={run_id}` — получить список тем по run_id
- `POST /webhook-test/generate-topics?run_id={run_id}` — генерация новых тем по run_id
- `POST /webhook-test/update-topics?run_id={run_id}` — обновление существующих тем по run_id
- `GET /webhook-test/topics/get?id={id}` — получить одну тему по ID
- `PUT /webhook/topics/{id}/outline` — обновить структуру темы

### Статьи

- `POST /webhook-test/generate-article?id={topic_id}` — генерация статьи из темы
- `GET /webhook/articles` — получить список статей
- `GET /webhook/articles/{id}` — получить одну статью
- `POST /webhook/articles/{id}/link` — связать статью с WP постом

### Telegram

- `POST /webhook-test/generate-telegram` — генерация TG поста
- `POST /webhook-test/publish-telegram` — публикация в TG

### Формат данных для генерации смыслов

При клике на кнопку "Сгенерировать смыслы" в n8n отправляется:

```json
{
  "context": {
    "service_name": "Название продукта/услуги",
    "service_description": "Описание ниши",
    "target_audience": "Описание целевой аудитории",
    "keywords": ["ключ1", "ключ2"],
    "updated_at": "2024-01-30 12:00:00"
  }
}
```

### Генерация статьи из темы

При клике на кнопку "Сгенерировать статью" в карточке темы отправляется POST-запрос:

**Endpoint:** `POST /webhook-test/generate-article?id={topic_id}`

**Query-параметры:**

- `id` — ID темы (topic_candidate_id)

**Body:** Пустой (все данные передаются через query-параметры)

**Пример запроса:** `POST /webhook-test/generate-article?id=12345`

## Требования

- WordPress 5.8+
- PHP 7.4+
- n8n с настроенными webhook endpoints

## Структура

```
content-factory-ui/
├── src/              # PHP классы (автолоад PSR-4)
├── assets/admin/     # CSS/JS для админки
└── languages/        # Переводы
```

## Разработка

Плагин следует WordPress Coding Standards и использует:

- Hooks API (actions/filters)
- Settings API
- REST API
- Transients API для кэширования
