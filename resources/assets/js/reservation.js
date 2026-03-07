import CryptoJS from "crypto-js";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()
    }
})

const SECRET_KEY = import.meta.env.VITE_APP_ENCRYPT_KEY; 

export function encryptNumber(number) {
    if (typeof number !== "number") throw new Error("Input must be a number");

    const text = number.toString();
    const iv = CryptoJS.lib.WordArray.random(16);
    const key = CryptoJS.enc.Utf8.parse(SECRET_KEY); 

    const encrypted = CryptoJS.AES.encrypt(text, key, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });

    const ivHex = iv.toString(CryptoJS.enc.Hex);
    const ciphertextHex = encrypted.ciphertext.toString(CryptoJS.enc.Hex);

    return ivHex + ":" + ciphertextHex;
}


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
  function displayPromos(reservations) {
    const $reservationList = $('#reservationList');
    $reservationList.empty();

    if (reservations.length === 0) {
      $reservationList.html(`<tr><td colspan="7" class="text-center text-muted">No Reservation Found.</td></tr>`);
      return;
    }

    reservations.forEach(reservation => {
      const fullname = reservation.name ?? reservation.firstname ?? ' ' + (reservation.middlename ?? ' ') + reservation.lastname ?? ' ';
      const Options = `
        ${reservation.status != 'Fully Paid' && reservation.status != 'Cancel'  && new Date(reservation.check_in) <= new Date() ? `
          <a class="dropdown-item DoneBtn" href="javascript:void(0);"
          data-id="${reservation.id}"
          data-payment_id="${reservation.payment_id}"
          data-payment_amount="${reservation.payment_amount}"
          >
            <i class="ri-checkbox-circle-line me-1"></i> Done
          </a>
        ` : ''}
      `;
      const Add = `
        ${new Date(reservation.check_out) > new Date() && reservation.payment_status != "Cancel"? `
          <a class="dropdown-item AddBtn" href="/reservations/add_food/${encryptNumber(reservation.id)}">
            <i class="ri-restaurant-2-line me-1"></i> Add Foods
          </a>
          <a class="dropdown-item AddBtn" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#Extend"
          data-id="${reservation.id}"
          data-name=${fullname}
          data-facility="${reservation.facilities_name} ${reservation.promos_name !== null ? ' - ' + reservation.promos_name : ''}"
          data-payment="${Number(reservation.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}"
          data-start="${reservation.check_in}"
          data-end="${reservation.check_out}"
          data-price="${reservation.promos_price ?? reservation.facilities_price}"
          data-category="${reservation.category}"
          data-additional_price_time="${reservation.additional_price_time}"
          >
            <i class="ri-time-line me-1"></i> Extend Time
          </a>
          <a class="dropdown-item AddGuest"
            data-id="${reservation.id}"
            href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#GuestModal"
          >
          <i class="ri-user-line me-1"></i> Add Guest
        ` : ''}
      `;
      const Pool = `
      ${new Date(reservation.check_out) > new Date() && reservation.category != "room" && reservation.payment_status != "Cancel" ? `
        </a>
          <a class="dropdown-item AddPool"
          data-numberofguest="${reservation.guest}"
          data-id="${reservation.encrypted_id}"
          data-guestpool="${reservation.guest_pool}"
          href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#PoolModal" >
          <i class="ri-umbrella-line"></i> Pool
          </a>`: ''}
      `;
      
      const reservationRow = `
        <tr>
          <td>
             ${fullname}
          </td>
          <td>
            ${reservation.facilities_name} ${reservation.promos_name !== null ? ' - ' + reservation.promos_name : ''}
          </td>
          <td>
          ₱${Number(reservation.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
          </td>
          <td>
          ₱${Number(reservation.payment_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
          </td>
          <td>
            ${formatDate(new Date(reservation.check_in))} - ${formatDate(new Date(reservation.check_out))}
          </td>
          <td>
          ${reservation.guest}
          </td>
          <td>
            <span class="badge rounded-pill 
              ${(() => {
                const now = new Date();
                const checkIn = new Date(reservation.check_in);
                const checkOut = new Date(reservation.check_out);

                if (reservation.status === "Cancel") {
                  return 'bg-label-danger';
                } else if (now > checkOut) {
                  return 'bg-label-secondary'; // Done
                } else if (now >= checkIn && now <= checkOut) {
                  return 'bg-label-primary'; // Ongoing
                } else if (now < checkIn) {
                  return 'bg-label-info'; // Incoming
                } else if (reservation.status === "Partial Payment") {
                  return 'bg-label-warning';
                }
                return 'bg-label-light';
              })()}
              me-1">
              
              ${(() => {
                const now = new Date();
                const checkIn = new Date(reservation.check_in);
                const checkOut = new Date(reservation.check_out);

                if (reservation.status === "Cancel") {
                  return "Cancelled";
                } else if (now > checkOut) {
                  return "Done";
                } else if (now >= checkIn && now <= checkOut) {
                  return "Ongoing";
                } else if (now < checkIn) {
                  return "Incoming";
                } else if (reservation.status === "Partial Payment") {
                  return "Partial Payment";
                }
                return "Unknown";
              })()}
            </span>
          </td>
          <td>
            <span class="badge rounded-pill ${reservation.status === "Partial Payment" ? 'bg-label-warning' : reservation.status === "Cancel" ? 'bg-label-danger' : 'bg-label-success'} me-1">${reservation.status}</span>
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item ViewBtn" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ViewReservation"
                    data-id="${reservation.encrypted_id}"
                    data-customer_name="${reservation.firstname} ${reservation.middlename == !null ? reservation.middlename : ' '} ${reservation.lastname}"
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
                    data-adult_count="${reservation.adult_count}"
                    data-children_count="${reservation.children_count}"
                    data-total_amount="${reservation.total_amount}"
                    data-category="${reservation.category}"
                    >
                  <i class="ri-eye-line me-1"></i> View
                </a>
                ${Options}
                ${Add}
                ${Pool}
          </td>
        </tr>
      `;
      $reservationList.append(reservationRow);
    });
  }

  function filterPromos(query) {
    const filtered = window.reservations.filter(reservation => {
      const fullName = `${reservation.firstname} ${reservation.lastname}`.toLowerCase();
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

    const price1 = 70; // 4-10 yrs
    const price2 = 100; // 11+

    let remainingGuests = 0;
    let count1 = 0;
    let count2 = 0;

    function updateUI() {
        $('#numberofguest').text(remainingGuests);
        $('#count1').text(count1);
        $('#count2').text(count2);

        let total1 = count1 * price1;
        let total2 = count2 * price2;
        let grandTotal = total1 + total2;

        $('#total1').text(total1);
        $('#total2').text(total2);
        $('#grandtotal').text(grandTotal);

        $('#children').val(count1);
        $('#adult').val(count2);
        $('#totalamountpool').val(grandTotal);
    }

    // Open modal
    $('body').on('click', '.AddPool', function () {
        const id = $(this).data('id');
        const guest_pool = $(this).data('guestpool')
        remainingGuests = parseInt($(this).data('numberofguest')) - guest_pool;
        count1 = 0;
        count2 = 0;

        updateUI();
        $('#PoolModal').modal('show');
        $('#reservationid').val(id);
    });

    // Add 4-10 yrs
    $('#adding1').click(function () {
        if (remainingGuests <= 0) {
            showToast();
            return;
        }
        count1++;
        remainingGuests--;
        updateUI();
    });

    // Remove 4-10 yrs
    $('#remove1').click(function () {
        if (count1 > 0) {
            count1--;
            remainingGuests++;
            updateUI();
        }
    });

    // Add 11+
    $('#adding2').click(function () {
        if (remainingGuests <= 0) {
            showToast();
            return;
        }
        count2++;
        remainingGuests--;
        updateUI();
    });

    // Remove 11+
    $('#remove2').click(function () {
        if (count2 > 0) {
            count2--;
            remainingGuests++;
            updateUI();
        }
    });

    function showToast() {
        Toastify({
            text: 'All guests are already added to the pool.',
            duration: 3000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: '#cc3300'
        }).showToast();
    }

});

$(document).ready(function() {
  $('body').on('click', '#SubmitPoolData', function() {
    $.ajax({
      type: 'Post',
      url: '/reservations/pool',
      cache: false,
      data: $('#poolData').serialize(),
      beforeSend: function () {
        $('#PoolModal').modal('hide');
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
    })
  })
})

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
    const adult_count = $(this).data('adult_count');
    const children_count = $(this).data('children_count');
    const category = $(this).data('category');

    const adultTotal = adult_count * 100;
    const childrenTotal = children_count * 70;

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

    let poolHtml = category === 'cottage'? `
            <div>4–10 yrs old: ${children_count ?? 0} x 70 = ${childrenTotal ?? 0}</div>
            <div class="mt-3">11 yrs old and above: ${adult_count ?? 0} x 100 = ${adultTotal ?? 0}</div>
            ` : `
            <div class="text-start fw-bold">Free Pool</div>
            `;
            
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
    let footerHtml = '';
    footerHtml = `<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              <a href="/booking/pdf/${id}" target="_blank" type="button" class="btn btn-primary" >Print PDF</a>`;
    $('#pool_list').html(poolHtml);
    $('#foods_list').html(foodHtml); 
    $('#footerBtn').html(footerHtml); 
  });
});


$(document).ready(function () {
  $('body').on('click', '.DoneBtn', function () {
    const id = $(this).data('id');
    const payment_id = $(this).data('payment_id');
    const amount = $(this).data('payment_amount');

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes!',
      cancelButtonText: 'No!',
      reverseButtons: true
    }).then(result => {
      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          url: '/reservations/done',
          cache: false,
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id,
            payment_id: payment_id,
            amount: amount
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
  });
});



// extension payment
function calculateDays(checkIn, checkOut) { 
    if (!checkIn || !checkOut) return 0;

    const inDate = new Date(checkIn);
    const outDate = new Date(checkOut);

    const diffMs = outDate - inDate;
    const diffDays = diffMs / (1000 * 60 * 60 * 24);
    return diffDays;
}

function calculateHours(start, end) {
    if (!start || !end) return 0;

    const inDate = new Date(start);
    const outDate = new Date(end);

    const diffMs = outDate - inDate;
    const diffHours = diffMs / (1000 * 60 * 60);
    return diffHours;
}

function normalizeDate(dateStr) {
    if (!dateStr) return null;

    dateStr = dateStr.replace(" ", "T");

    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(dateStr)) {
        dateStr += ":00";
    }
    return dateStr;
}
function calculateExtendedHours(endTimeStr, extendTimeStr) {
    // Convert strings to Date objects
    const end = new Date(endTimeStr.replace(" ", "T"));
    const extendTime = new Date(extendTimeStr.replace(" ", "T"));

    // If extended time is earlier than end, assume next day
    if (extendTime < end) {
        extendTime.setDate(extendTime.getDate() + 1);
    }

    // Calculate difference in hours
    const diffMs = extendTime - end;
    const hours = diffMs / (1000 * 60 * 60);

    // Round up to nearest hour
    return Math.ceil(hours);
}

$(document).ready(function () {
    $('body').on('click', '.AddBtn', function(){

        const id = $(this).data('id');
        const name = $(this).data('name');
        const facility = $(this).data('facility');
        const payment = $(this).data('payment');
        const start = $(this).data('start');
        const end = $(this).data('end');
        const price = $(this).data('price');
        const category = $(this).data('category');
        const additional_price_time = $(this).data('additional_price_time');
        
        $('#id').val(id);
        $('#name').val(name);
        $('#facilities').val(facility);
        $('#payment').val(payment);
        $('#checkin').val(start);
        $('#checkout').val(end);

        $('body').on('click', '#Check', function(event) {

            const fields = [
                { id: 'extend', label: 'New Date' },
            ];
            const isValid = validateForm(fields);

            if (!isValid) {
                event.preventDefault();
                return;
            }

            const extendTime = $('#extend').val();
            function isPositiveWholeNumber(num) {
                return Number.isInteger(num) && num > 0;
            }
            if (category === "room") {
                const days = calculateDays(normalizeDate(end), normalizeDate(extendTime));
                
                if(days >= 1){
                  const amount = price * days;
                  $('#additional').val(amount);
                }else{
                  const hours = calculateExtendedHours(end, extendTime);
                  const totalamount = hours * additional_price_time;
                  $('#additional').val(totalamount);
                }
                
            }

            else if (category === "cottage") {
                const extendDate = new Date(extendTime);
                const hour = extendDate.getHours();

                if (hour >= 22 || hour < 5) {
                    Toastify({
                        text: 'Cottage extensions are not allowed from 10:00 PM to 7:59 AM due to curfew.',
                        duration: 3000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#cc3300',
                        stopOnFocus: true
                    }).showToast();
                    return;
                }

                const hours = calculateHours(normalizeDate(end), normalizeDate(extendTime));
                const amount = hours * additional_price_time; // change into the actual additional time
                $('#additional').val(amount);
            }

        });
    });
});

$(document).ready(function() {
    $('body').on('click', '#SaveExtend', function() {

      const fields = [
          { id: 'extend', label: 'New Date' },
          { id: 'additional', label: 'Additional Payment'}
      ];
      const isValid = validateForm(fields);
      
      if (!isValid) {
          event.preventDefault();
          return;
      }

      $.ajax({
        type: 'POST',
        url: '/reservations/extend',
        cache: false,
        data: $('#ExtendData').serialize(),
        dataType: 'json',
        beforeSend: function () {
          $('#Extend').modal('hide');
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

    });
  });

  $(document).ready(function() {
    $('body').on('click', '.AddGuest', function() {
        const id = $(this).data('id');

        $('#reservation_id').val(id);
    })
    $('body').on('click', '#AddGuestSubmit', function() {

      const fields = [
          { id: 'guest', label: 'Number of Guest' },
      ];
      const isValid = validateForm(fields);
      
      if (!isValid) {
          event.preventDefault();
          return;
      }

      $.ajax({
        type: 'POST',
        url: '/reservations/guest',
        cache: false,
        data: $('#GuestData').serialize(),
        dataType: 'json',
        beforeSend: function () {
          $('#GuestModal').modal('hide');
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
        error: function (data) {
          $('.preloader').hide();
          Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
        }
      });

    });
  });


  