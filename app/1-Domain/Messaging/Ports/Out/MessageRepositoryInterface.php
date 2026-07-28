<?php

namespace Promolider\Domain\Messaging\Ports\Out;

use Promolider\Domain\Messaging\Entities\Message as MessageEntity;

interface MessageRepositoryInterface
{
    public function getRecentMessagesByUser(int $userId): array;
    public function getConversationWithUser(int $userId, string $email): array;
    public function createMessage(int $transmitterId, int $receiverId, string $message): MessageEntity;
}
