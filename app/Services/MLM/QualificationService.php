<?php

namespace App\Services\MLM;

use App\Models\Classified;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Unica definicion de "calificado" del sistema.
 *
 * Estaba implementada de cuatro maneras distintas —el panel del usuario, el arbol
 * binario, el corte y el monolito— y la del corte era la mas estricta de todas, asi
 * que el panel podia decir "Calificado" en verde y el corte pagar cero.
 *
 * La regla es: tener al menos un patrocinado directo activo, con membresia vigente y
 * de una cuenta que alimente la red, colocado a la izquierda, y otro a la derecha.
 */
class QualificationService
{
    /**
     * Cuentas que no cuentan para calificar: Socio Fundador e invitados.
     */
    private const TIPOS_EXCLUIDOS = [5, 6];

    /**
     * Calificacion de un usuario concreto.
     */
    public function isQualified(User $user): bool
    {
        $sponsored = $user->relationLoaded('classifiedSponsor')
            ? $user->classifiedSponsor
            : $user->classifiedSponsor()->with('user')->get();

        $left = false;
        $right = false;

        foreach ($sponsored as $row) {
            if (!$this->cuenta($row->user)) {
                continue;
            }

            if ((int) $row->position === 0) {
                $left = true;
            } elseif ((int) $row->position === 1) {
                $right = true;
            }

            if ($left && $right) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calificacion de toda la red de una sola vez, para el arbol y para el corte.
     * Evita una consulta por nodo.
     *
     * @return array<int, bool>
     */
    public function qualifiedMap(): array
    {
        $users = User::select([
            'id',
            'id_account_type',
            'request',
            'expiration_date',
            'expiration_membership_date',
        ])->get()->keyBy('id');

        $lados = [];

        $filas = DB::table('classified')
            ->select('user_id', 'id_user_sponsor', 'position')
            ->whereNotNull('id_user_sponsor')
            ->get();

        foreach ($filas as $fila) {
            $sponsorId = (int) $fila->id_user_sponsor;

            if ($sponsorId <= 0) {
                continue;
            }

            if (!$this->cuenta($users->get($fila->user_id))) {
                continue;
            }

            $lados[$sponsorId][(int) $fila->position] = true;
        }

        $mapa = [];

        foreach ($lados as $sponsorId => $posiciones) {
            $mapa[$sponsorId] = isset($posiciones[0]) && isset($posiciones[1]);
        }

        return $mapa;
    }

    /**
     * Un patrocinado suma para calificar si esta activo, con membresia vigente y su
     * cuenta alimenta la red.
     */
    private function cuenta(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array((int) $user->id_account_type, self::TIPOS_EXCLUIDOS, true)) {
            return false;
        }

        return $user->active && $user->membershipActive;
    }
}
