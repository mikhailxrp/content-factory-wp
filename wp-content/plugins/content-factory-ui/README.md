# Content Factory UI

WordPress плагин для управления контент-фабрикой через n8n.

## Возможности

- Управление нишей и целевой аудиторией
- Генерация смыслов
- Создание тем и редактирование структуры
- Генерация статей и создание черновиков в WordPress
- **Генерация статей прямо из редактора WordPress** (новое!)
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

- `POST /webhook-test/generate-article?id={topic_id}` — генерация статьи из темы (возвращает сразу `{"status": "started"}`, генерация идет в фоне)
- `GET /webhook-test/check-article-status?id={topic_id}` — проверка статуса генерации статьи
- `POST /webhook-test/update-article-status` — обновление статуса статьи (вызывается автоматически при публикации в WP)
- `GET /webhook/articles/list?run_id={run_id}&status={status}` — получить список статей с фильтрами
- `GET /webhook/articles/{id}` — получить одну статью
- `POST /webhook/articles/{id}/link` — связать статью с WP постом

### Telegram

- `POST /webhook-test/generate-telegram` — генерация TG поста
- `POST /webhook-test/publish-telegram` — публикация в TG

### Промпты

- `GET /webhook/prompts/list` — получить список всех промптов
- `POST /webhook/prompts/create` — создать новый промпт
- `POST /webhook/prompts/update` — обновить промпт
- `POST /webhook/prompts/delete` — удалить промпт

### Генерация из редактора (новое!)

- `POST /webhook/generate-article-from-editor` — генерация статьи прямо из редактора WordPress
- `GET /webhook/check-editor-article-status?post_id={post_id}` — проверка статуса генерации

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

**Ответ от n8n (сразу после получения запроса):**

```json
{
  "status": "started",
  "topic_id": "12345"
}
```

После этого генерация продолжается в фоне. Плагин автоматически проверяет статус каждую минуту.

### Проверка статуса генерации статьи

**Endpoint:** `GET /webhook-test/check-article-status?id={topic_id}`

**Query-параметры:**

- `id` — ID темы (topic_candidate_id)

**Возможные ответы:**

```json
{
  "status": "start",
  "topic_candidate_id": "12345"
}
```

```json
{
  "status": "draft",
  "topic_candidate_id": "12345",
  "wordpress_post_id": "789",
  "wp_post_link": "https://site.com/wp-json/wp/v2/posts/789"
}
```

```json
{
  "status": "error",
  "topic_candidate_id": "12345"
}
```

**Поведение плагина:**

- `status: "start"` — продолжает проверку каждую минуту
- `status: "draft"` или `status: "success"` — активирует кнопку "Перейти к статье" со ссылкой из `wp_post_link`
- `status: "error"` — показывает кнопку "Сгенерировать еще раз"

### Синхронизация статуса статьи при публикации

Плагин автоматически отслеживает изменение статуса постов в WordPress и отправляет уведомления в n8n.

**Endpoint:** `POST /webhook-test/update-article-status`

**Когда вызывается:** Автоматически при публикации статьи в WordPress (нажатие кнопки "Опубликовать" в редакторе)

**Отправляемые данные при публикации:**

```json
{
  "wordpress_post_id": 789,
  "topic_candidate_id": "12345",
  "status": "published",
  "published_at": "2024-01-30 12:00:00",
  "post_url": "https://site.com/article-slug/"
}
```

**Отправляемые данные при снятии с публикации:**

```json
{
  "wordpress_post_id": 789,
  "topic_candidate_id": "12345",
  "status": "draft",
  "unpublished_at": "2024-01-30 12:00:00"
}
```

**Важно:** При создании черновика в WordPress через n8n необходимо сохранять `topic_candidate_id` в post meta:

```json
POST /wp-json/wp/v2/posts
{
  "status": "draft",
  "title": "...",
  "content": "...",
  "meta": {
    "topic_candidate_id": "12345"
  }
}
```

Это позволяет плагину связать WordPress пост с темой во внешней БД и отправлять обновления статуса.

### Получение списка статей

**Endpoint:** `GET /webhook/articles/list`

**Query-параметры:**

- `run_id` (опционально) — фильтр по run_id
- `status` (опционально) — фильтр по статусу (`draft`, `published`)

**Примеры запросов:**

- `GET /webhook/articles/list` — все статьи
- `GET /webhook/articles/list?status=published` — только опубликованные
- `GET /webhook/articles/list?run_id=abc123&status=draft` — черновики по конкретному run_id

**Ответ:** Массив статей

```json
[
  {
    "wordpress_post_id": 789,
    "topic_candidate_id": "12345",
    "topic_title": "Название темы",
    "status": "published",
    "wp_post_link": "https://site.com/wp-admin/post.php?post=789&action=edit",
    "post_url": "https://site.com/article-slug/",
    "published_at": "2024-01-30 12:00:00",
    "created_at": "2024-01-29 10:00:00"
  }
]
```

**Поля ответа:**

- `wordpress_post_id` — ID поста в WordPress
- `topic_candidate_id` — ID темы из внешней БД
- `topic_title` — Название темы
- `status` — Статус статьи (`draft` или `published`)
- `wp_post_link` — Ссылка на редактирование в WP админке
- `post_url` — Публичная ссылка на статью (только для `published`)
- `published_at` — Дата публикации (только для `published`)
- `created_at` — Дата создания черновика

### Получение списка промптов

**Endpoint:** `GET /webhook/prompts/list`

**Когда вызывается:** Автоматически при открытии вкладки "Промпты" в админке

**Query-параметры:** Нет (пустой GET-запрос)

**Ответ:** Массив промптов

```json
[
  {
    "id": 1,
    "angle": "инструкция",
    "template_name": "How-to Guide",
    "system_prompt": "Ты опытный контент-маркетолог...",
    "structure_rules": {
      "sections": [
        "Введение: зачем это нужно",
        "Что потребуется (инструменты/знания)",
        "Пошаговая инструкция"
      ],
      "min_steps": 5,
      "include_warnings": true
    },
    "tone": "professional",
    "min_words": 2000,
    "max_words": 2500,
    "is_active": 1,
    "created_at": "2026-02-02 01:03:05",
    "updated_at": "2026-02-04 17:26:47"
  }
]
```

**Поля ответа:**

- `id` — ID промпта
- `angle` — Угол раскрытия темы (инструкция, цена, кейс и т.д.)
- `template_name` — Название шаблона
- `system_prompt` — Системный промпт для AI
- `structure_rules` — Правила структуры статьи (JSON-объект)
  - `sections` — Массив секций статьи
  - Дополнительные параметры: `min_steps`, `include_warnings`, `include_table`, `include_cta` и т.д.
- `tone` — Тон статьи (professional, expert и т.д.)
- `min_words` — Минимальное количество слов
- `max_words` — Максимальное количество слов
- `is_active` — Активен ли промпт (1 или 0)
- `created_at` — Дата создания
- `updated_at` — Дата обновления

**Отображение в UI:**

Промпты отображаются в виде карточек. При клике на карточку открывается детальный вид с:

- Полным системным промптом
- Списком секций статьи
- Всеми правилами структуры
- Кнопкой "Редактировать" для перехода в режим редактирования

### Создание нового промпта

**Endpoint:** `POST /webhook/prompts/create`

**Когда вызывается:** При нажатии кнопки "Создать" в форме создания нового промпта

**Отправляемые данные:**

```json
{
  "angle": "инструкция",
  "template_name": "How-to Guide",
  "system_prompt": "Ты опытный контент-маркетолог...",
  "structure_rules": {
    "sections": [
      "Введение: зачем это нужно",
      "Что потребуется (инструменты/знания)"
    ],
    "min_steps": 5,
    "include_warnings": true
  },
  "tone": "professional",
  "min_words": 2000,
  "max_words": 2500,
  "is_active": 1
}
```

**Поля запроса:**

- `angle` — Угол раскрытия темы (обязательно)
- `template_name` — Название шаблона (обязательно)
- `system_prompt` — Системный промпт для AI (обязательно)
- `structure_rules` — Правила структуры статьи (JSON-объект, опционально)
- `tone` — Тон статьи (по умолчанию: professional)
- `min_words` — Минимальное количество слов (по умолчанию: 2000)
- `max_words` — Максимальное количество слов (по умолчанию: 2500)
- `is_active` — Активен ли промпт (по умолчанию: 1)

**Примечание:** Поля `id`, `created_at` и `updated_at` не отправляются, они создаются автоматически на стороне n8n

**Ответ от n8n:**

```json
{
  "success": true,
  "message": "Промпт успешно создан",
  "data": {
    "id": 10,
    "angle": "инструкция",
    "template_name": "How-to Guide",
    "created_at": "2026-02-07 15:45:00",
    "updated_at": "2026-02-07 15:45:00"
  }
}
```

**Форма создания в UI:**

- Открывается при нажатии кнопки "Добавить промпт"
- Все поля пустые с дефолтными значениями
- Валидация обязательных полей
- Кнопки "Отмена" (закрыть форму) и "Создать"
- После успешного создания форма закрывается и список обновляется

### Обновление промпта

**Endpoint:** `POST /webhook/prompts/update`

**Когда вызывается:** При нажатии кнопки "Сохранить" в режиме редактирования промпта

**Отправляемые данные:**

```json
{
  "id": 1,
  "angle": "инструкция",
  "template_name": "How-to Guide",
  "system_prompt": "Ты опытный контент-маркетолог...",
  "structure_rules": {
    "sections": [
      "Введение: зачем это нужно",
      "Что потребуется (инструменты/знания)"
    ],
    "min_steps": 5,
    "include_warnings": true
  },
  "tone": "professional",
  "min_words": 2000,
  "max_words": 2500,
  "is_active": 1
}
```

**Поля запроса:**

- `id` — ID промпта (обязательно, не редактируется)
- `angle` — Угол раскрытия темы (обязательно)
- `template_name` — Название шаблона (обязательно)
- `system_prompt` — Системный промпт для AI (обязательно)
- `structure_rules` — Правила структуры статьи (JSON-объект)
- `tone` — Тон статьи (professional, expert, casual, friendly)
- `min_words` — Минимальное количество слов
- `max_words` — Максимальное количество слов
- `is_active` — Активен ли промпт (1 или 0)

**Примечание:** Поля `created_at` и `updated_at` не редактируются и не отправляются в запросе

**Ответ от n8n:**

```json
{
  "success": true,
  "message": "Промпт успешно обновлён",
  "data": {
    "id": 1,
    "angle": "инструкция",
    "template_name": "How-to Guide",
    "updated_at": "2026-02-07 15:30:00"
  }
}
```

**Режим редактирования в UI:**

- Все поля кроме ID и дат доступны для редактирования
- `structure_rules` редактируется как JSON в textarea
- Валидация JSON перед отправкой
- Кнопки "Отмена" (возврат к просмотру) и "Сохранить"
- После успешного сохранения автоматический возврат в режим просмотра

### Удаление промпта

**Endpoint:** `POST /webhook/prompts/delete`

**Когда вызывается:** При нажатии кнопки "Удалить" на карточке промпта

**Отправляемые данные:**

```json
{
  "id": 10
}
```

**Поля запроса:**

- `id` — ID промпта для удаления (обязательно)

**Ответ от n8n:**

```json
{
  "success": true,
  "message": "Промпт успешно удалён"
}
```

**Ограничения:**

- Кнопка "Удалить" отображается только для пользовательских промптов (ID > 9)
- Дефолтные промпты (ID 1-9) нельзя удалить
- Перед удалением показывается подтверждение
- После успешного удаления список промптов обновляется

**Отображение в UI:**

- Кнопка удаления (иконка корзины) в правом верхнем углу карточки
- Красная иконка с hover-эффектом
- Подтверждающий диалог перед удалением
- Если открыт детальный вид удалённого промпта, он автоматически закрывается

---

## Генерация статьи из редактора WordPress

### Описание функционала

Плагин добавляет в редактор WordPress (Gutenberg) панель **"Content Factory"** в правом сайдбаре с кнопкой **"Сгенерировать статью"**.

При нажатии на кнопку открывается модальное окно с полями:

1. **Роль** — от лица кого пишется статья (например: "SEO эксперт по недвижимости")
2. **Промпт** — описание того, что должно быть в статье
3. **Секции статьи** — структура статьи (каждая секция с новой строки)

После заполнения и отправки формы:

- Данные отправляются в N8N для генерации
- Запускается автоматическая проверка статуса каждые 10 секунд
- Когда статья готова, контент автоматически обновляется в редакторе
- Пользователь видит уведомление об успешной генерации

### Endpoint для генерации

**POST** `/webhook/generate-article-from-editor`

**Отправляемые данные:**

```json
{
  "post_id": 123,
  "role": "SEO эксперт по недвижимости",
  "prompt": "Напиши подробную статью про ипотеку для молодых семей с примерами и расчетами",
  "sections": "Введение\nУсловия ипотеки\nНеобходимые документы\nПреимущества программы\nЗаключение"
}
```

**Поля запроса:**

- `post_id` — ID поста в WordPress (обязательно)
- `role` — Роль автора статьи (обязательно)
- `prompt` — Описание задачи для генерации (обязательно)
- `sections` — Структура статьи, секции разделены переносом строки (обязательно)

**Ответ от N8N (сразу после получения запроса):**

```json
{
  "status": "started",
  "job_id": "uuid-xxx-xxx-xxx",
  "post_id": 123
}
```

**Поля ответа:**

- `status` — Статус генерации (всегда "started" при первом ответе)
- `job_id` — Уникальный ID задачи генерации (опционально, для отслеживания)
- `post_id` — ID поста WordPress

### Endpoint для проверки статуса

**GET** `/webhook/check-editor-article-status?post_id={post_id}&job_id={job_id}`

**Query-параметры:**

- `post_id` — ID поста в WordPress (обязательно)
- `job_id` — ID задачи генерации (опционально, если был возвращен при запуске)

**Возможные ответы:**

**1. Генерация в процессе:**

```json
{
  "status": "processing",
  "post_id": 123
}
```

или

```json
{
  "status": "started",
  "post_id": 123
}
```

**2. Генерация завершена успешно:**

```json
{
  "status": "completed",
  "post_id": 123,
  "content": "<h2>Введение</h2><p>Ипотека для молодых семей...</p><h2>Условия ипотеки</h2><p>...</p>",
  "title": "Ипотека для молодых семей: полное руководство 2026"
}
```

**Поля ответа:**

- `status` — "completed"
- `post_id` — ID поста WordPress
- `content` — HTML-контент статьи (обязательно)
- `title` — Новый заголовок статьи (опционально, если пусто — заголовок не меняется)

**3. Ошибка генерации:**

```json
{
  "status": "error",
  "post_id": 123,
  "error_message": "Превышен лимит токенов API"
}
```

**Поля ответа:**

- `status` — "error"
- `post_id` — ID поста WordPress
- `error_message` — Описание ошибки (опционально)

### Поведение плагина

1. **При запуске генерации:**
   - Отправляет POST-запрос с данными формы
   - Сохраняет в post meta: `_cf_generation_status = 'started'`, `_cf_generation_job_id`, `_cf_generation_started_at`
   - Показывает уведомление "Генерация статьи запущена"
   - Запускает polling (первая проверка через 5 секунд, затем каждые 10 секунд)

2. **При проверке статуса:**
   - Если `status === 'processing'` или `'started'` — продолжает polling
   - Если `status === 'completed'`:
     - Обновляет контент поста через `wp_update_post()`
     - Опционально обновляет заголовок (если передан в ответе)
     - Обновляет контент в редакторе без перезагрузки страницы
     - Показывает уведомление "Статья успешно сгенерирована!"
     - Останавливает polling
     - Закрывает модальное окно
     - Обновляет post meta: `_cf_generation_status = 'completed'`, `_cf_generation_completed_at`
   - Если `status === 'error'`:
     - Показывает уведомление с ошибкой
     - Останавливает polling
     - Обновляет post meta: `_cf_generation_status = 'error'`, `_cf_generation_error`

3. **Сохраняемые post meta:**
   - `_cf_generation_status` — текущий статус генерации (started, completed, error)
   - `_cf_generation_job_id` — ID задачи в N8N (если был возвращен)
   - `_cf_generation_started_at` — дата/время запуска генерации
   - `_cf_generation_completed_at` — дата/время завершения генерации
   - `_cf_generation_error` — текст ошибки (если была ошибка)

### Пример использования

1. Пользователь создает новый пост в WordPress
2. В правом сайдбаре видит панель "Content Factory"
3. Нажимает кнопку "Сгенерировать статью"
4. Заполняет форму:
   - Роль: "SEO эксперт по недвижимости"
   - Промпт: "Напиши статью про ипотеку для молодых семей"
   - Секции: "Введение\nУсловия\nДокументы\nПреимущества\nЗаключение"
5. Нажимает "Сгенерировать"
6. Видит уведомление "Генерация запущена"
7. Через 2-3 минуты контент автоматически появляется в редакторе
8. Может сразу редактировать и публиковать статью

### REST API маршруты (WordPress)

Плагин регистрирует следующие маршруты:

**POST** `/wp-json/content-factory/v1/posts/{id}/generate-article`

- Запускает генерацию статьи для поста с ID = {id}
- Требует права `edit_post` для этого поста

**GET** `/wp-json/content-factory/v1/posts/{id}/check-article-status`

- Проверяет статус генерации для поста с ID = {id}
- Требует права `edit_post` для этого поста

### Технические детали

**Frontend:**

- Использует WordPress Gutenberg API (`@wordpress/plugins`, `@wordpress/components`)
- Написано на чистом JavaScript (без сборки)
- Компоненты: `PluginDocumentSettingPanel`, `Modal`, `TextControl`, `TextareaControl`
- Polling реализован через `setInterval` с автоматической очисткой

**Backend:**

- Контроллер: `PostEditorController.php`
- Методы: `generate_article()`, `check_status()`, `check_permission()`
- Использует `N8n\Client` для запросов к N8N
- Использует `PostPublisher::update_post_content()` для обновления контента

**Безопасность:**

- Все запросы защищены WordPress nonce
- Проверка прав доступа `current_user_can('edit_post', $post_id)`
- Санитизация входных данных через `sanitize_text_field()` и `sanitize_textarea_field()`
- Фильтрация HTML контента через `wp_kses_post()`

---

## Требования

- WordPress 5.8+
- PHP 7.4+
- n8n с настроенными webhook endpoints

## Структура

```
content-factory-ui/
├── src/                    # PHP классы (автолоад PSR-4)
│   ├── Admin/              # Админ-панель
│   ├── Rest/Controllers/   # REST API контроллеры
│   ├── N8n/                # Интеграция с N8N
│   ├── WP/                 # WordPress интеграции
│   └── Support/            # Вспомогательные классы
├── assets/
│   ├── admin/              # CSS/JS для админки
│   └── editor/             # CSS/JS для редактора Gutenberg
└── languages/              # Переводы
```

## Разработка

Плагин следует WordPress Coding Standards и использует:

- Hooks API (actions/filters)
- Settings API
- REST API
- Transients API для кэширования
