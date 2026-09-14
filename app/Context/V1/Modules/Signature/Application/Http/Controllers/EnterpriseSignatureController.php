<?php

namespace App\Context\V1\Modules\Signature\Application\Http\Controllers;

use App\Context\V1\Modules\Signature\Application\Http\Requests\ChangeEnterpriseSignatureStatusRequest;
use App\Context\V1\Modules\Signature\Application\Http\Requests\CreateEnterpriseSignatureRequest;
use App\Context\V1\Modules\Signature\Application\Http\Requests\UpdateEnterpriseSignatureRequest;
use App\Context\V1\Modules\Signature\Application\UseCases\ChangeEnterpriseSignatureStatusUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\CreateEnterpriseSignatureUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\GetEnterpriseSignatureUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\UpdateEnterpriseSignatureUseCase;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterpriseSignatureController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateEnterpriseSignatureUseCase $createUseCase,
        private GetEnterpriseSignatureUseCase $getUseCase,
        private UpdateEnterpriseSignatureUseCase $updateUseCase,
        private ChangeEnterpriseSignatureStatusUseCase $changeStatusUseCase,
    ) {}

    /**
     * GET /api/enterprise-signature
     * Get the signature of the current enterprise (from token).
     */
    public function show(Request $request): JsonResponse
    {
        $enterpriseId = $this->getEnterpriseId($request);

        $signature = $this->getUseCase->execute($enterpriseId);

        if (!$signature) {
            return $this->errorResponse('Enterprise signature not found.', 404);
        }

        return $this->successResponse($signature);
    }

    /**
     * POST /api/enterprise-signature (multipart/form-data)
     * Create a signature for the current enterprise.
     * File is stored in public/{enterprise_ruc}/{file_name}.
     */
    public function store(CreateEnterpriseSignatureRequest $request): JsonResponse
    {
        $enterpriseId = $this->getEnterpriseId($request);
        $enterpriseRuc = $this->getEnterpriseRuc($request);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs($enterpriseRuc, $fileName, 'public');

        $data = array_merge($request->except('file'), [
            'enterprise_id' => $enterpriseId,
            'file_name' => $fileName,
            'file_path' => $filePath,
        ]);
        $dto = CreateEnterpriseSignatureRequest::toDTO($data);

        return $this->successResponse($this->createUseCase->execute($dto), 201);
    }

    /**
     * PUT/PATCH /api/enterprise-signature (multipart/form-data)
     * Update the signature of the current enterprise.
     */
    public function update(UpdateEnterpriseSignatureRequest $request): JsonResponse
    {
        $enterpriseId = $this->getEnterpriseId($request);
        $enterpriseRuc = $this->getEnterpriseRuc($request);

        // Get existing signature ID
        $existing = DB::connection('pgsql')->table('enterprise_signatures')
            ->where('enterprise_id', $enterpriseId)->first();

        if (!$existing) {
            return $this->errorResponse('Enterprise signature not found.', 404);
        }

        $data = $request->all();
        unset($data['file']);
        $data['id'] = $existing->id;
        $data['enterprise_id'] = $enterpriseId;

        // Preserve existing values for all fields when not provided
        $preserveFields = ['file_name', 'file_path', 'password', 'expires_at', 'environment', 'emission_type', 'status'];
        foreach ($preserveFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null) {
                $data[$field] = $existing->$field;
            }
        }

        // Override with new file if uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs($enterpriseRuc, $fileName, 'public');
            $data['file_name'] = $fileName;
            $data['file_path'] = $filePath;
        }

        $dto = UpdateEnterpriseSignatureRequest::toDTO($data);

        try {
            return $this->successResponse($this->updateUseCase->execute($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Enterprise signature not found.', 404);
        }
    }

    /**
     * PATCH /api/enterprise-signature/status
     * Change the status of the current enterprise's signature.
     */
    public function changeStatus(ChangeEnterpriseSignatureStatusRequest $request): JsonResponse
    {
        $enterpriseId = $this->getEnterpriseId($request);

        $existing = DB::connection('pgsql')->table('enterprise_signatures')
            ->where('enterprise_id', $enterpriseId)->first();

        if (!$existing) {
            return $this->errorResponse('Enterprise signature not found.', 404);
        }

        $status = $request->validated()['status'];

        $result = $this->changeStatusUseCase->execute($existing->id, $status);

        if (!$result) {
            return $this->errorResponse('Enterprise signature not found.', 404);
        }

        return $this->successResponse('Status updated successfully.');
    }

    private function getEnterpriseId(Request $request): int
    {
        $token = $request->user()->currentAccessToken();
        return $token->getAttribute('enterprise_id');
    }

    private function getEnterpriseRuc(Request $request): string
    {
        $enterpriseId = $this->getEnterpriseId($request);
        $enterprise = DB::connection('pgsql')->table('enterprises')->where('id', $enterpriseId)->first();
        return $enterprise->ruc ?? 'unknown';
    }
}
