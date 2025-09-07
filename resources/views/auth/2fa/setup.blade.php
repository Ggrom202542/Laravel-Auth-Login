@extends('layouts.app')

@section('title', 'ตั้งค่า Two-Factor Authentication')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 p-2">
                        <i class="bi bi-shield-lock"></i>
                        ตั้งค่า Two-Factor Authentication (2FA)
                    </h5>
                </div>
                <div class="card-body">
                    @if(!auth()->user()->hasTwoFactorEnabled())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>เพิ่มความปลอดภัย:</strong> เพิ่มชั้นความปลอดภัยให้กับบัญชีของคุณด้วยการเปิดใช้งาน Two-Factor Authentication
                        </div>

                        <div class="row">
                            <div class="col-md-6 setup-step">
                                <h5><i class="bi bi-1-circle text-primary"></i> ขั้นตอนที่ 1: ดาวน์โหลดแอป Authenticator</h5>
                                <p class="text-muted">คุณจะต้องมีแอป Authenticator บนมือถือเพื่อสร้างรหัสยืนยัน:</p>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="app-option p-2 rounded bg-light text-center">
                                            <i class="bi bi-google text-danger" style="font-size: 1.5rem;"></i>
                                            <small class="d-block">Google Authenticator</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="app-option p-2 rounded bg-light text-center">
                                            <i class="bi bi-microsoft text-primary" style="font-size: 1.5rem;"></i>
                                            <small class="d-block">Microsoft Authenticator</small>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mt-4"><i class="bi bi-2-circle text-primary"></i> ขั้นตอนที่ 2: สแกน QR Code</h5>
                                <p class="text-muted">คลิกปุ่มด้านล่างเพื่อสร้าง QR Code แล้วสแกนด้วยแอป Authenticator</p>
                                
                                @if(isset($qrCode) && $qrCode)
                                    <div class="text-center mb-3">
                                        <div class="qr-code-container p-3 bg-white border rounded shadow-sm d-inline-block">
                                            @if(str_starts_with($qrCode, 'data:image/'))
                                                {{-- Base64 encoded image --}}
                                                <img src="{{ $qrCode }}" 
                                                     alt="QR Code สำหรับ Two-Factor Authentication" 
                                                     class="img-fluid qr-image"
                                                     style="max-width: 200px; height: auto; display: block; margin: 0 auto;"
                                                     onload="console.log('QR Code (base64) loaded successfully'); document.getElementById('qr-fallback').style.display='none';"
                                                     onerror="console.error('Base64 QR Code failed to load'); this.style.display='none'; document.getElementById('qr-fallback').style.display='block';">
                                            @elseif(str_starts_with($qrCode, '<svg'))
                                                {{-- Raw SVG content --}}
                                                <div style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 0 auto;" class="qr-svg-container">
                                                    {!! $qrCode !!}
                                                </div>
                                                <script>
                                                    console.log('QR Code (SVG) loaded successfully');
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        const fallback = document.getElementById('qr-fallback');
                                                        if (fallback) fallback.style.display = 'none';
                                                    });
                                                </script>
                                            @elseif(str_starts_with($qrCode, 'http'))
                                                {{-- External URL (deprecated Google Charts) --}}
                                                <div class="text-center mb-2">
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        <strong>ข้อผิดพลาด:</strong> Google Charts API ไม่ทำงานแล้ว
                                                    </div>
                                                    <p class="text-muted small">กรุณาใช้รหัส Secret ด้านล่างแทน</p>
                                                </div>
                                            @else
                                                {{-- Unknown format - try to detect SVG in content --}}
                                                @if(str_contains($qrCode, 'svg') || str_contains($qrCode, 'SVG') || str_contains($qrCode, '<g') || str_contains($qrCode, 'xmlns'))
                                                    {{-- Contains SVG content --}}
                                                    <div style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 0 auto;" class="qr-svg-container">
                                                        {!! $qrCode !!}
                                                    </div>
                                                    <script>
                                                        console.log('QR Code (detected SVG) rendered successfully');
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            const fallback = document.getElementById('qr-fallback');
                                                            if (fallback) fallback.style.display = 'none';
                                                        });
                                                    </script>
                                                @else
                                                    <div class="text-center">
                                                        <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                                                        <p class="mt-2 text-muted">รูปแบบ QR Code ไม่รองรับ</p>
                                                        <small class="text-muted">Length: {{ strlen($qrCode) }} chars</small>
                                                    </div>
                                                @endif
                                            @endif
                                            
                                            {{-- Fallback message --}}
                                            <div id="qr-fallback" style="display: none;" class="text-muted p-3">
                                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                                <p class="mt-2 mb-0">ไม่สามารถแสดง QR Code ได้</p>
                                                <small>กรุณาใช้รหัส Secret ด้านล่างแทน</small>
                                            </div>
                                        </div>
                                        
                                        <p class="text-muted mt-3 small">
                                            <i class="bi bi-info-circle"></i>
                                            สแกน QR Code นี้ด้วยแอป Authenticator ของคุณ
                                        </p>
                                        
                                        {{-- Debug info --}}
                                        @if(config('app.debug') && (auth()->user()->hasRole('super-admin') || auth()->user()->email === 'admin@example.com'))
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Debug: 
                                                    @if(str_starts_with($qrCode, 'data:image/'))
                                                        Base64 Data URL
                                                    @elseif(str_starts_with($qrCode, 'http'))
                                                        External URL  
                                                    @else
                                                        Raw Content
                                                    @endif
                                                    ({{ strlen($qrCode) }} chars)
                                                </small>
                                                <br>
                                                <button class="btn btn-sm btn-outline-info" onclick="showQrDebug()">
                                                    <i class="bi bi-bug"></i> Debug QR
                                                </button>
                                                
                                                <script>
                                                    // Debug QR Code function (inline)
                                                    function showQrDebug() {
                                                        const qrCode = @json($qrCode ?? 'ไม่มีข้อมูล QR Code');
                                                        
                                                        const debugInfo = {
                                                            'QR Code Length': qrCode.length,
                                                            'QR Code Type': getQrCodeType(qrCode),
                                                            'First 100 chars': qrCode.substring(0, 100),
                                                            'Last 100 chars': qrCode.substring(qrCode.length - 100)
                                                        };
                                                        
                                                        console.group('🔍 QR Code Debug Information');
                                                        Object.entries(debugInfo).forEach(([key, value]) => {
                                                            console.log(`${key}:`, value);
                                                        });
                                                        console.groupEnd();
                                                        
                                                        // แสดงผลใน alert
                                                        const debugText = Object.entries(debugInfo)
                                                            .map(([key, value]) => `${key}: ${typeof value === 'string' && value.length > 50 ? value.substring(0, 50) + '...' : value}`)
                                                            .join('\n\n');
                                                            
                                                        alert('🔍 QR Debug Info:\n\n' + debugText);
                                                    }
                                                    
                                                    function getQrCodeType(qrCode) {
                                                        if (!qrCode) return 'ไม่มีข้อมูล';
                                                        if (qrCode.startsWith('data:image/')) return 'Base64 Data URL';
                                                        if (qrCode.startsWith('<svg') || qrCode.includes('<svg')) return 'Raw SVG';
                                                        if (qrCode.startsWith('http')) return 'External URL';
                                                        if (qrCode.includes('svg') || qrCode.includes('xmlns')) return 'SVG Content';
                                                        return 'Unknown Format';
                                                    }
                                                </script>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center mb-3">
                                        <div class="qr-placeholder">
                                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                                            <p class="mt-2 text-warning">ไม่สามารถสร้าง QR Code ได้</p>
                                            <small>กรุณาใช้รหัส Secret ด้านล่างเพื่อตั้งค่าด้วยตนเอง</small>
                                        </div>
                                    </div>
                                @endif
                                
                                <p class="text-muted">
                                    <strong>รหัส Secret (หาก QR Code ไม่ทำงาน):</strong><br>
                                    <code>{{ $secretKey ?? auth()->user()->google2fa_secret }}</code>
                                </p>
                                
                                @if(isset($qrCode) && $qrCode)
                                    <h5 class="mt-4"><i class="bi bi-3-circle text-success"></i> ขั้นตอนที่ 3: ยืนยันการตั้งค่า</h5>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>ขั้นตอนสุดท้าย:</strong> กรอกรหัส 6 หลักจากแอป Authenticator เพื่อยืนยันการตั้งค่า
                                    </div>
                                    <form action="{{ route('2fa.confirm') }}" method="POST">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label for="code" class="form-label fw-bold">รหัสยืนยัน (6 หลัก)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                                <input type="text" 
                                                       class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                                       id="code" 
                                                       name="code" 
                                                       maxlength="6" 
                                                       placeholder="000000"
                                                       required 
                                                       autocomplete="off"
                                                       style="font-size: 1.5rem; letter-spacing: 0.5rem; font-family: monospace;">
                                            </div>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i>
                                                    รหัสจะเปลี่ยนทุก 30 วินาที - ใส่รหัสปัจจุบันจากแอป
                                                </small>
                                            @enderror
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bi bi-check-circle"></i>
                                                ยืนยันและเปิดใช้งาน 2FA
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetQRCode()">
                                                <i class="bi bi-arrow-clockwise"></i>
                                                สร้าง QR ใหม่
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <form action="{{ route('2fa.enable') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-qr-code"></i>
                                            สร้าง QR Code
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <i class="bi bi-shield-check" style="font-size: 5rem; color: #28a745;"></i>
                                    <h5 class="mt-3">ปกป้องบัญชีของคุณ</h5>
                                    <p class="text-muted">Two-Factor Authentication ให้ความปลอดภัยเพิ่มเติมสำหรับบัญชีของคุณโดยต้องใช้ทั้งรหัสผ่านและรหัสยืนยันจากมือถือ</p>
                                </div>
                            </div>
                        </div>

                    @elseif(auth()->user()->hasTwoFactorSecret() && !auth()->user()->hasTwoFactorConfirmed())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>กำลังตั้งค่า:</strong> กรุณาสแกน QR Code และกรอกรหัสยืนยันเพื่อเสร็จสิ้นการตั้งค่า
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>ขั้นตอนที่ 2: สแกน QR Code</h5>
                                <div class="text-center mb-3">
                                    @if(isset($qrCode))
                                        <div class="qr-code-container p-3 bg-white border rounded shadow-sm d-inline-block">
                                            <img src="{{ $qrCode }}" 
                                                 alt="QR Code สำหรับ Two-Factor Authentication" 
                                                 class="img-fluid"
                                                 style="max-width: 200px; height: auto;">
                                        </div>
                                        <p class="text-muted mt-3 small">
                                            <i class="bi bi-info-circle"></i>
                                            สแกน QR Code นี้ด้วยแอป Authenticator ของคุณ
                                        </p>
                                    @else
                                        <div class="text-muted p-4">
                                            <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                                            <p class="mt-2">QR Code จะแสดงที่นี่หลังจากคลิก "สร้าง QR Code"</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-muted">
                                    <strong>รหัส Secret (สำหรับใส่ใน App ด้วยตนเอง):</strong><br>
                                    <div class="secret-code bg-light p-3 rounded border mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <code class="text-break flex-grow-1">{{ $secretKey ?? auth()->user()->google2fa_secret ?? 'ยังไม่ได้สร้าง' }}</code>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" 
                                                    onclick="copySecret()" 
                                                    title="คัดลอกรหัส Secret">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        ใช้รหัสนี้หาก QR Code ไม่สามารถสแกนได้
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>ขั้นตอนที่ 3: กรอกรหัสยืนยัน</h5>
                                <p class="text-muted">กรอกรหัส 6 หลักจากแอป Authenticator เพื่อยืนยันการตั้งค่า:</p>

                                <form action="{{ route('2fa.confirm') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="code" class="form-label">รหัสยืนยัน</label>
                                        <input type="text" 
                                               class="form-control @error('code') is-invalid @enderror" 
                                               id="code" 
                                               name="code" 
                                               maxlength="6" 
                                               placeholder="123456"
                                               required 
                                               autocomplete="off">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i>
                                            ยืนยันและเปิดใช้งาน 2FA
                                        </button>
                                        <form action="{{ route('2fa.disable') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-circle"></i>
                                                ยกเลิกการตั้งค่า
                                            </button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-shield-check"></i>
                            <strong>Two-Factor Authentication ทำงานอยู่:</strong> บัญชีของคุณได้รับการปกป้องด้วย 2FA
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>จัดการ Two-Factor Authentication</h5>
                                <p class="text-muted">Two-Factor Authentication ของคุณกำลังทำงานอยู่</p>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <form action="{{ route('2fa.disable') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('คุณแน่ใจหรือไม่ที่จะปิดการใช้งาน Two-Factor Authentication? การกระทำนี้จะทำให้บัญชีของคุณมีความปลอดภัยน้อยลง')">
                                            <i class="bi bi-shield-x"></i>
                                            ปิดการใช้งาน 2FA
                                        </button>
                                    </form>
                                </div>

                                <h6>รหัสกู้คืน (Recovery Codes)</h6>
                                <p class="text-muted small">
                                    รหัสกู้คืนสามารถใช้เข้าถึงบัญชีได้หากคุณสูญหายเครื่องมือ Authenticator
                                </p>
                                
                                @if(auth()->user()->hasRecoveryCodes())
                                    <p class="text-success small">
                                        <i class="bi bi-check-circle"></i>
                                        คุณมีรหัสกู้คืน {{ count(auth()->user()->recovery_codes) }} รหัสเหลืออยู่
                                        (สร้างเมื่อ: {{ auth()->user()->recovery_codes_generated_at->format('j M Y') }})
                                    </p>
                                @else
                                    <p class="text-warning small">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        ยังไม่ได้สร้างรหัสกู้คืน
                                    </p>
                                @endif

                                <form action="{{ route('2fa.recovery.generate') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i>
                                        {{ auth()->user()->hasRecoveryCodes() ? 'สร้างรหัสกู้คืนใหม่' : 'สร้างรหัสกู้คืน' }}
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <i class="bi bi-shield-fill-check" style="font-size: 5rem; color: #28a745;"></i>
                                    <h5 class="mt-3 text-success">บัญชีปลอดภัย</h5>
                                    <p class="text-muted">บัญชีของคุณได้รับการปกป้องด้วย Two-Factor Authentication</p>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>สถานะความปลอดภัย</h6>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <small class="text-muted">สถานะ 2FA</small><br>
                                                    <span class="badge bg-success">ทำงาน</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">วันที่ตั้งค่า</small><br>
                                                    <small>{{ auth()->user()->google2fa_confirmed_at->format('j M Y') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('profile.settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i>
                            กลับไปการตั้งค่าโปรไฟล์
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // จัดรูปแบบการกรอกรหัสยืนยัน
    document.getElementById('code')?.addEventListener('input', function(e) {
        // ลบตัวอักษรที่ไม่ใช่ตัวเลข
        this.value = this.value.replace(/\D/g, '');
        
        // จำกัดไว้ที่ 6 หลัก
        if (this.value.length > 6) {
            this.value = this.value.substring(0, 6);
        }
    });

    // ส่งฟอร์มอัตโนมัติเมื่อกรอกครบ 6 หลัก (ตัวเลือก)
    document.getElementById('code')?.addEventListener('input', function(e) {
        if (this.value.length === 6) {
            // ตัวเลือก: ส่งฟอร์มอัตโนมัติหลังจากกรอกครบ 6 หลัก
            // this.form.submit();
        }
    });

    // ฟังก์ชันคัดลอกรหัส Secret
    function copySecret() {
        const secretCode = document.querySelector('.secret-code code').textContent;
        
        if (navigator.clipboard && window.isSecureContext) {
            // ใช้ Clipboard API (HTTPS)
            navigator.clipboard.writeText(secretCode).then(function() {
                showCopySuccess();
            }).catch(function(err) {
                console.error('การคัดลอกล้มเหลว: ', err);
                fallbackCopyTextToClipboard(secretCode);
            });
        } else {
            // Fallback สำหรับ HTTP หรือเบราว์เซอร์เก่า
            fallbackCopyTextToClipboard(secretCode);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                showCopyError();
            }
        } catch (err) {
            console.error('การคัดลอกล้มเหลว: ', err);
            showCopyError();
        }
        
        document.body.removeChild(textArea);
    }

    function showCopySuccess() {
        const button = document.querySelector('button[onclick="copySecret()"]');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check text-success"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }

    function showCopyError() {
        alert('ไม่สามารถคัดลอกได้ กรุณาเลือกและคัดลอกด้วยตนเอง');
    }

    // ฟังก์ชันสร้าง QR Code ใหม่
    function resetQRCode() {
        if (confirm('คุณต้องการสร้าง QR Code ใหม่หรือไม่? รหัส Secret เก่าจะไม่สามารถใช้งานได้')) {
            // สร้างฟอร์มและส่งไปยัง enable route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("2fa.enable") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // เพิ่ม real-time validation สำหรับ input code
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');
        if (codeInput) {
            // เพิ่ม visual feedback เมื่อกรอกรหัส
            codeInput.addEventListener('input', function() {
                if (this.value.length === 6) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-valid');
                }
            });
            
            // Auto-focus เมื่อหน้าโหลด
            codeInput.focus();
        }
    });

    // Debug function สำหรับ QR Code
    function showQrDebug() {
        const qrImage = document.querySelector('.qr-image');
        const qrSvg = document.querySelector('.qr-svg-container');
        
        let debugInfo = 'QR Code Debug Information:\n\n';
        
        if (qrImage) {
            debugInfo += `Image Element Found: Yes\n`;
            debugInfo += `Image Source: ${qrImage.src.substring(0, 100)}...\n`;
            debugInfo += `Image Display: ${getComputedStyle(qrImage).display}\n`;
            debugInfo += `Image Visibility: ${getComputedStyle(qrImage).visibility}\n`;
            debugInfo += `Natural Width: ${qrImage.naturalWidth}\n`;
            debugInfo += `Natural Height: ${qrImage.naturalHeight}\n`;
        } else if (qrSvg) {
            debugInfo += `SVG Container Found: Yes\n`;
            debugInfo += `SVG Content: ${qrSvg.innerHTML.substring(0, 100)}...\n`;
        } else {
            debugInfo += `QR Element: Not found\n`;
        }
        
        debugInfo += `\nBrowser: ${navigator.userAgent}\n`;
        debugInfo += `SVG Support: ${document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1")}\n`;
        
        console.log(debugInfo);
        alert(debugInfo);
    }
</script>
@endpush

@push('styles')
<style>
    .qr-code-container {
        display: inline-block;
        background: #ffffff;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .qr-code-container:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
    
    .qr-code-container::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #007bff, #28a745);
        border-radius: 17px;
        z-index: -1;
    }
    
    .qr-code-container img {
        display: block;
        max-width: 200px;
        height: auto;
        border-radius: 8px;
        filter: contrast(1.1);
        margin: 0 auto;
    }
    
    .qr-image {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }
    
    .qr-svg-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }
    
    .qr-svg-container svg {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
    }
    
    .secret-code {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        word-break: break-all;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .secret-code:hover {
        border-color: #007bff;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    
    .setup-step {
        border-left: 4px solid #007bff;
        padding-left: 20px;
        margin-bottom: 25px;
        position: relative;
    }
    
    .setup-step::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #007bff 0%, rgba(0, 123, 255, 0.3) 100%);
        border-radius: 2px;
    }
    
    .qr-placeholder {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px dashed #ced4da;
        border-radius: 15px;
        padding: 40px;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .qr-placeholder:hover {
        border-color: #007bff;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    
    .app-option {
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 10px;
    }
    
    .app-option:hover {
        background: #e9ecef !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .bi-1-circle, .bi-2-circle, .bi-3-circle {
        margin-right: 8px;
    }
    
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .btn {
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-bottom: none;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .loading {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    // Debug QR Code function
    function showQrDebug() {
        const qrCode = @json($qrCode ?? 'ไม่มีข้อมูล QR Code');
        
        const debugInfo = {
            'QR Code Length': qrCode.length,
            'QR Code Type': getQrCodeType(qrCode),
            'First 100 chars': qrCode.substring(0, 100),
            'Last 100 chars': qrCode.substring(qrCode.length - 100)
        };
        
        console.group('🔍 QR Code Debug Information');
        Object.entries(debugInfo).forEach(([key, value]) => {
            console.log(`${key}:`, value);
        });
        console.groupEnd();
        
        // แสดง modal หรือ alert
        const debugText = Object.entries(debugInfo)
            .map(([key, value]) => `${key}: ${value}`)
            .join('\n\n');
            
        alert('QR Debug Info:\n\n' + debugText);
    }
    
    function getQrCodeType(qrCode) {
        if (!qrCode) return 'ไม่มีข้อมูล';
        if (qrCode.startsWith('data:image/')) return 'Base64 Data URL';
        if (qrCode.startsWith('<svg')) return 'Raw SVG';
        if (qrCode.startsWith('http')) return 'External URL';
        return 'Unknown Format';
    }
    
    // Reset QR Code function
    function resetQRCode() {
        if (confirm('ต้องการสร้าง QR Code ใหม่หรือไม่?')) {
            window.location.reload();
        }
    }
    
    // Auto-format verification code input
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                // อนุญาตเฉพาะตัวเลข
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // จำกัด 6 ตัวอักษร
                if (this.value.length > 6) {
                    this.value = this.value.substring(0, 6);
                }
            });
            
            // Auto-submit เมื่อครบ 6 หลัก
            codeInput.addEventListener('input', function(e) {
                if (this.value.length === 6) {
                    // ให้ user กด submit เอง เพื่อความปลอดภัย
                    this.parentElement.parentElement.querySelector('button[type="submit"]').focus();
                }
            });
        }
        
        // ซ่อน fallback หาก SVG โหลดสำเร็จ
        const svgContainer = document.querySelector('.qr-svg-container');
        const fallback = document.getElementById('qr-fallback');
        
        if (svgContainer && svgContainer.querySelector('svg') && fallback) {
            console.log('QR Code SVG detected, hiding fallback');
            fallback.style.display = 'none';
        }
    });
</script>
@endpush
