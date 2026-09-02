<?php

namespace App\Services\MLM;

use App\Models\AccountType;
use App\Models\AccountTypePointsMoney;
use App\Models\Classified;
use App\Models\Option;
use App\Models\Point;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\Log;

/**
 * Reparte lo que genera una afiliacion nueva: los puntos binarios que suben por el
 * arbol y el bono de inicio rapido del patrocinador directo.
 *
 * Antes esto vivia suelto dentro de UpdateNewUserRequestUseCase, asi que solo se
 * ejecutaba cuando un administrador aprobaba la solicitud a mano. El alta por pasarela
 * y el alta gratuita no pasaban por ahi y no generaban nada.
 */
class AffiliationRewardsService
{
    /**
     * Tipos de cuenta que no alimentan la red (invitados y socio fundador).
     */
    private const TIPOS_SIN_RED = [5, 6];

    private const MAX_NIVELES = 100;

    /**
     * @return array{puntos: int, bono_directo: float}
     */
    public function distribute(int $userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            Log::warning('[AFILIACION] Usuario no encontrado al repartir premios', ['user_id' => $userId]);
            return ['puntos' => 0, 'bono_directo' => 0.0];
        }

        if (in_array((int) $user->id_account_type, self::TIPOS_SIN_RED, true)) {
            return ['puntos' => 0, 'bono_directo' => 0.0];
        }

        return [
            'puntos'       => $this->distributePoints($user),
            'bono_directo' => $this->payFastStartBonus($user),
        ];
    }

    /**
     * Puntos de una recompra de OPC. Suben por el arbol igual que los de una afiliacion,
     * solo cambia el valor y el motivo.
     */
    public function distributeRepurchasePoints(int $userId, float $points): int
    {
        $user = User::find($userId);

        if (!$user || $points <= 0) {
            return 0;
        }

        if (in_array((int) $user->id_account_type, self::TIPOS_SIN_RED, true)) {
            return 0;
        }

        $nombre = trim($user->name . ' ' . $user->last_name);

        return $this->awardUpline($user, $points, 'OPC points, ' . $nombre);
    }

    /**
     * Sube por el arbol repartiendo el valor en puntos de la membresia del recien
     * afiliado. Cada ascendiente los cobra en la pierna por la que le cuelga.
     *
     * Cobran: los ascendientes activos, con membresia vigente y calificados; y siempre
     * el patrocinador directo, este calificado o no.
     *
     * @return int numero de filas de puntos creadas
     */
    private function distributePoints(User $user): int
    {
        $classified = Classified::where('user_id', $user->id)->first();

        if (!$classified) {
            Log::warning('[AFILIACION] El usuario no tiene sitio en el arbol', ['user_id' => $user->id]);
            return 0;
        }

        $pointsConfig = AccountTypePointsMoney::where('account_type_id', $user->id_account_type)->first();

        if (!$pointsConfig) {
            Log::warning('[AFILIACION] Sin configuracion de puntos para el tipo de cuenta', [
                'user_id'         => $user->id,
                'id_account_type' => $user->id_account_type,
            ]);
            return 0;
        }

        $points = (float) $pointsConfig->points;

        if ($points <= 0) {
            return 0;
        }

        $fullName = trim($user->name . ' ' . $user->last_name);

        return $this->awardUpline($user, $points, 'Binary Team Points, ' . $fullName . ' Affiliation');
    }

    /**
     * Sube por el arbol desde el usuario dado y va creando los puntos en la pierna por
     * la que le cuelga a cada ascendiente.
     *
     * @return int numero de filas de puntos creadas
     */
    private function awardUpline(User $user, float $points, string $reason): int
    {
        $classified = Classified::where('user_id', $user->id)->first();

        if (!$classified) {
            Log::warning('[RED] El usuario no tiene sitio en el arbol', ['user_id' => $user->id]);
            return 0;
        }

        $directSponsorId = (int) $classified->id_user_sponsor;

        // La pierna con la que entra al primer ascendiente es su propia posicion.
        $side = (int) $classified->position;
        $ancestorId = $classified->user_above;
        $created = 0;
        $level = 0;

        while ($ancestorId !== null && $ancestorId !== '' && $ancestorId !== 'top' && $level < self::MAX_NIVELES) {
            $level++;

            $ancestorClassified = Classified::where('user_id', $ancestorId)->first();
            $ancestor = User::find($ancestorId);

            if (!$ancestor) {
                break;
            }

            $esPatrocinadorDirecto = (int) $ancestor->id === $directSponsorId;
            $cobra = $esPatrocinadorDirecto
                || ($ancestor->active && $ancestor->membershipActive && $ancestor->qualified);

            if ($cobra) {
                Point::create([
                    'user_id'    => $user->id,
                    'sponsor_id' => $ancestor->id,
                    'points'     => $points,
                    'side'       => $side,
                    'status'     => 1,
                    'reason'     => $reason,
                ]);
                $created++;
            }

            if (!$ancestorClassified) {
                break;
            }

            // Para el siguiente nivel, la pierna es la del ascendiente actual.
            $side = (int) $ancestorClassified->position;
            $ancestorId = $ancestorClassified->user_above;
        }

        Log::info('[AFILIACION] Puntos repartidos', [
            'user_id' => $user->id,
            'puntos'  => $points,
            'filas'   => $created,
        ]);

        return $created;
    }

    /**
     * Bono de inicio rapido: un porcentaje del precio de la membresia contratada,
     * segun el tipo de cuenta del patrocinador directo.
     */
    private function payFastStartBonus(User $user): float
    {
        $sponsorId = (int) $user->id_referrer_sponsor;

        if ($sponsorId <= 0 || $sponsorId === 1) {
            return 0.0;
        }

        $accountType = AccountType::find($user->id_account_type);
        $sponsor = User::find($sponsorId);

        if (!$accountType || !$sponsor) {
            return 0.0;
        }

        $sponsorAccountType = AccountType::find($sponsor->id_account_type);

        if (!$sponsorAccountType) {
            return 0.0;
        }

        $amount = round((float) $accountType->price * ((float) $sponsorAccountType->fast_cash_bonus / 100), 2);

        if ($amount <= 0) {
            return 0.0;
        }

        $wallet = Wallet::where('user_id', $sponsorId)->first();

        if (!$wallet) {
            Log::warning('[AFILIACION] El patrocinador no tiene billetera', ['sponsor_id' => $sponsorId]);
            return 0.0;
        }

        $movement = new WalletMovements();
        $movement->wallet_id = $wallet->id;
        $movement->amount = $amount;
        $movement->type = 1;
        $movement->status = 1;
        $movement->batch = $this->currentBatch();
        $movement->bonus_type_id = 1;
        $movement->reason = 'Bono de efectivo rápido de ' . $user->username;
        $movement->save();

        Log::info('[AFILIACION] Bono de inicio rapido pagado', [
            'sponsor_id' => $sponsorId,
            'monto'      => $amount,
        ]);

        return $amount;
    }

    private function currentBatch(): int
    {
        $option = Option::firstOrCreate(['description' => 'batch'], ['value' => '1']);

        return (int) $option->value;
    }
}
