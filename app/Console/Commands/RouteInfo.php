<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
class RouteInfo extends Command
{
    protected $signature = 'route:info';
    protected $description = 'Show all routes with middleware, parameters, and controller dependencies';

    public function handle()
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $this->line("URI: " . $route->uri());
            $this->line("Action: " . ($route->getActionName() ?? 'Closure'));
            $this->line("Middleware: " . implode(', ', $route->middleware()));

            $params = $route->parameterNames();
            $this->line("Parameters: " . implode(', ', $params));

            if (str_contains($route->getActionName(), '@')) {
                [$controller, $method] = explode('@', $route->getActionName());
                $reflect = new ReflectionClass($controller);
                $deps = $reflect->getConstructor()?->getParameters() ?? [];
                $depsList = array_map(fn($p) => $p->getType() . ' $' . $p->getName(), $deps);
                $this->line("Controller Dependencies: " . implode(', ', $depsList));
            }

            $this->line(str_repeat('-', 50));
        }
    }
}
