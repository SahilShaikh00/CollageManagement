
        const toggel = document.getElementById("togglePassword");
        const password = document.getElementById("passwordInput");

        toggel.addEventListener("click",() => {
            const type = password.getAttribute("type") === "password" ? "text" :"password";
            password.setAttribute("type" , type);
            
            toggel.classList.toggle("fa-eye");
        toggel.classList.toggle("fa-eye-slash");
        });