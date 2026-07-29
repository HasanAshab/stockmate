<?php
 
namespace App\Console\Commands;
 
use ReflectionMethod;
use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Symfony\Component\Yaml\Yaml;
 
/**
 * Injects a generic OpenAPI 422 "Validation error" response into every
 * operation whose controller method is validated by a FormRequest,
 * unless that operation already documents its own 422 response.
 *
 * Run this immediately after `scribe:generate` and before your test suite
 * (which validates responses against the spec via Spectator).
 *
 * Why this exists: Scribe's response strategies are example-driven, not
 * schema-driven — every response Scribe documents gets its OpenAPI schema
 * inferred by reading the literal keys off a concrete JSON example. There
 * is no strategy or attribute that can emit `additionalProperties`, which
 * is what's needed to let a validation `errors` object contain arbitrary
 * field names (email, phone, password, ...) instead of one hardcoded
 * literal key. So instead of extending Scribe's extraction pipeline, this
 * command patches the already-generated OpenAPI file directly — which
 * sidesteps the limitation entirely and doesn't depend on Scribe's
 * internal strategy classes, which can shift between versions.
 */
#[Signature("
    scribe:patch-validation-responses
    {--spec=storage/app/private/scribe/openapi.yaml : Path to the generated OpenAPI spec, relative to the app base path}"
)]
#[Description("
    Add a generic 422 validation-error response to every
    FormRequest-validated operation in the generated OpenAPI spec.
")]
class PatchValidationResponses extends Command
{
     private const VALIDATION_RESPONSE = [
        'description' => 'Validation error',
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'required' => ['message', 'errors'],
                    'properties' => [
                        'message' => [
                            'type' => 'string',
                            'example' => 'The given data was invalid.',
                        ],
                        'errors' => [
                            'type' => 'object',
                            'additionalProperties' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                            'example' => [
                                'field' => ['The field is required.'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
 
    public function handle(): int
    {
        $path = base_path($this->option('spec'));
 
        if (! file_exists($path)) {
            $this->error("Spec file not found: {$path}");
            $this->line('Run `php artisan scribe:generate` first.');
 
            return self::FAILURE;
        }
 
        $spec = Yaml::parseFile($path);
        $validated = $this->mapValidatedOperations();
 
        $patched = 0;
        $skipped = 0;
 
        // IMPORTANT: `$spec['paths'] ?? []` is an expression, not a plain
        // variable — PHP cannot bind a `&` reference into the result of an
        // expression, so a reference-foreach directly on it silently
        // iterates a throwaway copy and every mutation is lost. Pulling it
        // into a real variable first makes the reference-foreach valid.
        $paths = $spec['paths'] ?? [];
 
        foreach ($paths as $uri => &$operations) {
            foreach ($operations as $verb => &$operation) {
                // Skip the shared path-level `parameters` array and any
                // other non-operation keys — a real operation always has
                // an operationId.
                if (! is_array($operation) || ! isset($operation['operationId'])) {
                    continue;
                }
 
                $key = strtoupper($verb).' '.$uri;
 
                if (! isset($validated[$key])) {
                    continue;
                }
 
                if (isset($operation['responses'][422])) {
                    $skipped++;
 
                    continue; // Respect a manually documented 422.
                }
 
                $operation['responses'][422] = self::VALIDATION_RESPONSE;
                $patched++;
            }
            unset($operation);
        }
        unset($operations);
 
        $spec['paths'] = $paths;
 
        file_put_contents($path, Yaml::dump($spec, 12, 2));
 
        $this->info("Patched {$patched} operation(s) with a generic 422 response.");
 
        if ($skipped > 0) {
            $this->line("Skipped {$skipped} operation(s) that already documented their own 422.");
        }
 
        return self::SUCCESS;
    }
 
    /**
     * Build a ['VERB /uri' => true] map of every registered route whose
     * controller method has a FormRequest-typed parameter.
     *
     * @return array<string, true>
     */
    private function mapValidatedOperations(): array
    {
        $map = [];
 
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction();
 
            if (! isset($action['controller']) || ! is_string($action['controller'])) {
                continue; // Closures aren't applicable here.
            }
 
            if (str_contains($action['controller'], '@')) {
                [$class, $method] = explode('@', $action['controller'], 2);
            } else {
                $class = $action['controller'];
                $method = '__invoke';
            }
 
            if (! class_exists($class) || ! method_exists($class, $method)) {
                continue;
            }
 
            if (! $this->methodHasFormRequestParameter($class, $method)) {
                continue;
            }
 
            $uri = '/'.ltrim($route->uri(), '/');
 
            foreach ($route->methods() as $verb) {
                if ($verb === 'HEAD') {
                    continue; // Scribe/OpenAPI don't document HEAD separately.
                }
 
                $map[$verb.' '.$uri] = true;
            }
        }
 
        return $map;
    }
 
    private function methodHasFormRequestParameter(string $class, string $method): bool
    {
        $reflection = new ReflectionMethod($class, $method);
 
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
 
            if (! $type || $type->isBuiltin()) {
                continue;
            }
 
            $typeName = $type->getName();
 
            if (class_exists($typeName) && is_subclass_of($typeName, FormRequest::class)) {
                return true;
            }
        }
 
        return false;
    }
}
