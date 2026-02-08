/**
 * Content Factory UI - Генерация статьи из редактора
 */

(function (wp) {
  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost;
  const { Button, Modal, TextControl, TextareaControl, Notice, Spinner } =
    wp.components;
  const { useState } = wp.element;
  const { useSelect, useDispatch } = wp.data;
  const { __ } = wp.i18n;
  const apiFetch = wp.apiFetch;

  const GenerateArticleButton = () => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const [role, setRole] = useState("SEO эксперт");
    const [prompt, setPrompt] = useState("");
    const [sections, setSections] = useState(
      "Введение\nОсновная часть\nЗаключение",
    );
    const [error, setError] = useState(null);
    const [pollingInterval, setPollingInterval] = useState(null);

    // Получаем ID текущего поста
    const postId = useSelect((select) => {
      return select("core/editor").getCurrentPostId();
    }, []);

    // Получаем dispatch для обновления контента
    const { editPost } = useDispatch("core/editor");
    const { createNotice } = useDispatch("core/notices");

    // Очистка интервала при размонтировании
    wp.element.useEffect(() => {
      return () => {
        if (pollingInterval) {
          clearInterval(pollingInterval);
        }
      };
    }, [pollingInterval]);

    const openModal = () => {
      setIsModalOpen(true);
      setError(null);
    };

    const closeModal = () => {
      setIsModalOpen(false);
      setError(null);
      if (pollingInterval) {
        clearInterval(pollingInterval);
        setPollingInterval(null);
      }
    };

    const handleGenerate = async () => {
      // Валидация
      if (!role.trim() || !prompt.trim() || !sections.trim()) {
        setError("Заполните все обязательные поля");
        return;
      }

      setIsGenerating(true);
      setError(null);

      try {
        // Отправляем запрос на генерацию
        const response = await apiFetch({
          path: `/content-factory/v1/posts/${postId}/generate-article`,
          method: "POST",
          data: {
            role: role.trim(),
            prompt: prompt.trim(),
            sections: sections.trim(),
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

              createNotice("success", "Статья успешно сгенерирована!", {
                type: "snackbar",
                isDismissible: true,
              });
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
            setError(errorMsg);
            setIsGenerating(false);

            createNotice("error", errorMsg, {
              type: "snackbar",
              isDismissible: true,
            });
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
            wp.element.createElement(TextControl, {
              label: "Роль *",
              value: role,
              onChange: setRole,
              placeholder: "Например: SEO эксперт по недвижимости",
              help: "Укажите роль, от лица которой будет написана статья",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Промпт *",
              value: prompt,
              onChange: setPrompt,
              placeholder:
                "Например: Напиши подробную статью про ипотеку для молодых семей",
              rows: 4,
              help: "Опишите, что должно быть в статье",
            }),
          ),
          wp.element.createElement(
            "div",
            { style: { marginBottom: "20px" } },
            wp.element.createElement(TextareaControl, {
              label: "Секции статьи *",
              value: sections,
              onChange: setSections,
              placeholder: "Введение\nОсновная часть\nЗаключение",
              rows: 6,
              help: "Укажите структуру статьи (каждая секция с новой строки)",
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
