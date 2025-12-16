<?php

namespace Database\Seeders;

use App\Models\Ticker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TickerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ticker::query()->delete();

        // 20 Cryptocurrencies (including XRP)
        $cryptocurrencies = [
            'BTC',   // Bitcoin
            'ETH',   // Ethereum
            'XRP',   // Ripple (as requested)
            'BNB',   // Binance Coin
            'ADA',   // Cardano
            'SOL',   // Solana
            'DOT',   // Polkadot
            'DOGE',  // Dogecoin
            'AVAX',  // Avalanche
            'LTC',   // Litecoin
            'MATIC', // Polygon
            'ATOM',  // Cosmos
            'LINK',  // Chainlink
            'UNI',   // Uniswap
            'XLM',   // Stellar
            'ETC',   // Ethereum Classic
            'ALGO',  // Algorand
            'VET',   // VeChain
            'XTZ',   // Tezos
            'FIL',   // Filecoin
        ];

        // Insert all cryptocurrencies
        foreach ($cryptocurrencies as $symbol) {
            Ticker::create([
                'symbol' => $symbol,
                'type' => 'crypto',
            ]);
        }

        // Count verification
        $count = Ticker::count();
        $xrpExists = Ticker::where('symbol', 'XRP')->where('type', 'crypto')->exists();
        
        if ($count === 20 && $xrpExists) {
            $this->command->info('✓ Successfully seeded 20 cryptocurrencies!');
            $this->command->info('✓ Ripple (XRP) is included.');
            $this->command->info('✓ Total tickers: ' . $count);
        } else {
            $this->command->error('✗ Seeding failed!');
            $this->command->error('  Expected: 20 tickers, Got: ' . $count);
            $this->command->error('  XRP exists: ' . ($xrpExists ? 'Yes' : 'No'));
        }

        // Display all seeded tickers
        $this->command->table(
            ['#', 'Symbol', 'Type'],
            Ticker::select('id', 'symbol', 'type')
                  ->orderBy('id')
                  ->get()
                  ->map(function($ticker, $index) {
                      return [
                          $index + 1,
                          $ticker->symbol,
                          $ticker->type_name
                      ];
                  })->toArray()
        );
    }
}
