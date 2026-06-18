<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Engine Parts' => [
                'Filters',
                'Belts & Hoses',
                'Gaskets',
            ],
            'Body Parts' => [
                'Bumpers',
                'Mirrors',
                'Lights',
            ],
            'Suspension' => [
                'Shock Absorbers',
                'Springs',
            ],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['name' => $parentName, 'parent_id' => null],
                ['is_active' => true]
            );

            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id],
                    ['is_active' => true]
                );
            }
        }
    }
}
