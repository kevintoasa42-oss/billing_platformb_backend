<?php

namespace App\Context\V1\Modules\Signature\Application\Http\Controllers;

use App\Context\V1\Modules\Signature\Application\Http\Requests\ChangeSignatureStatusRequest;
use App\Context\V1\Modules\Signature\Application\Http\Requests\CreateSignatureRequest;
use App\Context\V1\Modules\Signature\Application\Http\Requests\UpdateSignatureRequest;
use App\Context\V1\Modules\Signature\Application\UseCases\ChangeSignatureStatusUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\CreateSignatureUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\GetSignatureByIdUseCase;
use App\Context\V1\Modules\Signature\Application\UseCases\UpdateSignatureUseCase;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CarrierModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SignatureController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateSignatureUseCase $createUseCase,
        private GetSignatureByIdUseCase $getByIdUseCase,
        private UpdateSignatureUseCase $updateUseCase,
        private ChangeSignatureStatusUseCase $changeStatusUseCase,
    ) {}

    /**
     * GET /api/signatures/{id}
     * Get a signature by ID.
     */
    public function show(int $id): JsonResponse
    {
        $signature = $this->getByIdUseCase->execute($id);

        if (!$signature) {
            return $this->errorResponse('Signature not found.', 404);
        }

        return $this->successResponse($signature);
    }

    /**
     * POST /api/signatures (multipart/form-data)
     * Create a signature with file upload.
     * File is stored in public/{enterprise_ruc}/{carrier_ruc}/{file_name}.
     */
    public function store(CreateSignatureRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $carrierId = (int) $request->input('carrier_id');

        // Get enterprise RUC from central DB (from token's enterprise)
        $user = $request->user();
        $token = $user->currentAccessToken();
        $enterpriseId = $token->getAttribute('enterprise_id');
        $enterprise = DB::connection('pgsql')->table('enterprises')->where('id', $enterpriseId)->first();
        $enterpriseRuc = $enterprise->ruc ?? 'unknown';

        // Get carrier RUC from tenant DB
        $carrier = CarrierModel::find($carrierId);
        if (!$carrier) {
            return $this->errorResponse('Carrier not found.', 404);
        }
        $carrierRuc = $carrier->ruc;

        // Store file in public/{enterprise_ruc}/{carrier_ruc}/{file_name}
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs("{$enterpriseRuc}/{$carrierRuc}", $fileName, 'public');

        $data = array_merge($request->except('file'), [
            'file_name' => $fileName,
            'file_path' => $filePath,
        ]);
        $dto = CreateSignatureRequest::toDTO($data);

        return $this->successResponse($this->createUseCase->execute($dto), 201);
    }

    /**
     * PUT/PATCH /api/signatures/{id} (multipart/form-data)
     * Update a signature. If a new file is provided, replace the old one.
     */
    public function update(int $id, UpdateSignatureRequest $request): JsonResponse
    {
        $data = $request->except('file');
        $data['id'] = $id;

        // If new file provided, store it and update file_name/file_path
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $user = $request->user();
            $token = $user->currentAccessToken();
            $enterpriseId = $token->getAttribute('enterprise_id');
            $enterprise = DB::connection('pgsql')->table('enterprises')->where('id', $enterpriseId)->first();
            $enterpriseRuc = $enterprise->ruc ?? 'unknown';

            $carrierId = (int) ($data['carrier_id'] ?? 0);
            if ($carrierId) {
                $carrier = CarrierModel::find($carrierId);
            } else {
                // Get carrier_id from existing signature
                $existing = DB::connection('tenant')->table('signatures')->where('id', $id)->first();
                $carrier = $existing ? CarrierModel::find($existing->carrier_id) : null;
            }

            if (!$carrier) {
                return $this->errorResponse('Carrier not found.', 404);
            }

            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs("{$enterpriseRuc}/{$carrier->ruc}", $fileName, 'public');
            $data['file_name'] = $fileName;
            $data['file_path'] = $filePath;
        }

        $dto = UpdateSignatureRequest::toDTO($data);

        try {
            return $this->successResponse($this->updateUseCase->execute($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Signature not found.', 404);
        }
    }

    /**
     * PATCH /api/signatures/{id}/status
     * Change the status (active/inactive) of a signature.
     */
    public function changeStatus(int $id, ChangeSignatureStatusRequest $request): JsonResponse
    {
        $status = $request->validated()['status'];

        $result = $this->changeStatusUseCase->execute($id, $status);

        if (!$result) {
            return $this->errorResponse('Signature not found.', 404);
        }

        return $this->successResponse('Status updated successfully.');
    }
}
