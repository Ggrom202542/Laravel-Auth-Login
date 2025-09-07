@extends('layouts.app')

@section('title', 'Two-Factor Authentication Recovery')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(isset($recoveryCodes))
                {{-- Show newly generated recovery codes --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-key"></i>
                            Recovery Codes Generated
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Important:</strong> Save these recovery codes in a secure location. Each code can only be used once.
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <h5>Your Recovery Codes</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($recoveryCodes as $index => $code)
                                                <div class="col-md-6 mb-2">
                                                    <code class="d-block p-2 bg-white border rounded">{{ $code }}</code>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button onclick="downloadRecoveryCodes()" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-download"></i>
                                        Download as Text File
                                    </button>
                                    <button onclick="printRecoveryCodes()" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-printer"></i>
                                        Print Codes
                                    </button>
                                    <button onclick="copyToClipboard()" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-clipboard"></i>
                                        Copy to Clipboard
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Security Tips</h6>
                                <ul class="list-unstyled small text-muted">
                                    <li><i class="bi bi-check-circle text-success"></i> Store in a password manager</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Print and store securely</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Each code works only once</li>
                                    <li><i class="bi bi-x-circle text-danger"></i> Don't share with others</li>
                                    <li><i class="bi bi-x-circle text-danger"></i> Don't store in plain text files</li>
                                </ul>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i>
                                Back to 2FA Settings
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- Recovery code entry form --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="bi bi-key"></i>
                            Account Recovery
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-exclamation" style="font-size: 3rem; color: #ffc107;"></i>
                            <h5 class="mt-3">Enter Recovery Code</h5>
                            <p class="text-muted">
                                If you've lost access to your authenticator app, you can use one of your recovery codes to access your account.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('2fa.recovery.verify') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="recovery_code" class="form-label">Recovery Code</label>
                                <input type="text" 
                                       class="form-control form-control-lg text-center @error('recovery_code') is-invalid @enderror" 
                                       id="recovery_code" 
                                       name="recovery_code" 
                                       maxlength="8" 
                                       placeholder="ABC12345"
                                       required 
                                       autocomplete="off"
                                       autofocus
                                       style="letter-spacing: 0.3em; font-family: monospace;">
                                @error('recovery_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Enter one of your 8-character recovery codes (e.g., ABC12345)
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="bi bi-unlock"></i>
                                    Use Recovery Code
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <hr>
                            <p class="text-muted small">Remember your authenticator code?</p>
                            <a href="{{ route('2fa.challenge') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left"></i>
                                Back to 2FA Challenge
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted btn-sm">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Cancel & Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(isset($recoveryCodes))
    // Functions for managing recovery codes
    function downloadRecoveryCodes() {
        const codes = @json($recoveryCodes);
        const content = `Two-Factor Authentication Recovery Codes\n` +
                       `Generated: ${new Date().toLocaleDateString()}\n` +
                       `Account: {{ auth()->user()->email }}\n\n` +
                       `IMPORTANT: Keep these codes secure and private!\n` +
                       `Each code can only be used once.\n\n` +
                       codes.map((code, index) => `${index + 1}. ${code}`).join('\n') +
                       `\n\nStore these codes in a secure location like a password manager.`;
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = '2FA-Recovery-Codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    function printRecoveryCodes() {
        const codes = @json($recoveryCodes);
        const printContent = `
            <h2>Two-Factor Authentication Recovery Codes</h2>
            <p><strong>Generated:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Account:</strong> {{ auth()->user()->email }}</p>
            <div style="border: 2px solid #dc3545; padding: 15px; margin: 20px 0; background-color: #f8d7da;">
                <strong>IMPORTANT:</strong> Keep these codes secure and private!<br>
                Each code can only be used once.
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0;">
                ${codes.map((code, index) => `<div style="padding: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 16px;">${index + 1}. ${code}</div>`).join('')}
            </div>
            <p><em>Store these codes in a secure location like a password manager.</em></p>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head><title>2FA Recovery Codes</title></head>
                <body style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
                    ${printContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function copyToClipboard() {
        const codes = @json($recoveryCodes);
        const content = codes.join('\n');
        
        navigator.clipboard.writeText(content).then(() => {
            // Show success message
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i> Copied!';
            button.classList.remove('btn-outline-info');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-info');
            }, 2000);
        }).catch(() => {
            alert('Failed to copy to clipboard. Please copy manually.');
        });
    }
    @else
    // Format recovery code input
    document.addEventListener('DOMContentLoaded', function() {
        const recoveryInput = document.getElementById('recovery_code');
        
        recoveryInput.addEventListener('input', function(e) {
            // Convert to uppercase and remove invalid characters
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Limit to 8 characters
            if (this.value.length > 8) {
                this.value = this.value.substring(0, 8);
            }
        });

        recoveryInput.focus();
    });
    @endif
</script>
@endpush
