<?php

namespace ContentFactoryUI\N8n\DTO;

/**
 * DTO для смысла
 */
class SenseDTO {
  public $id;
  public $title;
  public $description;
  public $context_id;
  public $created_at;

  public static function from_array($data) {
    $dto = new self();
    $dto->id = $data['id'] ?? null;
    $dto->title = $data['title'] ?? '';
    $dto->description = $data['description'] ?? '';
    $dto->context_id = $data['context_id'] ?? null;
    $dto->created_at = $data['created_at'] ?? current_time('mysql');
    return $dto;
  }

  public function to_array() {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'description' => $this->description,
      'context_id' => $this->context_id,
      'created_at' => $this->created_at
    ];
  }
}
