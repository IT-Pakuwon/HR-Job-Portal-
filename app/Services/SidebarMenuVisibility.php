<?php

namespace App\Services;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class SidebarMenuVisibility
{
    public function routeUrl(?string $name): ?string
    {
        if (! $name || ! Route::has($name)) {
            return null;
        }
        try {
            return route($name);
        } catch (RouteNotFoundException|UrlGenerationException $exception) {
            // The database can reach another deployment before its route definitions.
            return null;
        }
    }

    public function tree(Collection $roots, Collection $allowedIds): Collection
    {
        $allowed = $allowedIds->map(fn ($id) => (string) $id)->all();
        $filter = function ($node, int $depth = 0) use (&$filter, $allowed) {
            if ($node->status !== 'A') {
                return null;
            }
            // The sidebar renders root, module, and submenu levels.
            $children = $depth < 2 ? $node->children : collect();
            $visible = $children->map(fn ($child) => $filter($child, $depth + 1))->filter()->values();
            $url = $this->routeUrl($node->menu_route);
            if ($visible->isEmpty() && (! $url || ! in_array((string) $node->menu_id, $allowed, true))) {
                return null;
            }
            // Never mutate the models stored in the sidebar's per-user cache.
            $copy = clone $node;
            $copy->setRelation('children', $visible);
            $copy->setAttribute('_sidebar_url', $url);

            return $copy;
        };

        return $roots->map(fn ($root) => $filter($root))->filter()->values();
    }
}
