<?php

namespace Kelude\LaravelApiStack\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    use Installable;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:api-stack';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        return $this->installApiStack();
    }

    /**
     * Install the API Breeze stack.
     *
     * @return int|null
     */
    protected function installApiStack()
    {
        $this->runCommands(['php artisan install:api']);

        $files = new Filesystem;

        // Middleware...
        $this->installMiddleware([
            '\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class',
        ], 'api', 'prepend');

        // Providers...
        $files->copyDirectory(__DIR__.'/../../../stubs/api/app/Providers', app_path('Providers'));

        // Routes...
        copy(__DIR__.'/../../../stubs/api/routes/api.php', base_path('routes/api.php'));
        copy(__DIR__.'/../../../stubs/api/routes/web.php', base_path('routes/web.php'));

        // Configuration...
        $files->copyDirectory(__DIR__.'/../../../stubs/api/config', config_path());

        // Environment...
        if (! $files->exists(base_path('.env'))) {
            copy(base_path('.env.example'), base_path('.env'));
        }

        file_put_contents(
            base_path('.env'),
            preg_replace('/APP_URL=(.*)/', 'APP_URL=http://localhost:8000'.PHP_EOL.'FRONTEND_URL=http://localhost:3000', file_get_contents(base_path('.env')))
        );

        // Cleaning...
        $this->removeScaffoldingUnnecessaryForApis();

        $this->components->info('API stack installed successfully.');
    }

    /**
     * Remove any application scaffolding that isn't needed for APIs.
     *
     * @return void
     */
    protected function removeScaffoldingUnnecessaryForApis()
    {
        $files = new Filesystem;

        // Remove frontend related files...
        $files->delete(base_path('package.json'));
        $files->delete(base_path('vite.config.js'));

        // Remove Laravel "welcome" view...
        $files->delete(resource_path('views/welcome.blade.php'));
        $files->put(resource_path('views/.gitkeep'), PHP_EOL);

        // Remove CSS and JavaScript directories...
        $files->deleteDirectory(resource_path('css'));
        $files->deleteDirectory(resource_path('js'));
    }
}
