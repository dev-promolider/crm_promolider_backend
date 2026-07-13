<?php

namespace Promolider\Domain\Marketing\Entities;

class ClassroomPointConfig
{
    public function __construct(
        private readonly ?int $id,
        private readonly float $passedCourse,
        private readonly float $dailyQuestion,
        private readonly float $achievement,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getPassedCourse(): float { return $this->passedCourse; }
    public function getDailyQuestion(): float { return $this->dailyQuestion; }
    public function getAchievement(): float { return $this->achievement; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'passed_course' => $this->passedCourse,
            'daily_question' => $this->dailyQuestion,
            'achievement' => $this->achievement,
        ];
    }
}
