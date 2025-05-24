<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminRole = Role::create(['name' => 'admin']);
        $atelierRole = Role::create(['name' => 'service atelier']);
        $magasinRole = Role::create(['name' => 'service magasin']);
        $achatRole = Role::create(['name' => 'service achat']);
        $coordinationRole = Role::create(['name' => 'service cf']);
        $paimentRole = Role::create(['name' => 'service paiment']);

        $editPostPermission = Permission::create(['name' => 'edit-post']);

        $admin = User::factory()->create([
            'name' => 'Hamid',
            'email' => 'hamidpro198@gmail.com',
            'password' => 'alemalem'
        ]);
        $atelier = User::factory()->create([
            'name' => 'Atelier User',
            'email' => 'atelier@example.com',
        ]);
        $atelier->assignRole($atelierRole);
        $admin->assignRole($adminRole);

        $editor = User::factory()->create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
        ]);
        $editor->givePermissionTo($editPostPermission);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' =>'alemalem',
        ]);

        DB::statement('UPDATE centres SET montant_disponible = seuil_centre');
    }
}
