<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'administrador',
            'email' => 'admin@ch.com',
            'status_id' => 1,
            'password' => Hash::make('1234')
        ]);
    }
}
