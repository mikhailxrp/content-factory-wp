<?php

namespace ContentFactoryUI\N8n\DTO;

/**
 * DTO для статьи
 */
class ArticleDTO {
  public $id;
  public $title;
  public $content;
  public $topic_id;
  public $wp_post_id;
  public $status;
  public $created_at;

  public static function from_array($data) {
    $dto = new self();
    $dto->id = $data['id'] ?? null;
    $dto->title = $data['title'] ?? '';
    $dto->content = $data['content'] ?? '';
    $dto->topic_id = $data['topic_id'] ?? null;
    $dto->wp_post_id = $data['wp_post_id'] ?? null;
    $dto->status = $data['status'] ?? 'draft';
    $dto->created_at = $data['created_at'] ?? current_time('mysql');
    return $dto;
  }

  public function to_array() {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'content' => $this->content,
      'topic_id' => $this->topic_id,
      'wp_post_id' => $this->wp_post_id,
      'status' => $this->status,
      'created_at' => $this->created_at
    ];
  }
}
