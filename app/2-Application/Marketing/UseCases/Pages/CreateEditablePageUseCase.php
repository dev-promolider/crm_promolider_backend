<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;

class CreateEditablePageUseCase
{
    public function __construct(
        private readonly EditablePageRepositoryInterface $repository,
    ) {}

    public function execute(array $data): array
    {
        $entity = $this->repository->create($data);
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
