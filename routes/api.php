<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes (ไม่ต้อง authentication)
|--------------------------------------------------------------------------
*/

// Password Policy Configuration
Route::get('/password-policy-config', function () {
    return response()->json([
        'enabled' => config('password_policy.enabled'),
        'strength' => [
            'minLength' => config('password_policy.strength.min_length'),
            'maxLength' => config('password_policy.strength.max_length'),
            'requireUppercase' => config('password_policy.strength.require_uppercase'),
            'requireLowercase' => config('password_policy.strength.require_lowercase'),
            'requireNumbers' => config('password_policy.strength.require_numbers'),
            'requireSymbols' => config('password_policy.strength.require_symbols'),
            'minUniqueChars' => config('password_policy.strength.min_unique_chars'),
            'allowedSymbols' => config('password_policy.strength.allowed_symbols'),
        ],
        'history' => [
            'enabled' => config('password_policy.history.enabled'),
            'count' => config('password_policy.history.count'),
            'checkSimilarity' => config('password_policy.history.check_similarity'),
        ],
        'messages' => config('password_policy.messages'),
        'strengthLabels' => config('password_policy.strength_labels'),
        'strengthColors' => config('password_policy.strength_colors'),
    ]);
})->name('api.password-policy.config');

/*
|--------------------------------------------------------------------------
| Protected API Routes (ต้อง authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Password Strength Validation
    Route::post('/password/validate', function (Request $request) {
        $request->validate([
            'password' => 'required|string'
        ]);

        $password = $request->password;
        $userId = $request->user_id; // สำหรับตรวจสอบ history

        // ใช้ Password Policy Rule
        $policyRule = new \App\Rules\PasswordPolicy();
        $historyRule = $userId ? new \App\Rules\PasswordHistoryRule($userId) : null;

        $errors = [];
        
        // ตรวจสอบ policy
        $policyRule->validate('password', $password, function($message) use (&$errors) {
            $errors[] = $message;
        });

        // ตรวจสอบ history (ถ้ามี user_id)
        if ($historyRule) {
            $historyRule->validate('password', $password, function($message) use (&$errors) {
                $errors[] = $message;
            });
        }

        // คำนวณ strength score
        $strengthMeter = new \App\Services\PasswordStrengthService();
        $analysis = $strengthMeter->analyze($password);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $analysis,
        ]);
    })->name('api.password.validate');
});
