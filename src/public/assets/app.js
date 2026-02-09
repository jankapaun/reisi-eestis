/* ==========================
   Globals
========================== */
let allSchedules = [];

/* ==========================
   Toast helper
========================== */
function showToast(message, type = 'primary', delay = 3000) {
  const toastContainer = $('#toast-container');
  const toastId = 'toast-' + Date.now();

  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;

  toastContainer.append(toastHtml);

  const toastEl = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastEl, { delay });
  toast.show();

  toastEl.addEventListener('hidden.bs.toast', () => {
    toastEl.remove();
  });
}

/* ==========================
   Date / time helpers
========================== */
function parseDate(dt) {
  if (!dt) return new Date(NaN);
  return new Date(dt.replace(' ', 'T'));
}

function formatDuration(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return `${h}:${m.toString().padStart(2, '0')}`;
}

function calculateDurationMinutes(start, end) {
  const startDate = parseDate(start);
  const endDate   = parseDate(end);

  let diff = (endDate - startDate) / 60000;

  if (isNaN(diff)) return 0;

  // Handle overnight trips
  if (diff < 0) diff += 1440;

  return diff;
}

function formatTime(dt) {
  const d = parseDate(dt);
  if (isNaN(d)) return '--:--';

  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
}

function formatDate(dt) {
  const d = parseDate(dt);
  if (isNaN(d)) return '--.--.----';

  return `${d.getDate().toString().padStart(2, '0')}.${(d.getMonth() + 1)
    .toString()
    .padStart(2, '0')}.${d.getFullYear()}`;
}

function formatDateTime(dt) {
  const d = parseDate(dt);
  if (isNaN(d)) return '--.--.---- ---:--';

  formatted_date_time = formatDate(dt) + ' ' + formatTime(dt);
  return formatted_date_time;
}

function getDayDifference(start, end) {
  const s = parseDate(start);
  const e = parseDate(end);

  if (isNaN(s) || isNaN(e)) return 0;

  const sDate = new Date(s.getFullYear(), s.getMonth(), s.getDate());
  const eDate = new Date(e.getFullYear(), e.getMonth(), e.getDate());

  const diff = (eDate - sDate) / (1000 * 60 * 60 * 24);
  return Math.max(0, Math.round(diff));
}

/* ==========================
   Checkout
========================== */
function renderCheckoutTable(data) {
  let html = `
    <thead>
      <tr>
        <th>Start → End<br>Company</th>
        <th class="text-end">Time</th>
        <th class="text-end">Price</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
  `;

  data.forEach(s => {
    let day_difference = getDayDifference(s.start_time, s.end_time);

    html += `
      <tr>
        <td>${s.from_city} → ${s.to_city}<br><i>${s.company_name}</i></td>
        <td class="text-end">
          ${formatTime(s.start_time)} → ${formatTime(s.end_time)}<br>
          <i>
            ${formatDate(s.start_time)}
            ${day_difference > 0 ? `(+${day_difference})` : ''}
          </i>
        </td>
        <td class="text-end text-nowrap">${s.price} €</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-danger remove-cart-item" data-id="${s.id}">Remove</button>
        </td>
      </tr>
    `;
  });

  html += `
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" class="text-end">Total:</th>
        <th id="cart-total" class="text-end">0 €</th>
      </tr>
    </tfoot>
  `;

  $('#checkout-cart').html(html);
}

function loadCheckoutCart() {
  $.getJSON('/api/cart.php?action=list', function(items) {
    
    renderCheckoutTable(items);
    
    const tbody = $('#checkout-cart tbody');
    let total = 0;

    if (!items.length) {
      tbody.html('<tr><td colspan="4" class="text-center text-muted">The list is empty. Please book a trip.</td></tr>');
      $('#cart-total').text('0 €');
      return;
    }

    items.forEach(i => {
      total += parseFloat(i.price) || 0;
    });

    $('#cart-total').text(total.toFixed(2) + ' €');
  });
}

/* ==========================
   Cart actions
========================== */
$(document).on('click', '.remove-cart-item', function() {
  const id = $(this).data('id');
  let msg = 'Error removing booked trip';

  $.ajax({
    url: '/api/cart.php?action=remove',
    method: 'POST',
    data: { schedule_id: id },
    dataType: 'json'
  })
  .done(res => {
    if (res.status === 'ok') {
      loadCheckoutCart();
      showToast('Booked trip successfully removed', 'success');
    } else {
      showToast(msg + ': ' + (res.message || ''), 'danger');
    }
  })
  .fail(jqXHR => {
    if (jqXHR.responseText) msg += ': ' + jqXHR.responseText;
    showToast(msg, 'danger');
  });
});

/* ==========================
   Confirm order
========================== */
$(document).on('click', '#confirm-order', function() {
  const first = $('#first').val().trim();
  const last  = $('#last').val().trim();

  $.ajax({
    url: '/api/checkout.php',
    method: 'POST',
    data: { first_name: first, last_name: last },
    dataType: 'json'
  })
  .done(res => {
    if (res.status === 'ok') {
      showToast('Order successfully placed!', 'success');
      setTimeout(() => window.location.href = '/', 1500);
    } else {
      showToast(res.message || 'Order failed', 'danger');
    }
  })
  .fail(jqXHR => {
    let msg = 'Error placing the order.';
    if (jqXHR.status >= 500) msg = 'Server error. Please try again later.';
    else if (jqXHR.responseText) msg += ' ' + jqXHR.responseText;
    showToast(msg, 'danger');
  });
});

/* ==========================
   Filters & trips
========================== */
function applyFilters() {
  const from = $('#from').val();
  const to = $('#to').val();
  const company = $('#company').val();

  const minDist = Number($('#minDistance').val()) || 0;
  const maxDist = Number($('#maxDistance').val()) || Infinity;
  const minDur  = Number($('#minDuration').val()) || 0;
  const maxDur  = Number($('#maxDuration').val()) || Infinity;
  const minPrice = Number($('#minPrice').val()) || 0;
  const maxPrice = Number($('#maxPrice').val()) || Infinity;

  const filtered = allSchedules.filter(s =>
    (from === '' || s.from_city === from) &&
    (to === '' || s.to_city === to) &&
    (company === '' || s.company_name === company) &&
    s.distance >= minDist &&
    s.distance <= maxDist &&
    s.duration_minutes >= minDur &&
    s.duration_minutes <= maxDur &&
    s.price >= minPrice &&
    s.price <= maxPrice
  );

  renderTripsTable(filtered);
}

function renderTripsTable(data) {
  let html = `
    <thead>
      <tr>
        <th>Start → End<br>Company</th>
        <th class="text-end">Distance<br>Duration</th>
        <th class="text-end">Time</th>
        <th class="text-end">Price</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
  `;

  if (!data.length) {
    html += '<tr><td colspan="5" class="text-center text-muted">No trips match your filters</td></tr>';
  } else {
    data.forEach(s => {
      let day_difference = getDayDifference(s.start_time, s.end_time);
      html += `
        <tr>
          <td>${s.from_city} → ${s.to_city}<br><i>${s.company_name}</i></td>
          <td class="text-end">${s.distance} km<br><i>${s.duration_text}</i></td>
          <td class="text-end">
            ${formatTime(s.start_time)} → ${formatTime(s.end_time)}<br>
            <i>
              ${formatDate(s.start_time)}
              ${day_difference > 0 ? `(+${day_difference})` : ''}
            </i>
          </td>
          <td class="text-end text-nowrap">${s.price} €</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary book-trip" data-id="${s.id}">Book</button>
          </td>
        </tr>
      `;
    });
  }

  html += '</tbody>';
  $('#results').html(html);
}

function loadAllSchedules() {
  $.getJSON('/api/schedules.php', function (data) {
    let cities = [];
    let companies = [];

    allSchedules = data.map(r => {
      r.duration_minutes = calculateDurationMinutes(r.start_time, r.end_time);
      r.duration_text = formatDuration(r.duration_minutes);
      cities.push(r.from_city, r.to_city);
      companies.push(r.company_name);
      return r;
    });

    // Render table
    renderTripsTable(allSchedules);

    // Populate filter dropdowns dynamically
    cities = [...new Set(cities)].sort((a, b) => a.localeCompare(b));
    companies = [...new Set(companies)].sort((a, b) => a.localeCompare(b));

    $('#from, #to').html('<option value="">Select</option>' + cities.map(c => `<option value="${c}">${c}</option>`).join(''));
    $('#company').html('<option value="">Select</option>' + companies.map(c => `<option value="${c}">${c}</option>`).join(''));
  });
}

/* ==========================
   Add to cart
========================== */
$(document).on('click', '.book-trip', function() {
  const id = $(this).data('id');
  let msg = 'Error adding trip to booking';

  $.ajax({
    url: '/api/cart.php?action=add',
    method: 'POST',
    data: { schedule_id: id },
    dataType: 'json'
  })
  .done(res => {
    if (res.status === 'ok') {
      showToast('Trip added to booking', 'success');
    } else {
      showToast(res.message || msg, 'danger');
    }
  })
  .fail(jqXHR => {
    if (jqXHR.responseText) msg += ': ' + jqXHR.responseText;
    showToast(msg, 'danger');
  });
});


function loadOrders() {
  $.getJSON('/api/orders.php', function(data) {
    // Group data by order_id
    const groupedOrders = {};
    data.forEach(item => {
      if (!groupedOrders[item.order_id]) {
        groupedOrders[item.order_id] = [];
      }
      groupedOrders[item.order_id].push(item);
    });

    let html = `
      <thead>
        <tr>
          <th>Order ID<br>Created</th>
          <th>Name</th>
          <th>Start → End<br>Company</th>
          <th class="text-end">Time<br>Departure date</th>
          <th class="text-end">Price</th>
        </tr>
      </thead>
      <tbody>
    `;

    if (data.length === 0) {
      html += '<tr><td colspan="9" class="text-center text-muted">No orders available</td></tr>';
    } else {

      let stripe = false; // to alternate colors
      
      // Loop through each order_id
      for (const orderId in groupedOrders) {
        const items = groupedOrders[orderId];

        // Display first item of the order as main row
        const firstItem = items[0];
        const stripeClass = stripe ? 'table-light' : 'table-secondary'; // Bootstrap classes
        stripe = !stripe;
        
        html += `
          <tr class="order-row ${stripeClass}">
            <td rowspan="${items.length}">${firstItem.order_id}<br><i>${formatDateTime(firstItem.created_at)}</i></td>
            <td rowspan="${items.length}">${firstItem.first_name} ${firstItem.last_name}</td>
            <td>${firstItem.from_city} → ${firstItem.to_city}<br><i>${firstItem.company_name}</i></td>
            <td class="text-end">
              ${formatTime(firstItem.start_time)} → ${formatTime(firstItem.end_time)}<br>
              <i>
                ${formatDate(firstItem.start_time)}
              </i>
            </td>
            <td class="text-end text-nowrap">${firstItem.price} €</td>
          </tr>
        `;

        // Display remaining items
        for (let i = 1; i < items.length; i++) {
          const item = items[i];
          html += `
            <tr class="${stripeClass}">
              <td>${item.from_city} → ${item.to_city}<br><i>${item.company_name}</i></td>
              <td class="text-end">
                ${formatTime(item.start_time)} → ${formatTime(item.end_time)}<br>
                <i>
                  ${formatDate(item.start_time)}
                </i>
              </td>
              <td class="text-end">${item.price} €</td>
            </tr>
          `;
        }
      }
    }

    html += '</tbody>';
    $('#orders-list').html(html);
  });
}



/* ==========================
  Pricelist sync (async)

  Try pricelist sync on page load.
  If the pricelist is still valid, sync.php will exit and do nothing.
  If the pricelist is expired, sync.php will fetch new data and update the database.
  TODO: Create a cron job to trigger this at regular intervals instead of doing it on page load.
========================== */

$.ajax({ 
  url: '/api/sync.php',
  method: 'GET',
  async: false
});


$(document).ready(function () {
  // Load all schedules on page load
  loadAllSchedules();

  // Only watch inputs inside the filters card
  $('.filter-card').on('input change', '.form-select, .form-control', function () {
    // Debounce filtering to improve UX
    clearTimeout($.data(this, 'timer'));
    $.data(this, 'timer', setTimeout(applyFilters, 200));
  });
});
