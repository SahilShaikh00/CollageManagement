// Create floating particles for background
function createParticles() {
  const particlesContainer = document.getElementById('particles');
  const numberOfParticles = 20;
  
  for (let i = 0; i < numberOfParticles; i++) {
    const particle = document.createElement('div');
    particle.classList.add('particle');
    
    // Random size between 5 and 15px
    const size = Math.random() * 10 + 5;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    
    // Random position
    particle.style.left = `${Math.random() * 100}%`;
    particle.style.top = `${Math.random() * 100}%`;
    
    // Random animation delay and duration
    particle.style.animationDuration = `${Math.random() * 10 + 15}s`;
    particle.style.animationDelay = `${Math.random() * 5}s`;
    
    particlesContainer.appendChild(particle);
  }
}

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

// Switch between login and signup forms
document.querySelectorAll('.switch-form').forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    console.log("Switch form clicked");
    const formToShow = link.getAttribute('data-form');
    console.log("Form to show:", formToShow);
    
    document.querySelectorAll('.formSection').forEach(section => {
      section.classList.remove('active');
    });
    
    const targetForm = document.querySelector(`.${formToShow}-form`);
    console.log("Target form element:", targetForm);
    
    if (targetForm) {
      targetForm.classList.add('active');
      console.log("Form switched successfully");
    } else {
      console.error("Target form not found!");
    }
  });
});

//apicall for sinup
document.getElementById('signupForm').addEventListener('submit', async (e) => { 
  e.preventDefault();
  console.log("=== SIGNUP FORM SUBMITTED ===");

  const button = e.target.querySelector('button');
  const originalText = button.textContent;

  const email = e.target.querySelector('input[type="text"]').value.trim();
  const password = e.target.querySelector('#signupPasswordInput').value;

  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
  button.disabled = true;

  try {
    console.log("Sending API request...");
    
    const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/login.php', {
      method: 'POST',        
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        action: 'signUp', // Make sure this matches PHP exactly
        email: email,
        password: password
      })
    });
    
    console.log("Response status:", response.status, response.statusText);
    
    // First get the response as text to see what's actually coming back
    const responseText = await response.text();
    console.log("Raw response text:", responseText);
    
    // Try to parse as JSON only if it looks like JSON
    let data;
    try {
      data = JSON.parse(responseText);
      console.log("Parsed JSON data:", data);
    } catch (parseError) {
      console.error("JSON parse error:", parseError);
      console.error("Raw response that failed to parse:", responseText);
      throw new Error("Server returned invalid JSON. Check PHP for errors.");
    }
    
    alert(data.message);

    if(data.success){
      window.location.href = "../../Admin/Html/index.html";
    }
  } catch(error) {
    console.error("Full error details:", error);
    alert('Error: ' + error.message);
  } finally {
    button.textContent = originalText;
    button.disabled = false;
  }
});

// Initialize the page
window.addEventListener('DOMContentLoaded', () => {
  createParticles();
  console.log("JavaScript loaded");
});


//api call for loginform
document.getElementById('loginForm').addEventListener('submit',async (e) => {
  e.preventDefault();

  console.log("SingUp Form Submited");

  const button = e.target.querySelector('button');
  const originalText = button.textContent;

  const email = e.target.querySelector('input[type="email"]').value.trim();
  const password = e.target.querySelector('#passwordInput').value;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
  button.disabled = true;

  try{
    console.log("Hitting api call now ");

    const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/login.php',{
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
    console.log("Response Status " , response.status , response.message);
    const responseText = await response.text();
    console.log("Raw response text:", responseText);
     let data;
    try {
      data = JSON.parse(responseText);
      console.log("Parsed JSON data:", data);
    } catch (parseError) {
      console.error("JSON parse error:", parseError);
      console.error("Raw response that failed to parse:", responseText);
      throw new Error("Server returned invalid JSON. Check PHP for errors.");
    }
    alert(data.message);

    if(data.success){
      window.location.href = "../../Admin/Html/index.html";
    }

  }catch(error) {
    console.error("Full error details:", error);
    alert('Error: ' + error.message);
  } finally {
    button.textContent = originalText;
    button.disabled = false;
  }
});





// Forgot Password Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize forgot password functionality if elements exist
    if (document.querySelector('.fg-link')) {
        initForgotPassword();
    }
});

// Function to update the step indicator
function updateStepIndicator(currentStep) {
    const steps = document.querySelectorAll('.step');
    const stepLines = document.querySelectorAll('.step-line');
    
    // Update steps
    steps.forEach((step, index) => {
        if (index + 1 < currentStep) {
            // Step is completed
            step.classList.remove('active');
            step.classList.add('completed');
        } else if (index + 1 === currentStep) {
            // Step is active
            step.classList.add('active');
            step.classList.remove('completed');
        } else {
            // Step is upcoming
            step.classList.remove('active', 'completed');
        }
    });
    
    // Update step lines
    stepLines.forEach((line, index) => {
        if (index + 1 < currentStep) {
            // Line is completed
            line.classList.add('completed', 'active');
        } else if (index + 1 === currentStep - 1) {
            // Line leads to active step
            line.classList.add('active');
            line.classList.remove('completed');
        } else {
            // Line is upcoming
            line.classList.remove('active', 'completed');
        }
    });
}

// Function to initialize step indicator with labels (optional)
function initStepIndicator(labels = []) {
    const steps = document.querySelectorAll('.step');
    
    // Add labels if provided
    if (labels.length === steps.length) {
        steps.forEach((step, index) => {
            const label = document.createElement('div');
            label.className = 'step-label';
            label.textContent = labels[index];
            step.appendChild(label);
        });
    }
    
    // Initialize with first step active
    updateStepIndicator(1);
}

function initForgotPassword() {
    // Elements
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
    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthText = document.getElementById('password-strength-text');
    const passwordMatch = document.getElementById('password-match');
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    
    let timerInterval;
    let timeLeft = 120; // 2 minutes in seconds
    
    // Initialize step indicator
    const stepLabels = ['Email', 'Verify', 'Reset'];
    initStepIndicator(stepLabels);
    
    // Event listener for forgot password link
    if (forgotLink) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            // Hide all forms
            document.querySelectorAll('.formSection').forEach(section => {
                section.classList.remove('active');
            });
            // Show forgot password form
            document.querySelector('.forgot-form').classList.add('active');
            // Reset to step 1
            goToForgotStep(1);
            updateStepIndicator(1); // Reset step indicator
        });
    }
    
    // Switch between forgot password steps
    function goToForgotStep(step) {
        forgotSteps.forEach(s => s.classList.remove('active'));
        document.getElementById(`forgotStep${step}`).classList.add('active');
    }
    
    // Start timer for OTP
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
    
    // Update timer display
    function updateTimer() {
        if (!timerEl) return;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerEl.textContent = `(${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')})`;
        
        if (timeLeft <= 0) {
            timerEl.textContent = '';
        }
    }
    
    // Check password strength
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return strength;
    }
    
    // Update password strength indicator
    function updatePasswordStrength() {
        if (!newPassword || !passwordStrengthBar || !passwordStrengthText) return;
        
        const password = newPassword.value;
        const strength = checkPasswordStrength(password);
        
        let strengthText = '';
        let strengthPercent = 0;
        let barColor = '';
        
        switch (strength) {
            case 0:
                strengthText = 'Very Weak';
                strengthPercent = 25;
                barColor = '#dc3545';
                break;
            case 1:
                strengthText = 'Weak';
                strengthPercent = 35;
                barColor = '#dc3545';
                break;
            case 2:
                strengthText = 'Fair';
                strengthPercent = 50;
                barColor = '#fd7e14';
                break;
            case 3:
                strengthText = 'Good';
                strengthPercent = 75;
                barColor = '#ffc107';
                break;
            case 4:
                strengthText = 'Strong';
                strengthPercent = 100;
                barColor = '#28a745';
                break;
        }
        
        passwordStrengthBar.style.width = `${strengthPercent}%`;
        passwordStrengthBar.style.background = barColor;
        passwordStrengthText.textContent = strengthText;
        passwordStrengthText.style.color = barColor;
    }
    
    // Check if passwords match
    function checkPasswordMatch() {
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
    
    // Toggle password visibility - FIXED VERSION
    function setupPasswordToggle(toggle, input) {
        if (!toggle || !input) return;
        
        toggle.addEventListener('click', function() {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.classList.toggle('fa-eye-slash', isPassword);
            toggle.classList.toggle('fa-eye', !isPassword);
        });
    }
    
    // Move to next OTP input
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
    
    // Event listeners for forgot password forms
    if (emailForm) {
        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, you would send the email to your backend here
            goToForgotStep(2);
            updateStepIndicator(2); // Update step indicator to step 2
            startTimer();
            if (resendBtn) {
                resendBtn.style.pointerEvents = 'none';
                resendBtn.style.opacity = '0.5';
            }
        });
    }
    
    if (otpForm) {
        otpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, you would verify the OTP with your backend here
            goToForgotStep(3);
            updateStepIndicator(3); // Update step indicator to step 3
            clearInterval(timerInterval);
        });
    }
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate passwords
            if (newPassword.value !== confirmPassword.value) {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.style.color = 'var(--secondary)';
                return;
            }
            
            if (checkPasswordStrength(newPassword.value) < 2) {
                alert('Please choose a stronger password');
                return;
            }
            
            // In a real app, you would send the new password to your backend here
            goToForgotStep(4);
            updateStepIndicator(4); // Update step indicator to step 4 (success)
        });
    }
    
    if (resendBtn) {
        resendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            startTimer();
            resendBtn.style.pointerEvents = 'none';
            resendBtn.style.opacity = '0.5';
            alert('Verification code has been resent to your email.');
        });
    }
    
    // Setup password visibility toggles - FIXED
    // Ensure this runs after DOM is fully loaded
    function initPasswordToggles() {
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        
        if (toggleNewPassword && newPassword) {
            setupPasswordToggle(toggleNewPassword, newPassword);
        }
        
        if (toggleConfirmPassword && confirmPassword) {
            setupPasswordToggle(toggleConfirmPassword, confirmPassword);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordToggles);
    } else {
        initPasswordToggles();
    }
    
    // Setup password strength and matching
    if (newPassword) {
        newPassword.addEventListener('input', updatePasswordStrength);
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', checkPasswordMatch);
    }
    
    // Add event listener for success message button
    const successButton = document.querySelector('.success-message button');
    if (successButton) {
        successButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.formSection').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelector('.login-form').classList.add('active');
        });
    }
}

// Initialize step indicator functions (if not already defined)
function initStepIndicator(labels) {
    // Implementation for step indicator initialization
    console.log("Initializing step indicator with labels:", labels);
}

function updateStepIndicator(step) {
    // Implementation for updating step indicator
    console.log("Updating step indicator to step:", step);
}

// Initialize the forgot password functionality when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initForgotPassword);
} else {
    initForgotPassword();
}
// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize forgot password functionality if elements exist
    if (document.querySelector('.fg-link')) {
        initForgotPassword();
    }
});