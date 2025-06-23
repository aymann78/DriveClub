// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchors = document.querySelectorAll('a[href^="#"]');
    
    anchors.forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        
        if (targetId !== '#') {
          e.preventDefault();
          
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            window.scrollTo({
              top: targetElement.offsetTop - 80,
              behavior: 'smooth'
            });
          }
        }
      });
    });
    
    // Featured Vehicle Rotation
    const featuredVehicles = document.querySelectorAll('.featured-vehicle');
    const indicators = document.querySelectorAll('.indicator');
    
    if (featuredVehicles.length > 0) {
      let currentIndex = 0;
      
      // Initialize rotation
      const rotationInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % featuredVehicles.length;
        updateFeaturedVehicle();
      }, 6000);
      
      // Click event for indicators
      indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
          currentIndex = index;
          updateFeaturedVehicle();
          
          // Reset the interval to prevent immediate rotation after manual selection
          clearInterval(rotationInterval);
          setTimeout(() => {
            setInterval(() => {
              currentIndex = (currentIndex + 1) % featuredVehicles.length;
              updateFeaturedVehicle();
            }, 6000);
          }, 6000);
        });
      });
      
      function updateFeaturedVehicle() {
        featuredVehicles.forEach(vehicle => {
          vehicle.classList.remove('active');
        });
        
        indicators.forEach(indicator => {
          indicator.classList.remove('active');
        });
        
        featuredVehicles[currentIndex].classList.add('active');
        indicators[currentIndex].classList.add('active');
      }
    }
    
    // Add animation on scroll
    const animatedElements = document.querySelectorAll('.feature-card, .plan-card, .why-join-feature');
    
    if (animatedElements.length > 0) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = 1;
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, { threshold: 0.1 });
      
      animatedElements.forEach(element => {
        element.style.opacity = 0;
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(element);
      });
    }
    
    // Password toggle functionality
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
      toggle.addEventListener('click', function() {
        const passwordInput = this.parentElement.querySelector('input');
        if (!passwordInput) return;
        
        const icon = this.querySelector('i');
        if (!icon) return;
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        } else {
          passwordInput.type = 'password';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        }
      });
    });
    
    // Formatear números de tarjeta de crédito
    function formatCreditCardNumber(value) {
      value = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
          }
          formattedValue += value[i];
        }
      return formattedValue;
    }
    window.formatCreditCardNumber = formatCreditCardNumber;
    
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
      cardNumberInput.addEventListener('input', function(e) {
        this.value = formatCreditCardNumber(this.value);
      });
    }
    
    // Limitar CVV a solo números
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
      cvvInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }
  });
  
  // Scroll-triggered header style change
  window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    
    if (header) {
      if (window.scrollY > 50) {
        header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
      } else {
        header.style.boxShadow = 'none';
      }
    }
  });

  // Manejo del modal de tarjeta de crédito
document.addEventListener('DOMContentLoaded', function() {
    // Si hay errores de tarjeta, mostrar el modal automáticamente
    const cardErrors = document.querySelector('#addCardModal .alert-danger');
    if (cardErrors) {
      const addCardModal = new bootstrap.Modal(document.getElementById('addCardModal'));
      addCardModal.show();
    }
    
    // Formatear número de tarjeta de crédito con espacios cada 4 dígitos
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
      cardNumberInput.addEventListener('input', function(e) {
        // Eliminar espacios y caracteres no numéricos
        let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        
        // Formatear con espacio cada 4 dígitos
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
          }
          formattedValue += value[i];
        }
        
        // Actualizar el valor manteniendo la posición del cursor
        const cursorPosition = this.selectionStart;
        const lengthDiff = formattedValue.length - this.value.length;
        this.value = formattedValue;
        
        // Restaurar la posición del cursor teniendo en cuenta los espacios agregados
        this.selectionEnd = cursorPosition + lengthDiff;
      });
    }
    
    // Formatear fecha de expiración (MM/AA)
    const expiryDateInput = document.getElementById('expiry_date');
    const expiryMonthInput = document.getElementById('expiry_month');
    const expiryYearInput = document.getElementById('expiry_year');
    
    if (expiryDateInput && expiryMonthInput && expiryYearInput) {
      expiryDateInput.addEventListener('input', function(e) {
        // Eliminar caracteres no numéricos y slash
        let value = this.value.replace(/[^0-9]/g, '');
        
        // Formatear a MM/AA
        if (value.length > 0) {
          // Limitar el mes a números válidos (01-12)
          let month = value.substring(0, 2);
          if (month.length === 1) {
            // Si ingresa un número > 1, agregar 0 al inicio
            if (parseInt(month) > 1) {
              month = '0' + month;
            }
          } else if (month.length === 2) {
            // Si el primer dígito es 1, el segundo debe ser 0-2
            if (month.charAt(0) === '1' && parseInt(month.charAt(1)) > 2) {
              month = '12';
            }
            // No permitir 00 como mes
            if (month === '00') {
              month = '01';
            }
          }
          
          // Formatear con slash
          if (value.length > 2) {
            let year = value.substring(2, 4);
            this.value = month + '/' + year;
            
            // Actualizar campos ocultos
            expiryMonthInput.value = month;
            expiryYearInput.value = year;
          } else {
            this.value = month;
            
            // Actualizar campo oculto
            expiryMonthInput.value = month;
          }
        }
      });
      
      // Al enviar el formulario, asegurarse de que los campos ocultos estén completos
      const addCardForm = document.getElementById('addCardForm');
      if (addCardForm) {
        addCardForm.addEventListener('submit', function(e) {
          const expiryDate = expiryDateInput.value;
          if (expiryDate.includes('/')) {
            const [month, year] = expiryDate.split('/');
            expiryMonthInput.value = month;
            expiryYearInput.value = year;
          } else {
            // Prevenir envío si el formato no es correcto
            e.preventDefault();
            alert('Por favor, introduce una fecha de expiración válida (MM/AA)');
          }
        });
      }
    }
    
    // Limitar CVV a solo números
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
      cvvInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }
    
    // Convertir a mayúsculas el titular de la tarjeta
    const cardHolderInput = document.getElementById('card_holder');
    if (cardHolderInput) {
      cardHolderInput.addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
      });
    }
  });