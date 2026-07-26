<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Pengguna::factory()->create([
            'name' => 'Admin Batam Pos',
            'email' => 'budi@batampos.co.id',
            'password' => 'password',
        ]);
    }
}
