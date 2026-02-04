<?php

namespace ContentFactoryUI\N8n\DTO;

/**
 * DTO для Telegram поста
 */
class TgPostDTO {
  public $id;
  public $text;
  public $article_id;
  public $published;
  public $telegram_message_id;
  public $created_at;

  public static function from_array($data) {
    $dto = new self();
    $dto->id = $data['id'] ?? null;
    $dto->text = $data['text'] ?? '';
    $dto->article_id = $data['article_id'] ?? null;
    $dto->published = $data['published'] ?? false;
    $dto->telegram_message_id = $data['telegram_message_id'] ?? null;
    $dto->created_at = $data['created_at'] ?? current_time('mysql');
    return $dto;
  }

  public function to_array() {
    return [
      'id' => $this->id,
      'text' => $this->text,
      'article_id' => $this->article_id,
      'published' => $this->published,
      'telegram_message_id' => $this->telegram_message_id,
      'created_at' => $this->created_at
    ];
  }
}
