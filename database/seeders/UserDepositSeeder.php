<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first 3 users (or create them if they don't exist)
        $users = User::take(3)->get();
        
        // If there are less than 3 users, create them
        if ($users->count() < 3) {
            $users = User::factory()->count(3)->create();
        }
        
        // Simulate deposits for each user
        foreach ($users as $user) {
            // Generate random deposit amount between 5000 and 100000 dollars
            $depositAmount = rand(500000, 10000000) / 100; // Convert to dollars with 2 decimals
            // Or more simply: random float between 5000 and 100000
            $depositAmount = mt_rand(5000 * 100, 100000 * 100) / 100;
            
            // Add the deposit to user's balance
            $user->addMoney($depositAmount);
            
            $this->command->info("User {$user->name} (ID: {$user->id}) deposited: $" . number_format($depositAmount, 2));
            $this->command->info("New balance: $" . number_format($user->balance / 100, 2));
        }
        
        $this->command->info('Deposits completed successfully!');
    }
}
