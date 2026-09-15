<?php

namespace App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Controllers;

use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\CurrentAuthenticationSession;
use App\Context\V3\Modules\Platform\Application\UseCases\PlatformAdministrationUseCase;
use App\Context\V3\Modules\Platform\Domain\Exceptions\PlatformAdministrationException;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\SavePlatformConfigurationRequest;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\StorePlatformTenantRequest;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\StorePlatformUserRequest;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\UpdatePlatformMembershipRequest;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\UpdatePlatformTenantRequest;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests\UpdatePlatformUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PlatformAdministrationController extends Controller
{
    public function __construct(
        private readonly PlatformAdministrationUseCase $useCase,
        private readonly CurrentAuthenticationSession $authenticatedSession,
    ) {}

    public function context(Request $request, string $context): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Contexto cargado.',
            'data' => $this->useCase->context($this->authenticatedSession->get(), $context),
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Resumen de administración cargado.',
            'data' => $this->useCase->overview($this->authenticatedSession->get()),
        ]);
    }

    public function platformTenants(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Tenants de la plataforma cargados.',
            'data' => $this->useCase->tenants($this->authenticatedSession->get()),
        ]);
    }

    public function platformInvoices(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Facturas de la plataforma cargadas.',
            'data' => $this->useCase->platformInvoices($this->authenticatedSession->get()),
        ]);
    }

    public function platformContracts(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Contratos de la plataforma cargados.',
            'data' => $this->useCase->overview($this->authenticatedSession->get())['contracts'],
        ]);
    }

    public function platformSupportTickets(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Soporte de la plataforma cargado.',
            'data' => $this->useCase->overview($this->authenticatedSession->get())['support_tickets'],
        ]);
    }

    public function platformUsers(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuarios de la plataforma cargados.',
            'data' => $this->useCase->users($this->authenticatedSession->get()),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuarios cargados.',
            'data' => $this->useCase->users($this->authenticatedSession->get()),
        ]);
    }

    public function createUser(StorePlatformUserRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuario creado.',
            'data' => $this->useCase->createUser($this->authenticatedSession->get(), $request->validated()),
        ], Response::HTTP_CREATED);
    }

    public function updateUser(UpdatePlatformUserRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuario actualizado.',
            'data' => $this->useCase->updateUser($this->authenticatedSession->get(), $id, $request->validated()),
        ]);
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuario desactivado.',
            'data' => $this->useCase->deleteUser($this->authenticatedSession->get(), $id),
        ]);
    }

    public function enterprises(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Empresas cargadas.',
            'data' => $this->useCase->tenants($this->authenticatedSession->get()),
        ]);
    }

    public function enterprise(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Empresa cargada.',
            'data' => $this->useCase->tenant($this->authenticatedSession->get(), $id),
        ]);
    }

    public function createEnterprise(StorePlatformTenantRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Empresa creada.',
            'data' => $this->useCase->createTenant($this->authenticatedSession->get(), $request->validated()),
        ], Response::HTTP_CREATED);
    }

    public function updateEnterprise(UpdatePlatformTenantRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Empresa actualizada.',
            'data' => $this->useCase->updateTenant($this->authenticatedSession->get(), $id, $request->validated()),
        ]);
    }

    public function enterpriseUsers(Request $request, int $enterprise): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuarios de la empresa cargados.',
            'data' => $this->useCase->tenantUsers($this->authenticatedSession->get(), $enterprise),
        ]);
    }

    public function assignUser(UpdatePlatformMembershipRequest $request, int $enterprise): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Usuario asignado.',
            'data' => $this->useCase->assignUser($this->authenticatedSession->get(), $enterprise, $request->validated()),
        ], Response::HTTP_CREATED);
    }

    public function updateUserAssignment(UpdatePlatformMembershipRequest $request, int $enterprise, int $user): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Asignación actualizada.',
            'data' => $this->useCase->updateAssignment($this->authenticatedSession->get(), $enterprise, $user, $request->validated()),
        ]);
    }

    public function removeUserAssignment(Request $request, int $enterprise, int $user): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Asignación eliminada.',
            'data' => $this->useCase->removeAssignment($this->authenticatedSession->get(), $enterprise, $user),
        ]);
    }

    public function taxSettings(Request $request, int $enterprise): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Configuración fiscal cargada.',
            'data' => $this->useCase->taxSettings($this->authenticatedSession->get(), $enterprise),
        ]);
    }

    public function saveTaxSettings(SavePlatformConfigurationRequest $request, int $enterprise): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Configuración fiscal guardada.',
            'data' => $this->useCase->saveTaxSettings($this->authenticatedSession->get(), $enterprise, $request->validated()),
        ]);
    }

    public function electronicSignature(Request $request, int $enterprise): JsonResponse
    {
        $this->useCase->assertEnterpriseAccess($this->authenticatedSession->get(), $enterprise);

        throw new PlatformAdministrationException('La firma electrónica no está configurada.', 'not_found', Response::HTTP_NOT_FOUND);
    }

    public function saveElectronicSignature(Request $request, int $enterprise): JsonResponse
    {
        $this->useCase->assertEnterpriseAccess($this->authenticatedSession->get(), $enterprise);

        throw new PlatformAdministrationException('La firma electrónica se administra fuera de este flujo V3.', 'electronic_signature_not_applicable', Response::HTTP_CONFLICT);
    }

    public function deleteElectronicSignature(Request $request, int $enterprise): JsonResponse
    {
        return $this->saveElectronicSignature($request, $enterprise);
    }

    public function sriCertification(Request $request, int $enterprise): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Estado de certificación cargado.',
            'data' => $this->useCase->certification($this->authenticatedSession->get(), $enterprise),
        ]);
    }

    public function startSriCertification(Request $request, int $enterprise): JsonResponse
    {
        $this->useCase->assertEnterpriseAccess($this->authenticatedSession->get(), $enterprise);

        throw new PlatformAdministrationException('La certificación SRI no se ejecuta desde este flujo V3.', 'sri_certification_not_applicable', Response::HTTP_CONFLICT);
    }

    public function sriCertificationRun(Request $request, int $enterprise, int $runId): JsonResponse
    {
        return $this->startSriCertification($request, $enterprise);
    }

    public function countries(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Países cargados.',
            'data' => $this->useCase->countries($this->authenticatedSession->get()),
        ]);
    }

    public function provinces(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Provincias cargadas.',
            'data' => $this->useCase->provinces($this->authenticatedSession->get(), $request->query('country_code')),
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Ciudades cargadas.',
            'data' => $this->useCase->cities($this->authenticatedSession->get(), $request->query('country_code'), $request->integer('province_id') ?: null),
        ]);
    }

    public function economicActivities(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Actividades económicas cargadas.',
            'data' => $this->useCase->economicActivities($this->authenticatedSession->get(), $request->query('search') ?: $request->query('q')),
        ]);
    }

    public function sriEnvironments(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Ambientes SRI cargados.',
            'data' => $this->useCase->sriEnvironments($this->authenticatedSession->get()),
        ]);
    }

    public function roles(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Roles cargados.',
            'data' => $this->useCase->roles($this->authenticatedSession->get()),
        ]);
    }

    public function role(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Rol cargado.',
            'data' => $this->useCase->role($this->authenticatedSession->get(), $id),
        ]);
    }

    public function createRole(SavePlatformConfigurationRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Rol creado.',
            'data' => $this->useCase->saveRole($this->authenticatedSession->get(), $request->validated()),
        ], Response::HTTP_CREATED);
    }

    public function updateRole(SavePlatformConfigurationRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Rol actualizado.',
            'data' => $this->useCase->saveRole($this->authenticatedSession->get(), $request->validated(), $id),
        ]);
    }

    public function deleteRole(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Rol eliminado.',
            'data' => $this->useCase->deleteRole($this->authenticatedSession->get(), $id),
        ]);
    }

    public function roleMenus(SavePlatformConfigurationRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Menús del rol actualizados.',
            'data' => $this->useCase->saveRoleMenus($this->authenticatedSession->get(), $id, $request->validated()),
        ]);
    }

    public function permissions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Permisos cargados.',
            'data' => $this->useCase->permissions($this->authenticatedSession->get()),
        ]);
    }

    public function menus(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Menús cargados.',
            'data' => $this->useCase->menus($this->authenticatedSession->get()),
        ]);
    }

    public function menuTree(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Árbol de menús cargado.',
            'data' => $this->useCase->menuTree($this->authenticatedSession->get()),
        ]);
    }

    public function createMenu(SavePlatformConfigurationRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Menú creado.',
            'data' => $this->useCase->saveMenu($this->authenticatedSession->get(), $request->validated()),
        ], Response::HTTP_CREATED);
    }

    public function updateMenu(SavePlatformConfigurationRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Menú actualizado.',
            'data' => $this->useCase->saveMenu($this->authenticatedSession->get(), $request->validated(), $id),
        ]);
    }

    public function deleteMenu(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Menú eliminado.',
            'data' => $this->useCase->deleteMenu($this->authenticatedSession->get(), $id),
        ]);
    }
}
