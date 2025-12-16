<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Tickers
        $this->call([
            TickerSeeder::class,
        ]);  
       
        // Call UserSeeder
        $this->call([
            UserSeeder::class,
        ]);

        // Add deposits
        $this->call([
            UserDepositSeeder::class,
        ]);

        //Assets
        $this->call([
            AssetSeeder::class,
        ]);        
    }
}
