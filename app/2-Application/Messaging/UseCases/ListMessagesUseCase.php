<?php

namespace Promolider\Application\Messaging\UseCases;

use Promolider\Domain\Messaging\Ports\Out\MessageRepositoryInterface;

class ListMessagesUseCase
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository
    ) {}

    public function execute(int $userId): array
    {
        return $this->messageRepository->getRecentMessagesByUser($userId);
    }
}
