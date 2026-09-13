<?php

namespace App\Context\V1\Modules\Enterprise\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base para los requests del contexto Enterprise.
 * Inyecta automaticamente los datos del user autenticado y la enterprise activa
 * (obtenida del token Sanctum via enterprise_id).
 */
abstract class EnterpriseFormRequest extends FormRequest
{
    /**
     * Obtiene el user autenticado.
     *
     * @return \App\Models\UserModel|null
     */
    public function getAuthUser()
    {
        return $this->user();
    }

    /**
     * Obtiene el ID del user autenticado.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user()?->id;
    }

    /**
     * Obtiene el enterprise_id del token Sanctum activo.
     *
     * @return int|null
     */
    public function getEnterpriseId(): ?int
    {
        $token = $this->user()?->currentAccessToken();

        return $token?->getAttribute('enterprise_id');
    }

    /**
     * Obtiene el RUC de la enterprise activa desde el token.
     *
     * @return string|null
     */
    public function getEnterpriseRuc(): ?string
    {
        $enterpriseId = $this->getEnterpriseId();

        if (!$enterpriseId) {
            return null;
        }

        return \App\Models\EnterpriseModel::where('id', $enterpriseId)
            ->value('ruc');
    }

    /**
     * Inyecta el user_id y enterprise_id en los datos validados.
     *
     * @return array
     */
    public function validatedWithUser(): array
    {
        return array_merge($this->validated(), [
            'user_id' => $this->getUserId(),
            'enterprise_id' => $this->getEnterpriseId(),
        ]);
    }
}
