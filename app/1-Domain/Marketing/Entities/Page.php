<?php

namespace Promolider\Domain\Marketing\Entities;

class Page
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $userId,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $content,
        public readonly ?string $contentHtml,
        public readonly ?string $stylesCss,
        public readonly ?string $thumbnail,
        public readonly ?string $description,
        public readonly ?string $template,
        public readonly ?string $editedFields,
        public readonly ?string $status,
        public readonly ?string $type,
        public readonly ?array $meta,
        public readonly ?string $publicUrl,
        public readonly ?\DateTime $createdAt,
        public readonly ?\DateTime $updatedAt,
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
