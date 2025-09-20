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

// Login form handling
document.getElementById('loginForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const button = e.target.querySelector('button');
  const originalText = button.textContent;
  
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
  button.disabled = true;
  
  // Simulate login process
  setTimeout(() => {
    button.textContent = originalText;
    button.disabled = false;
  }, 1500);
});

// SIGNUP FORM - ONLY THIS EVENT LISTENER SHOULD EXIST
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