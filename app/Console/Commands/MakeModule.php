<?php
namespace App\Console\Commands;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Create a complete module structure with all components';

    public function handle()
    {
        $name = $this->argument('name');
        $studlyName = Str::studly($name);
        $basePath = base_path("Modules/{$studlyName}");

        // Create directory structure (matches your folder layout)
        $dirs = [
            'App/Contracts',
            'App/Http/Controller',
            'App/Http/Requests',
            'App/Providers',
            'App/Resources',
            'App/Services',
            'config',
            'routes',
        ];

        foreach ($dirs as $dir) {
            $path = "{$basePath}/{$dir}";
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true, true);
                $this->info("Created directory: Modules/{$studlyName}/{$dir}");
            }
        }

        // Create files
        $this->createServiceProvider($studlyName, $basePath);
        $this->createApiController($studlyName, $basePath); // Updated to ApiController
        $this->createServiceInterface($studlyName, $basePath);
        $this->createServiceClass($studlyName, $basePath);
        $this->createResourceClass($studlyName, $basePath);
        $this->createConfigFile($studlyName, $basePath);
        $this->createRequestClass($studlyName, $basePath);
        $this->createRoutesFile($studlyName, $basePath); // Updated to use apiResource

        $this->info("Module {$studlyName} created successfully!");
        $this->info("Don't forget to register the service provider in config/app.php!");
     // Register service provider
        $this->registerServiceProvider($studlyName);

        $this->info("Module {$studlyName} created successfully!");
    }

    // Add this new method to register the provider
    protected function registerServiceProvider($name)
    {
        $providerClass = "Modules\\{$name}\\App\\Providers\\{$name}ServiceProvider::class";
        $providersFile = base_path('bootstrap/providers.php');
        
        // Create providers file if it doesn't exist
        if (!File::exists($providersFile)) {
            File::put($providersFile, "<?php\n\nreturn [\n];");
            $this->info("Created bootstrap/providers.php");
        }

        // Get current contents
        $contents = File::get($providersFile);

        // Check if provider is already registered
        if (str_contains($contents, $providerClass)) {
            $this->info("Provider already registered in bootstrap/providers.php");
            return;
        }

        // Add provider to the array
        $newContents = str_replace(
            'return [',
            "return [\n    {$providerClass},",
            $contents
        );

        File::put($providersFile, $newContents);
        $this->info("Registered service provider in bootstrap/providers.php");
    }

    protected function createServiceProvider($name, $basePath)   {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        \$this->app->bind(
            \\Modules\\{$name}\App\Contracts\\{$name}ServiceInterface::class,
            \\Modules\\{$name}\App\Services\\{$name}Service::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/{$name}/routes/api_v1.php'));
        
        // Load module config
        \$this->mergeConfigFrom(__DIR__.'/../../config/{$name}.php', '{$name}');
    }
}
EOT;

        File::put("{$basePath}/App/Providers/{$name}ServiceProvider.php", $stub);
    }

    // Updated to create ApiController
    protected function createApiController($name, $basePath)
    {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\\{$name}\App\Contracts\\{$name}ServiceInterface;
use Modules\\{$name}\App\Http\Requests\\{$name}Request;
use Modules\\{$name}\App\Resources\\{$name}ApiResource;

class {$name}ApiController extends Controller
{
    public function __construct(
        protected {$name}ServiceInterface \${$name}Service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        \$items = \$this->{$name}Service->getAll();
        return response()->json({$name}ApiResource::collection(\$items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store({$name}Request \$request): JsonResponse
    {
        \$data = \$request->validated();
        \$item = \$this->{$name}Service->create(\$data);
        return response()->json(new {$name}ApiResource(\$item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string \$id): JsonResponse
    {
        \$item = \$this->{$name}Service->find(\$id);
        return response()->json(new {$name}ApiResource(\$item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update({$name}Request \$request, string \$id): JsonResponse
    {
        \$data = \$request->validated();
        \$item = \$this->{$name}Service->update(\$id, \$data);
        return response()->json(new {$name}ApiResource(\$item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string \$id): JsonResponse
    {
        \$this->{$name}Service->delete(\$id);
        return response()->json(['message' => '{$name} deleted successfully']);
    }
}
EOT;

        File::put("{$basePath}/App/Http/Controller/{$name}ApiController.php", $stub);
    }

    protected function createServiceInterface($name, $basePath)
    {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Contracts;

interface {$name}ServiceInterface
{
    public function getAll();
    public function find(string \$id);
    public function create(array \$data);
    public function update(string \$id, array \$data);
    public function delete(string \$id);
}
EOT;

        File::put("{$basePath}/App/Contracts/{$name}ServiceInterface.php", $stub);
    }

    protected function createServiceClass($name, $basePath)
    {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Services;

use Modules\\{$name}\App\Contracts\\{$name}ServiceInterface;

class {$name}Service implements {$name}ServiceInterface
{
    public function getAll()
    {
        // TODO: Implement getAll() method
    }

    public function find(string \$id)
    {
        // TODO: Implement find() method
    }

    public function create(array \$data)
    {
        // TODO: Implement create() method
    }

    public function update(string \$id, array \$data)
    {
        // TODO: Implement update() method
    }

    public function delete(string \$id)
    {
        // TODO: Implement delete() method
    }
}
EOT;

        File::put("{$basePath}/App/Services/{$name}Service.php", $stub);
    }

    protected function createResourceClass($name, $basePath)
    {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}ApiResource extends JsonResource
{
    public function toArray(\$request)
    {
        return [
            'id' => \$this->id,
            // Add other resource fields
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
EOT;

        File::put("{$basePath}/App/Resources/{$name}ApiResource.php", $stub);
    }

    protected function createConfigFile($name, $basePath)
    {
        $stub = <<<EOT
<?php

return [
    // {$name} module configuration
];
EOT;

        File::put("{$basePath}/config/{$name}.php", $stub);
    }

    protected function createRequestClass($name, $basePath)
    {
        $stub = <<<EOT
<?php

namespace Modules\\{$name}\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$name}Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Validation rules
        ];
    }
}
EOT;

        File::put("{$basePath}/App/Http/Requests/{$name}Request.php", $stub);
    }

    // Updated to use apiResource routing
    protected function createRoutesFile($name, $basePath)
    {
        $resourceName = strtolower($name);
        
        $stub = <<<EOT
<?php

use Illuminate\Support\Facades\Route;
use Modules\\{$name}\App\Http\Controller\\{$name}ApiController;

Route::apiResource('{$resourceName}', {$name}ApiController::class);

EOT;

        File::put("{$basePath}/routes/api_v1.php", $stub);
    }
}
