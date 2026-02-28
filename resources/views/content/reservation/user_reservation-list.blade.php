@extends('layouts/homeLayout')

@section('title', 'Reservation List')

@section('page-script')
@vite('resources/assets/js/user-reservation.js')
@endsection

<style>
  .star-rating .bi-star,
  .star-rating .bi-star-fill {
    font-size: 2rem;
    color: #ccc;
    cursor: pointer;
  }

  .star-rating .bi-star-fill.active {
    color: #ffc107; 
  }
</style>
<!-- Content -->
@section('content')
<section class="min-vh-100 bg-white" style="padding-top: 110px;">
  <div class="container ">
    <div class="mt-5 pb-5">
      <header class=" navbar-nav-right d-flex align-items-center px-3 mt-3">
        <div class="navbar-nav align-items-start">
          <div class="nav-item d-flex align-items-center">
            <i class="ri-search-line ri-22px me-1_5"></i>
            <input type="search" id="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 ms-50" placeholder="Search..." aria-label="Search...">
          </div>
        </div>
      </header>
      <div id="reservationList">
      </div>
    </div>
  </div>
</section>
<div class="modal fade" id="ViewReservation" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link gap-3">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-5">

          <div class="mb-5 p-4">

            <div class="mb-3">
              <strong>Customer Name: </strong> <span id="facility-customer"></span>
            </div>

            <div class="mb-3">
              <strong>Facility Name: </strong> <span id="facility-name_details"></span>
            </div>

            <!-- Time In / Time Out -->
            <div class="row mb-2">
              <div class="col-12">
                <strong>Time In: </strong> <span id="time-in_details"></span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <strong>Time Out: </strong> <span id="time-out_details"></span>
              </div>
            </div>

            <!-- Promo -->
            <div class="mb-2">
              <strong>Promo: </strong> <span id="promo_details"></span>
            </div>
            <div class="mb-2">
              <strong>Guests: </strong> <span id="number_of_guests"></span>
            </div>

            <!-- Price & Service Fee -->
            <div class="row mb-2">
              <div class="col-12">
                <strong>Price: </strong><span><span id="price_details"></span>
              </div>
            </div>

            <div class="row mb-2">
              <div class="col-12">
                <strong>Date: </strong> <span id="date"></span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <strong>Payment: </strong> <span id="payment_amount"></span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <strong>Mode of Payment: </strong> <span>Gcash</span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <strong>Payment Status: </strong> <span id="status"></span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <strong>Service Fee: </strong> <span>50.00</span>
              </div>
            </div>

            <div><h5 class="text-center">Foods</h5></div>
            <div id="foods_list" class="mt-3"></div>

            <!-- Total -->
            <div class="border-top pt-2 mb-3">
              
              <h5 class="d-flex justify-content-between">
                <span>Total (PHP):</span>
                <span id="total_amount" class="text-primary fw-bold"></span>
              </h5>
            </div>

          </div>
        </div>
        <div class="modal-footer" id="footerBtn">
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="AddRating" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-center" id="exampleModalLabel1">Ratings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>We’d love to hear your feedback and rating of your experience at Blue Oasis..</p>
        <form id="ratingData" enctype="multipart/form-data">
          @csrf
          <div class="row mb-5">
            <div class="col">
              <div class="star-rating" id="starRating">
                <i class="ri-star-line" style="font-size: 2rem;" data-value="1"></i>
                <i class="ri-star-line" style="font-size: 2rem;" data-value="2"></i>
                <i class="ri-star-line" style="font-size: 2rem;" data-value="3"></i>
                <i class="ri-star-line" style="font-size: 2rem;" data-value="4"></i>
                <i class="ri-star-line" style="font-size: 2rem;" data-value="5"></i>
              </div>

              <input type="hidden" name="rating" id="ratingInput" value="0">
              <input type="hidden" name="bookings_id" id="bookings_id">
              <input type="hidden" name="facilities_id" id="facilities_id">
            </div>
          </div>
          <div class="row mb-5">
            <div class="col">
              <label for="Upload">Upload Images (Optional)</label>
              <div class="form-floating form-floating-outline mb-2">
                <input type="file"  name="imagesData[]" multiple accept="image/*" class="form-control" id="DataImages">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-floating form-floating-outline mb-2">
                <textarea class="form-control h-px-100" id="description" placeholder="Comments here..." name="description"></textarea>
                <label for="description">Comment</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="SaveComment">Save</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="UpdateReservation" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-center" id="exampleModalLabel1">Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>We’d be happy to assist you with updating the date of your reservation at Blue Oasis Beach Resort.</p>
        <form id="updateData" >
          @csrf
          <div class="row mb-5">
            <div class="col">
              <input type="hidden" id="reservationID" name="id">
              <input type="hidden" id="facilityID" name="facilityId">
              <input type="hidden" id="cat" name="cat">
              <input type="date" name="checkin" id="checkin" class="form-control">
            </div>
          </div>
          <div class="row mb-5">
            <div class="col">
              <input type="date" name="checkout" id="checkout" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="UpdateBtn">Update</button>
      </div>
    </div>
  </div>
</div>
<script>
  window.reservations = @json($reservations);
</script>
<script>
    // Laravel route and CSRF token passed to JS
    const fullpaidRoute = '{{ route("reservation-paid") }}';
    const csrfToken = '{{ csrf_token() }}';
</script>
@if(session('payment_error'))
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'error',
        title: 'Payment Error',
        text: '{{ session("payment_error") }}',
        confirmButtonText: 'Okay',
    });
});
</script>
@endif
@if(session('payment_success'))
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'success',
        title: 'Payment Success',
        text: '{{ session("payment_success") }}',
        confirmButtonText: 'Okay',
    });
});
</script>
@endif
@endsection
