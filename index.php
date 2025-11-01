<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>College Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./landing.css">
 
</head>

<body>
  <!-- Navbar -->
  <header class="navbar">
    <div class="nav-content">
      <h1 class="logo"><i class="fas fa-graduation-cap"></i> SmartEdu </h1>
      <div class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <nav>
        <ul id="navMenu">
          <li><a href="#home">Home</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#contact">Contact</a></li>
          <li><a href="../Auth/Html/login.html" class="login-btn">Login <i class="fas fa-arrow-right"></i></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section id="home" class="hero">
    <div class="hero-text">
      <h2>Smart EDU Management System</h2>
      <p>Manage students, faculty, attendance, and results — all in one secure and easy-to-use platform designed for modern educational institutions.</p>
      <div class="hero-buttons">
        <a href="#features" class="btn">Explore Features <i class="fas fa-arrow-down"></i></a>
        <a href="../Auth/Html/login.html" class="btn btn-outline">Login Now <i class="fas fa-sign-in-alt"></i></a>
      </div>
    </div>
    <div class="hero-image">
      <img src="/Asset/smartEdu.png" alt="College Illustration">
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="features">
    <h2 class="section-title">Our Features</h2>
    <p class="section-subtitle">Discover how our comprehensive platform can transform your educational institution's management processes</p>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <h3>Student Management</h3>
        <p>Manage student details, attendance, academic performance, and progress tracking in one centralized system.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <h3>Faculty Management</h3>
        <p>Track faculty information, courses, schedules, and performance metrics with intuitive tools.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <h3>Course Handling</h3>
        <p>Organize courses, subjects, and link them with respective teachers and classrooms efficiently.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-chart-bar"></i>
        </div>
        <h3>Event & Notice </h3>
        <p>View upcoming events and important announcements in one place.</p>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="about">
    <div class="about-text">
      <h2>About Our System</h2>
      <p>Our College Management System simplifies administrative tasks and provides a unified platform for managing students, faculty, and academics efficiently. It aims to reduce paperwork, minimize errors, and improve communication within educational institutions.</p>
      <p> we've designed a system that adapts to your institution's unique needs while providing an intuitive user experience for administrators, teachers, and students alike.</p>
    </div>
    <div class="about-image">
      <img src="/Asset/smartEdu.png" alt="About Image">
    </div>
  </section>

  <!-- CTA -->
  <section class="cta">
    <h2>Ready to Transform Your Institution?</h2>
    <p>Join hundreds of educational institutions already using EduPortal to streamline their operations and enhance the learning experience.</p>
    <a href="../Auth/Html/login.html" class="btn">Get Started Today <i class="fas fa-arrow-right"></i></a>
  </section>

  <!-- Contact -->
  <section id="contact" class="contact">
    <h2 class="section-title">Contact Us</h2>
    <p class="section-subtitle">Get in touch with our team for a personalized demo or answers to your questions</p>
    <div class="contact-info">
      <div class="contact-item">
        <div class="contact-icon">
          <i class="fas fa-map-marker-alt"></i>
        </div>
        <h3>Location</h3>
        <p>Pune, Maharashtra, India</p>
      </div>
      <div class="contact-item">
        <div class="contact-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <h3>Email</h3>
        <p>4621sahilshaikh@gmail.com</p>
      </div>
      <div class="contact-item">
        <div class="contact-icon">
          <i class="fas fa-phone"></i>
        </div>
        <h3>Phone</h3>
        <p>+91 98765 43210</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-column">
        <h3>EduPortal</h3>
        <p>Transforming educational management through innovative technology solutions designed for modern institutions.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul class="footer-links">
          <li><a href="#home">Home</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#about">About Us</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>     
    </div>
    <div class="copyright">
      © 2025 EduPortal. All Rights Reserved.
    </div>
  </footer>
<script src="./landing.js"></script>
</body>
</html>