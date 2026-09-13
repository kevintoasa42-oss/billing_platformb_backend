<?php

namespace Tests\Unit\Context\V1\Partners\Application\DTOs;

use App\Context\V1\Partners\Application\DTOs\PartnerDTO;
use PHPUnit\Framework\TestCase;

final class PartnerDTOTest extends TestCase
{
    public function test_it_keeps_only_the_supplied_fields_for_an_update(): void
    {
        $partner = PartnerDTO::fromArray(['name' => 'Nuevo nombre', 'status' => false]);

        self::assertSame([
            'name' => 'Nuevo nombre',
            'status' => false,
        ], $partner->inputArray());
    }
}
