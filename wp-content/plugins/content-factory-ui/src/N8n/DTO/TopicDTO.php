<?php

namespace ContentFactoryUI\N8n\DTO;

/**
 * DTO для темы
 */
class TopicDTO {
  public $id;
  public $title;
  public $outline;
  public $sense_id;
  public $status;
  public $created_at;

  public static function from_array($data) {
    $dto = new self();
    $dto->id = $data['id'] ?? null;
    $dto->title = $data['title'] ?? '';
    $dto->outline = $data['outline'] ?? [];
    $dto->sense_id = $data['sense_id'] ?? null;
    $dto->status = $data['status'] ?? 'draft';
    $dto->created_at = $data['created_at'] ?? current_time('mysql');
    return $dto;
  }

  public function to_array() {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'outline' => $this->outline,
      'sense_id' => $this->sense_id,
      'status' => $this->status,
      'created_at' => $this->created_at
    ];
  }
}
