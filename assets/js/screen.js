document.addEventListener('DOMContentLoaded', function() {
  // Screen transitions
  const transitions = {
    fadeIn: function(element, callback) {
      element.style.opacity = 0;
      element.style.display = 'block';
      
      setTimeout(() => {
        element.style.transition = 'opacity 0.5s ease';
        element.style.opacity = 1;
        
        if (callback && typeof callback === 'function') {
          setTimeout(callback, 500);
        }
      }, 10);
    },
    
    fadeOut: function(element, callback) {
      element.style.transition = 'opacity 0.5s ease';
      element.style.opacity = 0;
      
      setTimeout(() => {
        element.style.display = 'none';
        
        if (callback && typeof callback === 'function') {
          callback();
        }
      }, 500);
    },
    
    slideIn: function(element, callback) {
      element.style.transform = 'translateX(100%)';
      element.style.display = 'block';
      
      setTimeout(() => {
        element.style.transition = 'transform 0.5s ease';
        element.style.transform = 'translateX(0)';
        
        if (callback && typeof callback === 'function') {
          setTimeout(callback, 500);
        }
      }, 10);
    },
    
    slideOut: function(element, callback) {
      element.style.transition = 'transform 0.5s ease';
      element.style.transform = 'translateX(-100%)';
      
      setTimeout(() => {
        element.style.display = 'none';
        
        if (callback && typeof callback === 'function') {
          callback();
        }
      }, 500);
    }
  };
  
  // Screen initialization
  const initScreen = {
    Home: function() {
      console.log('Home screen initialized');
      // Add any specific home screen initialization
    },
    
    Vehicles: function() {
      console.log('Vehicles screen initialized');
      // Add any specific vehicles screen initialization
    },
    
    Plans: function() {
      console.log('Plans screen initialized');
      // Add any specific plans screen initialization
    },
    
    Account: function() {
      console.log('Account screen initialized');
      // Add any specific account screen initialization
    },
    
    Login: function() {
      console.log('Login screen initialized');
      // Add any specific login screen initialization
    },
    
    Vehicle: function() {
      console.log('Vehicle detail screen initialized');
      // Add any specific vehicle detail screen initialization
    }
  };
  
  // Determine current screen
  const path = window.location.pathname;
  let currentScreen = 'Home';
  
  // Actualizado para archivos .php
  if (path.includes('vehiculos.php')) {
    currentScreen = 'Vehicles';
  } else if (path.includes('planes.php')) {
    currentScreen = 'Plans';
  } else if (path.includes('mi-cuenta.php')) {
    currentScreen = 'Account';
  } else if (path.includes('login.php')) {
    currentScreen = 'Login';
  } else if (path.includes('detalle-vehiculo.php')) {
    currentScreen = 'Vehicle';
  }
  
  // Initialize current screen
  if (initScreen[currentScreen]) {
    initScreen[currentScreen]();
  }
  
  // Add transition effects to internal links
  const internalLinks = document.querySelectorAll('a:not([target="_blank"]):not([download])');
  
  internalLinks.forEach(link => {
    // Solo aplicar a enlaces internos (ignorar links que empiezan con http o #)
    const href = link.getAttribute('href');
    if (href && !href.startsWith('http') && !href.startsWith('#')) {
      link.addEventListener('click', function(e) {
        // Skip if modifier keys are pressed
        if (e.metaKey || e.ctrlKey || e.shiftKey) {
          return;
        }
        
        // Skip if it's a form action or special link
        if (this.getAttribute('role') === 'button' || this.hasAttribute('data-bs-toggle')) {
          return;
        }
        
        e.preventDefault();
        
        document.body.classList.add('is-fading');
        
        setTimeout(() => {
          window.location.href = href;
        }, 500);
      });
    }
  });
  
  // Restaurar opacidad al volver atrás o reactivar la pestaña
  function restoreBodyOpacity() {
    document.body.classList.remove('is-fading');
    document.body.style.opacity = 1;
  }
  
  document.addEventListener('DOMContentLoaded', restoreBodyOpacity);
  window.addEventListener('pageshow', restoreBodyOpacity);
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) restoreBodyOpacity();
  });
});