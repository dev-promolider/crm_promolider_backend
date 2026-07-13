<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class SpinRouletteUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, string $email): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica || !$dinamica->isActive) {
            throw new \RuntimeException('Dinámica no disponible', 404);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta dinámica', 403);
        }

        if ($registro->hasPlayed()) {
            throw new \RuntimeException('Ya has participado', 400);
        }

        if ($registro->hasWon()) {
            throw new \RuntimeException('Ya ganaste esta dinámica', 400);
        }

        // Validar que sea el turno actual
        $currentTurn = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);

        if (!$currentTurn || $currentTurn['id'] !== $registro->id) {
            throw new \RuntimeException('Debes esperar tu turno para poder girar', 409);
        }

        // Generar ángulo aleatorio (0-359)
        $angle = random_int(0, 359);

        // Setear turno_inicio si no está seteado
        if (!$registro->turnoInicio) {
            $this->dinamicaRepository->setTurnoInicio($registro->id);
        }

        // Guardar turno con el ángulo
        $this->dinamicaRepository->saveTurno(
            $dinamica->id,
            $registro->id,
            $registro->turno ?? 0,
            ['angulo' => $angle, 'estado' => 'en_progreso']
        );

        return [
            'success' => true,
            'angle' => $angle,
            'slug' => $dinamica->slug,
            'registro_id' => $registro->id,
        ];
    }
}
