document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const navbar = document.querySelector('.navbar');
  
  if (menuToggle) {
    menuToggle.addEventListener('click', function() {
      navbar.classList.toggle('active');
    });
  }

  // Car data organized by plan
  const vehicles = {
    basic: [
      {
        id: 'mazda-mx5-2022',
        name: 'Mazda MX‑5 Miata',
        year: '2022',
        category: 'Convertible Deportivo',
        image: 'https://media.ed.edmunds-media.com/mazda/hero/mazda_hero_502_1600.jpg',
        specs: {
          power: '181 CV',
          acceleration: '6.5s',
          transmission: 'Manual',
          traction: 'RWD'
        },
        price: 89
      },
      {
        id: 'toyota-gr86-2022',
        name: 'Toyota GR86',
        year: '2022',
        category: 'Coupé Deportivo',
        image: 'https://ddztmb1ahc6o7.cloudfront.net/gatewaytoyota/wp-content/uploads/2022/03/07145249/2022_GR86_Base_TruenoBlue_021_mid.jpg',
        specs: {
          power: '228 CV',
          acceleration: '6.1s',
          transmission: 'Manual',
          traction: 'RWD'
        },
        price: 95
      },
      {
        id: 'subaru-brz-2022',
        name: 'Subaru BRZ',
        year: '2022',
        category: 'Coupé Deportivo',
        image: 'https://ecoloauto.com/wp-content/uploads/2022-Subaru-BRZ-Sport-tech-01-MD.jpg',
        specs: {
          power: '228 CV',
          acceleration: '6.2s',
          transmission: 'Manual',
          traction: 'RWD'
        },
        price: 95
      },
      {
        id: 'fiat-124spider-2019',
        name: 'Fiat 124 Spider',
        year: '2019',
        category: 'Convertible Deportivo',
        image: 'https://carsguide-res.cloudinary.com/image/upload/f_auto,fl_lossy,q_auto,t_default/v1/editorial/abarth-124-spider-monza-my19-1200x800-(19).jpg',
        specs: {
          power: '164 CV',
          acceleration: '6.8s',
          transmission: 'Manual',
          traction: 'RWD'
        },
        price: 85
      },
      {
        id: 'nissan-370z-2020',
        name: 'Nissan 370Z',
        year: '2020',
        category: 'Coupé Deportivo',
        image: 'https://www.topgear.es/sites/navi.axelspringer.es/public/media/image/2023/07/nissan-370z-3080192.jpg',
        specs: {
          power: '332 CV',
          acceleration: '5.2s',
          transmission: 'Manual',
          traction: 'RWD'
        },
        price: 125
      },
      {
        id: 'bmw-z4-2021',
        name: 'BMW Z4',
        year: '2021',
        category: 'Convertible Deportivo',
        image: 'https://mediapool.bmwgroup.com/cache/P9/202209/P90479446/P90479446-bmw-z4-m40i-09-2022-600px.jpg',
        specs: {
          power: '255 CV',
          acceleration: '5.4s',
          transmission: 'Automática',
          traction: 'RWD'
        },
        price: 130
      },
      {
        id: 'honda-civic-typer-2022',
        name: 'Honda Civic Type R',
        year: '2022',
        category: 'Hatchback Deportivo',
        image: 'https://wieck-honda-production.s3.amazonaws.com/photos/aaf14fc8ae21ef4a43b40394791a7089a9be5638/preview-928x522.jpg',
        specs: {
          power: '320 CV',
          acceleration: '5.8s',
          transmission: 'Manual',
          traction: 'FWD'
        },
        price: 120
      },
      {
        id: 'volkswagen-gti-2022',
        name: 'Volkswagen Golf GTI',
        year: '2022',
        category: 'Hatchback Deportivo',
        image: 'https://www.cnet.com/a/img/resize/d0e13c91f648afcf4718f486d294c450f5ec3fd0/hub/2022/03/30/4d055b2e-d0c6-4858-9208-5818efeeadc3/2022-vw-golf-gti-long-term-009.jpg?auto=webp&width=1200',
        specs: {
          power: '241 CV',
          acceleration: '6.3s',
          transmission: 'DSG',
          traction: 'FWD'
        },
        price: 110
      },
      {
        id: 'ford-mustang-eco-2021',
        name: 'Ford Mustang EcoBoost',
        year: '2021',
        category: 'Coupé Deportivo',
        image: 'https://carsguide-res.cloudinary.com/image/upload/f_auto,fl_lossy,q_auto,t_default/v1/editorial/review/hero_image/2021-Ford-Mustang-GT-auto=green-coupe-peter-anderson-1001x565-(1).jpg',
        specs: {
          power: '310 CV',
          acceleration: '5.5s',
          transmission: 'Automática',
          traction: 'RWD'
        },
        price: 115
      },
      {
        id: 'hyundai-veloster-n-2022',
        name: 'Hyundai Veloster N',
        year: '2022',
        category: 'Hatchback Deportivo',
        image: 'https://www.cnet.com/a/img/resize/287dfaf309b3fed59f9933c8e5899620c56ea09d/hub/2022/04/19/8da47739-c866-4aff-9be9-1ba2d1b8f9d9/ogi-2022-hyundai-veloster-n-42.jpg?auto=webp&fit=crop&height=675&width=1200',
        specs: {
          power: '275 CV',
          acceleration: '5.6s',
          transmission: 'Manual',
          traction: 'FWD'
        },
        price: 105
      }
    ],
    premium: [
      {
        id: 'lancia-delta-integrale-1992',
        name: 'Lancia Delta HF Integrale',
        year: '1992',
        category: 'Clásico Rally',
        image: 'https://uncrate.com/p/2017/11/lancia-delt-hf-integrale-evo-1.jpg',
        specs: {
          power: '212 CV',
          acceleration: '5.7s',
          transmission: 'Manual',
          traction: 'AWD'
        },
        price: 180
      },
      {
        id: 'bmw-m240i-2022',
        name: 'BMW M240i',
        year: '2022',
        category: 'Coupé Performance',
        image: 'https://images.turo.com/media/vehicle/images/DKr8lDXBTqivYrTTQhPbwA.jpg',
        specs: {
          power: '382 CV',
          acceleration: '4.3s',
          transmission: 'Automática',
          traction: 'AWD'
        },
        price: 165
      },
      {
        id: 'audi-s3-2022',
        name: 'Audi S3',
        year: '2022',
        category: 'Compact Performance',
        image: 'https://images.carexpert.com.au/resize/3000/-/app/uploads/2022/08/2022-Audi-S3-Sportback-HERO-2.jpg',
        specs: {
          power: '306 CV',
          acceleration: '4.8s',
          transmission: 'S-Tronic',
          traction: 'Quattro'
        },
        price: 160
      },
      {
        id: 'porsche-718-cayman-2022',
        name: 'Porsche 718 Cayman',
        year: '2022',
        category: 'Sports Coupé',
        image: 'https://news.dupontregistry.com/wp-content/uploads/2022/09/cayman-t-1.jpg',
        specs: {
          power: '300 CV',
          acceleration: '4.9s',
          transmission: 'PDK',
          traction: 'RWD'
        },
        price: 195
      },
      {
        id: 'subaru-wrx-sti-2022',
        name: 'Subaru WRX STI',
        year: '2022',
        category: 'Sports Sedan',
        image: 'https://static1.topspeedimages.com/wordpress/wp-content/uploads/jpg/201701/2018-subaru-wrx-sti-6.jpg',
        specs: {
          power: '310 CV',
          acceleration: '5.2s',
          transmission: 'Manual',
          traction: 'AWD'
        },
        price: 150
      }
    ],
    elite: [
      {
        id: 'porsche-gt3-touring-2023',
        name: 'Porsche 992 GT3 Touring',
        year: '2023',
        category: 'Superdeportivo',
        image: 'https://richmonds.com.au/wp-content/uploads/2024/02/Porsche-992-GT3-Silver-3.jpg',
        specs: {
          power: '510 CV',
          acceleration: '3.4s',
          transmission: 'PDK',
          traction: 'RWD'
        },
        price: 399
      },
      {
        id: 'porsche-911-carrera-gts-2022',
        name: 'Porsche 911 Carrera GTS',
        year: '2022',
        category: 'Superdeportivo',
        image: 'https://images.unsplash.com/photo-1611651338412-8403fa6e3599?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        specs: {
          power: '473 CV',
          acceleration: '3.3s',
          transmission: 'PDK',
          traction: 'RWD'
        },
        price: 350
      },
      {
        id: 'mclaren-570s-2021',
        name: 'McLaren 570S',
        year: '2021',
        category: 'Superdeportivo',
        image: 'https://www.charlottemclaren.com/wp-content/uploads/2018/11/MCLAREN-570S.jpg',
        specs: {
          power: '570 CV',
          acceleration: '3.2s',
          transmission: 'Automática',
          traction: 'RWD'
        },
        price: 450
      },
      {
        id: 'nissan-gtr-nismo-2022',
        name: 'Nissan GT-R Nismo',
        year: '2022',
        category: 'Superdeportivo',
        image: 'https://car-images.bauersecure.com/wp-images/13098/2022-gt-r-nismo-07.jpg',
        specs: {
          power: '600 CV',
          acceleration: '2.5s',
          transmission: 'Automática',
          traction: 'AWD'
        },
        price: 425
      },
      {
        id: 'aston-martin-vantage-2020',
        name: 'Aston Martin Vantage',
        year: '2020',
        category: 'GT Deportivo',
        image: 'https://www.motortrend.com/uploads/sites/11/2020/07/2020-Aston-Martin-Vantage-front-three-quarter-1.jpg',
        specs: {
          power: '503 CV',
          acceleration: '3.6s',
          transmission: 'Automática',
          traction: 'RWD'
        },
        price: 380
      }
    ]
  };

  // Function to create vehicle cards
  function createVehicleCard(vehicle, plan) {
    return `
      <div class="vehicle-card">
        <div class="vehicle-image">
          <img src="${vehicle.image}" alt="${vehicle.name}">
          <div class="vehicle-plan plan-${plan}">${plan.charAt(0).toUpperCase() + plan.slice(1)}</div>
        </div>
        <div class="vehicle-details">
          <h3 class="vehicle-name">${vehicle.name} <br> ${vehicle.year}</h3>
          <p class="vehicle-category">${vehicle.category}</p>
          <div class="vehicle-specs">
            <div class="spec">
              <span class="spec-value">${vehicle.specs.power}</span>
              <span class="spec-label">CV</span>
            </div>
            <div class="spec">
              <span class="spec-value">${vehicle.specs.acceleration}</span>
              <span class="spec-label">0-100</span>
            </div>
            <div class="spec">
              <span class="spec-value">${vehicle.specs.traction}</span>
              <span class="spec-label">Tracción</span>
            </div>
          </div>
          <div class="vehicle-price">
            <div class="price">${vehicle.price}€<span>/día</span></div>
            <a href="vehicle.html?id=${vehicle.id}" class="btn-reserve">Ver</a>
          </div>
        </div>
      </div>
    `;
  }

  // Populate vehicles on page load
  const vehiclesContainer = document.getElementById('vehicles-container');
  
  if (vehiclesContainer) {
    // Get selected plan from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const selectedPlan = urlParams.get('plan');
    
    // Initialize with all vehicles or filtered by plan
    let allVehicles = '';
    
    if (selectedPlan && vehicles[selectedPlan]) {
      vehicles[selectedPlan].forEach(vehicle => {
        allVehicles += createVehicleCard(vehicle, selectedPlan);
      });
    } else {
      // Show all plans
      for (const plan in vehicles) {
        vehicles[plan].forEach(vehicle => {
          allVehicles += createVehicleCard(vehicle, plan);
        });
      }
    }
    
    vehiclesContainer.innerHTML = allVehicles;
    
    // Filter functionality
    const categorySelect = document.getElementById('category');
    const brandSelect = document.getElementById('brand');
    const planSelect = document.getElementById('plan');
    
    function filterVehicles() {
      const selectedCategory = categorySelect.value;
      const selectedBrand = brandSelect.value;
      const selectedPlanFilter = planSelect.value;
      
      let filteredVehicles = '';
      
      for (const plan in vehicles) {
        // Skip if not matching plan filter
        if (selectedPlanFilter !== 'all' && plan !== selectedPlanFilter) continue;
        
        vehicles[plan].forEach(vehicle => {
          // Skip if not matching category filter
          if (selectedCategory !== 'all') {
            const vehicleCategory = vehicle.category.toLowerCase();
            if (!vehicleCategory.includes(selectedCategory.toLowerCase())) return;
          }
          
          // Skip if not matching brand filter
          if (selectedBrand !== 'all') {
            const vehicleBrand = vehicle.name.split(' ')[0].toLowerCase();
            if (vehicleBrand !== selectedBrand.toLowerCase()) return;
          }
          
          filteredVehicles += createVehicleCard(vehicle, plan);
        });
      }
      
      vehiclesContainer.innerHTML = filteredVehicles.length > 0 ? 
        filteredVehicles : 
        '<div class="no-results">No se encontraron vehículos con los filtros seleccionados.</div>';
    }
    
    if (categorySelect && brandSelect && planSelect) {
      categorySelect.addEventListener('change', filterVehicles);
      brandSelect.addEventListener('change', filterVehicles);
      planSelect.addEventListener('change', filterVehicles);
      
      // Set initial plan filter if from URL
      if (selectedPlan) {
        planSelect.value = selectedPlan;
      }
    }
  }

  // Vehicle detail page functionality
  function initVehicleDetailPage() {
    // Get vehicle ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get('id');
    
    if (!vehicleId) return;
    
    // Find the vehicle
    let selectedVehicle = null;
    let vehiclePlan = '';
    
    for (const plan in vehicles) {
      const found = vehicles[plan].find(v => v.id === vehicleId);
      if (found) {
        selectedVehicle = found;
        vehiclePlan = plan;
        break;
      }
    }
    
    if (!selectedVehicle) return;
    
    // Update page with vehicle details
    document.querySelector('.vehicle-hero-content h1').textContent = `${selectedVehicle.name} - ${selectedVehicle.year}`;
    document.querySelector('.vehicle-category').textContent = selectedVehicle.category;
    document.querySelector('.vehicle-plan-badge').textContent = `Plan ${vehiclePlan.charAt(0).toUpperCase() + vehiclePlan.slice(1)}`;
    document.querySelector('.vehicle-plan-badge').className = `vehicle-plan-badge plan-${vehiclePlan}`;
    
    // Update main image
    document.querySelector('.main-image img').src = selectedVehicle.image;
    
    // Update specs
    document.querySelector('.spec-value:nth-child(3)').textContent = selectedVehicle.specs.power;
    document.querySelector('.spec-value:nth-child(6)').textContent = selectedVehicle.specs.acceleration;
    document.querySelector('.spec-value:nth-child(9)').textContent = selectedVehicle.specs.transmission;
    
    // Update plan info in reservation box
    document.querySelector('.plan-badge').className = `plan-badge plan-${vehiclePlan}`;
    document.querySelector('.plan-badge').textContent = `Plan ${vehiclePlan.charAt(0).toUpperCase() + vehiclePlan.slice(1)}`;
    
    const planPrices = {
      basic: '89€/mes',
      premium: '149€/mes',
      elite: '399€/mes'
    };
    
    document.querySelector('.plan-price').textContent = `desde ${planPrices[vehiclePlan]}`;
    
    // Initialize date picker functionality
    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    
    if (pickupDate && returnDate) {
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      pickupDate.min = today.toISOString().split('T')[0];
      pickupDate.value = tomorrow.toISOString().split('T')[0];
      
      returnDate.min = tomorrow.toISOString().split('T')[0];
      returnDate.value = new Date(tomorrow.setDate(tomorrow.getDate() + 2)).toISOString().split('T')[0];
      
      // Update availability when dates change
      function updateAvailability() {
        if (!pickupDate.value || !returnDate.value) return;
        
        // Simple availability check (weekends unavailable for demo purposes)
        const pickup = new Date(pickupDate.value);
        const dropoff = new Date(returnDate.value);
        const isWeekend = pickup.getDay() === 0 || pickup.getDay() === 6 || 
                          dropoff.getDay() === 0 || dropoff.getDay() === 6;
        
        const availabilityInfo = document.querySelector('.availability-info');
        
        if (isWeekend) {
          availabilityInfo.innerHTML = `
            <div class="availability unavailable">
              <span class="icon"><i class="fa-solid fa-xmark"></i></span>
              <span class="text">No disponible en las fechas seleccionadas</span>
            </div>
          `;
        } else {
          availabilityInfo.innerHTML = `
            <div class="availability available">
              <span class="icon"><i class="fa-solid fa-check"></i></span>
              <span class="text">Disponible en las fechas seleccionadas</span>
            </div>
          `;
        }
      }
      
      pickupDate.addEventListener('change', function() {
        // Ensure return date is after pickup date
        const newMinDate = new Date(this.value);
        returnDate.min = newMinDate.toISOString().split('T')[0];
        
        // If return date is now before pickup date, update it
        if (new Date(returnDate.value) <= newMinDate) {
          newMinDate.setDate(newMinDate.getDate() + 1);
          returnDate.value = newMinDate.toISOString().split('T')[0];
        }
        
        updateAvailability();
      });
      
      returnDate.addEventListener('change', updateAvailability);
      
      // Initial availability check
      updateAvailability();
    }
  }
  
  // Run vehicle detail initialization if on vehicle.html
  if (window.location.pathname.includes('vehicle.html')) {
    initVehicleDetailPage();
  }
});
