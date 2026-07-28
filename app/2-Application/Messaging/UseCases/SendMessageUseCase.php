<?php

namespace Promolider\Application\Messaging\UseCases;

use Promolider\Domain\Messaging\Ports\Out\MessageRepositoryInterface;
use Promolider\Domain\Messaging\Entities\Message as MessageEntity;

class SendMessageUseCase
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository
    ) {}

    public function execute(int $transmitterId, int $receiverId, string $message): MessageEntity
    {
        return $this->messageRepository->createMessage($transmitterId, $receiverId, $message);
    }
}
