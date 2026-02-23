<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'session' => [
                'lifetime' => config('session.lifetime'),
            ],
            'navigation' => [

                'breadcrumbs' => $this->getBreadcrumbs($request),
            ],
        ];
    }

    /**
     * Generate breadcrumbs similar to laravel-admin-core.
     * Uses Diglactic\\Breadcrumbs if available, otherwise falls back to path segments.
     */
    protected function getBreadcrumbs(Request $request): array
    {
        if (!$request->isMethod('get')) {
            return [];
        }

        // Prefer Diglactic Breadcrumbs if installed and configured
        if (class_exists(\Diglactic\Breadcrumbs\Breadcrumbs::class)) {
            try {
                $crumbs = \Diglactic\Breadcrumbs\Breadcrumbs::generate();
                // Map to array of ['title' => string, 'url' => string|null]
                return array_map(function ($item) {
                    return [
                        'title' => $item->title,
                        'url' => property_exists($item, 'url') ? $item->url : null,
                    ];
                }, $crumbs);
            } catch (\Throwable $e) {
                // Fall through to fallback generator
            }
        }

        // Fallback: build breadcrumbs from request path segments
        $path = trim($request->path(), '/');
        if ($path === '') {
            return [
                ['title' => 'Application', 'url' => url('/application')],
            ];
        }

        $segments = explode('/', $path);
        $accumulated = '';
        $items = [
            ['title' => 'Application', 'url' => url('/application')],
        ];
        $count = count($segments);
        foreach ($segments as $index => $segment) {
            $accumulated .= '/' . $segment;
            $title = ucwords(str_replace(['-', '_'], ' ', $segment));
            $items[] = [
                'title' => $title,
                'url' => $index < $count - 1 ? url($accumulated) : null,
            ];
        }
        return $items;
    }


}
