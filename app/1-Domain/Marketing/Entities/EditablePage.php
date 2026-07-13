<?php

namespace Promolider\Domain\Marketing\Entities;

class EditablePage
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly int $templateId,
        public readonly string $title,
        public readonly string $contentHtml,
        public readonly ?string $editedFields,
        public readonly string $status,
        public readonly ?string $slug,
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
}
