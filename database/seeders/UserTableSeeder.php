<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'email' => 'teste@teste.com',
            'password' => '$2a$12$gmpLDpQQTvPTV1gEjDBf9uSEp5zSOsYrEcWvxezWkEILI0dwJOACi'
        ]);
    }
}
