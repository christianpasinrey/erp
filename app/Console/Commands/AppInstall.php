<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class AppInstall extends Command
{
    protected $signature = 'app:install
                            {--force : Skip confirmations and overwrite existing data}
                            {--no-frontend : Skip npm install and build}
                            {--no-interaction : Use all defaults (for CI)}';

    protected $description = 'Install and configure the ERP application from scratch';

    private bool $noInteraction = false;

    public function handle(): int
    {
        $this->noInteraction = $this->option('no-interaction');

        $this->showWelcome();

        if (! $this->checkPrerequisites()) {
            return self::FAILURE;
        }

        $this->setupEnv();
        $this->generateAppKey();
        $this->createDatabase();
        $this->runMigrations();
        $this->runSeeders();
        $this->buildFrontend();
        $this->showSuccess();

        return self::SUCCESS;
    }

    private function showWelcome(): void
    {
        $this->newLine();
        info('========================================');
        info('       ERP Application Installer        ');
        info('========================================');
        $this->newLine();
    }

    private function checkPrerequisites(): bool
    {
        note('Checking prerequisites...');
        $ok = true;

        // PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $this->line('  PHP '.PHP_VERSION.' .......... OK');
        } else {
            error('  PHP >= 8.2 required, found '.PHP_VERSION);
            $ok = false;
        }

        // MySQL
        $result = Process::run('mysql --version');
        if ($result->successful()) {
            $this->line('  MySQL ................. OK');
        } else {
            error('  MySQL not found in PATH. Ensure MySQL is installed and accessible.');
            $ok = false;
        }

        // Node
        $result = Process::run('node --version');
        if ($result->successful()) {
            $version = trim($result->output());
            $major = (int) ltrim(explode('.', $version)[0], 'v');
            if ($major >= 18) {
                $this->line("  Node {$version} ........... OK");
            } else {
                error("  Node >= 18 required, found {$version}");
                $ok = false;
            }
        } else {
            warning('  Node.js not found. Frontend build will be skipped.');
        }

        // npm
        $result = Process::run('npm --version');
        if ($result->successful()) {
            $this->line('  npm '.trim($result->output()).' ............. OK');
        } else {
            warning('  npm not found. Frontend build will be skipped.');
        }

        $this->newLine();

        return $ok;
    }

    private function setupEnv(): void
    {
        note('Setting up environment...');

        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (! file_exists($envPath)) {
            if (! file_exists($examplePath)) {
                error('.env.example not found. Cannot continue.');

                return;
            }
            copy($examplePath, $envPath);
            $this->line('  .env created from .env.example');
        } else {
            $this->line('  .env already exists');
        }

        // Ask DB credentials
        $host = $this->askOrDefault('DB_HOST', '127.0.0.1');
        $port = $this->askOrDefault('DB_PORT', '3306');
        $database = $this->askOrDefault('DB_DATABASE', 'erp');
        $username = $this->askOrDefault('DB_USERNAME', 'root');
        $password = $this->noInteraction ? '' : text(
            label: 'Database password',
            default: '',
            hint: 'Leave empty for no password',
        );

        $appUrl = $this->askOrDefault('APP_URL', 'http://erp.test');

        // Write values to .env
        $this->updateEnvValue('DB_CONNECTION', 'mysql');
        $this->updateEnvValue('DB_HOST', $host);
        $this->updateEnvValue('DB_PORT', $port);
        $this->updateEnvValue('DB_DATABASE', $database);
        $this->updateEnvValue('DB_USERNAME', $username);
        $this->updateEnvValue('DB_PASSWORD', $password);
        $this->updateEnvValue('APP_URL', $appUrl);
        $this->updateEnvValue('SESSION_CONNECTION', 'mysql');

        // Refresh config so the new values are used
        Artisan::call('config:clear');

        // Also set the values in the current runtime config
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        // Purge existing connections so new config is picked up
        DB::purge('mysql');

        $this->line("  Database: mysql://{$username}@{$host}:{$port}/{$database}");
        $this->line("  APP_URL: {$appUrl}");
        $this->newLine();
    }

    private function generateAppKey(): void
    {
        $key = config('app.key') ?: env('APP_KEY');

        if (empty($key)) {
            note('Generating application key...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->line('  Application key generated');
            $this->newLine();
        }
    }

    private function createDatabase(): void
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        note("Creating database '{$database}' if not exists...");

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->line("  Database '{$database}' ready");
        } catch (\PDOException $e) {
            error('  Could not create database: '.$e->getMessage());
            error('  Please create the database manually and re-run the installer.');

            return;
        }

        // Check if DB already has tables
        DB::purge('mysql');
        $tables = DB::select('SHOW TABLES');

        if (count($tables) > 0 && ! $this->option('force')) {
            if ($this->noInteraction) {
                warning('  Database has existing tables. Use --force to wipe. Continuing with migrate.');
            } else {
                $continue = confirm(
                    label: "Database '{$database}' already has ".count($tables).' tables. Continue? (migrations will run, existing data may conflict)',
                    default: true,
                );

                if (! $continue) {
                    error('  Installation aborted.');
                    exit(self::FAILURE);
                }
            }
        }

        $this->newLine();
    }

    private function runMigrations(): void
    {
        note('Running central migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->line('  Central migrations complete');
        $this->newLine();
    }

    private function runSeeders(): void
    {
        note('Seeding database (landlord user + demo tenant + tenant data)...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->line('  Landlord user: landlord@erp.test');
        $this->line('  Demo tenant created with domain demo.erp.test');
        $this->line('  Tenant data: currencies, roles, companies, users, modules');
        $this->newLine();
    }

    private function buildFrontend(): void
    {
        if ($this->option('no-frontend')) {
            note('Skipping frontend build (--no-frontend)');
            $this->newLine();

            return;
        }

        note('Installing npm dependencies...');
        $install = Process::timeout(300)->run('npm install');

        if (! $install->successful()) {
            warning('  npm install failed. Backend is still functional.');
            warning('  Run "npm install && npm run build" manually.');
            $this->newLine();

            return;
        }

        $this->line('  npm dependencies installed');

        note('Building frontend assets...');
        $build = Process::timeout(300)->run('npm run build');

        if (! $build->successful()) {
            warning('  npm run build failed. Backend is still functional.');
            warning('  Run "npm run build" manually.');
        } else {
            $this->line('  Frontend assets built');
        }

        $this->newLine();
    }

    private function showSuccess(): void
    {
        $appUrl = config('app.url', 'http://erp.test');
        $domain = parse_url($appUrl, PHP_URL_HOST) ?? 'erp.test';

        info('========================================');
        info('     Installation Complete!             ');
        info('========================================');
        $this->newLine();

        $this->line("  <fg=cyan>Landlord</>:  {$appUrl}");
        $this->line('             Login: <fg=yellow>landlord@erp.test</> / <fg=yellow>password</>');
        $this->newLine();

        $this->line("  <fg=cyan>Tenant</>:    http://demo.{$domain}");
        $this->line('             Login: <fg=yellow>admin@erp.test</> / <fg=yellow>password</>');
        $this->newLine();

        note('Add to your hosts file (C:\Windows\System32\drivers\etc\hosts):');
        $this->line("  127.0.0.1  {$domain}");
        $this->line("  127.0.0.1  demo.{$domain}");
        $this->newLine();

        note('Quick start:');
        $this->line('  composer run dev');
        $this->newLine();
    }

    private function askOrDefault(string $key, string $default): string
    {
        if ($this->noInteraction) {
            return $default;
        }

        return text(
            label: $key,
            default: $default,
        );
    }

    private function updateEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $contents = file_get_contents($envPath);

        // Value needs quoting if it contains spaces or special chars
        $quotedValue = $value;
        if (preg_match('/[\s#]/', $value) || $value === '') {
            $quotedValue = "\"{$value}\"";
        }

        $pattern = "/^{$key}=.*/m";

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, "{$key}={$quotedValue}", $contents);
        } else {
            // Check for commented version and replace it
            $commentedPattern = "/^#\s*{$key}=.*/m";
            if (preg_match($commentedPattern, $contents)) {
                $contents = preg_replace($commentedPattern, "{$key}={$quotedValue}", $contents);
            } else {
                // Append to end
                $contents .= "\n{$key}={$quotedValue}";
            }
        }

        file_put_contents($envPath, $contents);
    }
}
