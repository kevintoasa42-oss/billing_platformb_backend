<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que resuelve la conexion "tenant" (base de datos por empresa)
 * a partir del enterprise_id almacenado en el token Sanctum del usuario
 * autenticado. El nombre de la DB del tenant es el RUC de la empresa.
 */
class SetTenantConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();

            // El token lleva el enterprise_id (columna añadida a personal_access_tokens).
            $enterpriseId = $token->getAttribute('enterprise_id') ?? null;

            if ($enterpriseId) {
                // Buscar la empresa en la DB central para obtener el RUC (nombre de la DB del tenant).
                $empresa = DB::connection('pgsql')
                    ->table('enterprises')
                    ->where('id', $enterpriseId)
                    ->first();

                if ($empresa && !empty($empresa->db_name)) {
                    // Setear la conexion tenant dinamicamente con el RUC como nombre de DB.
                    config(['database.connections.tenant.database' => $empresa->db_name]);
                    DB::purge('tenant');
                    DB::reconnect('tenant');
                }
            }
        }

        return $next($request);
    }
}
