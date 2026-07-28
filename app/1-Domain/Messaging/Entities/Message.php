<?php

namespace Promolider\Domain\Messaging\Entities;

class Message
{
    public function __construct(
        public ?int $id,
        public int $transmitterId,
        public int $receiverId,
        public string $message,
        public ?string $createdAt = null,
        public ?string $transmitterName = null,
        public ?string $transmitterEmail = null
    ) {}
}
