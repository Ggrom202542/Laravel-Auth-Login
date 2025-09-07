<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TestTwoFactor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:two-factor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Two-Factor Authentication system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Two-Factor Authentication System...');
        $this->newLine();

        // Test 1: Check Google2FA class
        $this->info('1. Google2FA Library:');
        try {
            $google2fa = new Google2FA();
            $secretKey = $google2fa->generateSecretKey();
            $this->info("   ✅ Google2FA can generate secret key: " . substr($secretKey, 0, 10) . '...');
        } catch (\Exception $e) {
            $this->error("   ❌ Google2FA error: " . $e->getMessage());
            return;
        }

        // Test 2: Check User model methods
        $this->info('2. User Model 2FA Methods:');
        $user = User::first();
        if ($user) {
            $methods = [
                'hasTwoFactorEnabled',
                'enableTwoFactor',
                'disableTwoFactor',
                'generateTwoFactorSecret',
                'getTwoFactorQrCodeUrl',
                'verifyTwoFactorCode',
                'generateRecoveryCodes',
                'regenerateRecoveryCodes',
                'verifyRecoveryCode'
            ];

            foreach ($methods as $method) {
                if (method_exists($user, $method)) {
                    $this->info("   ✅ Method exists: {$method}()");
                } else {
                    $this->error("   ❌ Method missing: {$method}()");
                }
            }
        } else {
            $this->error('   ❌ No users found in database');
            return;
        }

        // Test 3: Database columns
        $this->info('3. Database Schema:');
        try {
            $columns = DB::getSchemaBuilder()->getColumnListing('users');
            $requiredColumns = [
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at'
            ];

            foreach ($requiredColumns as $column) {
                if (in_array($column, $columns)) {
                    $this->info("   ✅ Column exists: {$column}");
                } else {
                    $this->error("   ❌ Column missing: {$column}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Database error: " . $e->getMessage());
        }

        // Test 4: Routes
        $this->info('4. 2FA Routes:');
        $routes = [
            '2fa.setup',
            '2fa.enable',
            '2fa.confirm',
            '2fa.disable',
            '2fa.challenge',
            '2fa.verify',
            '2fa.recovery',
            '2fa.recovery.verify',
            '2fa.recovery.generate'
        ];

        foreach ($routes as $routeName) {
            try {
                $route = route($routeName);
                $this->info("   ✅ Route exists: {$routeName} -> {$route}");
            } catch (\Exception $e) {
                $this->error("   ❌ Route missing: {$routeName}");
            }
        }

        // Test 5: Controller methods
        $this->info('5. TwoFactorController Methods:');
        $controller = new \App\Http\Controllers\Auth\TwoFactorController();
        $methods = [
            'setup',
            'enable',
            'confirm',
            'disable',
            'challenge',
            'verify',
            'recoveryForm',
            'verifyRecovery',
            'generateRecoveryCodes'
        ];

        foreach ($methods as $method) {
            if (method_exists($controller, $method)) {
                $this->info("   ✅ Controller method: {$method}()");
            } else {
                $this->error("   ❌ Controller method missing: {$method}()");
            }
        }

        // Test 6: Test actual functionality
        $this->info('6. Functional Tests:');
        
        // Find a user for testing
        /** @var User $testUser */
        $testUser = User::where('role', '!=', 'super_admin')->first();
        
        if ($testUser) {
            $this->info("   Testing with user: {$testUser->username}");
            
            // Test enabling 2FA
            if (!$testUser->hasTwoFactorEnabled()) {
                $this->info('   ✅ 2FA is currently disabled');
                
                try {
                    $secret = $testUser->generateTwoFactorSecret();
                    $this->info("   ✅ Generated secret key: " . substr($secret, 0, 10) . '...');
                    
                    $qrUrl = $testUser->getTwoFactorQrCodeUrl();
                    $this->info("   ✅ QR Code URL generated successfully");
                    
                    // Generate recovery codes
                    $recoveryCodes = $testUser->generateRecoveryCodes();
                    $this->info("   ✅ Generated " . count($recoveryCodes) . " recovery codes");
                    
                } catch (\Exception $e) {
                    $this->error("   ❌ Error testing 2FA: " . $e->getMessage());
                }
            } else {
                $this->info('   ✅ 2FA is currently enabled');
                
                try {
                    // Test recovery codes
                    $recoveryCodes = $testUser->regenerateRecoveryCodes();
                    $this->info("   ✅ Regenerated " . count($recoveryCodes) . " recovery codes");
                    
                } catch (\Exception $e) {
                    $this->error("   ❌ Error testing recovery codes: " . $e->getMessage());
                }
            }
        } else {
            $this->error('   ❌ No suitable test user found');
        }

        $this->newLine();
        $this->info('🎉 Two-Factor Authentication System Test Complete!');
        
        $this->newLine();
        $this->info('📋 Manual Testing Steps:');
        $this->info('1. Login to user dashboard');
        $this->info('2. Go to /user/dashboard');
        $this->info('3. Click "เปิดใช้งาน 2FA" button');
        $this->info('4. Scan QR code with authenticator app');
        $this->info('5. Enter verification code to confirm');
        $this->info('6. Test backup codes');
        $this->info('7. Try logging out and back in with 2FA');
    }
}
