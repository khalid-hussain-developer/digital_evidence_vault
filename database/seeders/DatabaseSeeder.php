<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * This creates one demo account per role so every role can be logged
     * into and tested locally. These are development fixtures only — they
     * are never created outside of `php artisan db:seed` and must not be
     * relied on in production.
     */
    public function run(): void
    {
        $demoAccounts = [
            ['name' => 'Khalid Admin', 'role' => UserRole::Admin],
            ['name' => 'Roshan Investigator', 'role' => UserRole::Investigator],
            ['name' => 'Farhan Forensic', 'role' => UserRole::ForensicOfficer],
            ['name' => 'Raza Reviewer', 'role' => UserRole::Reviewer],
        ];

        foreach ($demoAccounts as $account) {
            User::factory()->create([
                'name' => $account['name'],
                'email' => $account['role']->value.'@demo.test',
                'role' => $account['role'],
            ]);
        }
    }
}
