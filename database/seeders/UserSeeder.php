<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->delete();
        
        // Create first user
        User::create([
            'name' => 'Dusan Vlahovic',
            'email' => 'dusanv@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
        ]);
        
        // Create second user
        User::create([
            'name' => 'Sarah Connor',
            'email' => 'sarah@bigjeffs.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
        ]);
        
        // Create admin user
        User::create([
            'name' => 'Aaron Ramsey',
            'email' => 'aaron@arsenal.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
        ]);
        
        $this->command->info('Users created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info(' - Dusan Vlahovic: dusanv@gmail.com / password123');
        $this->command->info(' - Sarah Connor: sarah@bigjeffs.com / password123');
        $this->command->info(' - Aaron Ramsey: aaron@arsenal.com / password123');
    }
}
