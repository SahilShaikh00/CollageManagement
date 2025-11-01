document.addEventListener('DOMContentLoaded', async function() {
    const menuToggle = document.getElementById('menuToggle');
    const slideMenu = document.getElementById('slideMenu');
    const overlay = document.getElementById('overlay');
    
    // --- Verify Session ---
    try {
        console.log('Sending request to verify session...');

        const response = await fetch('http://localhost/COLLAGEMANAGEMENT/Config/verify_token.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include' // Include session cookies
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Full response data:', data);

        if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Session verification failed');
        }

        // Session is valid - proceed normally
        console.log('Session verified successfully.');

    } catch (error) {
        console.error('Token verification failed:', error);
        alert('Session expired. Please login again.');
        localStorage.clear();
        window.location.href = '../../Bolg/landing.html';
        return;
    }

    // --- Menu Toggle Functionality ---
    menuToggle.addEventListener('click', function() {
        slideMenu.classList.toggle('menu-open');
        overlay.classList.toggle('menu-open');

        // Change icon
        menuToggle.innerHTML = slideMenu.classList.contains('menu-open') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });

    // Close menu when clicking overlay
    overlay.addEventListener('click', function() {
        slideMenu.classList.remove('menu-open');
        overlay.classList.remove('menu-open');
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    });

    // Close menu when clicking a menu item (mobile)
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') {
                e.preventDefault();
            }
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            if (window.innerWidth <= 768) {
                slideMenu.classList.remove('menu-open');
                overlay.classList.remove('menu-open');
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            slideMenu.classList.remove('menu-open');
            overlay.classList.remove('menu-open');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
});


// --- Logout Functionality ---
const logout = document.querySelector("#Logoutbtn");
logout.addEventListener("click", async () => {
    try {
        const response = await fetch('http://localhost/COLLAGEMANAGEMENT/api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include' // Include session cookies
        });

        const data = await response.json();
        if (data.success) {
            localStorage.clear();
            alert("Logout Successful");
            window.location.href = '../../Bolg/landing.html';
        } else {
            alert("Logout failed");
        }
    } catch (error) {
        console.error('Logout error:', error);
        localStorage.clear();
        alert("Logout Successful");
        window.location.href = '../../Bolg/landing.html';
    }
});
