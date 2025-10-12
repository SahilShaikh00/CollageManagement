    document.addEventListener('DOMContentLoaded', async function() {
        const menuToggle = document.getElementById('menuToggle');
        const slideMenu = document.getElementById('slideMenu');
        const overlay = document.getElementById('overlay');
        
        // Get token and ensure it's valid
        let token = localStorage.getItem('token');
        
        // Debug token retrieval
        console.log('Token from localStorage:', token ? token.substring(0, 20) + '...' : 'NULL');
        
        if(!token || token === 'null' || token === 'undefined' || token === '') {
            alert('Please Login First');
            window.location.href ='../../Bolg/landing.html';
            return;
        }

        try {
            // Clean the token (remove quotes if any)
            token = token.replace(/^"(.*)"$/, '$1').trim();
            
            console.log('Sending request to verify token...');
            
            const response = await fetch('http://localhost/COLLAGEMANAGEMENT/Config/verify_token.php', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('Response status:', response.status);
            
            const data = await response.json();
            console.log('Full response data:', data);
            
            if(!response.ok || data.success === false) {
                throw new Error(data.message || 'Token verification failed');
            }
            
            // Token is valid - store user info
            if(data.user) {
                localStorage.setItem('user_id', data.user.id);
                localStorage.setItem('user_email', data.user.email);
                localStorage.setItem('role', data.user.role);
            }
            
            // Continue with role-based logic
            const role = data.user.role;
            console.log('User role:', role);
            
            // Update user-role text
            const userRoleElem = document.querySelector('.user-role');
            if (userRoleElem && role) {
                userRoleElem.textContent = role.charAt(0).toUpperCase() + role.slice(1);
            }

            // Role-based menu items visibility
            if (role === 'student') {
                document.querySelectorAll('.menu-item').forEach(item => {
                    const text = item.querySelector('.menu-text').textContent.toLowerCase();
                    if (['students','faculty','courses','schedule','attendance','grades','reports'].includes(text)) {
                        item.style.display = 'none';
                    }
                });
            } else if (role === 'teacher') {
                document.querySelectorAll('.menu-item').forEach(item => {
                    const text = item.querySelector('.menu-text').textContent.toLowerCase();
                    if (['settings','help'].includes(text)) {
                        item.style.display = 'none';
                    }
                });
            }
            // Admin sees everything (no need to hide anything)

        } catch(error) {
            console.error('Token verification failed:', error);
            alert('Session expired. Please login again.');
            localStorage.clear();
            window.location.href = '../../Bolg/landing.html';
            return;
        }

        // -------------------------
        // Menu toggle functionality
        // -------------------------
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
                e.preventDefault();
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



    const logout = document.querySelector("#Logoutbtn");
    logout.addEventListener("click", () => {
        localStorage.clear();
        alert("Logout Successful");   
        window.location.href = '../../Bolg/landing.html';
    });


