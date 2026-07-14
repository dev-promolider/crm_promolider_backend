<?php

namespace Promolider\Domain\Marketing\Entities;

class EditablePage
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public int $templateId,
        public string $title,
        public string $contentHtml,
        public ?string $editedFields,
        public string $status,
        public ?string $slug,
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
}
