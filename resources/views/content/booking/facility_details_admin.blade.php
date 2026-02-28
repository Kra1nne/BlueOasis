@extends('layouts/blankLayout')

@section('title', 'Booking')

@section('page-script')
@vite('resources/assets/js/details.js')
@endsection

@section('page-style')
@vite(['resources/assets/css/deatails.css'])
@endsection

@section('content')

<section class="min-vh-100 bg-white" id="details">
  <div class="container" style="padding-top: 90px;">
    <div class="row">
      <div class="col">
        <nav aria-label="breadcrumb" class="mt-8">
          <ol class="breadcrumb breadcrumb-style2">
            <li class="breadcrumb-item">
              <a href="{{ route('product-facilities')}}">Facilities</a>
            </li>
            <li class="breadcrumb-item active">Booking</li>
          </ol>
        </nav>
        <section class="mt-5">
        <div class="row g-0">
          @php
            $pictureCount = $venue->picture->count();
          @endphp
          @if ($pictureCount == 1)
              <div class="col-12">
                  <img src="{{ asset($venue->picture[0]->path) }}" alt="Featured Picture" class="img-fluid rounded" style="width: 100%; height: auto; object-fit: cover;">
              </div>
          @else
              @foreach($venue->picture->take(5) as $index => $pic)
                  @if($index == 0)
                      <!-- First image: 60% -->
                      <div class="col-12 col-md-7">
                          <img src="{{ asset($pic->path) }}" alt="Featured Picture" class="img-fluid rounded" style="height: 100%; width: 100%; object-fit: cover;">
                      </div>
                      <!-- Container for the remaining images -->
                      <div class="col-12 col-md-5 d-flex flex-wrap">
                  @else
                      @php
                          $remaining = min(4, $venue->picture->count() - 1);
                          $widthClass = 'w-100'; // default if only 1 extra image
                          if ($remaining > 1) {
                              $widthClass = 'w-50'; // two columns if more than 1 remaining
                          }
                      @endphp
                      <div class="{{ $widthClass }} p-1" style="height: {{ 100 / ceil($remaining / 2) }}%;">
                          <img src="{{ asset($pic->path) }}" alt="Gallery Picture" class="img-fluid rounded" style="height: 100%; width: 100%; object-fit: cover;">
                      </div>
                  @endif
              @endforeach

              @if($venue->picture->count() > 0)
                  </div> <!-- Close remaining images container -->
              @endif
          @endif

        </div>
        <div class="d-flex align-items-center">
          <h3 class="my-3 font-bolder">{{$venue->name}}</h3>
          <span style="color: #ffc107; font-size: 1.2em;" class="ms-5">&#9733;</span>
          <span class="ms-1 fw-bold" style="font-size: 1.2em;">{{ number_format($venue->rating->avg('rating'), 1); }}</span>
        </div>

        <div class="row mt-5">
            @if (!empty($venue->amenities))
            <h5 class="fw-bolder mt-5">What this place can offer</h5>
        @endif
          <div class="col-md-6 col-lg-5">
          <div class="mb-5 d-flex flex-wrap gap-3">
            @if(!empty($venue->amenities))
              @foreach (explode(',', $venue->amenities) as $amenity)
                <span class="badge bg-label-primary">{{ trim($amenity) }}</span>
              @endforeach
            @endif
          </div>
        </div>
        </div>
         <div class="mt-4 px-2">
          <ul>
                <li>These facilities have a capacity limit of {{  $venue->max_person }} people{{ $venue->limit_add > 0 ? " and can accommodate up to $venue->limit_add additional persons." : " and do not allow any additional persons beyond that limit."}}</li>
            @foreach ($venue->promo as $item)
                <li>
                  The promo {{ $item->name }} have a capacity limit of {{ $item->max_person }} and do not allow any additional persons beyond that limit.
                </li>
            @endforeach
          </ul>
          </div>
        <div class="row justify-content-between mt-5">
          <div class="col-md-6 col-lg-6 mb-4 d-flex align-items-stretch">
            <div id="calendar" style="width: 100%; height: 500px;"></div>
          </div>
          <div class="col-md-6 col-lg-5 mb-9">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title"><span class="fw-bold" id="price"></span> {{$venue->category == "Cottage" ? 'per day' : ''}} </h5>
                <div class="d-flex my-4 align-items-center gap-5">
                  <div class="row w-100">
                    <div id="reservation-form">
                      <input type="hidden" id="venue_id" name="venue_id" >
                      <input type="hidden" id="venue_name" name="venue_name">
                      <input type="hidden" id="venue_price" name="venue_price">
                      @if ($venue->category == "room")
                           <div class="col mb-4">
                            <div class="form-floating form-floating-outline">
                              <input class="form-control" type="date" id="checkin-date"  />
                              <label for="checkin-date">CHECK-IN</label>
                            </div>
                          </div>
                          <div class="col mb-4">
                            <div class="form-floating form-floating-outline">
                              <input class="form-control" type="date" id="checkout-date"  />
                              <label for="checkout-date">CHECK-OUT</label>
                            </div>
                          </div>
                      @endif
                      @if ($venue->category == "cottage")
                      <input class="form-control d-none" type="datetime-local" id="checkin-date" />
                      <input class="form-control d-none" type="datetime-local" id="checkout-date" />
                          <div class="col mb-4">
                            <div class="form-floating form-floating-outline">
                              <input class="form-control" type="date" id="date"  />
                              <label for="date">DATE</label>
                            </div>
                          </div>
                          <div class="col mb-4">
                            <button type="button" class="badge bg-label-primary border border-white cottage-btn">5:00 AM - 5:00 PM</button>
                            <button type="button" class="badge bg-label-gray border border-white cottage-btn">5:00 PM - 10:00 PM</button>
                            <button type="button" class="badge bg-label-gray border border-white cottage-btn">5:00 AM - 10:00 PM</button>
                          </div>
                      @endif
                    <div class="col mb-4">
                      <div class="form-floating form-floating-outline">
                        <input class="form-control" type="number" id="guest" min="1" placeholder="Number of Guest" />
                        <label for="guest">Guest</label>
                      </div>
                    </div>
                    <div class="col mb-4">
                        <button type="button" class="badge bg-label-primary border border-white promo-btn active" data-promo-id="">Current</button>
                        @foreach ($venue->promo as $item => $info)
                            <button type="button" class="badge bg-label-gray border border-white promo-btn" data-promo-id="{{ $info->id }}">{{ $info->name }}</button>
                        @endforeach
                        <input type="hidden" id="promo_id" name="promo_id">
                    </div>
                  </div>
                  </div>
                </div>
                <button
                    class="btn btn-primary w-100"
                    @if(Auth::check())
                        id="reservationBtn"
                    @else
                        onclick="window.location.href='{{ route('login') }}'"
                    @endif
                >
                    Book
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-5">
          <h3 class="fw-bold text-start">Comments</h3>
          <div class="mb-4">
            @forelse ($ratings as $item)
              <div class="flex mb-4 align-content-start mt-2">
              <div class="d-flex align-items-center mb-4 mt-2">
                <img src="{{ asset('assets/img/profile/profile.png') }}" alt class="w-px-40 h-auto rounded-circle">

                </span>
                <span class="ms-3 fw-bold">{{ $item->firstname }} {{ $item->middlename ?? " "}} {{ $item->lastname}}<br><span class="text-muted" style="font-size: 13px;">{{$item->email}}</span></span>
              </div>
              <div>
                <div class="d-flex align-items-center mt-2 mb-2">
                  <span style="color: #ffc107; font-size: 1.2em;">&#9733;</span>
                  <span class="ms-1 fw-bold">{{ number_format($item->rating, 1)}}</span>
                  <span class="text-muted ms-3" style="font-size: 13px;">{{ date('F j, Y', strtotime($item->CommentDate)) }}</span>
                </div>
                <div>
                  <p class="text-start">{{$item->comments}}</p>
                </div>
              </div>
            </div>
            @empty
            <div>
              No reviews yet. Be the first to share your experience!
            </div>
            @endforelse

          </div>
        </div>
      </section>
      </div>
    </div>
  </div>
</section>
<div class="modal fade" id="BookingDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title">Booking Summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="BookingData" method="get" action="{{ route('product-facilities-booking-foods') }}">
          @csrf
          <input type="text" name="facility_category" id="facility_category" class="d-none">
          <input type="number" name="facility_id" value="0" id="facility_id" class="d-none">
          <input type="text" name="facility_name" id="facility_name" class="d-none">
          <input type="number" name="price" id="facility_price" class="d-none">
          <input type="text" name="promo_name" id="promo_name" class="d-none">
          <input type="datetime" name="checkin" id="facility_checkin" class="d-none">
          <input type="datetime" name="checkout" id="facility_checkout" class="d-none">
          <input type="number" name="number_of_guests" id="facility_number_of_guests" class="d-none">
          <input type="number" name="promo_id" id="facility_promo_id" class="d-none">
          <input type="number" name="number_of_days" id="facility_number_of_days" class="d-none">
          <input type="number" name="total_amount" id="facility_total_amount" class="d-none">
          <!-- Facility Details -->
          <div class="mb-3">
            <strong>Facility Name:</strong> <span id="facility-name_details"></span>
          </div>

          <!-- Time In / Time Out -->
          <div class="row mb-2">
            <div class="col-6">
              <strong>Time In:</strong> <span id="time-in_details"></span>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-6">
              <strong>Time Out:</strong> <span id="time-out_details"></span>
            </div>
          </div>

          <!-- Promo -->
          <div class="mb-2">
            <strong>Promo:</strong> <span id="promo_details"></span>
          </div>
          <div class="mb-2">
            <strong>Guests:</strong> <span id="number_of_guests"></span>
          </div>

          <!-- Price & Service Fee -->
          <div class="row mb-2">
            <div class="col-6">
              <strong>Price: </strong><span><span id="price_details"></span> x <span id="number_of_days"></span></span>
            </div>
          </div>
          <div class="mb-2">
            <div class="col-6">
              <strong>Service Fee:</strong> <span id="service-fee"></span>
            </div>
          </div>

          <!-- Total -->
          <div class="border-top pt-2 mb-3">
            <h5 class="d-flex justify-content-between">
              <span>Total (PHP):</span>
              <span id="total_amount" class="text-primary fw-bold"></span>
            </h5>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="SaveOrder">Confirm Booking</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="reservationModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reservation Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reservationList"></div>
    </div>
  </div>
</div>

<script>
  window.venue = @json($venue);
  window.bookingDetails = @json($bookingDetails);
</script>
@endsection
