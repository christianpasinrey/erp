<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    private static bool $tenantMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Force re-migration when switching from non-tenant to tenant test classes.
     * The default RefreshDatabaseState::$migrated may be true from a prior
     * non-tenant test that only ran central migrations (no companies table, etc.).
     */
    protected function beforeRefreshingDatabase(): void
    {
        if (! static::$tenantMigrated) {
            RefreshDatabaseState::$migrated = false;
            RefreshDatabaseState::$inMemoryConnections = [];
            static::$tenantMigrated = true;
        }
    }

    /**
     * Override to run tenant migrations only (which include users, cache, jobs + tenant tables).
     */
    protected function migrateFreshUsing(): array
    {
        $seeder = $this->seeder();

        return array_merge(
            [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--path' => [database_path('migrations/tenant')],
                '--realpath' => true,
            ],
            $seeder ? ['--seeder' => $seeder] : ['--seed' => $this->shouldSeed()]
        );
    }
}
