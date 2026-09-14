<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Resources;

use App\Context\V3\Modules\Authentication\Application\DTOs\AuthenticationSessionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuthenticationSessionDTO */
final class AuthenticationSessionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var AuthenticationSessionDTO $session */
        $session = $this->resource;

        return $session->toArray();
    }
}
