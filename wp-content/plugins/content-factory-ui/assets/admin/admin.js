/**
 * Content Factory UI - Admin JavaScript
 */

(function ($) {
  "use strict";

  const cfUI = {
    init() {
      // Проверка наличия глобальных данных
      if (!window.cfUIData || !window.cfUIData.restUrl) {
        console.error("cfUIData global object not found");
        return;
      }

      this.setupApp();
      this.bindEvents();
      this.loadPageData();
    },

    setupApp() {
      const $app = $("#cf-ui-app");
      this.currentPage = $app.data("page");
    },

    bindEvents() {
      // Settings
      $("#cf-settings-form").on("submit", this.saveSettings.bind(this));
      $("#cf-test-connection").on("click", this.testConnection.bind(this));

      // Context
      $("#cf-context-form").on("submit", this.saveContext.bind(this));
      $("#cf-generate-senses").on("click", this.generateSenses.bind(this));

      // Senses
      $("#cf-load-senses").on("click", this.loadSensesByRunId.bind(this));
      $("#cf-refresh-run-ids").on("click", () => this.refreshRunIds());

      // Topics
      $("#cf-refresh-run-ids-topics").on("click", () =>
        this.loadRunIdsForTopics(),
      );
      $("#cf-list-topics").on("click", this.listTopics.bind(this));
      $("#cf-generate-topics").on("click", this.generateTopics.bind(this));
      $("#cf-update-topics").on("click", this.updateTopics.bind(this));

      // Articles
      $("#cf-load-articles").on("click", this.loadArticles.bind(this));
      $("#cf-refresh-articles").on("click", this.loadArticles.bind(this));

      // Logs
      $("#cf-logs-filter").on("change", this.filterLogs.bind(this));
      $("#cf-refresh-logs").on("click", () => this.loadList("logs"));
      $("#cf-clear-logs").on("click", this.clearLogs.bind(this));

      // Telegram
      $("#cf-tg-generate-form").on("submit", this.generateTelegram.bind(this));
      $("#cf-tg-publish").on("click", this.publishTelegram.bind(this));
    },

    loadPageData() {
      setTimeout(() => {
        $(".cf-ui-loading").hide();
        $(".cf-ui-content").show();

        switch (this.currentPage) {
          case "settings":
            this.loadSettings();
            break;
          case "context":
            this.loadContext();
            break;
          case "senses":
            this.loadRunIds();
            break;
          case "topics":
            this.loadRunIdsForTopics();
            break;
          case "articles":
            this.loadRunIdsForArticles();
            this.loadArticles();
            break;
          case "telegram":
            this.loadArticlesForSelect();
            break;
          case "logs":
            this.loadList("logs");
            break;
        }
      }, 300);
    },

    // API requests
    apiRequest(endpoint, method = "GET", data = null) {
      const url = `${window.cfUIData.restUrl}/${endpoint}`;
      console.log(`[API] ${method} ${url}`, data ? data : "");

      return $.ajax({
        url: url,
        method: method,
        data: data ? JSON.stringify(data) : null,
        contentType: "application/json",
        beforeSend: (xhr) => {
          xhr.setRequestHeader("X-WP-Nonce", window.cfUIData.nonce);
        },
      })
        .done((response) => {
          console.log(`[API] ${method} ${url} - Success:`, response);
          return response;
        })
        .fail((xhr, status, error) => {
          console.error(
            `[API] ${method} ${url} - Error:`,
            status,
            error,
            xhr.responseJSON,
          );
          throw xhr;
        });
    },

    // Settings
    loadSettings() {
      this.apiRequest("settings").done((response) => {
        if (response.success && response.data) {
          $("#n8n_url").val(response.data.n8n_url || "");
        }
      });
    },

    saveSettings(e) {
      e.preventDefault();

      const data = {
        n8n_url: $("#n8n_url").val(),
      };

      this.apiRequest("settings", "POST", data)
        .done((response) => {
          this.showNotice(
            response.message,
            response.success ? "success" : "error",
          );
        })
        .fail(() => {
          this.showNotice(window.cfUIData.i18n.error, "error");
        });
    },

    testConnection() {
      const $btn = $("#cf-test-connection");
      $btn.prop("disabled", true).text(window.cfUIData.i18n.loading);

      this.apiRequest("settings/test", "POST")
        .done((response) => {
          $("#cf-test-result")
            .removeClass("success error")
            .addClass(response.success ? "success" : "error")
            .text(response.message)
            .show();
        })
        .always(() => {
          $btn.prop("disabled", false).text("Проверить подключение");
        });
    },

    // Context
    loadContext() {
      this.apiRequest("context").done((response) => {
        if (response.success && response.data) {
          $("#service_name").val(response.data.service_name || "");
          $("#service_description").val(
            response.data.service_description || "",
          );
          $("#target_audience").val(response.data.target_audience || "");
          $("#keywords").val((response.data.keywords || []).join(", "));
        }
      });
    },

    saveContext(e) {
      e.preventDefault();

      const keywords = $("#keywords")
        .val()
        .split(",")
        .map((k) => k.trim())
        .filter((k) => k);

      const data = {
        service_name: $("#service_name").val(),
        service_description: $("#service_description").val(),
        target_audience: $("#target_audience").val(),
        keywords: keywords,
      };

      console.log("Sending context data:", data);

      this.apiRequest("context", "POST", data)
        .done((response) => {
          console.log("Response:", response);
          this.showNotice(
            response.message,
            response.success ? "success" : "error",
          );
        })
        .fail((xhr) => {
          console.error("Error:", xhr.status, xhr.responseJSON);
          const errorMsg =
            xhr.responseJSON?.message || window.cfUIData.i18n.error;
          this.showNotice(errorMsg, "error");
        });
    },

    generateSenses() {
      const $btn = $("#cf-generate-senses");
      $btn.prop("disabled", true).text(window.cfUIData.i18n.loading);

      this.apiRequest("context/generate-senses", "POST")
        .done((response) => {
          this.showNotice(
            response.message,
            response.success ? "success" : "error",
          );
          if (response.success) {
            setTimeout(() => {
              window.location.href = "admin.php?page=content-factory-senses";
            }, 1500);
          }
        })
        .always(() => {
          $btn.prop("disabled", false).text("Сгенерировать смыслы");
        });
    },

    // Senses - работа с run_id
    loadRunIds() {
      console.log("=== loadRunIds: начало загрузки run_ids ===");
      const $select = $("#cf-run-id-select");
      $select.html('<option value="">Загрузка...</option>');

      this.apiRequest("senses/run-ids")
        .done((response) => {
          console.log("loadRunIds: получен ответ", response);
          console.log("response.success:", response.success);
          console.log("response.data:", response.data);
          console.log(
            "response.data.length:",
            response.data ? response.data.length : "undefined",
          );

          if (response.success && response.data && response.data.length > 0) {
            console.log("loadRunIds: формируем опции для селекта");
            const options = response.data
              .map(
                (runId) =>
                  `<option value="${this.escapeHtml(runId)}">${this.escapeHtml(runId)}</option>`,
              )
              .join("");
            $select.html(options);

            // Автоматически выбираем последний (первый в списке)
            const lastRunId = response.data[0];
            console.log("loadRunIds: выбираем run_id:", lastRunId);
            $select.val(lastRunId);
          } else {
            console.log("loadRunIds: нет данных о запусках");
            $select.html('<option value="">Нет запусков генерации</option>');
          }
        })
        .fail((xhr, status, error) => {
          console.error("loadRunIds: ошибка загрузки", xhr, status, error);
          console.error("loadRunIds: xhr.responseJSON:", xhr.responseJSON);
          $select.html('<option value="">Ошибка загрузки</option>');
          $("#cf-senses-list").html(
            '<p class="cf-ui-notice error">Ошибка загрузки run_id</p>',
          );
        });
    },

    refreshRunIds() {
      console.log("=== refreshRunIds: обновление списка run_ids ===");
      this.loadRunIds();
    },

    loadSensesByRunId() {
      const runId = $("#cf-run-id-select").val();
      console.log("=== loadSensesByRunId: загрузка смыслов для run_id:", runId);

      if (!runId) {
        console.log("loadSensesByRunId: run_id не выбран");
        $("#cf-senses-list").html("<p>Выберите запуск генерации</p>");
        return;
      }

      const $list = $("#cf-senses-list");
      $list.html("<p>" + window.cfUIData.i18n.loading + "</p>");

      const url = `senses/list?run_id=${encodeURIComponent(runId)}`;
      console.log("loadSensesByRunId: запрос к URL:", url);

      this.apiRequest(url)
        .done((response) => {
          console.log("loadSensesByRunId: получен ответ", response);
          if (response.success && response.data) {
            console.log(
              "loadSensesByRunId: рендерим список, количество:",
              response.data.length,
            );
            this.renderList("senses", response.data, $list);
          } else {
            console.log("loadSensesByRunId: нет данных для запуска");
            $list.html("<p>Нет данных для выбранного запуска</p>");
          }
        })
        .fail((xhr, status, error) => {
          console.error(
            "loadSensesByRunId: ошибка загрузки",
            xhr,
            status,
            error,
          );
          $list.html(
            '<p class="cf-ui-notice error">Ошибка загрузки смыслов</p>',
          );
        });
    },

    // Lists
    loadList(type) {
      const $list = $(`#cf-${type}-list`);
      $list.html("<p>" + window.cfUIData.i18n.loading + "</p>");

      this.apiRequest(type)
        .done((response) => {
          if (response.success && response.data) {
            this.renderList(type, response.data, $list);
          } else {
            $list.html("<p>Нет данных</p>");
          }
        })
        .fail(() => {
          $list.html('<p class="cf-ui-notice error">Ошибка загрузки</p>');
        });
    },

    renderList(type, items, $container) {
      if (!items || items.length === 0) {
        $container.html("<p>Список пуст</p>");
        return;
      }

      const html = items
        .map((item) => this.renderListItem(type, item))
        .join("");
      $container.html(html);

      // Bind click events
      $container.find(".cf-ui-list-item").on("click", function () {
        const $item = $(this);
        const id = $item.data("id");
        const meaningId = $item.data("meaning-id");
        // Для смыслов используем meaning_id, для остальных - id
        const itemId = type === "senses" && meaningId ? meaningId : id;

        // Для тем используем аккордеон
        if (type === "topics") {
          cfUI.loadDetailInline(type.slice(0, -1), itemId, $item);
        } else {
          cfUI.loadDetail(type.slice(0, -1), itemId);
        }
      });
    },

    renderListItem(type, item) {
      // Для смыслов используем специальные поля
      if (type === "senses") {
        const title = `${item.service} — ${item.audience}`;
        const problem = item.problem || "";
        const date = item.created_at || "";

        return `
          <div class="cf-ui-list-item" data-id="${item.id}" data-meaning-id="${item.meaning_id}">
            <h3>${this.escapeHtml(title)}</h3>
            ${problem ? `<p><strong>Проблема:</strong> ${this.escapeHtml(this.truncate(problem, 150))}</p>` : ""}
            <div class="cf-ui-meta">
              <span>ID: ${item.meaning_id}</span> | 
              <span>Run: ${item.run_id}</span> | 
              <span>${date}</span>
            </div>
          </div>
        `;
      }

      // Для тем используем специальные поля
      if (type === "topics") {
        const title = item.topic_title || "Без названия";
        const angle = item.angle || "";
        const query = item.top3_query_texts || "";
        const date = item.topic_created_at || "";
        const score = item.topic_score || 0;
        const status = item.status || "";

        // Добавляем класс для опубликованных тем
        const publishedClass =
          status === "published" ? " cf-ui-list-item-published" : "";

        return `
          <div class="cf-ui-list-item${publishedClass}" data-id="${item.topic_candidate_id}">
            <h3>${this.escapeHtml(title)}</h3>
            ${angle ? `<p><strong>Угол:</strong> ${this.escapeHtml(angle)}</p>` : ""}
            ${query ? `<p><strong>Запрос:</strong> ${this.escapeHtml(this.truncate(query, 100))}</p>` : ""}
            <div class="cf-ui-meta">
              <span>Score: ${score}</span> | 
              <span>Status: <strong class="status-${status}">${status}</strong></span> | 
              <span>Meaning: ${item.meaning_id}</span> | 
              <span>${date}</span>
            </div>
          </div>
        `;
      }

      // Для остальных типов используем стандартные поля
      const title = item.title || item.text || "Без названия";
      const desc = item.description || item.content || "";
      const date = item.created_at || "";

      return `
        <div class="cf-ui-list-item" data-id="${item.id}">
          <h3>${this.escapeHtml(title)}</h3>
          ${desc ? `<p>${this.escapeHtml(this.truncate(desc, 150))}</p>` : ""}
          <div class="cf-ui-meta">${date}</div>
        </div>
      `;
    },

    loadDetail(type, id) {
      const $detail = $(`#cf-${type}-detail`);
      $detail.html("<p>" + window.cfUIData.i18n.loading + "</p>").show();

      // Для тем используем query-параметр
      const endpoint =
        type === "topic"
          ? `${type}s/get?id=${encodeURIComponent(id)}`
          : `${type}s/${id}`;

      this.apiRequest(endpoint).done((response) => {
        if (response.success && response.data) {
          // Если данные приходят массивом, берём первый элемент
          const itemData = Array.isArray(response.data)
            ? response.data[0]
            : response.data;
          this.renderDetail(type, itemData, $detail);
        }
      });
    },

    loadDetailInline(type, id, $clickedItem) {
      // Проверяем, открыта ли уже эта карточка
      const $existingDetail = $clickedItem.next(".cf-ui-detail-inline");

      if ($existingDetail.length > 0) {
        // Если детали уже открыты для этой карточки - закрываем
        $existingDetail.slideUp(300, function () {
          $(this).remove();
        });
        $clickedItem.removeClass("active");
        return;
      }

      // Закрываем все другие открытые детали
      $(".cf-ui-detail-inline").slideUp(300, function () {
        $(this).remove();
      });
      $(".cf-ui-list-item").removeClass("active");

      // Создаём контейнер для деталей
      const $detailContainer = $(
        '<div class="cf-ui-detail-inline" style="display:none;"><p>' +
          window.cfUIData.i18n.loading +
          "</p></div>",
      );

      // Вставляем после кликнутой карточки
      $clickedItem.after($detailContainer);
      $clickedItem.addClass("active");

      // Показываем с анимацией
      $detailContainer.slideDown(300);

      // Для тем используем query-параметр
      const endpoint = `${type}s/get?id=${encodeURIComponent(id)}`;

      this.apiRequest(endpoint)
        .done((response) => {
          if (response.success && response.data) {
            // Если данные приходят массивом, берём первый элемент
            const itemData = Array.isArray(response.data)
              ? response.data[0]
              : response.data;
            this.renderDetailInline(type, itemData, $detailContainer);
          }
        })
        .fail(() => {
          $detailContainer.html(
            '<p class="cf-ui-notice error">Ошибка загрузки деталей</p>',
          );
        });
    },

    renderDetail(type, item, $container) {
      // Для смыслов показываем детальную информацию
      if (type === "sense") {
        const keywords = Array.isArray(item.keywords)
          ? item.keywords.join(", ")
          : "";

        let html = `
          <div class="cf-ui-detail-header">
            <h2>${this.escapeHtml(item.service)} — ${this.escapeHtml(item.audience)}</h2>
            <button type="button" class="button" onclick="$('#cf-sense-detail').hide()">Закрыть</button>
          </div>
          <div class="cf-ui-detail-content">
            <p><strong>ID смысла:</strong> ${this.escapeHtml(item.meaning_id)}</p>
            <p><strong>Run ID:</strong> ${item.run_id}</p>
            <hr>
            <h3>Проблема</h3>
            <p>${this.escapeHtml(item.problem)}</p>
            <h3>Риск</h3>
            <p>${this.escapeHtml(item.risk)}</p>
            <h3>Подход</h3>
            <p>${this.escapeHtml(item.approach)}</p>
            <h3>Результат</h3>
            <p>${this.escapeHtml(item.result)}</p>
            <h3>Доказательство</h3>
            <p>${this.escapeHtml(item.proof_hint)}</p>
            ${keywords ? `<p><strong>Ключевые слова:</strong> ${this.escapeHtml(keywords)}</p>` : ""}
            <p><small>Создано: ${item.created_at}</small></p>
          </div>
        `;

        $container.html(html);
        return;
      }

      // Для тем показываем детальную информацию
      if (type === "topic") {
        const keywords = Array.isArray(item.keywords)
          ? item.keywords.join(", ")
          : "";
        const title = item.topic_title || "Без названия";
        const angle = item.angle || "";
        const reason = item.reason || "";
        const query = item.top3_query_texts || "";
        const queryMeta = item.top3_query_meta || "";
        const score = item.topic_score || 0;
        const status = item.status || "";
        const date = item.topic_created_at || "";

        let html = `
          <div class="cf-ui-detail-header">
            <h2>${this.escapeHtml(title)}</h2>
            <button type="button" class="button" onclick="$('#cf-topic-detail').hide()">Закрыть</button>
          </div>
          <div class="cf-ui-detail-content">
            <p><strong>ID темы:</strong> ${item.topic_candidate_id}</p>
            <p><strong>Meaning ID:</strong> ${item.meaning_id}</p>
            <p><strong>Run ID:</strong> ${item.run_id}</p>
            <p><strong>Статус:</strong> ${this.escapeHtml(status)}</p>
            <p><strong>Оценка:</strong> ${score}</p>
            <hr>
            <h3>Угол раскрытия темы</h3>
            <p>${this.escapeHtml(angle)}</p>
            ${reason ? `<h3>Обоснование</h3><p>${this.escapeHtml(reason)}</p>` : ""}
            <h3>Основной запрос</h3>
            <p>${this.escapeHtml(query)}</p>
            ${queryMeta ? `<p><small>Метаданные запроса: ${this.escapeHtml(queryMeta)}</small></p>` : ""}
            ${keywords ? `<p><strong>Ключевые слова:</strong> ${this.escapeHtml(keywords)}</p>` : ""}
            <p><small>Создано: ${date}</small></p>
          </div>
        `;

        $container.html(html);
        return;
      }

      // Для остальных типов используем стандартный вид
      let html = `
        <div class="cf-ui-detail-header">
          <h2>${this.escapeHtml(item.title || "Детали")}</h2>
        </div>
        <div class="cf-ui-detail-content">
          ${item.description || item.content || ""}
        </div>
      `;

      $container.html(html);
    },

    renderDetailInline(type, item, $container) {
      // Для тем показываем детальную информацию в inline режиме
      if (type === "topic") {
        const keywords = Array.isArray(item.keywords)
          ? item.keywords.join(", ")
          : "";
        const title = item.topic_title || "Без названия";
        const angle = item.angle || "";
        const reason = item.reason || "";
        const query = item.top3_query_texts || "";
        const queryMeta = item.top3_query_meta || "";
        const score = item.topic_score || 0;
        const status = item.status || "";
        const date = item.topic_created_at || "";
        const wpPostLink = item.wp_post_link || "";

        // Проверяем, есть ли готовая статья (статус draft и есть ссылка)
        const hasArticle = status === "draft" && wpPostLink;

        let html = `
          <div class="cf-ui-detail-inline-header">
            <h3>${this.escapeHtml(title)}</h3>
            <button type="button" class="cf-ui-detail-close">
              <span class="dashicons dashicons-no-alt"></span>
            </button>
          </div>
          <div class="cf-ui-detail-inline-content">
            <div class="cf-ui-detail-meta">
              <span><strong>ID:</strong> ${item.topic_candidate_id}</span>
              <span><strong>Meaning:</strong> ${item.meaning_id}</span>
              <span><strong>Run:</strong> ${item.run_id}</span>
              <span><strong>Статус:</strong> ${this.escapeHtml(status)}</span>
              <span><strong>Оценка:</strong> ${score}</span>
            </div>
            <div class="cf-ui-detail-section">
              <h4>Угол раскрытия темы</h4>
              <p>${this.escapeHtml(angle)}</p>
            </div>
            ${reason ? `<div class="cf-ui-detail-section"><h4>Обоснование</h4><p>${this.escapeHtml(reason)}</p></div>` : ""}
            <div class="cf-ui-detail-section">
              <h4>Основной запрос</h4>
              <p>${this.escapeHtml(query)}</p>
              ${queryMeta ? `<p class="cf-ui-detail-small">Метаданные: ${this.escapeHtml(queryMeta)}</p>` : ""}
            </div>
            ${keywords ? `<div class="cf-ui-detail-section"><h4>Ключевые слова</h4><p>${this.escapeHtml(keywords)}</p></div>` : ""}
            <p class="cf-ui-detail-date"><small>Создано: ${date}</small></p>
            <div class="cf-ui-detail-actions">
              <button type="button" class="button button-primary cf-generate-article-btn" data-topic-id="${item.topic_candidate_id}">
                Сгенерировать статью
              </button>
              <button type="button" class="button cf-goto-article-btn" data-topic-id="${item.topic_candidate_id}" ${hasArticle ? "" : "disabled"} data-post-link="${hasArticle ? this.escapeHtml(wpPostLink) : ""}">
                ${hasArticle ? "Перейти к статье" : "Перейти к статье"}
              </button>
            </div>
          </div>
        `;

        $container.html(html);

        // Добавляем обработчик для кнопки закрытия
        $container.find(".cf-ui-detail-close").on("click", function () {
          $(this)
            .closest(".cf-ui-detail-inline")
            .slideUp(300, function () {
              $(this).remove();
              $(".cf-ui-list-item").removeClass("active");
            });
        });

        // Добавляем обработчик для кнопки генерации статьи
        $container.find(".cf-generate-article-btn").on("click", function () {
          const topicId = $(this).data("topic-id");
          cfUI.generateArticleFromTopic(topicId);
        });

        // Добавляем обработчик для кнопки "Перейти к статье"
        $container.find(".cf-goto-article-btn").on("click", function () {
          const postLink = $(this).data("post-link");
          if (postLink) {
            window.open(postLink, "_blank");
          }
        });

        return;
      }
    },

    // Telegram
    loadArticlesForSelect() {
      this.apiRequest("articles").done((response) => {
        if (response.success && response.data) {
          const options = response.data
            .map(
              (article) =>
                `<option value="${article.id}">${this.escapeHtml(article.title)}</option>`,
            )
            .join("");
          $("#article_id").append(options);
        }
      });
    },

    generateTelegram(e) {
      e.preventDefault();
      const articleId = $("#article_id").val();

      this.apiRequest("telegram/generate", "POST", {
        article_id: articleId,
      }).done((response) => {
        if (response.success && response.data) {
          $("#cf-tg-text").text(response.data.text);
          $("#cf-tg-preview").data("post-id", response.data.id).show();
        }
      });
    },

    publishTelegram() {
      const postId = $("#cf-tg-preview").data("post-id");
      const text = $("#cf-tg-text").text();

      this.apiRequest("telegram/publish", "POST", {
        post_id: postId,
        text: text,
      }).done((response) => {
        this.showNotice(
          response.message,
          response.success ? "success" : "error",
        );
      });
    },

    // Logs
    filterLogs() {
      const type = $("#cf-logs-filter").val();
      this.apiRequest(`logs?type=${type}`).done((response) => {
        if (response.success) {
          this.renderLogs(response.data);
        }
      });
    },

    renderLogs(logs) {
      const $list = $("#cf-logs-list");

      if (!logs || logs.length === 0) {
        $list.html("<p>Логов нет</p>");
        return;
      }

      const html = logs
        .map(
          (log) => `
        <div class="cf-ui-log-item log-${log.type}">
          <div class="log-timestamp">${log.timestamp}</div>
          <div class="log-details">${JSON.stringify(log, null, 2)}</div>
        </div>
      `,
        )
        .join("");

      $list.html(html);
    },

    clearLogs() {
      if (!confirm("Очистить все логи?")) return;

      this.apiRequest("logs/clear", "POST").done((response) => {
        if (response.success) {
          $("#cf-logs-list").html("<p>Логи очищены</p>");
        }
      });
    },

    // Utilities
    showNotice(message, type = "info") {
      const $notice = $('<div class="cf-ui-notice"></div>')
        .addClass(type)
        .text(message)
        .prependTo(".cf-ui-content");

      setTimeout(() => $notice.fadeOut(() => $notice.remove()), 5000);
    },

    escapeHtml(text) {
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    },

    truncate(text, length) {
      return text.length > length ? text.substring(0, length) + "..." : text;
    },

    // Topics - работа с run_id
    loadRunIdsForTopics() {
      console.log(
        "=== loadRunIdsForTopics: начало загрузки run_ids для тем ===",
      );
      const $select = $("#cf-topics-run-id-select");
      $select.html('<option value="">Загрузка...</option>');

      this.apiRequest("senses/run-ids")
        .done((response) => {
          console.log("loadRunIdsForTopics: получен ответ", response);

          if (response.success && response.data && response.data.length > 0) {
            console.log("loadRunIdsForTopics: формируем опции для селекта");
            const options = response.data
              .map(
                (runId) =>
                  `<option value="${this.escapeHtml(runId)}">${this.escapeHtml(runId)}</option>`,
              )
              .join("");
            $select.html(options);

            // Автоматически выбираем последний (первый в списке)
            const lastRunId = response.data[0];
            console.log("loadRunIdsForTopics: выбираем run_id:", lastRunId);
            $select.val(lastRunId);
          } else {
            console.log("loadRunIdsForTopics: нет данных о запусках");
            $select.html('<option value="">Нет запусков генерации</option>');
          }
        })
        .fail((xhr, status, error) => {
          console.error(
            "loadRunIdsForTopics: ошибка загрузки",
            xhr,
            status,
            error,
          );
          $select.html('<option value="">Ошибка загрузки</option>');
        });
    },

    listTopics() {
      const runId = $("#cf-topics-run-id-select").val();

      if (!runId) {
        this.showNotice("Выберите run_id", "error");
        return;
      }

      const $btn = $("#cf-list-topics");
      const originalText = $btn.text();
      $btn.prop("disabled", true).text(window.cfUIData.i18n.loading);

      this.apiRequest(`topics/list?run_id=${encodeURIComponent(runId)}`)
        .done((response) => {
          console.log("listTopics: получен ответ", response);
          console.log("listTopics: список тем (response.data):", response.data);
          console.log(
            "listTopics: количество тем:",
            response.data ? response.data.length : 0,
          );

          // Выводим каждую тему отдельно для детального просмотра
          if (response.data && response.data.length > 0) {
            console.log("listTopics: первая тема в списке:", response.data[0]);
          }

          if (response.success && response.data) {
            this.showNotice("Темы загружены", "success");
            this.renderList("topics", response.data, $("#cf-topics-list"));
          } else {
            this.showNotice(response.message || "Ошибка загрузки тем", "error");
          }
        })
        .fail((xhr) => {
          const errorMsg = xhr.responseJSON?.message || "Ошибка загрузки тем";
          this.showNotice(errorMsg, "error");
        })
        .always(() => {
          $btn.prop("disabled", false).text(originalText);
        });
    },

    generateTopics() {
      const runId = $("#cf-topics-run-id-select").val();

      if (!runId) {
        this.showNotice("Выберите run_id", "error");
        return;
      }

      if (
        !confirm("Запустить генерацию тем? Это может занять некоторое время.")
      ) {
        return;
      }

      const $btn = $("#cf-generate-topics");
      const originalText = $btn.text();
      $btn.prop("disabled", true).text("Генерация...");

      this.apiRequest(
        `topics/generate?run_id=${encodeURIComponent(runId)}`,
        "POST",
      )
        .done((response) => {
          console.log("generateTopics: получен ответ", response);

          if (response.success) {
            this.showNotice(
              response.message || "Темы сгенерированы",
              "success",
            );

            // Показываем результат, если есть данные
            if (response.data) {
              this.renderList("topics", response.data, $("#cf-topics-list"));
            }
          } else {
            this.showNotice(
              response.message || "Ошибка генерации тем",
              "error",
            );
          }
        })
        .fail((xhr) => {
          const errorMsg = xhr.responseJSON?.message || "Ошибка генерации тем";
          this.showNotice(errorMsg, "error");
        })
        .always(() => {
          $btn.prop("disabled", false).text(originalText);
        });
    },

    updateTopics() {
      const runId = $("#cf-topics-run-id-select").val();

      if (!runId) {
        this.showNotice("Выберите run_id", "error");
        return;
      }

      if (
        !confirm("Запустить обновление тем? Это может занять некоторое время.")
      ) {
        return;
      }

      const $btn = $("#cf-update-topics");
      const originalText = $btn.text();
      $btn.prop("disabled", true).text("Обновление...");

      this.apiRequest(
        `topics/update?run_id=${encodeURIComponent(runId)}`,
        "POST",
      )
        .done((response) => {
          console.log("updateTopics: получен ответ", response);

          if (response.success) {
            this.showNotice(response.message || "Темы обновлены", "success");

            // Показываем результат, если есть данные
            if (response.data) {
              this.renderList("topics", response.data, $("#cf-topics-list"));
            }
          } else {
            this.showNotice(
              response.message || "Ошибка обновления тем",
              "error",
            );
          }
        })
        .fail((xhr) => {
          const errorMsg = xhr.responseJSON?.message || "Ошибка обновления тем";
          this.showNotice(errorMsg, "error");
        })
        .always(() => {
          $btn.prop("disabled", false).text(originalText);
        });
    },

    generateArticleFromTopic(topicId) {
      if (!topicId) {
        this.showNotice("ID темы не найден", "error");
        return;
      }

      if (
        !confirm(
          "Запустить генерацию статьи? Это может занять некоторое время.",
        )
      ) {
        return;
      }

      const $btn = $(`.cf-generate-article-btn[data-topic-id="${topicId}"]`);
      const $gotoBtn = $(`.cf-goto-article-btn[data-topic-id="${topicId}"]`);

      // Сбрасываем состояние кнопки на дефолтное (если это повторная генерация)
      $btn
        .removeClass("button-secondary")
        .addClass("button-primary")
        .text("Генерация...")
        .prop("disabled", true);

      $gotoBtn
        .prop("disabled", true)
        .text("Генерация в процессе...")
        .off("click");

      this.apiRequest(`topics/${topicId}/generate-article`, "POST")
        .done((response) => {
          console.log("generateArticleFromTopic: получен ответ", response);
          console.log("generateArticleFromTopic: response.data", response.data);
          console.log(
            "generateArticleFromTopic: полный ответ от n8n:",
            JSON.stringify(response, null, 2),
          );

          if (response.success && response.data?.status === "started") {
            this.showNotice("Генерация статьи запущена в фоне", "success");
            // Деактивируем кнопку "Перейти к статье"
            $gotoBtn.prop("disabled", true).text("Генерация в процессе...");
            // Запускаем проверку статуса каждую минуту
            this.startArticleStatusPolling(topicId);
          } else if (response.success) {
            this.showNotice(
              response.message || "Статья генерируется",
              "success",
            );
          } else {
            this.showNotice(
              response.message || "Ошибка генерации статьи",
              "error",
            );
          }
        })
        .fail((xhr) => {
          const errorMsg =
            xhr.responseJSON?.message || "Ошибка генерации статьи";
          this.showNotice(errorMsg, "error");
        })
        .always(() => {
          $btn.prop("disabled", false).text("Сгенерировать статью");
        });
    },

    startArticleStatusPolling(topicId) {
      console.log(`Запуск polling для темы ${topicId}`);

      // Очищаем предыдущий интервал, если был
      if (this.pollingIntervals && this.pollingIntervals[topicId]) {
        clearInterval(this.pollingIntervals[topicId]);
      }

      // Инициализируем объект для хранения интервалов
      if (!this.pollingIntervals) {
        this.pollingIntervals = {};
      }

      // Проверяем статус каждую минуту (60000 мс)
      this.pollingIntervals[topicId] = setInterval(() => {
        this.checkArticleStatus(topicId);
      }, 60000);

      // Делаем первую проверку сразу через 5 секунд
      setTimeout(() => {
        this.checkArticleStatus(topicId);
      }, 5000);
    },

    checkArticleStatus(topicId) {
      console.log(`Проверка статуса генерации для темы ${topicId}`);

      this.apiRequest(`topics/${topicId}/check-article-status`, "GET")
        .done((response) => {
          console.log("checkArticleStatus: ответ", response);

          if (!response.success || !response.data) {
            return;
          }

          const status = response.data.status;
          const $btn = $(
            `.cf-generate-article-btn[data-topic-id="${topicId}"]`,
          );
          const $gotoBtn = $(
            `.cf-goto-article-btn[data-topic-id="${topicId}"]`,
          );

          if (status === "success" || status === "draft") {
            // Генерация завершена успешно
            console.log("Генерация завершена успешно");
            this.stopArticleStatusPolling(topicId);

            const postLink = response.data.wp_post_link;
            if (postLink) {
              // Делаем кнопку активной ссылкой
              $gotoBtn
                .prop("disabled", false)
                .text("Перейти к статье")
                .off("click")
                .on("click", function () {
                  window.open(postLink, "_blank");
                });

              this.showNotice("Статья готова!", "success");
            }
          } else if (status === "error") {
            // Ошибка генерации
            console.log("Ошибка генерации статьи");
            this.stopArticleStatusPolling(topicId);

            // Заменяем кнопку "Сгенерировать статью" на "Сгенерировать еще раз"
            $btn
              .text("Сгенерировать еще раз")
              .prop("disabled", false)
              .removeClass("button-primary")
              .addClass("button-secondary");

            $gotoBtn.prop("disabled", true).text("Ошибка генерации");

            this.showNotice("Ошибка при генерации статьи", "error");
          } else if (status === "start") {
            // Генерация еще в процессе
            console.log("Генерация в процессе...");
          }
        })
        .fail((xhr) => {
          console.error("Ошибка проверки статуса:", xhr);
        });
    },

    stopArticleStatusPolling(topicId) {
      if (this.pollingIntervals && this.pollingIntervals[topicId]) {
        console.log(`Остановка polling для темы ${topicId}`);
        clearInterval(this.pollingIntervals[topicId]);
        delete this.pollingIntervals[topicId];
      }
    },

    // Articles - загрузка run_ids
    loadRunIdsForArticles() {
      console.log("=== loadRunIdsForArticles: загрузка run_ids ===");
      const $select = $("#cf-articles-run-id-select");

      this.apiRequest("senses/run-ids")
        .done((response) => {
          if (response.success && response.data && response.data.length > 0) {
            const options =
              '<option value="">Все</option>' +
              response.data
                .map(
                  (runId) =>
                    `<option value="${this.escapeHtml(runId)}">${this.escapeHtml(runId)}</option>`,
                )
                .join("");
            $select.html(options);
          }
        })
        .fail(() => {
          $select.html('<option value="">Ошибка загрузки</option>');
        });
    },

    // Articles - загрузка списка статей
    loadArticles() {
      const runId = $("#cf-articles-run-id-select").val();
      const status = $("#cf-articles-status-select").val();

      const $list = $("#cf-articles-list");
      $list.html("<p>" + window.cfUIData.i18n.loading + "</p>");

      // Формируем параметры запроса
      let url = "articles";
      const params = [];
      if (runId) params.push(`run_id=${encodeURIComponent(runId)}`);
      if (status) params.push(`status=${encodeURIComponent(status)}`);
      if (params.length > 0) {
        url += "?" + params.join("&");
      }

      console.log("loadArticles: запрос к", url);

      this.apiRequest(url)
        .done((response) => {
          console.log("loadArticles: получен ответ", response);

          if (response.success && response.data) {
            this.renderArticlesList(response.data, $list);
          } else {
            $list.html("<p>Нет статей</p>");
          }
        })
        .fail((xhr) => {
          console.error("loadArticles: ошибка", xhr);
          $list.html(
            '<p class="cf-ui-notice error">Ошибка загрузки статей</p>',
          );
        });
    },

    // Articles - рендер списка
    renderArticlesList(articles, $container) {
      if (!articles || articles.length === 0) {
        $container.html("<p>Список статей пуст</p>");
        return;
      }

      const html = articles
        .map((article) => {
          const title = article.topic_title || "Без названия";
          const status = article.status || "draft";
          const publishedClass =
            status === "published" ? " cf-ui-list-item-published" : "";
          const date = article.published_at || article.created_at || "";
          const wpPostId = article.wordpress_post_id || "";
          const postLink = article.wp_post_link || "";
          const publicUrl = article.post_url || "";

          return `
            <div class="cf-ui-list-item${publishedClass}">
              <h3>${this.escapeHtml(title)}</h3>
              <div class="cf-ui-meta">
                <span>WP Post ID: ${wpPostId}</span> | 
                <span>Topic ID: ${article.topic_candidate_id}</span> | 
                <span>Status: <strong class="status-${status}">${status}</strong></span> | 
                <span>${date}</span>
              </div>
              <div class="cf-ui-detail-actions" style="margin-top: 10px;">
                ${postLink ? `<a href="${postLink}" target="_blank" class="button">Редактировать в WP</a>` : ""}
                ${publicUrl ? `<a href="${publicUrl}" target="_blank" class="button">Посмотреть на сайте</a>` : ""}
              </div>
            </div>
          `;
        })
        .join("");

      $container.html(html);
    },
  };

  // Init on DOM ready
  $(document).ready(() => cfUI.init());

  // Export to global
  window.cfUI = cfUI;
})(jQuery);
