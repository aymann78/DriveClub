document.addEventListener('DOMContentLoaded', function() {
    // Reservation date picker functionality
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    if (startDateInput && endDateInput) {
      // Set minimum date to today
      const today = new Date();
      const isoDate = today.toISOString().split('T')[0];
      startDateInput.min = isoDate;
      
      // Update end date based on start date
      startDateInput.addEventListener('change', function() {
        if (this.value) {
          const minEndDate = new Date(this.value);
          minEndDate.setDate(minEndDate.getDate() + 1);
          endDateInput.min = minEndDate.toISOString().split('T')[0];
          
          if (!endDateInput.value || new Date(endDateInput.value) <= new Date(this.value)) {
            const defaultEndDate = new Date(this.value);
            defaultEndDate.setDate(defaultEndDate.getDate() + 7);
            endDateInput.value = defaultEndDate.toISOString().split('T')[0];
          }
        }
      });
      
      // Initialize with some default dates if not set by PHP
      if (!startDateInput.value) {
        const defaultStartDate = new Date();
        defaultStartDate.setDate(defaultStartDate.getDate() + 3);
        startDateInput.value = defaultStartDate.toISOString().split('T')[0];
      }
      
      if (!endDateInput.value) {
        const defaultEndDate = new Date();
        defaultEndDate.setDate(defaultEndDate.getDate() + 10);
        endDateInput.value = defaultEndDate.toISOString().split('T')[0];
      }
      
      endDateInput.min = startDateInput.value;
    }
  
    // Add class to vehicle detail page wrapper and sections for responsive ordering
    const vehicleDetailContainer = document.querySelector('.vehicle-detail-section .container > .row');
    const mainContentColumn = document.querySelector('.vehicle-detail-section .container > .row > .col-lg-8');
    const sidebarColumn = document.querySelector('.vehicle-detail-section .container > .row > .col-lg-4');
    
    if (vehicleDetailContainer && mainContentColumn && sidebarColumn) {
      vehicleDetailContainer.classList.add('vehicle-detail-container');
      mainContentColumn.classList.add('vehicle-content-section');
      sidebarColumn.classList.add('reservation-sidebar-section');
      
      // Handle mobile-first ordering
      function updateColumnOrder() {
        if (window.innerWidth < 992) {
          // Mover sidebar después de las imágenes pero antes de las especificaciones técnicas
          const imagesSection = mainContentColumn.querySelector('.vehicle-images');
          const techSpecsSection = mainContentColumn.querySelector('.technical-specs');
          
          if (imagesSection && techSpecsSection && sidebarColumn.parentNode) {
            // Primero devolvemos el sidebar a su posición original si estuviera en otra posición
            vehicleDetailContainer.appendChild(sidebarColumn);
            
            // Ahora lo insertamos después de las imágenes
            mainContentColumn.insertBefore(sidebarColumn, techSpecsSection);
          }
        } else {
          // En desktop - restaurar orden original
          if (sidebarColumn.parentNode === mainContentColumn) {
            vehicleDetailContainer.appendChild(sidebarColumn);
          }
        }
      }
      
      // Initial call
      updateColumnOrder();
      
      // Add event listener for window resize
      window.addEventListener('resize', updateColumnOrder);
    }
    
    // Add event listener for carousel controls
    const carouselPrev = document.querySelector('.carousel-control-prev');
    const carouselNext = document.querySelector('.carousel-control-next');
    
    if (carouselPrev && carouselNext) {
      const handleCarouselNavigation = function(direction) {
        const carousel = document.getElementById('vehicleCarousel');
        if (!carousel) return;
        
        const items = carousel.querySelectorAll('.carousel-item');
        if (items.length <= 1) return;
        
        const activeItem = carousel.querySelector('.carousel-item.active');
        if (!activeItem) return;
        
        let nextIndex = 0;
        
        // Find current active index
        for (let i = 0; i < items.length; i++) {
          if (items[i] === activeItem) {
            if (direction === 'next') {
              nextIndex = (i + 1) % items.length;
            } else {
              nextIndex = (i - 1 + items.length) % items.length;
            }
            break;
          }
        }
        
        activeItem.classList.remove('active');
        items[nextIndex].classList.add('active');
      };
      
      carouselPrev.addEventListener('click', function(e) {
        e.preventDefault();
        handleCarouselNavigation('prev');
      });
      
      carouselNext.addEventListener('click', function(e) {
        e.preventDefault();
        handleCarouselNavigation('next');
      });
    }
  });