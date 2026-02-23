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
  $('body').on('click', '#SaveProduct', function () {
    const fields = [
      { id: 'DataImages', label: 'Images' },
      { id: 'name', label: 'Name' },
      { id: 'price', label: 'Price' },
      { id: 'category', label: 'Category' },
      { id: 'description', label: 'Description' },
      { id: 'max_person', label: 'Number of Pax' },
      { id: 'additional_price', label: 'Additional Price (person)' },
      { id: 'addprice_perhour', label: 'Additional Price (hour)' },
      { id: 'limit_add', label: 'Additional Person' }
    ];

    const isValid = validateForm(fields);

    if (!isValid) {
      event.preventDefault();
      return;
    }
    var formData = new FormData($('#ProductData')[0]);
    $.ajax({
      type: 'POST',
      url: '/facilities/add',
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      dataType: 'json',
      beforeSend: function () {
        $('#AddProduct').modal('hide');
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
// filter
$(document).ready(function () {
  $('body').on('click', '#FilterBtn', function () {
    $.ajax({
      url: '/admin/product/filter',
      type: 'post',
      cache: false,
      data: $('#filterData').serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#FilterModal').modal('hide');
        $('.preloader').show();
      },
      success: function (data) {
        $('.preloader').hide();
        if (data.Error == 1) {
          Swal.fire('Error!', data.Message, 'error');
        } else if (data.Error == 0) {
          console.log(data.Message);
          location.reload();
        }
      },
      error: function () {
        $('.preloader').hide();
        Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
      }
    });
  });
});

// retrieve a data
$(document).ready(function () {
  $('body').on('click', '.Edit', function () {
    const id = $(this).data('id');
    const images = $(this).data('images');
    const name = $(this).data('name');
    const price = $(this).data('price');
    const category = $(this).data('category');
    const description = $(this).data('description');
    const max = $(this).data('max');
    const additional = $(this).data('additional');
    const amenities = $(this).data('amenities');
    const limit_add = $(this).data('limit_add');
    const additional_price_time = $(this).data('additional_price_time');
    console.log(limit_add);
    const imageArray = images.split(',');

    $('#Edit_id').val(id);
    $('#Edit_name').val(name);
    $('#Edit_price').val(price);
    $('#Edit_category').val(category);
    $('#Edit_description').val(description);
    $('#Edit_maxPerson').val(max);
    $('#Edit_additionalPrice').val(additional);
    $('#Edit_amenities').val(amenities);
    $('#Edit_addprice_perhour').val(additional_price_time);
    $('#Edit_limit_add').val(limit_add);

    $('#Edit_ImagePreview').empty();

    imageArray.forEach(function (image, index) {
      $('#Edit_ImagePreview').append(`
        <div class="position-relative" style="width: 100px; height: 100px;">
          <img src="${image}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
          <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image" data-image="${image}" style="padding:2px 6px; font-size:12px;">&times;</button>
        </div>
      `);
    });
  });
  $('body').on('click', '.remove-image', function () {
    $(this).parent().remove();

    let removedImages = $('#removed_images').val() ? $('#removed_images').val().split(',') : [];
    removedImages.push($(this).data('image'));
    $('#removed_images').val(removedImages.join(','));
  });
});
// update
$(document).ready(function () {
  $('body').on('click', '#UpdateProduct', function () {
    const fields = [
      { id: 'Edit_name', label: 'Name' },
      { id: 'Edit_price', label: 'Price' },
      { id: 'Edit_category', label: 'Category' },
      { id: 'Edit_description', label: 'Description' },
      { id: 'Edit_maxPerson', label: 'Number of Pax' },
      { id: 'Edit_additionalPrice', label: 'Additional Price (person)' },
      { id: 'Edit_addprice_perhour', label: 'Additional Price (hour)' },
      { id: 'Edit_limit_add', label: 'Additional Person' }
    ];

    const isValid = validateForm(fields);

    if (!isValid) {
      event.preventDefault();
      return;
    }
    var formData = new FormData($('#ProductDataUpdate')[0]);
    console.log(formData);
    $.ajax({
      type: 'POST',
      url: '/facilities/update',
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      dataType: 'json',
      beforeSend: function () {
        $('#EditProduct').modal('hide');
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

    //
  });
});

//delete
$(document).ready(function () {
  $('body').on('click', '.DeleteBtn', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'No, cancel!',
      reverseButtons: true
    }).then(result => {
      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          url: '/facilities/delete',
          cache: false,
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id
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

$(document).ready(function () {
  // Function to render venues
  function displayVenues(venues) {
    const categories = ['room', 'cottage'];
    categories.forEach(category => {
      const $categoryList = $(`#${category}s`);
      $categoryList.empty();

      const filteredVenues = venues.filter(venue => venue.category === category);

      if (filteredVenues.length === 0) {
        $categoryList.html(`
          <div class="text-left text-muted">
          </div>
        `);
        return;
      }

      filteredVenues.forEach(venue => {
        const carouselId = 'carouselVenue' + venue.id;

        const venueCard = `
          <div class="col mb-5">
            <div class="card">
              <div id="${carouselId}" class="carousel carousel-dark slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-indicators">
                  ${venue.picture
                    .map(
                      (pic, index) => `
                    <button type="button" data-bs-target="#${carouselId}" data-bs-slide-to="${index}"
                      ${index === 0 ? 'class="active" aria-current="true"' : ''} aria-label="Slide ${index + 1}">
                    </button>`
                    )
                    .join('')}
                </div>

                <div class="carousel-inner">
                  ${venue.picture
                    .map(
                      (pic, index) => `
                    <div class="carousel-item ${index === 0 ? 'active' : ''}">
                      <img class="d-block w-100" src="${pic.path}" alt="Slide ${index + 1}" style="height: 300px; object-fit: cover;">
                    </div>`
                    )
                    .join('')}
                </div>

                <a class="carousel-control-prev" href="#${carouselId}" role="button" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </a>
                <a class="carousel-control-next" href="#${carouselId}" role="button" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </a>
              </div>

              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h5><a href="/facilities/details/${venue.encrypted_id}" class="card-title me-auto">${venue.name}</a></h5>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="ri-more-2-line"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item Edit" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#EditProduct"
                        data-max="${venue.max_person}" data-additional="${venue.additional_price}"
                        data-id="${venue.encrypted_id}" data-name="${venue.name}" data-price="${venue.price}"
                        data-category="${venue.category}" data-description="${venue.description}"
                        data-amenities="${venue.amenities}"
                        data-additional_price_time="${venue.additional_price_time}"
                        data-limit_add="${venue.limit_add}"
                        data-images="${venue.picture.map(pic => pic.path).join(',')}" data-code="${venue.picture[0]?.v_code}">
                        <i class="ri-pencil-line me-1"></i> Edit
                      </a>
                      <a class="dropdown-item DeleteBtn" data-id="${venue.encrypted_id}" href="javascript:void(0);">
                        <i class="ri-delete-bin-6-line me-1"></i> Delete
                      </a>
                    </div>
                  </div>
                </div>
                <p><strong>₱${venue.price.toFixed(2)}</strong></p>
              </div>
            </div>
          </div>
        `;
        $categoryList.append(venueCard);
      });
    });
  }

  // Function to filter venues based on search query
  function filterVenues(query) {
    const filtered = window.venues.filter(venue => venue.name.toLowerCase().includes(query));
    displayVenues(filtered);
  }

  // Search input handler
  $('#search').on('input', function () {
    const query = $(this).val().toLowerCase();
    filterVenues(query);
  });

  // Render all venues initially on page load
  displayVenues(window.venues);
});
