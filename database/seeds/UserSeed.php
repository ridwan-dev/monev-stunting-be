<?php

use Illuminate\Database\Seeder;
use App\Models\Sys\User;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@email.dev',
            'password' => bcrypt('12345')
        ]);
        $user->assignRole('administrator');

        $user = User::create([
            'name' => 'User 1',
            'email' => 'user1@email.dev',
            'password' => bcrypt('12345')
        ]);
        $user->assignRole('administrator');

        $user = User::create([
            'name' => 'User 2',
            'email' => 'user2@email.dev',
            'password' => bcrypt('12345')
        ]);
        $user->assignRole('administrator');

    }
}
