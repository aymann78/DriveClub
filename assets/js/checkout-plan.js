// Estado local del checkout
const checkoutState = {
  personal: null, // {nombre, apellidos, telefono, direccion, ciudad, postal, pais}
  card_id: null, // id de la tarjeta seleccionada (si es guardada)
  card: null,    // datos de nueva tarjeta (si se añade)
  termsAccepted: false,
  submitting: false
};

let savedCards = window.DRIVECLUB_CHECKOUT_PLAN.cards || [];

// Utilidades
function showFlash(type, msg) {
  // Elimina el viejo flash si existe
  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(toastContainer);
  }
  // Limpia toasts previos
  toastContainer.innerHTML = '';
  // Crea el toast
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : type} border-0 show`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  `;
  toastContainer.appendChild(toast);
  // Cierre manual
  toast.querySelector('.btn-close').onclick = () => toast.remove();
  // Cierre automático
  setTimeout(() => { if (toast.parentNode) toast.remove(); }, 4000);
}
function clearFlash() {
  let toastContainer = document.querySelector('.toast-container');
  if (toastContainer) toastContainer.innerHTML = '';
}

// Renderizado de datos personales
function renderPersonalCard(editing = false) {
  const el = document.getElementById('checkout-personal-card');
  const requiredFields = ['nombre','apellidos','telefono','direccion','ciudad','postal','pais'];
  const p = checkoutState.personal || {};
  const missing = requiredFields.filter(f => !p[f] || p[f].trim() === '');
  if (!editing && missing.length === 0) {
    el.innerHTML = `<div class="content-card"><div class="d-flex justify-content-between align-items-center mb-2"><h4 class="mb-0"><i class="bi bi-person-circle me-2" style="color:#1d3557;font-size:1.5rem;"></i>Datos Personales</h4><button class="btn btn-link p-0" onclick="renderPersonalCard(true)">Editar</button></div><ul class="mb-2 list-unstyled"><li><strong>Nombre:</strong> ${p.nombre} ${p.apellidos}</li><li><strong>Teléfono:</strong> <i class="bi bi-telephone me-1"></i>${p.telefono}</li><li><strong>Dirección:</strong> <i class="bi bi-geo-alt me-1"></i>${p.direccion}, ${p.ciudad}, ${p.postal}, ${p.pais}</li></ul></div>`;
    return;
  }
  // Formulario de edición si falta algún campo o está en modo edición
  el.innerHTML = `<div class="content-card"><h4 class="mb-3"><i class="bi bi-person-circle me-2" style="color:#1d3557;font-size:1.5rem;"></i>Datos Personales</h4><form id="personalForm"><div class="row g-2"><div class="col-md-6 mb-2"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="${p.nombre||''}" required></div><div class="col-md-6 mb-2"><label class="form-label">Apellidos</label><input type="text" class="form-control" name="apellidos" value="${p.apellidos||''}" required></div></div><div class="mb-2"><label class="form-label">Teléfono</label><input type="tel" class="form-control" name="telefono" value="${p.telefono||''}" required></div><div class="mb-2"><label class="form-label">Dirección</label><input type="text" class="form-control" name="direccion" value="${p.direccion||''}" required></div><div class="row g-2"><div class="col-md-4 mb-2"><label class="form-label">Ciudad</label><input type="text" class="form-control" name="ciudad" value="${p.ciudad||''}" required></div><div class="col-md-4 mb-2"><label class="form-label">Código Postal</label><input type="text" class="form-control" name="postal" value="${p.postal||''}" required></div><div class="col-md-4 mb-2"><label class="form-label">País</label><input type="text" class="form-control" name="pais" value="${p.pais||''}" required></div></div><div class="d-grid mt-3"><button type="submit" class="btn btn-primary">Guardar</button></div></form></div>`;
  document.getElementById('personalForm').onsubmit = function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    checkoutState.personal = Object.fromEntries(fd.entries());
    renderPersonalCard(false);
    clearFlash();
    renderCTA();
  };
}

// Renderizado de tarjetas de crédito
function renderCardCard(editing = false) {
  const el = document.getElementById('checkout-card-card');
  // Si hay tarjetas guardadas y no estamos añadiendo nueva
  if (!editing && savedCards.length > 0 && !checkoutState.card) {
    let html = `<div class="content-card"><div class="d-flex justify-content-between align-items-center mb-2"><h4 class="mb-0">Tarjeta de Crédito</h4><button class="btn btn-link p-0" onclick="renderCardCard(true)">Añadir nueva</button></div>`;
    html += '<div class="row g-2">';
    savedCards.forEach((c, idx) => {
      const checked = checkoutState.card_id === c.id ? 'checked' : (!checkoutState.card_id && c.is_default ? 'checked' : '');
      let icon = '<i class="bi bi-credit-card-2-front" style="font-size:1.6rem;color:#adb5bd;margin-right:18px;"></i>';
      if (c.type === 'Visa') icon = '<i class="bi bi-credit-card" style="font-size:1.6rem;color:#1a1f71;margin-right:18px;"></i>';
      else if (c.type === 'Mastercard') icon = '<i class="bi bi-credit-card" style="font-size:1.6rem;color:#eb001b;margin-right:18px;"></i>';
      else if (c.type === 'Amex') icon = '<i class="bi bi-credit-card" style="font-size:1.6rem;color:#2e77bb;margin-right:18px;"></i>';
      html += `<div class="col-12 mb-2"><label class="card-radio w-100 d-flex align-items-center p-3 mb-2 position-relative ${checked ? 'selected' : ''}" style="border: 2.5px solid ${checked ? '#e63946' : '#dee2e6'}; border-radius: 14px; cursor: pointer; transition: border 0.25s, box-shadow 0.25s, background 0.25s; background: ${checked ? 'rgba(230,57,70,0.09)' : '#fff'}; box-shadow: ${checked ? '0 4px 16px rgba(230,57,70,0.13)' : 'none'};">
        <input type="radio" name="card_select" value="${idx}" ${checked} style="margin-right:16px;width:22px;height:22px;accent-color:#e63946;">
        ${icon}
        <span class="card-summary" style="font-size:1.13rem;"><strong>${c.type}</strong> **** ${c.last_four} <span class="text-muted">(${c.expiry_month}/${c.expiry_year})</span></span>
        ${checked ? '<span class="position-absolute top-0 end-0 m-2"><i class="bi bi-check-circle-fill" style="color:#e63946;font-size:1.4rem;"></i></span>' : ''}
      </label></div>`;
    });
    html += '</div>';
    html += `</div>`;
    el.innerHTML = html;
    document.getElementsByName('card_select').forEach(radio => {
      radio.onchange = () => {
        checkoutState.card_id = savedCards[radio.value].id;
        checkoutState.card = null;
        renderCardCard(false);
        clearFlash();
        renderCTA();
      };
    });
    return;
  }
  // Formulario de nueva tarjeta
  const c = checkoutState.card || {};
  el.innerHTML = `<div class="content-card"><h4 class="mb-3">Añadir nueva tarjeta</h4><form id="cardForm"><div class="mb-2"><label class="form-label">Titular</label><input type="text" class="form-control" name="holder" value="${c.holder||''}" required></div><div class="mb-2"><label class="form-label">Número</label><input type="text" class="form-control" name="number" maxlength="19" value="${c.number||''}" required></div><div class="row g-2"><div class="col-md-4 mb-2"><label class="form-label">Mes (MM)</label><input type="text" class="form-control" name="expiry_month" maxlength="2" value="${c.expiry_month||''}" required></div><div class="col-md-4 mb-2"><label class="form-label">Año (AA)</label><input type="text" class="form-control" name="expiry_year" maxlength="2" value="${c.expiry_year||''}" required></div><div class="col-md-4 mb-2"><label class="form-label">CVV</label><input type="text" class="form-control" name="cvv" maxlength="4" value="${c.cvv||''}" required></div></div><div class="d-grid mt-3"><button type="submit" class="btn btn-primary">Guardar</button></div><div class="d-grid mt-2"><button type="button" class="btn btn-link" id="cancelAddCard">Cancelar</button></div></form></div>`;
  document.getElementById('cardForm').onsubmit = async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const number = fd.get('number').replace(/\s+/g, '');
    const last_four = number.slice(-4);
    let type = 'Otra';
    if (number[0] === '4') type = 'Visa';
    else if (number[0] === '5') type = 'Mastercard';
    else if (number[0] === '3') type = 'Amex';
    checkoutState.card = {...Object.fromEntries(fd.entries()), last_four, type};
    checkoutState.card_id = null;
    // Simular alta de tarjeta para obtener el id real
    const payload = {
      personal: checkoutState.personal || {},
      card: checkoutState.card,
      plan_id: window.DRIVECLUB_CHECKOUT_PLAN.plan_id,
      only_add_card: true
    };
    const resp = await fetch('api/checkout-plan.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const text = await resp.text();
    let data;
    try { data = JSON.parse(text); } catch { showFlash('danger', 'Error inesperado: ' + text); return; }
    if (data.success && data.cards) {
      savedCards = data.cards;
      let def = savedCards.find(c => c.is_default);
      checkoutState.card_id = def ? def.id : savedCards[savedCards.length-1].id;
      checkoutState.card = null;
      renderCardCard(false);
      clearFlash();
      renderCTA();
    } else {
      showFlash('danger', data.error || 'Error al guardar la tarjeta.');
    }
  };
  document.getElementById('cancelAddCard').onclick = function() {
    checkoutState.card = null;
    renderCardCard(false);
    clearFlash();
    renderCTA();
  };
}

// Renderizado del resumen del plan
function renderPlanSummary() {
  const el = document.getElementById('checkout-plan-summary');
  const plan = window.DRIVECLUB_CHECKOUT_PLAN;
  el.innerHTML = `<div class="content-card"><h4 class="mb-3"><i class="bi bi-clipboard2-check me-2" style="color:#e63946;font-size:1.4rem;"></i>Resumen del Plan</h4><ul class="mb-2 list-unstyled"><li><strong>Plan:</strong> <i class="bi bi-star-fill text-warning me-1"></i>${plan.plan_name}</li><li><strong>Precio:</strong> <i class="bi bi-cash-coin text-success me-1"></i>${plan.plan_price} €/mes</li></ul><ul class="mb-0 list-unstyled">${(plan.plan_features||[]).map(f=>`<li><i class='bi bi-check-circle text-success'></i> ${f.name||f}</li>`).join('')}</ul></div>`;
}

// Renderizado de términos y condiciones
function renderTerms() {
  const el = document.getElementById('checkout-terms');
  el.innerHTML = `<div class="content-card"><div class="form-check"><input class="form-check-input" type="checkbox" id="termsCheck"><label class="form-check-label" for="termsCheck">Acepto los <a href="#" target="_blank">términos y condiciones</a> de DriveClub</label></div></div>`;
  document.getElementById('termsCheck').onchange = function() {
    checkoutState.termsAccepted = this.checked;
    renderCTA();
  };
}

// Renderizado del CTA final
function renderCTA() {
  const el = document.getElementById('checkout-cta');
  const requiredFields = ['nombre','apellidos','telefono','direccion','ciudad','postal','pais'];
  const p = checkoutState.personal || {};
  const missing = requiredFields.filter(f => !p[f] || p[f].trim() === '');
  const ready = checkoutState.personal && missing.length === 0 && (checkoutState.card_id || checkoutState.card) && checkoutState.termsAccepted && !checkoutState.submitting;
  el.innerHTML = `<button class="btn btn-primary w-100 btn-lg" id="checkout-final-btn" ${ready ? '' : 'disabled'}>${checkoutState.submitting ? 'Procesando...' : 'Aceptar y Suscribirme'}</button>`;
  if (ready) {
    document.getElementById('checkout-final-btn').onclick = submitCheckout;
  }
}

// Envío final
function submitCheckout() {
  const requiredFields = ['nombre','apellidos','telefono','direccion','ciudad','postal','pais'];
  const p = checkoutState.personal || {};
  const missing = requiredFields.filter(f => !p[f] || p[f].trim() === '');
  if (missing.length > 0) {
    showFlash('danger', 'Debes completar todos los datos personales antes de suscribirte.');
    return;
  }
  checkoutState.submitting = true;
  renderCTA();
  clearFlash();
  const payload = {
    personal: checkoutState.personal,
    plan_id: window.DRIVECLUB_CHECKOUT_PLAN.plan_id
  };
  if (checkoutState.card) {
    payload.card = checkoutState.card;
  } else if (checkoutState.card_id) {
    payload.card_id = checkoutState.card_id;
  }
  fetch('api/checkout-plan.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(payload)
  })
  .then(async r => {
    const text = await r.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      showFlash('danger', 'Error inesperado: ' + text);
      checkoutState.submitting = false;
      renderCTA();
      return;
    }
    checkoutState.submitting = false;
    if (data.success) {
      showFlash('success', '¡Suscripción realizada correctamente! Redirigiendo...');
      setTimeout(()=>window.location.href='mi-cuenta.php?tab=subscription', 1500);
    } else {
      showFlash('danger', data.error || 'Error al procesar la suscripción.');
      renderCTA();
    }
  })
  .catch((err) => {
    checkoutState.submitting = false;
    showFlash('danger', 'Error de red o servidor: ' + (err && err.message ? err.message : ''));
    renderCTA();
  });
}

// Inicialización con datos precargados
const preload = window.DRIVECLUB_CHECKOUT_PLAN;
if (preload.user && Object.values(preload.user).some(v => v)) {
  checkoutState.personal = {...preload.user};
}
if (preload.cards && preload.cards.length > 0) {
  savedCards = preload.cards.map((c, idx) => ({...c, id: c.id || idx+1}));
  let def = savedCards.find(c => c.is_default);
  checkoutState.card_id = def ? def.id : savedCards[0].id;
}

// Añadir formateo visual al campo de número de tarjeta en el checkout
function attachCardNumberFormatter() {
  const form = document.getElementById('cardForm');
  if (!form) return;
  const numberInput = form.querySelector('input[name="number"]');
  if (!numberInput) return;
  numberInput.addEventListener('input', function(e) {
    if (window.formatCreditCardNumber) {
      this.value = window.formatCreditCardNumber(this.value);
    } else {
      // Fallback: formateo local
      let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
      let formattedValue = '';
      for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
          formattedValue += ' ';
        }
        formattedValue += value[i];
      }
      this.value = formattedValue;
    }
  });
}

// Modificar renderCardCard para incluir campo combinado de fecha de expiración
const origRenderCardCard = renderCardCard;
renderCardCard = function(editing) {
  origRenderCardCard.apply(this, arguments);
  setTimeout(() => {
    attachCardNumberFormatter();
    attachExpiryDateFormatter();
  }, 0);
};

function attachExpiryDateFormatter() {
  const form = document.getElementById('cardForm');
  if (!form) return;
  // Si ya existe el campo, no duplicar
  if (form.querySelector('#expiry_date')) return;
  // Buscar los campos originales
  const monthInput = form.querySelector('input[name="expiry_month"]');
  const yearInput = form.querySelector('input[name="expiry_year"]');
  if (!monthInput || !yearInput) return;
  // Crear campo combinado
  const expiryGroup = document.createElement('div');
  expiryGroup.className = 'mb-2';
  expiryGroup.innerHTML = `<label class="form-label">Fecha de expiración (MM/AA)</label><input type="text" class="form-control" id="expiry_date" maxlength="5" placeholder="MM/AA" autocomplete="cc-exp" required>`;
  // Insertar antes del mes
  monthInput.closest('.col-md-4').parentNode.insertBefore(expiryGroup, monthInput.closest('.col-md-4'));
  // Ocultar los campos originales y sus contenedores visuales
  monthInput.type = 'hidden';
  yearInput.type = 'hidden';
  // Ocultar los contenedores visuales de mes y año
  const monthCol = monthInput.closest('.col-md-4');
  const yearCol = yearInput.closest('.col-md-4');
  if (monthCol) monthCol.style.display = 'none';
  if (yearCol) yearCol.style.display = 'none';
  // Lógica de formateo y autocompletado
  const expiryDateInput = expiryGroup.querySelector('#expiry_date');
  expiryDateInput.addEventListener('input', function(e) {
    let value = this.value.replace(/[^0-9]/g, '');
    let month = '';
    let year = '';
    if (value.length > 0) {
      month = value.substring(0, 2);
      if (month.length === 1 && parseInt(month) > 1) {
        month = '0' + month;
      } else if (month.length === 2) {
        if (month.charAt(0) === '1' && parseInt(month.charAt(1)) > 2) month = '12';
        if (month === '00') month = '01';
      }
      if (value.length > 2) {
        year = value.substring(2, 4);
        this.value = month + '/' + year;
      } else {
        this.value = month;
      }
    }
    monthInput.value = month;
    yearInput.value = year;
  });
  // Si hay valores precargados, mostrarlos
  if (monthInput.value && yearInput.value) {
    expiryDateInput.value = monthInput.value + '/' + yearInput.value;
  }
  // Al enviar el formulario, asegurar que los campos ocultos están completos
  form.addEventListener('submit', function(e) {
    const val = expiryDateInput.value;
    if (val.includes('/')) {
      const [m, y] = val.split('/');
      monthInput.value = m;
      yearInput.value = y;
    } else {
      e.preventDefault();
      alert('Por favor, introduce una fecha de expiración válida (MM/AA)');
    }
  });
}

renderPersonalCard(false);
renderCardCard(false);
renderPlanSummary();
renderTerms();
renderCTA(); 