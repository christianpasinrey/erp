<?php

declare(strict_types=1);

namespace App\Modules\Contacts;

use App\Modules\ModuleServiceProvider;

class ContactsModuleServiceProvider extends ModuleServiceProvider
{
    public function moduleId(): string
    {
        return 'contacts';
    }

    public function moduleName(): string
    {
        return 'Contacts';
    }

    public function permissions(): array
    {
        return [
            'contacts.view' => 'View contacts',
            'contacts.create' => 'Create contacts',
            'contacts.edit' => 'Edit contacts',
            'contacts.delete' => 'Delete contacts',
        ];
    }

    public function navigationItems(): array
    {
        return [
            [
                'label' => 'Contacts',
                'route' => '/contacts',
                'icon' => 'Users',
                'order' => 10,
            ],
        ];
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
