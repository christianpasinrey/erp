<?php

declare(strict_types=1);

namespace App\Modules\Ai;

use App\Modules\Ai\Specializations\BaseAiSpecialization;
use App\Modules\ModuleServiceProvider;

class AiModuleServiceProvider extends ModuleServiceProvider
{
    public function moduleId(): string
    {
        return 'ai';
    }

    public function moduleName(): string
    {
        return 'AI Assistant';
    }

    public function permissions(): array
    {
        return [
            'ai.chat.use' => 'Use AI chat',
            'ai.chat.manage' => 'Manage AI conversations',
        ];
    }

    public function navigationItems(): array
    {
        return [
            [
                'label' => 'AI Coach',
                'route' => '/ai',
                'icon' => 'Bot',
                'order' => 90,
            ],
        ];
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(AiCoachRegistry::class, function ($app) {
            return new AiCoachRegistry($app->make(\App\Modules\ModuleRegistry::class));
        });
    }

    public function boot(): void
    {
        parent::boot();

        // Register base AI specialization
        $registry = app(AiCoachRegistry::class);
        $registry->register(new BaseAiSpecialization);
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
