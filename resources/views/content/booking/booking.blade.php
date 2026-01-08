@extends('layouts/homeLayout')

@section('title', 'Booking')

@section('page-script')
@vite('resources/assets/js/booking.js')
@endsection

<style> 
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

  body, p, a, button {
    font-family: 'Poppins', sans-serif;
  }
</style>
@section('content')
<section class="min-vh-100 bg-white" id="bookingsection" style="padding-top: 100px;">
  <div class="container">
    <div class="row">
      <div class="col">
        <!-- Header / Search -->
        <header class="mb-4 navbar-nav-right d-flex flex-wrap align-items-center px-3 mt-4">
          <div class="navbar-nav align-items-start flex-grow-1 mb-2 mb-sm-0">
            <div class="nav-item d-flex align-items-center w-100">
              <i class="ri-search-line ri-22px me-2"></i>
              <input type="text" id="search" class="form-control border-0 shadow-none ps-2 w-100" placeholder="Search for your ideal venue..." aria-label="Search...">
            </div>
          </div>
          <div class="navbar-nav d-flex flex-row align-items-center gap-2 ms-auto">

          @if(!$isFiltering)
              <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filtering">
                  <i class="ri-filter-line ri-22px me-1"></i>Filter
              </button>
          @endif
            {{-- Dropdown appears ONLY if user applied filters --}}
          @if($isFiltering)
              <div class="dropdown">
                  <a class="btn btn-outline-secondary" href="{{ route('booking') }}">
                      <i class="ri-loader-line"></i> Reload
                  </a>
              </div>
          @endif
        </div>
        </header>

        <!-- Facilities Grid -->
        <div class="nav-align-top mb-6">
          <div>
            <div class="row row-cols-1 row-cols-md-3 g-4" id="facilities">
              <!-- Facility items will populate here -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filter Modal -->
<div class="modal fade" id="filtering" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title text-center w-100">Filter for venues</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="filteringData" action="{{ route('booking')}}" method="GET">
          @csrf
          <div class="row mb-4">
            <div class="col">
              <label for="checkin" class="form-label">Start Date</label>
              <input type="date" name="checkin" id="checkin" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <div class="col">
              <label for="checkout" class="form-label">End Date</label>
              <input type="date" name="checkout" id="checkout" class="form-control">
            </div>
          </div>
           <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="SaveComment">Apply Filters</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Scroll to top button -->
<a href="#bookingsection"
  class="btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center shadow-lg"
  style="width: 50px; height: 50px; z-index: 1030;">
  <i class="ri-arrow-up-line fs-4 text-white"></i>
</a>

@php
  $venues = collect($venues)->map(function($venue) {
    return [
      'id' => $venue->id,
      'name' => $venue->name,
      'category' => $venue->category,
      'description' => $venue->description,
      'picture' => $venue->picture,
      'price' => $venue->price,
      'encrypted_id' => Crypt::encryptString($venue->id),
      'max_person' => $venue->max_person,
      'additional_price' => $venue->additional_price,
      'rating' => $venue->rating
    ];
  })->values()->toArray();
@endphp

<script>
  window.venues = @json($venues);

  // Simple fade-in effect for facilities
  document.addEventListener('DOMContentLoaded', function() {
    const facilitiesContainer = document.getElementById('facilities');
    facilitiesContainer.querySelectorAll('.facility-item').forEach((item, index) => {
      item.style.opacity = 0;
      item.style.transform = 'scale(0.95)';
      setTimeout(() => {
        item.style.transition = 'all 0.5s ease';
        item.style.opacity = 1;
        item.style.transform = 'scale(1)';
      }, index * 150);
    });
  });
</script>

@if(session('show_modal'))
    <a id="pdfDownloadBtn"
       href="{{ route('generatePDF', ['booking' => session('booking_id')]) }}"
       target="_blank"
       style="display:none; !important">
    </a>
    <script>
        Toastify({
            text: "Payment Successful!",
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "green",
            stopOnFocus: true
        }).showToast();
        window.onload = function() {
            document.getElementById('pdfDownloadBtn').click();
        };
    </script>
@endif
@if(session('show_modal'))
    <script>
        window.onload = function () {
            fetch("{{ route('email.success') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    booking_id: "{{ session('booking_id') }}"
                })
            });
        };
    </script>
@endif


<style>
  /* Fade-in and scale-up effect for dynamically added facilities */
  .facility-item {
    opacity: 0;
    transform: scale(0.95);
  }
</style>
@endsection
