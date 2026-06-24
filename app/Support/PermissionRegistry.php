<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PermissionRegistry
{
    /**
     * @return list<string>
     */
    public static function allNames(): array
    {
        $names = [];

        foreach (config('permissions.groups', []) as $group) {
            foreach ($group['permissions'] ?? [] as $name => $label) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @return Collection<string, Collection<int, array{name: string, label: string}>>
     */
    public static function grouped(): Collection
    {
        return collect(config('permissions.groups', []))
            ->map(function (array $group, string $key) {
                $items = collect($group['permissions'] ?? [])
                    ->map(fn (string $label, string $name) => ['name' => $name, 'label' => $label])
                    ->values();

                return [
                    'key' => $key,
                    'label' => $group['label'] ?? ucfirst(str_replace('-', ' ', $key)),
                    'permissions' => $items,
                ];
            })
            ->values()
            ->keyBy('key')
            ->map(fn (array $group) => collect($group['permissions']));
    }

    /**
     * @return Collection<string, array{key: string, label: string, permissions: Collection}>
     */
    public static function groupedWithMeta(): Collection
    {
        return collect(config('permissions.groups', []))
            ->map(function (array $group, string $key) {
                $permissions = collect($group['permissions'] ?? [])
                    ->map(fn (string $label, string $name) => ['name' => $name, 'label' => $label])
                    ->values();

                return [
                    'key' => $key,
                    'label' => $group['label'] ?? ucfirst(str_replace('-', ' ', $key)),
                    'permissions' => $permissions,
                ];
            });
    }

    public static function label(string $permission): string
    {
        foreach (config('permissions.groups', []) as $group) {
            if (isset($group['permissions'][$permission])) {
                return $group['permissions'][$permission];
            }
        }

        return $permission;
    }

    /**
     * @return list<string>
     */
    public static function defaultsForRole(string $roleName): array
    {
        $defaults = config("permissions.role_defaults.{$roleName}");

        if ($defaults === '*') {
            return self::allNames();
        }

        return is_array($defaults) ? $defaults : [];
    }
}
