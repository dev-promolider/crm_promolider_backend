<?php
namespace Promolider\Application\Registration\UseCases;

use App\Models\PreregistroLink;
use Exception;

class SavePreregistroConfigUseCase
{
    public function execute(string $username, string $lado, string $landing): PreregistroLink
    {
        $link = PreregistroLink::updateOrCreate(
            ['username' => $username],
            ['lado' => $lado, 'landing' => $landing]
        );

        return $link;
    }
}
