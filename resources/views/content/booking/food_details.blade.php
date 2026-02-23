@extends('layouts/homeLayout')

@section('title', 'Booking')

@section('page-script')
@vite('resources/assets/js/foodOrderDetails.js')
@endsection

@section('content')
<section class="min-vh-100 bg-white" id="details" style="padding-top: 90px;">
  <div class="container">
    <div class="row mb-3">
      <div class="col">
        <nav aria-label="breadcrumb" class="mt-8">
          <ol class="breadcrumb breadcrumb-style2">
            <li class="breadcrumb-item">
              <a href="{{ route('booking') }}">Booking</a>
            </li>
            <li class="breadcrumb-item active">Foods</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row">
      {{-- Left Column: Foods --}}
      <div class="col-lg-8">
        <h4 class="mb-4">Available Foods</h4>
        <div class="row">
          @foreach($foods as $food)
            <div class="col-md-6 mb-4">
              <div class="card h-100 shadow-sm">
                @php
                  $firstPicture = $food->picture->first();
                @endphp
                @if($firstPicture && $firstPicture->path)
                  <img src="{{ asset($firstPicture->path) }}" class="card-img-top" alt="{{ $food->name }}" style="height: 350px; object-fit: cover;">
                @else
                  <img src="{{ asset('images/no-image.png') }}" class="card-img-top" alt="No Image">
                @endif
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">{{ $food->name }} - {{ number_format($food->price, 2) }}</h5>
                  <div class="mt-auto">
                    <form class="add-food-form" data-food-name="{{ $food->name }}" data-food-id="{{ $food->id }}" data-food-price="{{ $food->price }}">
                      @csrf
                      <input type="hidden" name="food_id" value="{{ $food->id }}">
                      @foreach($bookingDetails as $key => $value)
                        <input type="hidden" name="bookingDetails[{{ $key }}]" value="{{ $value }}">
                      @endforeach
                      <div class="input-group mb-2">
                        <input type="number" min="1" value="1" class="form-control food-qty" style="max-width:80px;" placeholder="Qty">
                        <button type="submit" class="btn btn-primary btn-block add-food-btn">Add Food</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Right Column: Booking Details (Sticky) --}}
      <div class="col-lg-4">
        <div class="card shadow-sm sticky-top" style="top: 100px;">
          <div class="card-body">
            <h5 class="card-title mb-3">Booking Details</h5>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <strong>Facility Name: </strong> {{ $bookingDetails['facility_name'] }}
              </li>
              <li class="list-group-item">
                <strong>Promo Name: </strong> {{ $bookingDetails['promo_name'] }}
              </li>
              <li class="list-group-item">
                <strong>Facility Price: </strong>₱<span id="facility-price">{{ number_format($bookingDetails['price'], 2) }}</span>
              </li>
              <li class="list-group-item">
                <strong>Check in: </strong>
                @php
                  $checkin = $bookingDetails['checkin'];
                  $checkinTime = date('H:i:s', strtotime($checkin));
                @endphp
                {{ date('F j, Y', strtotime($checkin)) }}
                @if($checkinTime != '00:00:00')
                  , {{ date('g:i A', strtotime($checkin)) }}
                @endif
              </li>
              <li class="list-group-item">
                <strong>Check out: </strong>
                @php
                  $checkout = $bookingDetails['checkout'];
                  $checkoutTime = date('H:i:s', strtotime($checkout));
                @endphp
                {{ date('F j, Y', strtotime($checkout)) }}
                @if($checkoutTime != '00:00:00')
                  , {{ date('g:i A', strtotime($checkout)) }}
                @endif
              </li>
              <li class="list-group-item">
                <strong>Number of Guests: </strong> {{ $bookingDetails['number_of_guests'] }}
              </li>
               <li class="list-group-item">
                <strong>Number of Days: </strong> {{ $bookingDetails['number_of_days'] }}
              </li>
              <li class="list-group-item">
                <strong>Booking Amount: </strong>₱<span id="base-total">{{ number_format($bookingDetails['total_amount'], 2) }}</span>
              </li>
              <li class="list-group-item">
                <strong>Food: </strong> <span id="food-name">None</span>
              </li>
              <li class="list-group-item">
                <strong>Food Price: </strong>₱<span id="food-price">0.00</span>
              </li>
              <li class="list-group-item">
                <strong  class="text-primary fw-bold">Partial Payment: </strong><span id="partial-amount" class="text-primary fw-bold">₱{{ number_format($bookingDetails['total_amount'], 2) }}</span>
              </li>
              <li class="list-group-item">
                <strong  class="text-primary fw-bold">Total Amount: </strong><span id="total-amount" class="text-primary fw-bold">₱{{ number_format($bookingDetails['total_amount'], 2) }}</span>
              </li>
            </ul>
            <div class="mt-4 px-2">
              <p class="text-muted">Note: Please be advised that the payment for your reservation is non-refundable, even in the event of cancellation. By confirming your booking, you acknowledge and agree to this policy.</p>
            </div>
            <form class="mt-3 d-flex justify-content-end" method="POST" action="{{ route('paymongo.checkout') }}">
              @csrf
              <input type="hidden" name="facility_income" id="facility_income">
              <input type="hidden" name="food_id" id="food_id">
              <input type="hidden" name="food_quantity" id="food_quantity">
              <input type="hidden" name="facility_id" value="{{ $bookingDetails['facility_id'] }}">
              <input type="hidden" id="promo_id" name="promo_id" value="{{ $bookingDetails['promo_id'] }}">
              <input type="hidden" id="number_of_days" name="number_of_days" value="{{ $bookingDetails['number_of_days'] }}">
              <input type="hidden" id="total_amount" name="total_amount">
              <input type="hidden" name="check_in" id="check_in" value="{{ $bookingDetails['checkin'] }}">
              <input type="hidden" name="check_out" id="check_out" value="{{ $bookingDetails['checkout'] }}">
              <input type="hidden" name="guest_count" id="guest_count" value="{{ $bookingDetails['number_of_guests'] }}">
              
              <div class="d-flex gap-3">
                  <button type="submit" name="payment_type" value="partial" class="btn btn-success">
                      Partial Payment
                  </button>
                  <button type="submit" name="payment_type" value="full" class="btn btn-success">
                      Full Payment
                  </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
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
@endsection
