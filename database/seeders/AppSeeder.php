<?php

use Illuminate\Database\Seeder;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([

            'name' => 'administrador',

            'email' => 'admin@ch.com',

            'status_id' => 1,

            'password' => \Illuminate\Support\Facades\Hash::make('1234')
        ]);
    }
}
