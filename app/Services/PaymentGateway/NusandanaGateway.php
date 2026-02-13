<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NusandanaGateway
{
    protected string $baseUrl;
    protected string $merchantNo;
    protected string $signatureKey;

    public function __construct()
    {
    // FIXED: Use config() first (set by Cart.php), fallback to env()
    $this->baseUrl = config('services.nusandana.api_base_url', 
                            env('NUSANDANA_API_BASE_URL', 'https://api.nusandana.co.id'));
    
    $this->merchantNo = config('services.nusandana.merchant_no', 
                               env('NUSANDANA_MERCHANT_NO'));
    
    $this->signatureKey = config('services.nusandana.signature_key', 
                                 env('NUSANDANA_SIGNATURE_KEY'));

    if (!$this->merchantNo || !$this->signatureKey) {
        throw new \Exception('Nusandana credentials not configured');
    }
}

protected function generateSignature(array $params): string
{
    // Remove empty values and sign key if present
    $params = array_filter($params, fn($value) => $value !== null && $value !== '');
    unset($params['sign']); // Remove sign if it exists
    
    // Sort by key (ASCII order)
    ksort($params);
    
    // Build query string WITHOUT URL encoding (Method 4)
    $parts = [];
    foreach ($params as $key => $value) {
        $parts[] = $key . '=' . $value;
    }
    $queryString = implode('&', $parts);
    
    // Append signature key
    $stringToSign = $queryString . '&key=' . $this->signatureKey;
    
    // Return MD5 hash
    return md5($stringToSign);
}

/**
 * Get timestamp in milliseconds (as integer string)
 */
protected function getTimestamp(): string
{
    // FIXED: Cast to int to remove decimal places
    return (string)(int)(microtime(true) * 1000);
}

public function queryBalance(): array
{
    $params = [
        'merchantno' => $this->merchantNo,
        'timestamp' => $this->getTimestamp(), // Use helper method
    ];

    $params['sign'] = $this->generateSignature($params);

    Log::info('Nusandana Balance Query', ['params' => $params]);

    try {
        $response = Http::timeout(30)
            ->post($this->baseUrl . '/pay/account/query', $params);

        $result = $response->json();

        Log::info('Nusandana Balance Response', [
            'status_code' => $response->status(),
            'response' => $result
        ]);

        if ($response->successful() && isset($result['code']) && $result['code'] == 200) {
            return [
                'success' => true,
                'balance' => $result['data']['balance'] ?? 0,
                'merchant_no' => $result['data']['merchantno'] ?? $this->merchantNo,
                'message' => $result['msg'] ?? 'Success',
            ];
        }

        return [
            'success' => false,
            'message' => $result['msg'] ?? 'Query failed',
            'code' => $result['code'] ?? $response->status(),
            'raw_response' => $result,
        ];

    } catch (\Exception $e) {
        Log::error('Nusandana Balance Query Error', [
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Connection error: ' . $e->getMessage(),
        ];
    }
}

public function createPayment(array $data): array
{

    $amount = (int)round($data['amount']);

    $params = [
        'merchantno' => $this->merchantNo,
        'morderno' => $data['order_no'],
        'amount' => (string)$amount,
        'paycode' => $data['payment_method'] ?? 'qrcode',
        'timestamp' => $this->getTimestamp(), // Use helper method
        'callbackurl' => $data['callback_url'] ?? url('/webhook/nusandana/payment'),
        'clientip' => request()->ip() ?? '127.0.0.1',
    ];

    if (!empty($data['bank_code'])) {
        $params['bankcode'] = $data['bank_code'];
    }

    if (!empty($data['redirect_url'])) {
        $params['redirecturl'] = $data['redirect_url'];
    }

    $params['sign'] = $this->generateSignature($params);

    Log::info('Nusandana Create Payment', ['params' => $params]);

    try {
        $response = Http::timeout(30)
            ->post($this->baseUrl . '/pay/payin/create', $params);

        $result = $response->json();

        Log::info('Nusandana Payment Response', [
            'status_code' => $response->status(),
            'response' => $result
        ]);

        if ($response->successful() && isset($result['code']) && $result['code'] == 200) {
            return [
                'success' => true,
                'platform_order_no' => $result['data']['orderno'] ?? null,
                'payment_url' => $result['data']['payurl'] ?? null,
                'qr_code' => $result['data']['qrcode'] ?? null,
                'amount' => $result['data']['amount'] ?? $data['amount'],
                'message' => $result['msg'] ?? 'Payment created',
            ];
        }

        return [
            'success' => false,
            'message' => $result['msg'] ?? 'Payment creation failed',
            'code' => $result['code'] ?? $response->status(),
            'raw_response' => $result,
        ];

    } catch (\Exception $e) {
        Log::error('Nusandana Payment Error', [
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Connection error: ' . $e->getMessage(),
        ];
    }
}

public function createPayout(array $data): array
{
    $params = [
        'merchantno' => $this->merchantNo,
        'morderno' => $data['order_no'],
        'bankcode' => $data['bank_code'],
        'accountname' => $data['account_name'],
        'accountno' => $data['account_no'],
        'callbackurl' => $data['callback_url'] ?? url('/webhook/nusandana/payout'),
        'timestamp' => $this->getTimestamp(), // Use helper method
        'clientip' => request()->ip() ?? '127.0.0.1',
    ];

    if (!empty($data['branch_code'])) {
        $params['branchcode'] = $data['branch_code'];
    }

    $params['sign'] = $this->generateSignature($params);

    Log::info('Nusandana Create Payout', ['params' => $params]);

    try {
        $response = Http::timeout(30)
            ->post($this->baseUrl . '/pay/payout/create', $params);

        $result = $response->json();

        Log::info('Nusandana Payout Response', [
            'status_code' => $response->status(),
            'response' => $result
        ]);

        if ($response->successful() && isset($result['code']) && $result['code'] == 200) {
            return [
                'success' => true,
                'platform_order_no' => $result['data']['orderno'] ?? null,
                'amount' => $result['data']['amount'] ?? $data['amount'],
                'fee' => $result['data']['fee'] ?? 0,
                'message' => $result['msg'] ?? 'Payout created',
            ];
        }

        return [
            'success' => false,
            'message' => $result['msg'] ?? 'Payout creation failed',
            'code' => $result['code'] ?? $response->status(),
            'raw_response' => $result,
        ];

    } catch (\Exception $e) {
        Log::error('Nusandana Payout Error', [
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Connection error: ' . $e->getMessage(),
        ];
    }
}

public function queryPayment(string $merchantOrderNo, string $platformOrderNo = ''): array
{
    $params = [
        'merchantno' => $this->merchantNo,
        'morderno' => $merchantOrderNo,
        'orderno' => $platformOrderNo,
        'timestamp' => $this->getTimestamp(), // Use helper method
    ];

    $params['sign'] = $this->generateSignature($params);

    try {
        $response = Http::post($this->baseUrl . '/pay/payin/query', $params);
        $result = $response->json();

        if ($response->successful() && isset($result['code']) && $result['code'] == 200) {
            return [
                'success' => true,
                'data' => $result['data'],
            ];
        }

        return [
            'success' => false,
            'message' => $result['msg'] ?? 'Query failed',
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}

public function queryPayout(string $merchantOrderNo, string $platformOrderNo = ''): array
{
    $params = [
        'merchantno' => $this->merchantNo,
        'morderno' => $merchantOrderNo,
        'orderno' => $platformOrderNo,
        'timestamp' => $this->getTimestamp(), // Use helper method
    ];

    $params['sign'] = $this->generateSignature($params);

    try {
        $response = Http::post($this->baseUrl . '/pay/payout/query', $params);
        $result = $response->json();

        if ($response->successful() && isset($result['code']) && $result['code'] == 200) {
            return [
                'success' => true,
                'data' => $result['data'],
            ];
        }

        return [
            'success' => false,
            'message' => $result['msg'] ?? 'Query failed',
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}

public function verifyCallback(array $data): bool
{
    $receivedSign = $data['sign'] ?? '';
    unset($data['sign']);
    $calculatedSign = $this->generateSignature($data);
    return $calculatedSign === $receivedSign;
}
}