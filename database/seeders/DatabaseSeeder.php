<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['administrator' => 'Administrateur', 'secretary' => 'Secrétaire', 'finance' => 'Finance', 'logistics' => 'Logistique', 'event-coordinator' => 'Coordinateur d’événements', 'reviewer' => 'Lecteur'] as $slug => $name) {
            Role::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
        $admin = User::firstOrCreate(['email' => env('INITIAL_ADMIN_EMAIL', 'admin@example.test')], [
            'name' => env('INITIAL_ADMIN_NAME', 'Administrateur'),
            'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'ChangeMeBeforeLaunch!')),
            'preferred_locale' => 'fr', 'is_active' => true,
        ]);
        $admin->roles()->syncWithoutDetaching([Role::where('slug', 'administrator')->value('id')]);
    }
}
