/**
 * Password Strength Meter
 * Real-time password strength analysis with visual feedback
 */
class PasswordStrengthMeter {
    constructor(passwordInput, options = {}) {
        this.passwordInput = document.getElementById(passwordInput);
        this.options = {
            showRequirements: true,
            showScore: true,
            containerClass: 'password-strength-container',
            meterClass: 'password-strength-meter',
            requirementsClass: 'password-requirements',
            ...options
        };
        
        this.requirements = {
            minLength: 8,
            requireUppercase: true,
            requireLowercase: true,
            requireNumbers: true,
            requireSymbols: true,
            minUniqueChars: 4
        };

        this.strengthLevels = {
            0: { label: 'อ่อนแอมาก', color: '#dc3545', class: 'very-weak' },
            25: { label: 'อ่อนแอ', color: '#fd7e14', class: 'weak' },
            50: { label: 'ปานกลาง', color: '#ffc107', class: 'fair' },
            75: { label: 'ดี', color: '#20c997', class: 'good' },
            90: { label: 'แข็งแกร่ง', color: '#28a745', class: 'strong' },
            100: { label: 'แข็งแกร่งมาก', color: '#007bff', class: 'very-strong' }
        };

        this.init();
    }

    init() {
        if (!this.passwordInput) {
            console.error('Password input element not found');
            return;
        }

        this.createContainer();
        this.bindEvents();
        this.loadConfig();
    }

    async loadConfig() {
        try {
            const response = await fetch('/api/password-policy-config');
            if (response.ok) {
                const config = await response.json();
                if (config.strength) {
                    this.requirements = { 
                        ...this.requirements, 
                        minLength: config.strength.minLength,
                        requireUppercase: config.strength.requireUppercase,
                        requireLowercase: config.strength.requireLowercase,
                        requireNumbers: config.strength.requireNumbers,
                        requireSymbols: config.strength.requireSymbols,
                        minUniqueChars: config.strength.minUniqueChars
                    };
                    this.updateRequirementsDisplay();
                }
            }
        } catch (error) {
            console.warn('Could not load password policy config, using defaults');
        }
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = this.options.containerClass;
        
        container.innerHTML = `
            <div class="${this.options.meterClass}">
                <div class="strength-bar">
                    <div class="strength-fill"></div>
                </div>
                <div class="strength-label">เริ่มพิมพ์รหัสผ่าน...</div>
                <div class="strength-score" style="display: ${this.options.showScore ? 'block' : 'none'}">
                    คะแนน: <span class="score-value">0</span>/100
                </div>
            </div>
            <div class="${this.options.requirementsClass}" style="display: ${this.options.showRequirements ? 'block' : 'none'}">
                <div class="requirements-title">ข้อกำหนดรหัสผ่าน:</div>
                <ul class="requirements-list"></ul>
            </div>
        `;

        this.passwordInput.parentNode.insertBefore(container, this.passwordInput.nextSibling);
        this.container = container;
        this.strengthBar = container.querySelector('.strength-fill');
        this.strengthLabel = container.querySelector('.strength-label');
        this.scoreValue = container.querySelector('.score-value');
        this.requirementsList = container.querySelector('.requirements-list');

        this.updateRequirementsDisplay();
    }

    updateRequirementsDisplay() {
        if (!this.requirementsList) return;

        const requirements = [
            { key: 'minLength', text: `อย่างน้อย ${this.requirements.minLength} ตัวอักษร` },
            { key: 'requireUppercase', text: 'ตัวอักษรพิมพ์ใหญ่ (A-Z)' },
            { key: 'requireLowercase', text: 'ตัวอักษรพิมพ์เล็ก (a-z)' },
            { key: 'requireNumbers', text: 'ตัวเลข (0-9)' },
            { key: 'requireSymbols', text: 'สัญลักษณ์พิเศษ (!@#$%^&*)' },
            { key: 'minUniqueChars', text: `ตัวอักษรที่แตกต่างกันอย่างน้อย ${this.requirements.minUniqueChars} ตัว` }
        ];

        this.requirementsList.innerHTML = requirements
            .filter(req => this.requirements[req.key])
            .map(req => `<li class="requirement" data-key="${req.key}">
                <i class="requirement-icon">✗</i>
                <span class="requirement-text">${req.text}</span>
            </li>`)
            .join('');
    }

    bindEvents() {
        this.passwordInput.addEventListener('input', (e) => {
            this.analyzePassword(e.target.value);
        });

        this.passwordInput.addEventListener('focus', () => {
            this.container.classList.add('focused');
        });

        this.passwordInput.addEventListener('blur', () => {
            this.container.classList.remove('focused');
        });
    }

    analyzePassword(password) {
        const analysis = this.calculateStrength(password);
        this.updateDisplay(analysis);
        this.updateRequirements(password);
    }

    calculateStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= this.requirements.minLength,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            numbers: /\d/.test(password),
            symbols: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password),
            uniqueChars: new Set(password).size >= this.requirements.minUniqueChars
        };

        // Base score for length
        if (password.length >= this.requirements.minLength) {
            score += 20;
        } else {
            score += Math.min(password.length * 2, 19);
        }

        // Complexity bonuses
        if (checks.uppercase) score += 15;
        if (checks.lowercase) score += 15;
        if (checks.numbers) score += 15;
        if (checks.symbols) score += 20;
        if (checks.uniqueChars) score += 10;

        // Length bonuses
        if (password.length >= 12) score += 5;
        if (password.length >= 16) score += 5;

        // Penalty for common patterns
        if (this.hasCommonPatterns(password)) {
            score -= 15;
        }

        return {
            score: Math.min(Math.max(score, 0), 100),
            checks: checks,
            level: this.getStrengthLevel(score)
        };
    }

    hasCommonPatterns(password) {
        const commonPatterns = [
            /123456/,
            /password/i,
            /qwerty/i,
            /abc123/i,
            /(.)\1{2,}/, // Repeated characters
            /^(.)(.*\1){2,}/ // Repetitive patterns
        ];

        return commonPatterns.some(pattern => pattern.test(password));
    }

    getStrengthLevel(score) {
        const levels = Object.keys(this.strengthLevels)
            .map(Number)
            .sort((a, b) => b - a);

        for (const level of levels) {
            if (score >= level) {
                return this.strengthLevels[level];
            }
        }

        return this.strengthLevels[0];
    }

    updateDisplay(analysis) {
        const { score, level } = analysis;
        
        // Update progress bar
        this.strengthBar.style.width = `${score}%`;
        this.strengthBar.style.backgroundColor = level.color;
        
        // Update label
        this.strengthLabel.textContent = level.label;
        this.strengthLabel.className = `strength-label ${level.class}`;
        
        // Update score
        if (this.scoreValue) {
            this.scoreValue.textContent = score;
        }

        // Update container class
        this.container.className = this.container.className.replace(/strength-\w+/g, '');
        this.container.classList.add(`strength-${level.class}`);
    }

    updateRequirements(password) {
        const requirements = this.container.querySelectorAll('.requirement');
        
        requirements.forEach(req => {
            const key = req.dataset.key;
            const icon = req.querySelector('.requirement-icon');
            let met = false;

            switch (key) {
                case 'minLength':
                    met = password.length >= this.requirements.minLength;
                    break;
                case 'requireUppercase':
                    met = /[A-Z]/.test(password);
                    break;
                case 'requireLowercase':
                    met = /[a-z]/.test(password);
                    break;
                case 'requireNumbers':
                    met = /\d/.test(password);
                    break;
                case 'requireSymbols':
                    met = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password);
                    break;
                case 'minUniqueChars':
                    met = new Set(password).size >= this.requirements.minUniqueChars;
                    break;
            }

            req.classList.toggle('met', met);
            icon.textContent = met ? '✓' : '✗';
            icon.style.color = met ? '#28a745' : '#dc3545';
        });
    }

    // Public API
    getStrength(password = null) {
        const pwd = password || this.passwordInput.value;
        return this.calculateStrength(pwd);
    }

    isValid(password = null) {
        const analysis = this.getStrength(password);
        return analysis.score >= 50; // Minimum acceptable strength
    }

    destroy() {
        if (this.container) {
            this.container.remove();
        }
    }
}

// Auto-initialize for common password fields
document.addEventListener('DOMContentLoaded', function() {
    // Initialize for registration form
    const regPasswordInput = document.getElementById('password');
    if (regPasswordInput) {
        window.passwordStrengthMeter = new PasswordStrengthMeter('password');
    }

    // Initialize for change password form
    const changePasswordInput = document.getElementById('new_password');
    if (changePasswordInput) {
        window.changePasswordStrengthMeter = new PasswordStrengthMeter('new_password');
    }

    // Initialize for profile password change
    const profilePasswordInput = document.getElementById('current_password');
    if (profilePasswordInput && document.getElementById('password_new')) {
        window.profilePasswordStrengthMeter = new PasswordStrengthMeter('password_new');
    }
});
