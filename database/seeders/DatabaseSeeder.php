<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PermissionSeeder::class,
        ]);

        function getRandomRole()
        {
            $randomNumber = rand(0, 99);
            if ($randomNumber < 5) {
                return 'Super Admin';
            } elseif ($randomNumber < 15) {
                return 'Admin';
            } else {
                return 'User';
            }
        }

        User::factory(100)->create()->after(function ($user) {
            $user->assignRole(getRandomRole());
        });


        $user = User::factory()->create([
            'firstname' => 'nassim',
            'lastname' => 'sadi',
            'email' => 'nacimbreeze@gmail.com',
            'password' => 'password',
            'image' => null,
            'status' => '1',
        ]);

        $user->assignRole('Super Admin');

        $this->call([
            CategorySeeder::class,
            ProductOptionSeeder::class,
        ]);
    }
}
