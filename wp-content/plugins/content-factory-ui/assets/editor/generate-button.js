/**
 * Content Factory UI - Генерация статьи из редактора
 */

(function (wp) {
  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost;
  const {
    Button,
    Modal,
    TextControl,
    TextareaControl,
    SelectControl,
    Notice,
    Spinner,
  } = wp.components;
  const { useState } = wp.element;
  const { useSelect, useDispatch } = wp.data;
  const { __ } = wp.i18n;
  const apiFetch = wp.apiFetch;

  const GenerateArticleButton = () => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const [request, setRequest] = useState("");
    const [audience, setAudience] = useState("");
    const [keywords, setKeywords] = useState("");
    const [volumeFrom, setVolumeFrom] = useState("500");
    const [volumeTo, setVolumeTo] = useState("3000");
    const [requirements, setRequirements] = useState("");
    const [tone, setTone] = useState("");
    const [angle, setAngle] = useState("");
    const [context, setContext] = useState("");
    const [additionalElements, setAdditionalElements] = useState("");
    const [avoid, setAvoid] = useState("");
    const [prompts, setPrompts] = useState([]);
    const [isLoadingPrompts, setIsLoadingPrompts] = useState(true);
    const [error, setError] = useState(null);
    const [pollingInterval, setPollingInterval] = useState(null);
    const [notificationShown, setNotificationShown] = useState(false);

    // Получаем ID текущего поста
    const postId = useSelect((select) => {
      return select("core/editor").getCurrentPostId();
    }, []);

    // Получаем dispatch для обновления контента
    const { editPost } = useDispatch("core/editor");
    const { createNotice } = useDispatch("core/notices");

    // Загрузка промптов при монтировании
    wp.element.useEffect(() => {
      loadPrompts();
    }, []);

    // Очистка интервала при размонтировании
    wp.element.useEffect(() => {
      return () => {
        if (pollingInterval) {
          clearInterval(pollingInterval);
        }
      };
    }, [pollingInterval]);

    const loadPrompts = async () => {
      setIsLoadingPrompts(true);
      try {
        const response = await apiFetch({
          path: "/content-factory/v1/prompts",
          method: "GET",
        });

        if (response.success && response.data) {
          // Фильтруем только активные промпты
          const activePrompts = response.data.filter((p) => p.is_active === 1);
          setPrompts(activePrompts);
        }
      } catch (err) {
        console.error("Ошибка загрузки промптов:", err);
      } finally {
        setIsLoadingPrompts(false);
      }
    };

    const openModal = () => {
      setIsModalOpen(true);
      setError(null);
    };

    const closeModal = () => {
      setIsModalOpen(false);
      setError(null);
      setNotificationShown(false);
      if (pollingInterval) {
        clearInterval(pollingInterval);
        setPollingInterval(null);
      }
    };

    const handleGenerate = async () => {
      // Валидация
      if (
        !request.trim() ||
        !audience.trim() ||
        !keywords.trim() ||
        !volumeFrom.trim() ||
        !volumeTo.trim() ||
        !requirements.trim() ||
        !tone.trim() ||
        !angle.trim() ||
        !context.trim() ||
        !additionalElements.trim() ||
        !avoid.trim()
      ) {
        setError("Заполните все обязательные поля");
        return;
      }

      // Валидация диапазона объёма
      const volumeFromValue = parseInt(volumeFrom);
      const volumeToValue = parseInt(volumeTo);

      if (volumeFromValue < 500 || volumeFromValue > 3000) {
        setError("Объём 'от' должен быть от 500 до 3000");
        return;
      }

      if (volumeToValue < 500 || volumeToValue > 3000) {
        setError("Объём 'до' должен быть от 500 до 3000");
        return;
      }

      if (volumeFromValue > volumeToValue) {
        setError("Объём 'от' не может быть больше чем 'до'");
        return;
      }

      setIsGenerating(true);
      setError(null);
      setNotificationShown(false);

      try {
        // Преобразуем keywords из строки в массив
        const keywordsArray = keywords
          .split(",")
          .map((k) => k.trim())
          .filter((k) => k);

        // Отправляем запрос на генерацию
        const response = await apiFetch({
          path: `/content-factory/v1/posts/${postId}/generate-article`,
          method: "POST",
          data: {
            request: request.trim(),
            audience: audience.trim(),
            keywords: keywordsArray,
            volume_from: volumeFromValue,
            volume_to: volumeToValue,
            requirements: requirements.trim(),
            tone: tone.trim(),
            angle: angle.trim(),
            context: context.trim(),
            format: "WordPress",
            additional_elements: additionalElements.trim(),
            avoid: avoid.trim(),
          },
        });

        if (response.success) {
          createNotice(
            "success",
            "Генерация статьи запущена. Ожидайте обновления контента...",
            { type: "snackbar", isDismissible: true },
          );

          // Запускаем polling для проверки статуса
          startPolling();
        } else {
          setError(response.message || "Ошибка при запуске генерации");
          setIsGenerating(false);
        }
      } catch (err) {
        console.error("Ошибка генерации:", err);
        setError(err.message || "Ошибка при отправке запроса");
        setIsGenerating(false);
      }
    };

    const startPolling = () => {
      // Первая проверка через 5 секунд
      setTimeout(() => {
        checkStatus();
      }, 5000);

      // Затем проверяем каждые 10 секунд
      const interval = setInterval(() => {
        checkStatus();
      }, 10000);

      setPollingInterval(interval);
    };

    const checkStatus = async () => {
      try {
        const response = await apiFetch({
          path: `/content-factory/v1/posts/${postId}/check-article-status`,
          method: "GET",
        });

        console.log("=== CHECK ARTICLE STATUS ===");
        console.log("Response:", response);
        console.log("response.success:", response.success);
        console.log("response.data:", response.data);

        if (response.data) {
          console.log("Status:", response.data.status);
          console.log("Post ID:", response.data.post_id);
          console.log("Has content:", !!response.data.content);
          console.log("Content length:", response.data.content?.length);
          console.log("Title:", response.data.title);
          console.log("Error message:", response.data.error_message);
        }

        if (response.success && response.data) {
          const status = response.data.status;

          if (status === "completed") {
            // Генерация завершена
            if (pollingInterval) {
              clearInterval(pollingInterval);
              setPollingInterval(null);
            }

            // Обновляем контент в редакторе
            if (response.data.content) {
              editPost({
                content: response.data.content,
                ...(response.data.title && { title: response.data.title }),
              });

              // Показываем уведомление только один раз
              if (!notificationShown) {
                createNotice("success", "Статья успешно сгенерирована!", {
                  type: "snackbar",
                  isDismissible: true,
                });
                setNotificationShown(true);
              }
            }

            setIsGenerating(false);
            closeModal();
          } else if (status === "error") {
            // Ошибка генерации
            if (pollingInterval) {
              clearInterval(pollingInterval);
              setPollingInterval(null);
            }

            const errorMsg =
              response.data.error_message || "Ошибка при генерации статьи";

            // Проверяем, если это ошибка "Max attempts reached"
            let userFriendlyError = errorMsg;
            if (
              errorMsg.includes("Max attempts reached") &&
              errorMsg.includes("Suggest reduce min_words")
            ) {
              // Извлекаем количество слов из ошибки
              const wordCountMatch = errorMsg.match(/word_count=(\d+)/);
              const minWordsMatch = errorMsg.match(/min_words=(\d+)/);
              const wordCount = wordCountMatch
                ? wordCountMatch[1]
                : "неизвестно";
              const minWords = minWordsMatch ? minWordsMatch[1] : "неизвестно";

              userFriendlyError = `Статья не сгенерирована. AI сгенерировал только ${wordCount} слов из требуемых ${minWords}.\n\nУменьшите минимальное количество слов и попробуйте снова.`;
            }

            setError(userFriendlyError);
            setIsGenerating(false);

            // Показываем уведомление только один раз
            if (!notificationShown) {
              createNotice("error", userFriendlyError, {
                type: "snackbar",
                isDismissible: true,
              });
              setNotificationShown(true);
            }
          }
          // Если status === 'processing' или 'started', продолжаем polling
        }
      } catch (err) {
        console.error("Ошибка проверки статуса:", err);
        // Не останавливаем polling при ошибке проверки, продолжаем попытки
      }
    };

    return wp.element.createElement(
      PluginDocumentSettingPanel,
      {
        name: "content-factory-generate",
        title: "Content Factory",
        className: "content-factory-panel",
      },
      wp.element.createElement(
        "div",
        { style: { padding: "16px 0" } },
        wp.element.createElement(
          Button,
          {
            variant: "primary",
            onClick: openModal,
            disabled: isGenerating,
            style: { width: "100%" },
          },
          isGenerating ? "Генерация..." : "Сгенерировать статью",
        ),
        isGenerating &&
          wp.element.createElement(
            "p",
            {
              style: {
                marginTop: "8px",
                fontSize: "12px",
                color: "#757575",
                textAlign: "center",
              },
            },
            "Статья генерируется, это может занять несколько минут...",
          ),
      ),
      isModalOpen &&
        wp.element.createElement(
          Modal,
          {
            title: "Сгенерировать статью",
            onRequestClose: closeModal,
            className: "content-factory-modal",
            style: { maxWidth: "600px" },
          },
          error &&
            wp.element.createElement(
              Notice,
              {
                status: "error",
                isDismissible: false,
                style: { marginBottom: "16px" },
              },
              error,
            ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Запрос *",
              value: request,
              onChange: setRequest,
              placeholder: "Например: Как выбрать CRM для малого бизнеса",
              rows: 3,
              help: "Основная тема или вопрос статьи",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Аудитория *",
              value: audience,
              onChange: setAudience,
              placeholder:
                "Например: Владельцы малого бизнеса без технических знаний",
              rows: 3,
              help: "Целевая аудитория статьи",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextControl, {
              label: "Ключевые слова *",
              value: keywords,
              onChange: setKeywords,
              placeholder: "выбор CRM, CRM для малого бизнеса, лучшая CRM",
              help: "Укажите ключевые слова через запятую",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px", display: "flex", gap: "12px" } },
            wp.element.createElement(
              "div",
              { style: { flex: 1 } },
              wp.element.createElement(TextControl, {
                label: "Объём от (слов) *",
                value: volumeFrom,
                onChange: setVolumeFrom,
                type: "number",
                min: 500,
                max: 3000,
                placeholder: "500",
                help: "Минимум 500 слов",
              }),
            ),
            wp.element.createElement(
              "div",
              { style: { flex: 1 } },
              wp.element.createElement(TextControl, {
                label: "Объём до (слов) *",
                value: volumeTo,
                onChange: setVolumeTo,
                type: "number",
                min: 500,
                max: 3000,
                placeholder: "3000",
                help: "Максимум 3000 слов",
              }),
            ),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Требования *",
              value: requirements,
              onChange: setRequirements,
              placeholder:
                "Например: Сравнительная таблица 5-7 систем, чек-лист выбора",
              rows: 4,
              help: "Особые требования к контенту",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextControl, {
              label: "Тон *",
              value: tone,
              onChange: setTone,
              placeholder: "Например: Дружелюбный, простой язык",
              help: "Стиль и тон статьи",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(SelectControl, {
              label: "Угол раскрытия статьи *",
              value: angle,
              onChange: setAngle,
              disabled: isLoadingPrompts,
              help: isLoadingPrompts
                ? "Загрузка списка углов..."
                : "Выберите угол раскрытия статьи",
              options: [
                { value: "", label: "-- Выбрать --" },
                ...prompts.map((p) => ({
                  value: p.template_name,
                  label: p.angle,
                })),
              ],
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Контекст *",
              value: context,
              onChange: setContext,
              placeholder:
                "Например: Бюджет до 10 000 руб/мес, команда 5-10 человек",
              rows: 3,
              help: "Дополнительный контекст для статьи",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Доп. элементы *",
              value: additionalElements,
              onChange: setAdditionalElements,
              placeholder: "Например: FAQ, калькулятор стоимости (описание)",
              rows: 3,
              help: "Дополнительные элементы в статье",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Избегать *",
              value: avoid,
              onChange: setAvoid,
              placeholder:
                "Например: Сложных технических терминов без объяснений",
              rows: 3,
              help: "Чего следует избегать в статье",
            }),
          ),
          wp.element.createElement(
            "div",
            {
              style: {
                display: "flex",
                justifyContent: "flex-end",
                gap: "8px",
                marginTop: "24px",
              },
            },
            wp.element.createElement(
              Button,
              {
                variant: "secondary",
                onClick: closeModal,
                disabled: isGenerating,
              },
              "Отмена",
            ),
            wp.element.createElement(
              Button,
              {
                variant: "primary",
                onClick: handleGenerate,
                disabled: isGenerating,
                isBusy: isGenerating,
              },
              isGenerating ? "Генерация..." : "Сгенерировать",
            ),
          ),
        ),
    );
  };

  // Регистрируем плагин
  registerPlugin("content-factory-generate-article", {
    render: GenerateArticleButton,
    icon: "edit",
  });
})(window.wp);
