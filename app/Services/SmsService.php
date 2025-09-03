<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    private $provider;
    private $config;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'log'); // log, twilio, aws, thai_bulk_sms
        $this->config = config('services.sms');
    }

    /**
     * Send SMS message
     */
    public function send(string $phoneNumber, string $message): bool
    {
        try {
            // Clean phone number
            $phoneNumber = $this->cleanPhoneNumber($phoneNumber);
            
            // Validate phone number
            if (!$this->isValidPhoneNumber($phoneNumber)) {
                throw new \Exception("Invalid phone number: {$phoneNumber}");
            }

            // Send based on provider
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendViaTwilio($phoneNumber, $message);
                    
                case 'aws':
                    return $this->sendViaAwsSns($phoneNumber, $message);
                    
                case 'thai_bulk_sms':
                    return $this->sendViaThaiProvider($phoneNumber, $message);
                    
                case 'log':
                default:
                    return $this->logSms($phoneNumber, $message);
            }
            
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'provider' => $this->provider
            ]);
            
            return false;
        }
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Convert Thai format to international format
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '0') {
            // 08xxxxxxxx -> 668xxxxxxxx
            $cleaned = '66' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }

    /**
     * Validate Thai phone number
     */
    private function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Thai mobile number patterns
        $patterns = [
            '/^66[689]\d{8}$/', // International format: 668xxxxxxxx, 669xxxxxxxx, 666xxxxxxxx
            '/^0[689]\d{8}$/'   // Local format: 08xxxxxxxx, 09xxxxxxxx, 06xxxxxxxx
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phoneNumber)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Send via Twilio (example implementation)
     */
    private function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        try {
            $sid = $this->config['twilio']['sid'];
            $token = $this->config['twilio']['token'];
            $from = $this->config['twilio']['from'];
            
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => '+' . $phoneNumber,
                    'Body' => $message
                ]);
                
            if ($response->successful()) {
                Log::info('SMS sent via Twilio', [
                    'to' => $phoneNumber,
                    'status' => 'success',
                    'message_id' => $response->json('sid')
                ]);
                return true;
            }
            
            throw new \Exception('Twilio API error: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send via AWS SNS
     */
    private function sendViaAwsSns(string $phoneNumber, string $message): bool
    {
        try {
            // AWS SNS implementation would go here
            // This is a placeholder for actual AWS SNS integration
            
            Log::info('SMS would be sent via AWS SNS', [
                'to' => $phoneNumber,
                'message_length' => strlen($message)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('AWS SNS SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send via Thai SMS Provider (example for local providers)
     */
    private function sendViaThaiProvider(string $phoneNumber, string $message): bool
    {
        try {
            $apiKey = $this->config['thai_provider']['api_key'];
            $sender = $this->config['thai_provider']['sender'];
            $url = $this->config['thai_provider']['url'];
            
            $response = Http::post($url, [
                'api_key' => $apiKey,
                'sender' => $sender,
                'msisdn' => $phoneNumber,
                'message' => $message
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success') {
                    Log::info('SMS sent via Thai Provider', [
                        'to' => $phoneNumber,
                        'status' => 'success'
                    ]);
                    return true;
                }
            }
            
            throw new \Exception('Thai Provider API error: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Thai Provider SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Log SMS (for development/testing)
     */
    private function logSms(string $phoneNumber, string $message): bool
    {
        Log::info('SMS Message (Development Mode)', [
            'to' => $phoneNumber,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'note' => 'This SMS was logged for development. In production, it would be sent via real SMS provider.'
        ]);
        
        return true;
    }

    /**
     * Get SMS sending statistics
     */
    public function getStats(): array
    {
        // This could be expanded to track SMS statistics
        return [
            'provider' => $this->provider,
            'enabled' => $this->provider !== 'log'
        ];
    }

    /**
     * Validate SMS configuration
     */
    public function isConfigured(): bool
    {
        switch ($this->provider) {
            case 'twilio':
                return !empty($this->config['twilio']['sid']) && 
                       !empty($this->config['twilio']['token']);
                       
            case 'aws':
                return !empty($this->config['aws']['region']) && 
                       !empty($this->config['aws']['key']);
                       
            case 'thai_bulk_sms':
                return !empty($this->config['thai_provider']['api_key']);
                
            case 'log':
            default:
                return true;
        }
    }
}
