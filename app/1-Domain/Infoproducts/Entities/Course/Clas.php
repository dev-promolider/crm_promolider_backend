<?php

namespace Promolider\Domain\Infoproducts\Entities\Course;

use JsonSerializable;

class Clas implements JsonSerializable
{
    public function __construct(
        private int $id,
        private int $id_modules,
        private string $name,
        private ?string $slug,
        private ?string $time,
        private ?string $description,
        private ?string $url,
        private ?int $order,
        private ?string $status,
        private ?int $progress,
        private ?bool $has_video = null,
        private ?string $video_url = null
    ){}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'id_modules' => $this->id_modules,
            'name' => $this->name,
            'slug' => $this->slug,
            'time' => $this->time,
            'url' => $this->url,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status ?? '0',
            'progress' => $this->progress ?? 0,
            'has_video' => $this->has_video ?? false,
            'video_url' => $this->video_url,
        ];
    }
}
