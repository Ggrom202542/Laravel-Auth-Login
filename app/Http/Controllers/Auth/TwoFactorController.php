<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Models\User;

class TwoFactorController extends Controller
{
    /**
     * แสดงหน้าตั้งค่า Two-Factor Authentication
     */
    public function setup()
    {
        /** @var User $user */
        $user = Auth::user();
        $data = [];
        
        // ถ้า 2FA ยังไม่เปิดใช้งาน หรือยังไม่ยืนยัน
        if (!$user->hasTwoFactorEnabled() || !$user->hasTwoFactorConfirmed()) {
            // สร้าง secret key ใหม่ถ้ายังไม่มี
            if (!$user->hasTwoFactorSecret()) {
                $google2fa = new Google2FA();
                $user->google2fa_secret = $google2fa->generateSecretKey();
                $user->save();
            }

            // สร้าง QR Code URL
            $google2fa = new Google2FA();
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->google2fa_secret
            );

            // สร้าง QR Code image
            $data['qrCode'] = $this->generateQrCode($qrCodeUrl);
            $data['secretKey'] = $user->google2fa_secret;
        }

        return view('auth.2fa.setup', $data);
    }

    /**
     * เปิดใช้งาน 2FA - สร้าง secret และ QR code
     */
    public function enable(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // สร้าง secret key ใหม่
        $google2fa = new Google2FA();
        $user->google2fa_secret = $google2fa->generateSecretKey();
        $user->save();

        // สร้าง QR Code URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        // สร้าง QR Code image
        $qrCode = $this->generateQrCode($qrCodeUrl);

        Log::info('2FA Secret สร้างใหม่แล้ว', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        return view('auth.2fa.setup', [
            'qrCode' => $qrCode,
            'secretKey' => $user->google2fa_secret
        ])->with('success', 'กรุณาสแกน QR Code ด้วยแอป Authenticator ของคุณ');
    }

    /**
     * ยืนยันการตั้งค่า 2FA ด้วยรหัสยืนยัน
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ], [
            'code.required' => 'กรุณากรอกรหัสยืนยัน 6 หลัก',
            'code.digits' => 'รหัสยืนยันต้องเป็นตัวเลข 6 หลัก'
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        // ตรวจสอบว่ามี secret key หรือไม่
        if (!$user->hasTwoFactorSecret()) {
            return back()->withErrors(['code' => 'ไม่พบข้อมูล 2FA กรุณาเริ่มการตั้งค่าใหม่']);
        }

        // ตรวจสอบรหัสยืนยัน
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'รหัสยืนยันไม่ถูกต้อง กรุณาลองใหม่']);
        }

        // เปิดใช้งาน 2FA
        $user->update([
            'google2fa_enabled' => true,
            'google2fa_confirmed_at' => now()
        ]);

        // สร้าง Recovery Codes
        $recoveryCodes = $user->generateRecoveryCodes();

        Log::info('เปิดใช้งาน Two-Factor Authentication สำเร็จ', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        return redirect()->route('2fa.recovery')->with([
            'recoveryCodes' => $recoveryCodes,
            'success' => 'เปิดใช้งาน Two-Factor Authentication สำเร็จ! กรุณาเก็บรหัสกู้คืนไว้ในที่ปลอดภัย'
        ]);
    }

    /**
     * ปิดการใช้งาน 2FA
     */
    public function disable(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // ปิดการใช้งาน 2FA
        $user->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
            'google2fa_confirmed_at' => null,
            'recovery_codes' => null,
            'recovery_codes_generated_at' => null
        ]);

        Log::info('ปิดการใช้งาน Two-Factor Authentication', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        return redirect()->route('2fa.setup')->with('success', 'ปิดการใช้งาน Two-Factor Authentication สำเร็จ');
    }

    /**
     * แสดงหน้าตรวจสอบ 2FA สำหรับการเข้าสู่ระบบ
     */
    public function challenge()
    {
        return view('auth.2fa.challenge');
    }

    /**
     * ตรวจสอบรหัส 2FA ขณะเข้าสู่ระบบ
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ], [
            'code.required' => 'กรุณากรอกรหัสยืนยัน 6 หลัก',
            'code.digits' => 'รหัสยืนยันต้องเป็นตัวเลข 6 หลัก'
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        // ตรวจสอบว่าผู้ใช้เปิดใช้งาน 2FA หรือไม่
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('login')->withErrors(['email' => 'บัญชีนี้ไม่ได้เปิดใช้งาน Two-Factor Authentication']);
        }

        // ตรวจสอบรหัสยืนยัน
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'รหัสยืนยันไม่ถูกต้อง กรุณาลองใหม่']);
        }

        // บันทึกสถานะว่าผ่านการตรวจสอบ 2FA แล้ว
        session(['2fa_verified' => true]);

        Log::info('ผ่านการตรวจสอบ Two-Factor Authentication', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * แสดงหน้ากู้คืนบัญชีด้วย Recovery Code
     */
    public function recoveryForm()
    {
        return view('auth.2fa.recovery');
    }

    /**
     * ตรวจสอบรหัสกู้คืน (Recovery Code)
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string|size:8'
        ], [
            'recovery_code.required' => 'กรุณากรอกรหัสกู้คืน',
            'recovery_code.size' => 'รหัสกู้คืนต้องมี 8 ตัวอักษร'
        ]);

        /** @var User $user */
        $user = Auth::user();
        $recoveryCode = strtoupper($request->recovery_code);

        // ใช้รหัสกู้คืน
        if (!$user->useRecoveryCode($recoveryCode)) {
            return back()->withErrors(['recovery_code' => 'รหัสกู้คืนไม่ถูกต้องหรือถูกใช้ไปแล้ว']);
        }

        // บันทึกสถานะว่าผ่านการตรวจสอบ 2FA แล้ว
        session(['2fa_verified' => true]);

        Log::warning('ใช้รหัสกู้คืน Two-Factor Authentication', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'remaining_codes' => count($user->recovery_codes ?? []),
            'ip' => $request->ip()
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('warning', 'เข้าสู่ระบบด้วยรหัสกู้คืนสำเร็จ กรุณาตรวจสอบความปลอดภัยของบัญชี');
    }

    /**
     * สร้างรหัสกู้คืนใหม่
     */
    public function generateRecoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // ตรวจสอบว่าผู้ใช้เปิดใช้งาน 2FA หรือไม่
        if (!$user->hasTwoFactorEnabled()) {
            return back()->withErrors(['error' => 'กรุณาเปิดใช้งาน Two-Factor Authentication ก่อน']);
        }

        // สร้างรหัสกู้คืนใหม่
        $recoveryCodes = $user->generateRecoveryCodes();

        Log::info('สร้างรหัสกู้คืน Two-Factor Authentication ใหม่', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        return view('auth.2fa.recovery', [
            'recoveryCodes' => $recoveryCodes,
            'success' => 'สร้างรหัสกู้คืนใหม่สำเร็จ กรุณาเก็บรหัสเหล่านี้ไว้ในที่ปลอดภัย'
        ]);
    }

    /**
     * สร้าง QR Code สำหรับ 2FA
     */
    private function generateQrCode($url)
    {
        try {
            Log::info('เริ่มสร้าง QR Code', ['url' => $url]);
            
            // ใช้ SVG Backend เป็นหลัก (ไม่ต้องพึ่ง Google)
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($url);
            
            Log::info('QR Code SVG สร้างสำเร็จ', [
                'length' => strlen($qrCodeSvg)
            ]);
            
            // ทำให้ SVG responsive และปรับแต่ง
            $optimizedSvg = str_replace(
                ['<svg', 'width="200"', 'height="200"'],
                ['<svg style="max-width: 100%; height: auto;"', 'width="200" viewBox="0 0 200 200"', ''],
                $qrCodeSvg
            );
            
            return $optimizedSvg;
            
        } catch (\Exception $e) {
            Log::error('เกิดข้อผิดพลาดในการสร้าง QR Code SVG', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            
            // ลองใช้ PNG แทน SVG
            try {
                Log::info('ลองสร้าง QR Code ด้วย PNG');
                
                $renderer = new ImageRenderer(
                    new RendererStyle(200),
                    new ImagickImageBackEnd()
                );
                
                $writer = new Writer($renderer);
                $qrCodeData = $writer->writeString($url);
                
                // แปลง PNG เป็น base64
                $base64QrCode = 'data:image/png;base64,' . base64_encode($qrCodeData);
                
                Log::info('QR Code PNG สร้างสำเร็จ', [
                    'length' => strlen($base64QrCode)
                ]);
                
                return $base64QrCode;
                
            } catch (\Exception $fallbackError) {
                Log::error('ทั้ง SVG และ PNG ล้มเหลว', [
                    'svg_error' => $e->getMessage(),
                    'png_error' => $fallbackError->getMessage()
                ]);
                
                return null;
            }
        }
    }
}
