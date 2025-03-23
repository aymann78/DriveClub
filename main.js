// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelector('.nav-links');
    const authButtons = document.querySelector('.auth-buttons');
  
    if (menuToggle) {
      menuToggle.addEventListener('click', () => {
        navbar.classList.toggle('active');
        
        // Animación para el menú toggle
        const spans = menuToggle.querySelectorAll('span');
        spans.forEach(span => span.classList.toggle('active'));
      });
    }
  
    // Add scroll event for navbar background
    window.addEventListener('scroll', function() {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  
    // Simple showcase slider functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.showcase-card');
    
    if (slides.length > 0) {
      // Function to move to the next slide
      function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
      }
      
      // Set first slide as active
      slides[0].classList.add('active');
      
      // Auto rotate slides every 5 seconds
      setInterval(nextSlide, 5000);
    }
  
    // Animation on scroll
    const animatedElements = document.querySelectorAll('.feature-card, .plan-card, .section-title, .vehicle-card');
    
    function checkScroll() {
      animatedElements.forEach(el => {
        const elementTop = el.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight * 0.9) {
          el.classList.add('appear');
        }
      });
    }
    
    window.addEventListener('scroll', checkScroll);
    checkScroll(); // Initial check
  
    // Handle plan selection in URL
    const urlParams = new URLSearchParams(window.location.search);
    const planParam = urlParams.get('plan');
    
    if (planParam && document.getElementById('plan')) {
      document.getElementById('plan').value = planParam;
      // If on vehicles page and has a plan parameter, trigger filter
      if (window.location.pathname.includes('vehicles') && typeof filterVehicles === 'function') {
        filterVehicles();
      }
    }
  
    // Dashboard tabs functionality (if on dashboard page)
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    if (tabBtns.length > 0 && tabPanes.length > 0) {
      tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          // Remove active class from all buttons
          tabBtns.forEach(b => b.classList.remove('active'));
          // Add active class to clicked button
          btn.classList.add('active');
          
          // Show corresponding tab pane
          const tabId = btn.getAttribute('data-tab');
          tabPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === tabId) {
              pane.classList.add('active');
            }
          });
        });
      });
    }
  });
  