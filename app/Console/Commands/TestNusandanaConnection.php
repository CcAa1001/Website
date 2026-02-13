<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentGateway\NusandanaGateway;

class TestNusandanaConnection extends Command
{
    protected $signature = 'nusandana:test {action=all}';
    protected $description = 'Test Nusandana payment gateway connection';

    public function handle()
    {
        $this->info('🧪 Testing Nusandana Payment Gateway');
        $this->newLine();

        $action = $this->argument('action');

        try {
            $gateway = new NusandanaGateway();

            switch ($action) {
                case 'balance':
                    $this->testBalance($gateway);
                    break;
                
                case 'payment':
                    $this->testPayment($gateway);
                    break;
                
                case 'all':
                default:
                    $this->testBalance($gateway);
                    $this->newLine();
                    $this->testPayment($gateway);
                    break;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

protected function testBalance($gateway)
{
    $this->info('📊 Test 1: Query Balance');
    $this->line('Testing API connection and authentication...');
    $this->newLine();

    $result = $gateway->queryBalance();

    // Show the full result for debugging
    $this->line('Full Response:');
    $this->line(json_encode($result, JSON_PRETTY_PRINT));
    $this->newLine();

    if ($result['success']) {
        $this->info('✅ SUCCESS! Connection working!');
        $this->line('  Merchant No: ' . $result['merchant_no']);
        $this->line('  Balance: Rp ' . number_format($result['balance'], 0, ',', '.'));
        $this->line('  Message: ' . $result['message']);
    } else {
        $this->error('❌ FAILED');
        $this->line('  Error: ' . $result['message']);
        if (isset($result['code'])) {
            $this->line('  Code: ' . $result['code']);
        }
        
        // Show all data for debugging
        if (isset($result['raw_response'])) {
            $this->newLine();
            $this->warn('Raw API Response:');
            $this->line(json_encode($result['raw_response'], JSON_PRETTY_PRINT));
        }
    }
}

    protected function testPayment($gateway)
    {
        $this->info('💳 Test 2: Create Test Payment');
        $this->line('Creating a test payment of Rp 10,000...');
        $this->newLine();

        $testOrderNo = 'TEST' . date('YmdHis');

        $result = $gateway->createPayment([
            'order_no' => $testOrderNo,
            'amount' => 10000,
            'payment_method' => 'qrcode',
            'callback_url' => url('/webhook/nusandana/payment'),
        ]);

        if ($result['success']) {
            $this->info('✅ SUCCESS! Payment created!');
            $this->line('  Order No: ' . $testOrderNo);
            $this->line('  Platform Order: ' . $result['platform_order_no']);
            $this->line('  Amount: Rp ' . number_format($result['amount'], 0, ',', '.'));
            
            if (!empty($result['payment_url'])) {
                $this->line('  Payment URL: ' . $result['payment_url']);
            }
            
            if (!empty($result['qr_code'])) {
                $this->line('  QR Code: [Generated]');
            }

            $this->newLine();
            $this->warn('⚠️  This is a REAL test payment!');

        } else {
            $this->error('❌ FAILED');
            $this->line('  Error: ' . $result['message']);
            if (isset($result['code'])) {
                $this->line('  Code: ' . $result['code']);
            }
        }
    }
}