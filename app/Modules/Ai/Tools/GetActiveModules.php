<?php

declare(strict_types=1);

namespace App\Modules\Ai\Tools;

use App\Modules\ModuleRegistry;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetActiveModules implements Tool
{
    public function __construct(
        private readonly ModuleRegistry $moduleRegistry,
    ) {}

    public function description(): Stringable|string
    {
        return 'Get a list of all modules available in this ERP tenant and whether they are currently active';
    }

    public function schema(\Laravel\Ai\JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $modules = [];

        foreach ($this->moduleRegistry->all() as $id => $provider) {
            $modules[] = [
                'id' => $id,
                'name' => $provider->moduleName(),
                'is_active' => $this->moduleRegistry->isActive($id),
            ];
        }

        return json_encode($modules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
