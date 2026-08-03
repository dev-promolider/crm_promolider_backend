<?php
namespace Promolider\Application\Registration\UseCases;

use App\Models\Preregistro;
use App\Models\UnverifiedUser;
use Exception;

class GetPreregistroReferralsUseCase
{
    public function execute(string $username, int $userId): array
    {
        // Defensivo: asegurar que $userId es entero antes de construir el patrón LIKE.
        $userId = (int) $userId;
        $needleJson = '%"id_referrer_sponsor":"' . $userId . '"%';
        $needleRaw = '%"id_referrer_sponsor":' . $userId . '%';

        // 1. Preregistros que iniciaron pago (origen: preregistro, pendiente)
        // UnverifiedUser almacena en su JSON data el id_referrer_sponsor o username
        $preregistrosConPago = collect();
        // Optimización: Usar LIKE en lugar de JSON_EXTRACT (menos carga de CPU)
        $unverifiedRows = UnverifiedUser::whereRaw('data LIKE ?', [$needleJson])
            ->orWhereRaw('data LIKE ?', [$needleRaw])
            ->get();

        $preregistroIdsConPago = [];
        $uvMap = [];

        foreach ($unverifiedRows as $uv) {
            $uvData = is_string($uv->data) ? json_decode($uv->data, true) : $uv->data;
            $preregistroId = $uvData['preregistro_id'] ?? null;

            if ($preregistroId) {
                $preregistroIdsConPago[] = $preregistroId;
                $uvMap[$preregistroId] = $uvData;
            }
        }

        $preregistros = Preregistro::whereIn('id', $preregistroIdsConPago)->get();

        foreach ($preregistros as $preregistro) {
            $uvData = $uvMap[$preregistro->id] ?? [];
            
            $preregistrosConPago->push([
                'id'             => $preregistro->id,
                'nombre'         => trim(($preregistro->nombres ?? '') . ' ' . ($preregistro->apellidos ?? '')),
                'lado'           => isset($uvData['binary_position'])
                    ? ($uvData['binary_position'] == 0 ? 'izquierda' : 'derecha')
                    : ($preregistro->lado ?? '—'),
                'whatsapp'       => $preregistro->whatsapp ?? $uvData['phone'] ?? '',
                'correo'         => $preregistro->correo ?? $uvData['email'] ?? '',
                'fecha_registro' => $preregistro->created_at
                    ? $preregistro->created_at->toDateTimeString()
                    : null,
                'origen'         => 'preregistro',
                'pago_estado'    => 'pendiente',
            ]);
        }

        // 2. Preregistros sin iniciar pago (origen: preregistro, sin_pago)
        $preregistrosSinPago = Preregistro::where('referrer_username', $username)
            ->whereNotIn('id', $preregistroIdsConPago)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'nombre'         => trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? '')),
                'lado'           => $p->lado ?? '—',
                'whatsapp'       => $p->whatsapp ?? '',
                'correo'         => $p->correo ?? '',
                'fecha_registro' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                'origen'         => 'preregistro',
                'pago_estado'    => 'sin_pago',
            ]);

        $allRows = $preregistrosConPago->concat($preregistrosSinPago);

        return [
            'rows'    => $allRows->values()->all(),
            'summary' => [
                'total_preregistro_pago' => $preregistrosConPago->count(),
                'total_preregistro'      => $preregistrosSinPago->count(),
            ],
        ];
    }
}
