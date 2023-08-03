<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Manchester City',
            'Arsenal',
            'Manchester United',
            'Newcastle United',
        ];

        foreach ($names as $name) {
            Team::updateOrCreate(['name' => $name]);
        }
    }
}
