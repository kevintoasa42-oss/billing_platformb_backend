<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions\ThirdPartyException;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldValueRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyMapper;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final readonly class EloquentThirdPartyRepository implements ThirdPartyRepositoryInterface
{
    public function __construct(
        private ThirdPartyMapper $mapper,
        private ThirdPartyFieldValueRepositoryInterface $fieldValues,
    ) {}

    public function identificationExists(string $identification, string $identificationType): bool
    {
        return ThirdPartyModel::query()
            ->where('identification_type', $identificationType)
            ->whereRaw("upper(regexp_replace(trim(identification), '[[:space:]]+', '', 'g')) = ?", [$identification])
            ->exists();
    }

    public function create(ThirdParty $thirdParty): ThirdParty
    {
        try {
            return DB::connection('master_v3')->transaction(function () use ($thirdParty): ThirdParty {
                $record = ThirdPartyModel::query()->create($this->mapper->toDatabaseArray($thirdParty));

                $record->roles()->createMany(array_map(
                    static fn (string $role): array => ['role' => $role],
                    $thirdParty->roles,
                ));

                $customFields = $this->fieldValues->replace(
                    (string) $record->getKey(),
                    $thirdParty->roles,
                    $thirdParty->customFields,
                );

                return $this->mapper
                    ->toDomain($record->load('roles'))
                    ->withCustomFields($customFields);
            });
        } catch (QueryException $exception) {
            $message = strtolower($exception->getMessage());
            if ($exception->getCode() === '23505'
                && str_contains($message, 'core_third_parties_canonical_identification')) {
                throw new ThirdPartyException(
                    'Ya existe un cliente o tercero con ese número de identificación.',
                    'identification_already_exists',
                    409,
                );
            }

            throw $exception;
        }
    }
}
