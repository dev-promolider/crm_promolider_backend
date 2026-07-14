<?php

namespace Promolider\Domain\Marketing\Entities;

class Page
{
    public function __construct(
        public ?int $id,
        public ?int $userId,
        public string $title,
        public ?string $slug,
        public ?string $content,
        public ?string $contentHtml,
        public ?string $stylesCss,
        public ?string $thumbnail,
        public ?string $description,
        public ?string $template,
        public ?string $editedFields,
        public ?string $status,
        public ?string $type,
        public ?array $meta,
        public ?string $publicUrl,
        public ?\DateTime $createdAt,
        public ?\DateTime $updatedAt,
    ) {}

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getPublicUrl(): ?string
    {
        return $this->publicUrl;
    }
}
