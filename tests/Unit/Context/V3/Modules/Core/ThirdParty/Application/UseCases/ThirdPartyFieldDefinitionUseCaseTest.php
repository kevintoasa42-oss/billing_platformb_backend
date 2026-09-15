<?php

declare(strict_types=1);

namespace Tests\Unit\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyFieldDefinitionUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ThirdPartyFieldDefinitionUseCaseTest extends TestCase
{
    public function test_it_lists_definitions_for_the_requested_scope(): void
    {
        $definitions = (new ThirdPartyFieldDefinitionUseCase($this->repository()))->all('customer', true);

        self::assertCount(1, $definitions);
        self::assertSame('license_plate', $definitions[0]->code);
    }

    public function test_it_creates_a_definition_from_its_dto(): void
    {
        $definition = (new ThirdPartyFieldDefinitionUseCase($this->repository()))->create(
            ThirdPartyFieldDefinitionCreateDTO::fromArray([
                'code' => 'fleet_code',
                'label' => 'Código de flota',
                'scope' => 'carrier',
                'data_type' => 'select',
                'validation' => ['options' => ['A', 'B']],
                'sort_order' => 20,
                'is_required' => true,
            ]),
        );

        self::assertSame('fleet_code', $definition->code);
        self::assertSame(['options' => ['A', 'B']], $definition->validation);
        self::assertTrue($definition->isRequired);
        self::assertTrue($definition->isActive);
    }

    public function test_it_forwards_partial_updates_and_deactivations(): void
    {
        $useCase = new ThirdPartyFieldDefinitionUseCase($this->repository());
        $updated = $useCase->update(
            '00000000-0000-4000-8000-000000000201',
            ThirdPartyFieldDefinitionUpdateDTO::fromArray(['label' => 'Matrícula']),
        );
        $deactivated = $useCase->deactivate('00000000-0000-4000-8000-000000000201');

        self::assertSame('Matrícula', $updated?->label);
        self::assertFalse($deactivated?->isActive ?? true);
    }

    private function repository(): ThirdPartyFieldDefinitionRepositoryInterface
    {
        return new class implements ThirdPartyFieldDefinitionRepositoryInterface
        {
            public function all(?string $scope, bool $activeOnly): array
            {
                return [$this->definition()];
            }

            public function create(array $attributes): ThirdPartyFieldDefinition
            {
                return new ThirdPartyFieldDefinition(
                    '00000000-0000-4000-8000-000000000202',
                    $attributes['code'],
                    $attributes['label'],
                    $attributes['scope'],
                    $attributes['data_type'],
                    $attributes['validation'],
                    $attributes['sort_order'],
                    $attributes['is_required'],
                    $attributes['is_active'],
                );
            }

            public function update(string $id, array $attributes): ?ThirdPartyFieldDefinition
            {
                $definition = $this->definition();

                return new ThirdPartyFieldDefinition(
                    $definition->id,
                    $attributes['code'] ?? $definition->code,
                    $attributes['label'] ?? $definition->label,
                    $attributes['scope'] ?? $definition->scope,
                    $attributes['data_type'] ?? $definition->dataType,
                    $attributes['validation'] ?? $definition->validation,
                    $attributes['sort_order'] ?? $definition->sortOrder,
                    $attributes['is_required'] ?? $definition->isRequired,
                    $attributes['is_active'] ?? $definition->isActive,
                );
            }

            public function deactivate(string $id): ?ThirdPartyFieldDefinition
            {
                $definition = $this->definition();

                return new ThirdPartyFieldDefinition(
                    $definition->id,
                    $definition->code,
                    $definition->label,
                    $definition->scope,
                    $definition->dataType,
                    $definition->validation,
                    $definition->sortOrder,
                    $definition->isRequired,
                    false,
                );
            }

            private function definition(): ThirdPartyFieldDefinition
            {
                return new ThirdPartyFieldDefinition(
                    '00000000-0000-4000-8000-000000000201',
                    'license_plate',
                    'Matrícula',
                    'customer',
                    'text',
                    [],
                    10,
                    false,
                    true,
                );
            }
        };
    }
}
