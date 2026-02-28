function formatDate(date) {
  return date
    .toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    })
    .replace(',', '')
    .replace(/^([A-Za-z]+)\s/, '$1. ');
}

$(document).ready(function () {
  function displayPromos(reservations) {
    const $reservationList = $('#reservationList');
    $reservationList.empty();

    if (reservations.length === 0) {
      $reservationList.html(`<div></div>`);
      return;
    }

    reservations.forEach(reservation => {
      
      const Options = `
        ${reservation.status != 'Fully Paid' && reservation.status != 'Cancel' && new Date(reservation.check_in) >= new Date() ? `
          <a class="btn btn-sm btn-outline-gray UpdateBtn" data-bs-toggle="modal" data-bs-target="#UpdateReservation" href="javascript:void(0);"
          data-checkin="${reservation.check_in}"
          data-checkout="${reservation.check_out}"
          data-id="${reservation.id}"
          data-category="${reservation.fac_category}"
          data-facilities_id="${reservation.facilities_id}"
          >
            <i class="ri-edit-2-line me-1 text-warning"></i> Update
          </a>
          <a class="btn btn-sm btn-outline-gray CancelBtn" href="javascript:void(0);"
          data-id="${reservation.id}"
          data-payment_id="${reservation.payment_id}"
          >
            <i class="ri-close-circle-line me-1 text-danger"></i> Cancel
          </a>
          <form class="d-inline fully-paid-form" method="POST" action="${fullpaidRoute}">
              <input type="hidden" name="_token" value="${csrfToken}">
              <input type="hidden" name="id" value="${reservation.id}">
              <button type="submit" class="btn btn-sm btn-outline-gray" title="Mark as Fully Paid">
                  <i class="ri-bill-line me-1 text-success"></i> Fully Paid
              </button>
          </form>

        ` : ''}
      `;
      const reservationRow = `
          
        <div class="card mb-5 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">
              ${reservation.facilities_name}
              ${reservation.promos_name !== null ? ' - ' + reservation.promos_name : ''}
            </h5>

            <p class="card-text mb-1">
              <strong>Check-in/Check-out:</strong>
              ${formatDate(new Date(reservation.check_in))} - ${formatDate(new Date(reservation.check_out))}
            </p>

            <p class="card-text mb-1">
              <strong>Total Amount:</strong>
              ₱${Number(reservation.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </p>
            <p class="card-text mb-1">
              <strong>Payment:</strong>
              ₱${Number(reservation.payment_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </p>

            <p class="card-text mb-3">
              <span class="badge rounded-pill ${reservation.status == 'Fully Paid' ? 'bg-label-success' : reservation.status == 'Cancel' ? 'bg-label-danger' :'bg-label-warning'}">${reservation.status}</span>
            </p>

            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="dropdown">
                  <a class="btn btn-sm ${new Date(reservation.check_out) > new Date() ? 'btn-outline-gray' : 'btn-outline-warning'} rating ${reservation.rate != null ? 'text-warning' : 'ratings'}"
                    ${new Date(reservation.check_out) < new Date() && reservation.rate == null && reservation.status != "Cancel" ? 'data-bs-toggle="modal" data-bs-target="#AddRating"' : ''}
                    href="javascript:void(0);"
                    data-bookings_id="${reservation.id}"
                    data-facilities_id="${reservation.facilities_id}">
                    <i class="ri-star-line me-1"></i> ${reservation.rate != null ? 'Done' : 'Rate'}
                  </a>
                   <a class="btn btn-sm btn-outline-gray ViewBtn" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ViewReservation"
                      data-id="${reservation.encrypted_id}"
                      data-customer_name="${reservation.firstname} ${reservation.middlename !== null ? reservation.middlename : ''} ${reservation.lastname}"
                      data-facility_name="${reservation.facilities_name}"
                      data-promos="${reservation.promos_name}"
                      data-time_in="${reservation.check_in}"
                      data-time_out="${reservation.check_out}"
                      data-guest="${reservation.guest}"
                      data-price="${reservation.promos_price ?? reservation.facilities_price}"
                      data-date="${reservation.created_at}"
                      data-amount="${reservation.amount}"
                      data-foods='${JSON.stringify(reservation.foods)}'
                      data-payment_amount="${reservation.payment_amount}"
                      data-status="${reservation.status}"
                      data-name="${reservation.name}"
                      >
                      <i class="ri-eye-line me-1 text-primary"></i> View
                    </a>
                    ${Options}
                </div>
              </div>

              <div title="Rated ${reservation.rate} out of 5">
                <i class="ri-star-line me-1 ${reservation.rate >= 1 ? 'text-warning' : ''}"></i>
                <i class="ri-star-line me-1 ${reservation.rate >= 2 ? 'text-warning' : ''}"></i>
                <i class="ri-star-line me-1 ${reservation.rate >= 3 ? 'text-warning' : ''}"></i>
                <i class="ri-star-line me-1 ${reservation.rate >= 4 ? 'text-warning' : ''}"></i>
                <i class="ri-star-line me-1 ${reservation.rate >= 5 ? 'text-warning' : ''}"></i>
              </div>
            </div>
          </div>
        </div>
      `;
      $reservationList.append(reservationRow);
    });
  }

  function filterPromos(query) {
    const filtered = window.reservations.filter(reservation => {
      const fullName = `${reservation.facilities_name}`.toLowerCase();
      return fullName.includes(query);
    });
    displayPromos(filtered);
  }

  $('#search').on('input', function () {
    const query = $(this).val().toLowerCase();
    filterPromos(query);
  });

  // Render all promos on page load
  displayPromos(window.reservations);
});

$(document).ready(function () {
  $('body').on('click', '.ViewBtn', function () {
    const id = $(this).data('id');
    const fullname = $(this).data('customer_name');
    const facility_name = $(this).data('facility_name');
    const promos = $(this).data('promos') ?? 'No Promo';
    const time_in = $(this).data('time_in');
    const time_out = $(this).data('time_out');
    const guest = $(this).data('guest');
    const price = $(this).data('price');
    const date = $(this).data('date');
    const amount = $(this).data('amount');
    const foods = JSON.parse($(this).attr('data-foods') || '[]');
    const payment_amount = $(this).data('payment_amount');
    const status = $(this).data('status');
    const name = $(this).data('name');
    

    $('#facility-customer').text(name ?? fullname);
    $('#facility-name_details').text(facility_name);
    $('#time-in_details').text(formatDate(new Date(time_in)));
    $('#time-out_details').text(formatDate(new Date(time_out)));
    $('#payment_amount').text(Number(payment_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
    $('#promo_details').text(promos);
    $('#number_of_guests').text(guest);
    $('#price_details').text(Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
    $('#date').text(formatDate(new Date(date)));
    $('#total_amount').text(Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
    $('#status').text(status);

    let foodHtml = '';
    if (foods.length > 0) {
      foods.forEach(food => {
        foodHtml += `<div class="row mb-2">
                        <div class="col-12">
                          <strong>${food.pivot.quantity} ${food.name}</strong> <span> x ${food.price} = ${food.price * food.pivot.quantity}</span>
                        </div>
                      </div>`;
      });
    } else {
      foodHtml = '<p class="text-muted">No food ordered.</p>';
    }
    let footersHtml = '';
    footersHtml = `<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <a href="/booking/pdf/${id}" target="_blank"  type="button" class="btn btn-primary" >Print PDF</a>`;
                  
    $('#foods_list').html(foodHtml);
    $('#footerBtn').html(footersHtml); 
  });
});
$(document).ready(function () {
  const $stars = $('#starRating i');
  const $ratingInput = $('#ratingInput');
  const $ratingValue = $('#ratingValue');

  $stars.on('click', function () {
    const rating = parseInt($(this).data('value'));

    $ratingInput.val(rating);
    $ratingValue.text(rating);

    // Reset all stars
    $stars.removeClass('bi-star-fill active').addClass('bi-star');

    // Highlight selected stars
    $stars.each(function (index) {
      if (index < rating) {
        $(this).removeClass('bi-star').addClass('bi-star-fill active');
      }
    });
  });
});

$(document).ready(function () {
  $('body').on('click', '.ratings', function() {
    const bookings_id = $(this).data('bookings_id');
    const facilities_id = $(this).data('facilities_id');
    
    $('#bookings_id').val(bookings_id);
    $('#facilities_id').val(facilities_id);
  })
})

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

$(document).ready(function() {
  $('body').on('click', '#SaveComment', function() {
    const fields = [
      { id: 'ratingInput', label: 'Rating' },
      { id: 'bookings_id', label: 'Bookings ID' },
      { id: 'facilities_id', label: 'Facilities ID' },
      { id: 'description', label: 'Description'}
    ];

    const isValid = validateForm(fields);

    if (!isValid) {
      event.preventDefault();
      return;
    }
    var formData = new FormData($('#ratingData')[0]);
    $.ajax({
      type: 'POST',
      url: '/reservations-list/rating',
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      dataType: 'json',
      beforeSend: function () {
        $('#AddRating').modal('hide');
        $('.preloader').show();
      },
      success: function (data) {
        $('.preloader').hide();
        if (data.Error == 1) {
          Swal.fire('Error!', data.Message, 'error');
        } else if (data.Error == 0) {
          Swal.fire({
            position: 'center',
            icon: 'success',
            title: 'Saved!',
            text: data.Message,
            showConfirmButton: true,
            confirmButtonText: 'OK'
          }).then(result => {
            location.reload();
          });
        }
      },
      error: function () {
        $('.preloader').hide();
        Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
      }
    });
    
  })
})


$(document).ready(function() {
  $('body').on('click', '.CancelBtn', function() {
    const id = $(this).data('id');
    const payment_id = $(this).data('payment_id');

    Swal.fire({
      title: 'Are you sure?',
      text: "No refunds will be issued for this",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      reverseButtons: true
    }).then(result => {
      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          url: '/reservations-list/cancel',
          cache: false,
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id,
            payment_id: payment_id
          },
          dataType: 'json',
          beforeSend: function () {
            $('.preloader').show();
          },
          success: function (data) {
            $('.preloader').hide();
            if (data.Error == 1) {
              Swal.fire('Error!', data.Message, 'error');
            } else if (data.Error == 0) {
              Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Saved!',
                text: data.Message,
                showConfirmButton: true,
                confirmButtonText: 'OK'
              }).then(result => {
                location.reload();
              });
            }
          },
          error: function () {
            $('.preloader').hide();
            Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
          }
        });

      }
    });
  })
})
  
$(document).ready(function() {
  let day = 0;
  let facility_category;
  function calculateDays(checkIn, checkOut) {
    if (checkIn && checkOut) {
      let inDate = new Date(checkIn);
      let outDate = new Date(checkOut);

      let diffMs = outDate - inDate;
      let diffDays = diffMs / (1000 * 60 * 60 * 24);

      $('#time-in').text(checkIn);
      $('#time-out').text(checkOut);
      $('#days').text(diffDays + ' days');

      return diffDays;
    }
    return 0; // if missing dates
  }
  $('body').on('click', '.UpdateBtn', function(){
    const start = $(this).data('checkin');
    const end = $(this).data('checkout');
    const id = $(this).data('id');
    const facilities_id = $(this).data('facilities_id');
    facility_category = $(this).data('category');

    $('#reservationID').val(id);
    $('#facilityID').val(facilities_id);
    $('#cat').val(facility_category);
    day = calculateDays(start, end);

    if (facility_category === 'cottage') {
        $('#checkin').attr('type', 'datetime-local');
        $('#checkout').attr('type', 'datetime-local');
    } else {
        $('#checkin').attr('type', 'date');
        $('#checkout').attr('type', 'date');
    }

    // Set values (make sure format matches input type)
    $('#checkin').val(start);
    $('#checkout').val(end);
  });

  $('body').on('click', '#UpdateBtn', function(envent) {
    const fields = [
      { id: 'checkin', label: 'Checkin Date' },
      { id: 'checkout', label: 'Checkout Date' },
    ];

    const isValid = validateForm(fields);

    if (!isValid) {
      event.preventDefault();
      return;
    }

    const checkin = $('#checkin').val();
    const checkout = $('#checkout').val();
    const countDay = calculateDays(checkin, checkout);
 
    let valid = true;

    if(facility_category == 'cottage'){
      const checkinDate = new Date(checkin);
      const checkoutDate = new Date(checkout);

      // Extract time in HH:MM AM/PM format
      const checkinTime = checkinDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });

      const checkoutTime = checkoutDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });

      // Allowed time ranges
      const isValidTime =
        (checkinTime === "08:00 AM" && checkoutTime === "05:00 PM") ||
        (checkinTime === "05:00 PM" && checkoutTime === "10:00 PM") ||
        (checkinTime === "08:00 AM" && checkoutTime === "10:00 PM");

      if (!isValidTime) {
        Toastify({
          text: 'Invalid input. The time and date duration is incorrect for the cottage',
          duration: 3000,
          close: true,
          gravity: 'top',
          position: 'right',
          backgroundColor: '#cc3300',
          stopOnFocus: true
        }).showToast();
        return;
      }
    }
    
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


    if(checkin && checkout){
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

    if(valid == true)
    {
      $.ajax({
        type: 'POST',
        url: '/reservations-list/update',
        cache: false,
        data: $('#updateData').serialize(),
        dataType: 'json',
        beforeSend: function () {
          $('#UpdateReservation').modal('hide');
          $('.preloader').show();
        },
        success: function (data) {
          $('.preloader').hide();
          if (data.Error == 1) {
            Swal.fire('Error!', data.Message, 'error');
          } else if (data.Error == 0) {
            Swal.fire({
              position: 'center',
              icon: 'success',
              title: 'Saved!',
              text: data.Message,
              showConfirmButton: true,
              confirmButtonText: 'OK'
            }).then(result => {
              location.reload();
            });
          }
        },
        error: function () {
          $('.preloader').hide();
          Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
        }
      });
    }

  })
})