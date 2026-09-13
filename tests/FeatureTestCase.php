<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * The landlord migrations live in database/migrations/landlord, so the
     * default RefreshDatabase migrate:fresh call must point there instead of
     * the empty database/migrations root.
     */
    protected function migrateFreshUsing()
    {
        return [
            '--drop-views' => false,
            '--drop-types' => false,
            '--path' => 'database/migrations/landlord',
        ];
    }
}
