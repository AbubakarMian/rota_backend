<?php

use Illuminate\Database\Seeder;
use App\User;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            ['email'    => 'admin@mail.com'],
            [
                'name'   => 'admin',
                'role_id'   => '1',
                'password'   =>  Hash::make('abc123'),
            ]
        );
    }
}

