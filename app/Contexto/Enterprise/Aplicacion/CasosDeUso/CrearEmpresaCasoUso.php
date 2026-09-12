<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO;
use App\Contexto\Enterprise\Dominio\Modelos\Empresa;
use App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CrearEmpresaCasoUso
{
    public function __construct(
        private EmpresaRepositoryInterface $empresaRepository,
    ) {}

    /**
     * Crea una empresa y su base de datos de tenant (nombrada por el RUC).
     *
     * @param  EmpresaDTO  $dto
     * @return EmpresaDTO
     */
    public function ejecutar(EmpresaDTO $dto): EmpresaDTO
    {
        $empresa = new Empresa(
            nombre: $dto->nombre,
            ruc: $dto->ruc,
            tradename: $dto->tradename,
            matrixname: $dto->matrixname,
            telefono: $dto->telefono,
            correo_corporativo: $dto->correo_corporativo,
            db_name: $dto->ruc,
        );

        // Persistir la empresa en la DB central.
        $empresa = $this->empresaRepository->crear($empresa);

        // Crear la base de datos del tenant (nombre = RUC).
        $this->crearBaseDeDatosTenant($empresa->db_name);

        return EmpresaDTO::fromArray($empresa->id ? [
            'id' => $empresa->id,
            'nombre' => $empresa->nombre,
            'ruc' => $empresa->ruc,
            'tradename' => $empresa->tradename,
            'matrixname' => $empresa->matrixname,
            'telefono' => $empresa->telefono,
            'correo_corporativo' => $empresa->correo_corporativo,
        ] : []);
    }

    /**
     * Crea la base de datos PostgreSQL del tenant.
     *
     * @param  string  $dbName
     * @return void
     */
    private function crearBaseDeDatosTenant(string $dbName): void
    {
        // Sanitizar el nombre de la DB (solo alfanuméricos y guion bajo).
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        // Crear la base de datos si no existe.
        $existe = DB::connection('pgsql')
            ->select("SELECT 1 FROM pg_database WHERE datname = ?", [$dbName]);

        if (empty($existe)) {
            DB::connection('pgsql')->statement("CREATE DATABASE \"{$dbName}\"");
        }
    }
}
