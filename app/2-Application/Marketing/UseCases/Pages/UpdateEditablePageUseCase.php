<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;

class UpdateEditablePageUseCase
{
    public function __construct(
        private EditablePageRepositoryInterface $repository,
    ) {}

    public function execute(int $id, array $data): ?array
    {
        $entity = $this->repository->update($id, $data);
        if (!$entity) return null;

        return [
            'id' => $entity->id,
            'user_id' => $entity->userId,
            'template_id' => $entity->templateId,
            'title' => $entity->title,
            'content_html' => $entity->contentHtml,
            'edited_fields' => $entity->editedFields,
            'status' => $entity->status,
            'slug' => $entity->slug,
            'public_url' => $entity->publicUrl,
            'created_at' => $entity->createdAt?->toIso8601String(),
            'updated_at' => $entity->updatedAt?->toIso8601String(),
        ];
    }
}
