<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'abbreviation' => 'PCS'],
            ['name' => 'Set', 'abbreviation' => 'SET'],
            ['name' => 'Pair', 'abbreviation' => 'PR'],
            ['name' => 'Litre', 'abbreviation' => 'LTR'],
            ['name' => 'Kilogram', 'abbreviation' => 'KG'],
            ['name' => 'Box', 'abbreviation' => 'BOX'],
            ['name' => 'Carton', 'abbreviation' => 'CTN'],
        ];

        foreach ($units as $data) {
            Unit::updateOrCreate(
                ['name' => $data['name']],
                ['abbreviation' => $data['abbreviation'], 'is_active' => true]
            );
        }
    }
}
