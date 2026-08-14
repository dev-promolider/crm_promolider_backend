<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\Dinamica;
use Promolider\Domain\Marketing\Entities\DinamicaRegistro;

interface DinamicaRepositoryInterface
{
    // === Public methods ===
    public function findBySlug(string $slug): ?Dinamica;
    public function getRegistros(int $dinamicaId): array;
    public function getRegistroByEmail(int $dinamicaId, string $email): ?DinamicaRegistro;
    public function getCurrentRegistroCount(int $dinamicaId): int;
    public function createRegistro(int $dinamicaId, array $data): DinamicaRegistro;
    public function markAsPlayed(int $registroId): void;
    public function markAsWinner(int $registroId, ?string $premio): void;
    public function getNextTurno(int $dinamicaId): ?int;
    public function hasWinner(int $dinamicaId): bool;
    public function getTriviaConfig(int $dinamicaId): ?array;
    public function getParticipantsFeed(int $dinamicaId): array;
    public function getActiveParticipants(int $dinamicaId): array;
    public function saveTurno(int $dinamicaId, int $registroId, int $turno, array $data = []): void;
    public function setTurnoInicio(int $registroId): void;
    public function finalizeTurnHistory(int $dinamicaId, int $registroId, string $estado, array $extra = []): void;
    public function deactivateDinamica(int $dinamicaId): void;
    public function getWonPremioNames(int $dinamicaId): array;
    public function getCurrentTurnRegistro(int $dinamicaId): ?array;
    public function getTriviaAnswers(int $dinamicaId, int $registroId): array;
    public function saveTriviaAnswer(int $dinamicaId, int $registroId, array $data): void;
    public function getLeaderboard(int $dinamicaId): array;
    public function getCategoryQuestions(int $categoryId, bool $onlyActive = true): array;

    // === Admin/Management methods ===
    public function getAllByUser(int $userId, ?int $courseId = null): array;
    public function findById(int $id, int $userId): ?array;
    public function create(array $data): array;
    public function update(int $id, array $data, int $userId): array;
    public function delete(int $id, int $userId): array;
    public function toggleStatus(int $id, int $userId): array;
    public function storeSpecifications(int $dinamicaId, array $data, int $userId): array;
    public function saveTriviaConfig(int $dinamicaId, array $data, int $userId): array;
}
