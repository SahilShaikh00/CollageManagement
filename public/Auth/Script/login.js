// Toggle password visibility
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("passwordInput");
const toggleSignupPassword = document.getElementById("toggleSignupPassword");
const signupPassword = document.getElementById("signupPasswordInput");

if (togglePassword && password) {
    togglePassword.addEventListener("click", () => {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);

        togglePassword.classList.toggle("fa-eye");
        togglePassword.classList.toggle("fa-eye-slash");
    });
}

if (toggleSignupPassword && signupPassword) {
    toggleSignupPassword.addEventListener("click", () => {
        const type = signupPassword.getAttribute("type") === "password" ? "text" : "password";
        signupPassword.setAttribute("type", type);

        toggleSignupPassword.classList.toggle("fa-eye");
        toggleSignupPassword.classList.toggle("fa-eye-slash");
    });
}

function showMessage(message, type = "success", duration = 3000) {
    const messageBox = document.getElementById('messageBox');
    messageBox.textContent = message;
    messageBox.className = 'message-box show ' + type; // show class for animation

    // Hide after duration
    setTimeout(() => {
        messageBox.classList.remove('show');
        messageBox.classList.add('hide');

        // completely hide after animation ends
        messageBox.addEventListener('animationend', () => {
            messageBox.style.display = 'none';
            messageBox.classList.remove('hide');
        }, { once: true });

    }, duration);
}


// Switch between login and signup forms
document.querySelectorAll('.switch-form').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const formToShow = link.getAttribute('data-form');
        document.querySelectorAll('.formSection').forEach(section => {
            section.classList.remove('active');
        });
        const targetForm = document.querySelector(`.${formToShow}-form`);
        if (targetForm) {
            targetForm.classList.add('active');
        }
    });
});

// API call for signup
document.getElementById('signupForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const button = e.target.querySelector('button');
    const originalText = button.textContent;

    const email = e.target.querySelector('input[type="text"]').value.trim();
    const password = e.target.querySelector('#signupPasswordInput').value;

    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    button.disabled = true;

    try {
        const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'signUp',
                email: email,
                password: password
            })
        });

        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error("JSON parse error:", parseError);
            console.error("Raw response that failed to parse:", responseText);
            throw new Error("Server returned invalid JSON. Check PHP for errors.");
        }

        if (data.success) {
            showMessage(data.message, 'success');
            window.location.href = "../../Admin/Html/index.html";
        } else {
            showMessage(data.message, 'error');
        }

    } catch (error) {
        console.error("Full error details:", error);
        showMessage('Error: ' + error.message, 'error');
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
});

// Initialize the page
window.addEventListener('DOMContentLoaded', () => {
    createParticles();
});

// API call for login
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const button = e.target.querySelector('button');
    const originalText = button.textContent;

    const email = e.target.querySelector('input[type="email"]').value.trim();
    const password = e.target.querySelector('#passwordInput').value;

    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Login...';
    button.disabled = true;

    try {
        const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                email: email,
                password: password
            })
        });

        const data = await response.json();

        if (data.success) {
            localStorage.setItem('token', data.token);
            localStorage.setItem('role', data.user.role);
            localStorage.setItem('email', data.user.email);
            showMessage('User Login SuccesFully ','success');     
             setTimeout(() => {
        window.location.href = '../../Admin/Html/index.html';
    }, 500);
        } else {
            showMessage(data.message, 'error');
        }

    } catch (error) {
        console.error("Full error details:", error);
        showMessage('Error: ' + error.message, 'error');
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
});

// Forgot Password Functionality
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.fg-link')) {
        initForgotPassword();
    }
});

function initForgotPassword() {
    const forgotLink = document.querySelector('.fg-link');
    const forgotSteps = document.querySelectorAll('.forgot-step');
    const emailForm = document.getElementById('forgotEmailForm');
    const otpForm = document.getElementById('forgotOtpForm');
    const passwordForm = document.getElementById('forgotPasswordForm');
    const otpInputs = document.querySelectorAll('.otp-input');
    const resendBtn = document.getElementById('resend-btn');
    const timerEl = document.getElementById('timer');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');

    let timerInterval;
    let timeLeft = 120;

    const stepLabels = ['Email', 'Verify', 'Reset'];
    initStepIndicator(stepLabels);

    if (forgotLink) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.formSection').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelector('.forgot-form').classList.add('active');
            goToForgotStep(1);
            updateStepIndicator(1);
        });
    }

    function goToForgotStep(step) {
        forgotSteps.forEach(s => s.classList.remove('active'));
        document.getElementById(`forgotStep${step}`).classList.add('active');
    }

    function startTimer() {
        clearInterval(timerInterval);
        timeLeft = 120;
        updateTimer();

        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimer();

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                if (resendBtn) {
                    resendBtn.style.pointerEvents = 'auto';
                    resendBtn.style.opacity = '1';
                }
            }
        }, 1000);
    }

    function updateTimer() {
        if (!timerEl) return;

        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerEl.textContent = `(${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')})`;

        if (timeLeft <= 0) {
            timerEl.textContent = '';
        }
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }

    function updatePasswordStrength() {
        if (!newPassword) return;

        const password = newPassword.value;
        const strength = checkPasswordStrength(password);
        let strengthText = '';
        let strengthPercent = 0;
        let barColor = '';

        switch (strength) {
            case 0: strengthText = 'Very Weak'; strengthPercent = 25; barColor = '#dc3545'; break;
            case 1: strengthText = 'Weak'; strengthPercent = 35; barColor = '#dc3545'; break;
            case 2: strengthText = 'Fair'; strengthPercent = 50; barColor = '#fd7e14'; break;
            case 3: strengthText = 'Good'; strengthPercent = 75; barColor = '#ffc107'; break;
            case 4: strengthText = 'Strong'; strengthPercent = 100; barColor = '#28a745'; break;
        }

        const passwordStrengthBar = document.getElementById('password-strength-bar');
        const passwordStrengthText = document.getElementById('password-strength-text');
        if (passwordStrengthBar) passwordStrengthBar.style.width = `${strengthPercent}%`;
        if (passwordStrengthBar) passwordStrengthBar.style.background = barColor;
        if (passwordStrengthText) {
            passwordStrengthText.textContent = strengthText;
            passwordStrengthText.style.color = barColor;
        }
    }

    function checkPasswordMatch() {
        const passwordMatch = document.getElementById('password-match');
        if (!confirmPassword || !passwordMatch) return;

        if (confirmPassword.value === '') {
            passwordMatch.textContent = '';
            passwordMatch.style.color = '';
            return;
        }

        if (newPassword.value === confirmPassword.value) {
            passwordMatch.textContent = 'Passwords match';
            passwordMatch.style.color = 'var(--success)';
        } else {
            passwordMatch.textContent = 'Passwords do not match';
            passwordMatch.style.color = 'var(--secondary)';
        }
    }

    function setupPasswordToggle(toggle, input) {
        if (!toggle || !input) return;

        toggle.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.classList.toggle('fa-eye-slash', isPassword);
            toggle.classList.toggle('fa-eye', !isPassword);
        });
    }

    if (otpInputs.length > 0) {
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
    }

    // Forgot Password Forms
    if (emailForm && !emailForm.dataset.bound) {
        emailForm.dataset.bound = "true";
        emailForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const userEmail = document.getElementById('forgotEmail').value.trim();
            const submitBtn = emailForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/forgetpassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'sendotp', email: userEmail })
                });
                const data = await response.json();
                if (data.status === "success") {
                    sessionStorage.setItem('resetEmail', userEmail);
                    goToForgotStep(2);
                    updateStepIndicator(2);
                    startTimer();
                    if (resendBtn) { resendBtn.style.pointerEvents = 'none'; resendBtn.style.opacity = '0.5'; }
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to send verification code. Please try again.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    if (otpForm && !otpForm.dataset.listener) {
        otpForm.dataset.listener = "true";
        otpForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const email = sessionStorage.getItem('resetEmail');
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            if (otp.length !== 6) {
                showMessage('Please enter the full 6-digit code', 'error');
                return;
            }

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/forgetpassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'verify', email: email, otp: otp })
                });
                const data = await response.json();
                if (data.success === true) {
                    goToForgotStep(3);
                    updateStepIndicator(3);
                    clearInterval(timerInterval);
                    showMessage('Verify Successfully', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to verify code. Please try again.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const email = sessionStorage.getItem('resetEmail');
            const password = newPassword.value;
            const confirm = confirmPassword.value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            if (password !== confirm) {
                document.getElementById('password-match').textContent = 'Passwords do not match';
                document.getElementById('password-match').style.color = 'var(--secondary)';
                return;
            }

            if (checkPasswordStrength(password) < 2) {
                showMessage('Please choose a stronger password', 'error');
                return;
            }

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/forgetpassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'resetPassword', email: email, password: password })
                });

                const data = await response.json();
                if (data.success === 'success') {
                    sessionStorage.removeItem('resetEmail');
                    goToForgotStep(4);
                    updateStepIndicator(4);
                    showMessage('Password reset successfully!', 'success');
                } else {
                    showMessage('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to reset password. Please try again.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }

            goToForgotStep(4);
            updateStepIndicator(4);
        });
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            const email = sessionStorage.getItem('resetEmail');
            const originalText = resendBtn.innerHTML;

            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resending...';
            resendBtn.disabled = true;

            try {
                const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/forgetpassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'sendotp', email: email })
                });
                const data = await response.json();
                if (data.status === "success") {
                    startTimer();
                    resendBtn.style.pointerEvents = 'none';
                    resendBtn.style.opacity = '0.5';
                    showMessage('Verification code has been resent to your email.', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to send verification code. Please try again.', 'error');
            } finally {
                resendBtn.innerHTML = originalText;
                resendBtn.disabled = false;
            }
        });
    }

    function initPasswordToggles() {
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

        if (toggleNewPassword && newPassword) setupPasswordToggle(toggleNewPassword, newPassword);
        if (toggleConfirmPassword && confirmPassword) setupPasswordToggle(toggleConfirmPassword, confirmPassword);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordToggles);
    } else {
        initPasswordToggles();
    }

    if (newPassword) newPassword.addEventListener('input', updatePasswordStrength);
    if (confirmPassword) confirmPassword.addEventListener('input', checkPasswordMatch);

    const successButton = document.querySelector('.success-message button');
    if (successButton) {
        successButton.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.formSection').forEach(section => section.classList.remove('active'));
            document.querySelector('.login-form').classList.add('active');
        });
    }
}

// Step indicator placeholders
function initStepIndicator(labels) { console.log("Initializing step indicator with labels:", labels); }
function updateStepIndicator(step) { console.log("Updating step indicator to step:", step); }

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initForgotPassword);
} else {
    initForgotPassword();
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.fg-link')) initForgotPassword();
});
