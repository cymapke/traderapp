<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use App\Models\Ticker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing assets first
        Asset::query()->delete();

        // Get users (create if they don't exist)
        $users = User::take(3)->get();
        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        // Get all tickers (must exist first)
        $tickers = Ticker::all();

        if ($tickers->isEmpty()) {
            $this->command->error('No tickers found. Please run TickerSeeder first.');
            return;
        }

        $assetsCreated = 0;
        
        // Create assets for each user
        foreach ($users as $user) {
            // Give each user 3-5 random assets
            $randomTickers = $tickers->random(rand(3, 5));
            
            foreach ($randomTickers as $ticker) {
                $amount = $this->generateRandomCryptoAmount($ticker->symbol);
                $lockedAmount = rand(0, 1) ? bcdiv($amount, rand(2, 10), 18) : '0';
                
                Asset::create([
                    'user_id' => $user->id,
                    'ticker_id' => $ticker->id,
                    'amount' => $amount,
                    'locked_amount' => $lockedAmount,
                ]);
                
                $assetsCreated++;
                $this->command->info("Created asset for {$user->name}: {$ticker->symbol} - Amount: {$amount}, Locked: {$lockedAmount}");
            }
        }

        $this->command->info("Successfully seeded {$assetsCreated} assets!");
    }

    /**
     * Generate realistic crypto amounts based on symbol.
     */
    private function generateRandomCryptoAmount(string $symbol): string
    {
        // Different ranges for different cryptocurrencies
        $ranges = [
            'BTC' => ['0.001', '5'],        // Bitcoin: 0.001 to 5 BTC
            'ETH' => ['0.01', '50'],        // Ethereum: 0.01 to 50 ETH
            'XRP' => ['10', '50000'],       // Ripple: 10 to 50,000 XRP
            'BNB' => ['0.1', '100'],        // BNB: 0.1 to 100 BNB
            'ADA' => ['10', '100000'],      // Cardano: 10 to 100,000 ADA
            'SOL' => ['0.1', '100'],        // Solana: 0.1 to 100 SOL
            'DOT' => ['1', '1000'],         // Polkadot: 1 to 1000 DOT
            'DOGE' => ['100', '1000000'],   // Dogecoin: 100 to 1,000,000 DOGE
            'AVAX' => ['0.1', '100'],       // Avalanche: 0.1 to 100 AVAX
            'LTC' => ['0.1', '100'],        // Litecoin: 0.1 to 100 LTC
            'MATIC' => ['10', '10000'],     // Polygon: 10 to 10,000 MATIC
            'ATOM' => ['1', '500'],         // Cosmos: 1 to 500 ATOM
            'LINK' => ['0.1', '100'],       // Chainlink: 0.1 to 100 LINK
            'UNI' => ['0.1', '100'],        // Uniswap: 0.1 to 100 UNI
            'XLM' => ['10', '50000'],       // Stellar: 10 to 50,000 XLM
            'ETC' => ['0.1', '100'],        // Ethereum Classic: 0.1 to 100 ETC
            'ALGO' => ['10', '50000'],      // Algorand: 10 to 50,000 ALGO
            'VET' => ['100', '100000'],     // VeChain: 100 to 100,000 VET
            'XTZ' => ['1', '5000'],         // Tezos: 1 to 5,000 XTZ
            'FIL' => ['0.1', '100'],        // Filecoin: 0.1 to 100 FIL
        ];

        if (isset($ranges[$symbol])) {
            $min = $ranges[$symbol][0];
            $max = $ranges[$symbol][1];
            // Generate random float between min and max with 8 decimal places
            $random = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
            return number_format($random, 8, '.', '');
        }

        // Default range for any other ticker
        $random = 0.1 + (mt_rand() / mt_getrandmax()) * (1000 - 0.1);
        return number_format($random, 8, '.', '');
    }
}
