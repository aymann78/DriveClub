// Sample vehicle data
const vehicles = [
    {
      id: 1,
      name: "Mazda MX-5",
      category: "Sport",
      plan: "basic",
      image: "https://images.unsplash.com/photo-1600706932019-1ebdb9ec8bf4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1374&q=80",
      specs: {
        power: "181 CV",
        acceleration: "6.5s",
        topSpeed: "219 km/h"
      },
      price: 89
    },
    {
      id: 2,
      name: "BMW M2 Competition",
      category: "Sport",
      plan: "premium",
      image: "https://images.unsplash.com/photo-1580274455191-1c62238fa333?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=764&q=80",
      specs: {
        power: "410 CV",
        acceleration: "4.2s",
        topSpeed: "250 km/h"
      },
      price: 149
    },
    {
      id: 3,
      name: "Aston Martin Vantage",
      category: "Super",
      plan: "premium",
      image: "https://images.unsplash.com/photo-1617531653332-bd46c24f2068?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1015&q=80",
      specs: {
        power: "510 CV",
        acceleration: "3.6s",
        topSpeed: "314 km/h"
      },
      price: 249
    },
    {
      id: 4,
      name: "Ferrari 488 GTB",
      category: "Super",
      plan: "elite",
      image: "https://images.unsplash.com/photo-1592198084033-aade902d1aae?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80",
      specs: {
        power: "670 CV",
        acceleration: "3.0s",
        topSpeed: "330 km/h"
      },
      price: 399
    },
    {
      id: 5,
      name: "Lamborghini Huracán",
      category: "Super",
      plan: "elite",
      image: "https://images.unsplash.com/photo-1519245659620-e859806a8d3b?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=687&q=80",
      specs: {
        power: "640 CV",
        acceleration: "2.9s",
        topSpeed: "325 km/h"
      },
      price: 429
    },
    {
      id: 6,
      name: "Porsche 911 GT3",
      category: "Sport",
      plan: "premium",
      image: "https://images.unsplash.com/photo-1611016186353-9af58c69a533?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1471&q=80",
      specs: {
        power: "510 CV",
        acceleration: "3.4s",
        topSpeed: "318 km/h"
      },
      price: 219
    }
  ];
  
  // DOM Elements
  const vehiclesContainer = document.getElementById('vehicles-container');
  const categoryFilter = document.getElementById('category');
  const brandFilter = document.getElementById('brand');
  const planFilter = document.getElementById('plan');
  
  // Function to render vehicle cards
  function renderVehicles(vehiclesToRender) {
    vehiclesContainer.innerHTML = '';
    
    if (vehiclesToRender.length === 0) {
      vehiclesContainer.innerHTML = '<p class="no-results">No se encontraron vehículos con los filtros seleccionados.</p>';
      return;
    }
    
    vehiclesToRender.forEach(vehicle => {
      const planClass = `plan-${vehicle.plan}`;
      
      const vehicleCard = document.createElement('div');
      vehicleCard.classList.add('vehicle-card');
      vehicleCard.innerHTML = `
        <div class="vehicle-image">
          <img src="${vehicle.image}" alt="${vehicle.name}">
          <div class="vehicle-plan ${planClass}">${getPlanName(vehicle.plan)}</div>
        </div>
        <div class="vehicle-details">
          <h3 class="vehicle-name">${vehicle.name}</h3>
          <p class="vehicle-category">${vehicle.category}</p>
          <div class="vehicle-specs">
            <div class="spec">
              <span class="spec-value">${vehicle.specs.power}</span>
              <span class="spec-label">Potencia</span>
            </div>
            <div class="spec">
              <span class="spec-value">${vehicle.specs.acceleration}</span>
              <span class="spec-label">0-100 km/h</span>
            </div>
            <div class="spec">
              <span class="spec-value">${vehicle.specs.topSpeed}</span>
              <span class="spec-label">Vel. Máx.</span>
            </div>
          </div>
          <div class="vehicle-price">
            <div class="price">${vehicle.price}€<span>/día</span></div>
            <a href="vehicle.html?id=${vehicle.id}" class="btn-reserve">Reservar</a>
          </div>
        </div>
      `;
      
      vehiclesContainer.appendChild(vehicleCard);
    });
  }
  
  // Helper function to get proper plan name
  function getPlanName(plan) {
    switch(plan) {
      case 'basic':
        return 'Básico';
      case 'premium':
        return 'Premium';
      case 'elite':
        return 'Elite';
      default:
        return '';
    }
  }
  
  // Filter vehicles based on selected filters
  function filterVehicles() {
    const categoryValue = categoryFilter.value;
    const brandValue = brandFilter.value;
    const planValue = planFilter.value;
    
    let filteredVehicles = [...vehicles];
    
    if (categoryValue !== 'all') {
      filteredVehicles = filteredVehicles.filter(v => 
        v.category.toLowerCase() === categoryValue
      );
    }
    
    if (brandValue !== 'all') {
      filteredVehicles = filteredVehicles.filter(v => 
        v.name.toLowerCase().includes(getBrandKeyword(brandValue))
      );
    }
    
    if (planValue !== 'all') {
      filteredVehicles = filteredVehicles.filter(v => 
        v.plan === planValue
      );
    }
    
    renderVehicles(filteredVehicles);
  }
  
  // Helper function to match brand values with vehicle names
  function getBrandKeyword(brand) {
    switch(brand) {
      case 'mazda':
        return 'mazda';
      case 'bmw':
        return 'bmw';
      case 'aston':
        return 'aston';
      case 'ferrari':
        return 'ferrari';
      case 'lamborghini':
        return 'lamborghini';
      case 'porsche':
        return 'porsche';
      default:
        return '';
    }
  }
  
  // Event listeners for filters
  categoryFilter.addEventListener('change', filterVehicles);
  brandFilter.addEventListener('change', filterVehicles);
  planFilter.addEventListener('change', filterVehicles);
  
  // Mobile menu toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const navbar = document.querySelector('.navbar');
  
  if (menuToggle) {
    menuToggle.addEventListener('click', () => {
      navbar.classList.toggle('active');
    });
  }
  
  // Add smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });
  
  // Initialize: render all vehicles on page load
  document.addEventListener('DOMContentLoaded', () => {
    renderVehicles(vehicles);
  
    // Add a little animation when scrolling
    const handleScroll = () => {
      const vehicleCards = document.querySelectorAll('.vehicle-card');
      
      vehicleCards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (cardTop < windowHeight * 0.9) {
          card.classList.add('appear');
        }
      });
    };
    
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
  });
  