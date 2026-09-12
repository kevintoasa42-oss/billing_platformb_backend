<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base para los requests del contexto Enterprise.
 * Inyecta automaticamente los datos del usuario autenticado y la empresa activa
 * (obtenida del token Sanctum via enterprise_id).
 */
abstract class EnterpriseFormRequest extends FormRequest
{
    /**
     * Obtiene el usuario autenticado.
     *
     * @return \App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel|null
     */
    public function getUsuario()
    {
        return $this->user();
    }

    /**
     * Obtiene el ID del usuario autenticado.
     *
     * @return int|null
     */
    public function getUsuarioId(): ?int
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
     * Obtiene el RUC de la empresa activa desde el token.
     *
     * @return string|null
     */
    public function getEnterpriseRuc(): ?string
    {
        $enterpriseId = $this->getEnterpriseId();

        if (!$enterpriseId) {
            return null;
        }

        return \App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel::where('id', $enterpriseId)
            ->value('ruc');
    }

    /**
     * Inyecta el usuario_id y enterprise_id en los datos validados.
     *
     * @return array
     */
    public function validatedWithUser(): array
    {
        return array_merge($this->validated(), [
            'usuario_id' => $this->getUsuarioId(),
            'enterprise_id' => $this->getEnterpriseId(),
        ]);
    }
}
