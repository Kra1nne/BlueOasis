// error trapping
function validateForm(fields) {
  let valid = true;

  // Loop through all fields to check if any are empty
  fields.forEach(field => {
    const input = document.getElementById(field.id);
    const value = input.value.trim();
    const errorMessages = [];

    // Check for empty fields
    if (!value) {
      valid = false;
      errorMessages.push(`${field.label} is required.`);
    }

    if (errorMessages.length > 0) {
      input.classList.add('is-invalid'); // Add Bootstrap 'is-invalid' class
      let errorMessageContainer = input.parentNode.querySelector('.invalid-feedback');
      if (!errorMessageContainer) {
        errorMessageContainer = document.createElement('div');
        errorMessageContainer.classList.add('invalid-feedback');
        input.parentNode.appendChild(errorMessageContainer);
      }
      errorMessageContainer.innerHTML = errorMessages.join('<br>'); // Display all errors for this field
    } else {
      input.classList.remove('is-invalid'); // Remove 'is-invalid' class if valid
      let errorMessageContainer = input.parentNode.querySelector('.invalid-feedback');
      if (errorMessageContainer) {
        errorMessageContainer.remove(); // Remove error messages
      }
    }
  });

  return valid;
}

$(document).ready(function () {
  const facilitydetails = window.venue;
  var promo_max = 0;
  
  $('#venue_id').val(facilitydetails.id);
  $('#venue_name').val(facilitydetails.name);
  $('#venue_price').val(facilitydetails.price);
  $('#price').text(formatPrice(facilitydetails.price));
  let selectedPromo = null;

  function formatPrice(price) {
    return '₱' + Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
  }

  function calculateTotalPrice(basePrice, guestCount, maxPerson, additionalPrice) {
    let total = Number(basePrice);
    if (guestCount > maxPerson && maxPerson > 0) {
      total += (guestCount - maxPerson) * additionalPrice;
    }
    return total;
  }

  function updatePriceDisplay() {
    const guestCount = Number($('#guest').val()) || 1;

    // Start from base venue price
    let basePrice = facilitydetails.price;
    let maxPerson = Number(facilitydetails.max_person) || 0;
    let additionalPrice = Number(facilitydetails.additional_price) || 0;

    // Apply promo override if any
    if (selectedPromo && selectedPromo.price) {
        basePrice = selectedPromo.price;
        maxPerson = Number(selectedPromo.max_person) || maxPerson;
        additionalPrice = Number(selectedPromo.additional_price) || additionalPrice;
    }

    // Apply guest count logic
    let total = calculateTotalPrice(basePrice, guestCount, maxPerson, additionalPrice);

    // 🔥 Add ₱500 whole-day fee IF selected
    let selectedTime = $('.cottage-btn.bg-label-primary').text().trim();
    if (facilitydetails.category === 'cottage' && selectedTime === "5:00 AM - 10:00 PM") {
        total += 500;
    }

    // Output
    $('#price').text(formatPrice(total));
    $('#venue_price').val(total);
}

  // Promo button selection
  $('.promo-btn').on('click', function () {
    $('.promo-btn').removeClass('active bg-label-primary bg-label-gray');
    $(this).addClass('active bg-label-primary');
    $('.promo-btn').not('.active').addClass('bg-label-gray');
    $('#promo_id').val($(this).data('promo-id') || '');
    promo_max = 0;
    const promoId = $(this).data('promo-id');
    selectedPromo = null;
    if (promoId) {
      selectedPromo = facilitydetails.promo.find(p => p.id == promoId);
      promo_max = selectedPromo.max_person;
    }
    updatePriceDisplay();
  });

  $(document).ready(function () {
    // Cottage date & time logic
    if (facilitydetails.category === 'cottage') {
      // When a time button is clicked
      $('.cottage-btn').on('click', function () {
        // Remove active class from all, add to clicked
        $('.cottage-btn').removeClass('bg-label-primary').addClass('bg-label-gray');
        $(this).removeClass('bg-label-gray').addClass('bg-label-primary');
        $('#guest').val("");

        // Get selected date
        const date = $('#date').val();
        if (!date) return;

        // Get time range from button text
        const timeRange = $(this).text().trim();
        const [start, end] = timeRange.split(' - ');

        // Format date for input[type="datetime-local"]
        function formatDateTime(dateStr, timeStr) {
          // dateStr: yyyy-mm-dd, timeStr: hh:mm AM/PM
          let [h, m] = timeStr.split(':');
          let [hour, minute] = [parseInt(h), m.slice(0, 2)];
          let ampm = m.slice(3).toUpperCase();
          if (ampm === 'PM' && hour < 12) hour += 12;
          if (ampm === 'AM' && hour === 12) hour = 0;
          return `${dateStr}T${hour.toString().padStart(2, '0')}:${minute}`;
        }

        let selectedTime = $(this).text().trim();
        let basePrice = facilitydetails.price;

        if (selectedTime === "5:00 AM - 10:00 PM") {
            basePrice += 500;
        }

        // update UI price and hidden input
        $('#price').text(formatPrice(basePrice));
        $('#venue_price').val(basePrice);
        // Set check-in and check-out values
        $('#checkin-date').val(formatDateTime(date, start));
        $('#checkout-date').val(formatDateTime(date, end));
      });

      // When date changes, if a time button is active, update check-in/out
      $('#date').on('change', function () {
        const activeBtn = $('.cottage-btn.bg-label-primary');
        if (activeBtn.length) {
          activeBtn.trigger('click');
        }
      });
    }

    // ...existing code...
  });

  // Guest input change
  $('#guest').on('input change', function () {
    updatePriceDisplay();
  });

  // Initial price calculation
  updatePriceDisplay();

  function isFutureOrToday(dateStr) {
    const selected = new Date(dateStr);
    const today = new Date();
    // Set time to 00:00:00 for accurate comparison
    selected.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    return selected >= today;
  }
  function calculateDays(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 0;

      const inDate = new Date(checkIn);
      const outDate = new Date(checkOut);

      const diffMs = outDate - inDate;
      const diffDays = diffMs / (1000 * 60 * 60 * 24);

      // If exactly 24 hours or more than 0, return as number of days
      return diffDays;
  }
  function isFullDayBooking(checkin, checkout) {
      if (!checkin || !checkout) return false;

      const inDate = new Date(checkin);
      const outDate = new Date(checkout);

      // Remove time portion to count full days only
      inDate.setHours(0, 0, 0, 0);
      outDate.setHours(0, 0, 0, 0);

      const diffMs = outDate - inDate;
      const diffDays = diffMs / (1000 * 60 * 60 * 24);

      // Return true only if 1 or more full days and an integer
      return diffDays >= 1 && Number.isInteger(diffDays);
  }
  $('#reservationBtn').on('click', function (e) {
    const fields = [
      { id: 'checkin-date', label: 'Check-in Date' },
      { id: 'checkout-date', label: 'Check-out Date' },
      { id: 'guest', label: 'Number of Guests' }
    ];
    e.preventDefault();
    let valid = validateForm(fields);
    const facilitydetails = window.venue;

    const checkin = $('#checkin-date').val();
    const checkout = $('#checkout-date').val();
    const guest = $('#guest').val();

    const maxGuestAllowed = facilitydetails.max_person + facilitydetails.limit_add;
    
    if(guest > maxGuestAllowed && promo_max == 0){
      Toastify({
        text: 'Please reduce occupants to the allowed limit.',
        duration: 3000,
        close: true,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#cc3300',
        stopOnFocus: true
      }).showToast();
      return;
    }

    if(guest > promo_max && promo_max != 0){
      Toastify({
        text: 'Please reduce occupants to the allowed limit.',
        duration: 3000,
        close: true,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#cc3300',
        stopOnFocus: true
      }).showToast();
      return;
    }
    

    let wholeDay = isFullDayBooking(checkin, checkout);
   
    if (checkin) {
      const checkinDate = new Date(checkin);
      const now = new Date();
      if (checkinDate < now) {
        valid = false;
        Toastify({
          text: 'Check-in date and time cannot be in the past.',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
      }
    }


    if (checkout) {
      const checkoutDate = new Date(checkout);
      const now = new Date();
      if (checkoutDate < now) {
        valid = false;
        Toastify({
          text: 'Check-out date and time cannot be in the past.',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
      }
    }

    
    if (checkin && checkout) {
      const checkinDate = new Date(checkin);
      const checkoutDate = new Date(checkout);
      if (checkoutDate <= checkinDate) {
        valid = false;
        Toastify({
          text: 'Check-out must be after check-in.',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
      }
    }
    function isPositiveWholeNumber(num) {
        return Number.isInteger(num) && num > 0;
    }
     let days = calculateDays(checkin, checkout)
    if(facilitydetails.category == "room"){
      if(!isPositiveWholeNumber(days)){
        valid = false;
        Toastify({
          text: 'The minimum booking is full day.',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
      }
    }
    let isConflict = false;
    if(facilitydetails.category === 'cottage')
    {
      console.log("Cottoges");
      isConflict = window.bookingDetails.some(booking => {
        const reservedStart = new Date(booking.time_in);
        const reservedEnd = new Date(booking.time_out);
        const inDate = new Date(checkin);
        const outDate = new Date(checkout);
        return inDate <= reservedEnd && outDate >= reservedStart;
      });
    }else{
      function setTimeFromDateInput(dateString, hours, minutes = 0) {
        const d = new Date(dateString); // comes as 00:00
        d.setHours(hours, minutes, 0, 0);
        return d;
      }

      isConflict = window.bookingDetails.some(booking => {

          const reservedStart = new Date(booking.time_in);
          const reservedEnd = new Date(booking.time_out);

          const inDate = setTimeFromDateInput(checkin, 14);   // 2:00 PM
          const outDate = setTimeFromDateInput(checkout, 12); // 12:00 PM

          return inDate < reservedEnd && outDate > reservedStart;
      });
    }
    
   
    if (isConflict) {
        Toastify({
          text: 'Oops! These dates are already reserved. Please choose different dates.',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
        event.preventDefault();
        return;
      }

    if (valid) {
      var checkinDate = $('#checkin-date').val();
      var checkoutDate = $('#checkout-date').val();

      $('#BookingDetailsModal').modal('show');
      $('#facility-name_details').text(facilitydetails.name);

      if(facilitydetails.category === "cottage"){
        $('#time-in_details').text(checkinDate);
        $('#time-out_details').text(checkoutDate);
      }
      else{
        $('#time-in_details').text(checkinDate + ' 2:00 PM');
        $('#time-out_details').text(checkoutDate + ' 12:00 PM');
      }

      $('#promo_details').text(selectedPromo ? selectedPromo.name : 'No Promo');
      $('#price_details').text(formatPrice($('#venue_price').val()));
      $('#number_of_days').text(facilitydetails.category === 'cottage' ? '1' : calculateDays(checkin, checkout));
      $('#number_of_guests').text($('#guest').val());

      let total = 0;

      let serviceFee = 0;
      if (facilitydetails.category === 'cottage') {
        total = Number($('#venue_price').val()) + serviceFee;
      } else {
        total = Number($('#venue_price').val()) * calculateDays(checkin, checkout) + serviceFee;
      }
      $('#total_amount').text(formatPrice(total));
      $('#service-fee').text(formatPrice(serviceFee));

      // store to the input fields
      
      $('#facility_category').val(facilitydetails.category);
      $('#facility_id').val(facilitydetails.id);
      $('#facility_name').val(facilitydetails.name);
      $('#facility_price').val($('#venue_price').val());

      if(facilitydetails.category === "cottage"){
        $('#facility_checkin').val(checkinDate);
        $('#facility_checkout').val(checkoutDate);
      }
      else{
        $('#facility_checkin').val(checkinDate + 'T14:00');
        $('#facility_checkout').val(checkoutDate + 'T12:00');
      }

      $('#facility_number_of_guests').val($('#guest').val());
      $('#facility_promo_id').val($('#promo_id').val() || '');
      $('#facility_total_amount').val(total);
      $('#promo_name').val(selectedPromo ? selectedPromo.name : 'No Promo');
      $('#facility_number_of_days').val(facilitydetails.category === 'cottage' ? 1 : calculateDays(checkin, checkout));
    }
  });
});

// Calendar initialization


document.addEventListener('DOMContentLoaded', function () {
  const now = new Date();
  const calendarEl = document.getElementById('calendar');

  // Convert datetime to "YYYY-MM-DD hh:mm AM/PM"
  function formatDateTime(datetime) {
    const d = new Date(datetime.replace(" ", "T")); // fix 12a issue
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;
    return `${year}-${month}-${day} ${hours}:${minutes} ${ampm}`;
  }

  // Determine reservation status
  function getStatus(checkIn, checkOut) {
    const start = new Date(checkIn.replace(" ", "T"));
    const end = new Date(checkOut.replace(" ", "T"));

    if (now < start) return "UPCOMING";
    if (now >= start && now <= end) return "ONGOING";
    return "DONE";
  }

  // Prepare FullCalendar events
  const formattedEvents = window.bookingDetails.map(event => ({
    title: "Reserved",
    start: event.time_in.replace(" ", "T"),  // use ISO format
    end: event.time_out.replace(" ", "T"),   // use ISO format
    extendedProps: {
      checkIn: event.time_in,
      checkOut: event.time_out,
      status: getStatus(event.time_in, event.time_out)
    },
    backgroundColor: "#0722cf",
    borderColor: "#0722cf",
    textColor: "#fff",
    display: "block"
  }));

  // Initialize FullCalendar
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    initialDate: '2025-12-01',
    dayMaxEvents: 1,
    moreLinkClick: false,
    displayEventTime: false, 
    events: formattedEvents,

    // Click event to show modal
    eventClick: function(info) {
      const props = info.event.extendedProps;
      const html = `
        <div style="font-size:14px; line-height:1.4;">
          <strong>Reservation</strong><br>
          Check-in: ${formatDateTime(props.checkIn)}<br>
          Check-out: ${formatDateTime(props.checkOut)}<br>
          Status: ${props.status}
        </div>
      `;
      document.getElementById("reservationList").innerHTML = html;
      new bootstrap.Modal(document.getElementById("reservationModal")).show();
    },

    // Style event text
    eventDidMount: function(info) {
      info.el.style.whiteSpace = "normal";
      info.el.style.fontSize = "12px";
      info.el.style.padding = "2px";
    }
  });

  calendar.render();
});
