@extends('layouts/homeLayout')

@section('title', 'Home')

<style>
  /* === GLOBAL FONTS === */
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

  body, p, a, button {
    font-family: 'Poppins', sans-serif;
  }

  h1, h2, h3, h4, h5, h6, .section-title {
    font-family: 'Playfair Display', serif;
  }

  /* === HERO SECTION === */
  .hero-section {
    position: relative;
    background: url('assets/img/backgrounds/cover5.jpg') 
                center center/cover no-repeat;
    color: white;
    min-height: 100vh;
  }

  .hero-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1;
  }

  .hero-section .container {
    position: relative;
    z-index: 2;
  }

  .btn-primary {
    background-color: #0077b6;
    border: none;
    border-radius: 50px;
    font-weight: 500;
  }

  .btn-primary:hover {
    background-color: #005f87;
  }

  /* === EXPLORE SECTION === */
  .section-title {
    font-weight: bold;
  }

  .section-subtitle {
    color: #6c757d;
    font-style: italic;
  }

  /* === GALLERY SECTION === */
  .gallery-section img {
    border-radius: 1rem;
    object-fit: cover;
    width: 100%;
    height: 260px;
    transition: transform 0.3s ease;
  }

  .gallery-section img:hover {
    transform: scale(1.02);
  }

  /* === RATING SECTION === */
  .rating-section {
    background: #ffffff;
    color: #2c2c2c;
    padding: 80px 0;
  }

  .rating-card {
    background: linear-gradient(135deg, #fff8e1, #fff3cd);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }

  .rating-stars {
    color: #f9a825;
    font-size: 1.8rem;
  }

  .rating-number {
    font-size: 3rem;
    font-weight: 700;
  }

  .rating-quote {
    font-style: italic;
    max-width: 700px;
    margin: 20px auto;
    color: #444;
  }

  .rating-author {
    font-weight: 600;
    color: #333;
  }
  /* === TESTIMONIAL SECTION === */
  .testimonial-section {
    background: #f8f9fa;
  }

  .testimonial-card {
    border-radius: 1rem;
    background: #ffffff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }

  .testimonial-card p {
    color: #555;
  }

  .testimonial-card .rating-stars {
    color: #f9a825;
    font-size: 1.4rem;
  }

</style>

@section('content')

{{-- HERO SECTION --}}
<section class="hero-section d-flex flex-column justify-content-center align-items-center text-center">
  <div class="container">
    <h1 class="display-2 fw-bold mb-3 text-white">
      BLUE OASIS
    </h1>
    <p class="lead mb-4 px-3 mx-auto" style="max-width: 700px;">
      “Turn clicks into memories at Blue Oasis Beach Resort—designed for dreamers, explorers, and families who value togetherness.”
    </p>
    <p>
      <a href="https://www.google.com/maps/place/Blue+Oasis+Beach+Resort/@10.2689303,124.9816673,17z"
         target="_blank"
         class="text-white text-decoration-none">
        <i class="ri-map-pin-2-fill"></i> Tomas Oppus, Southern Leyte
      </a>
    </p>
    <a href="/booking" class="btn btn-primary btn-lg px-4 py-2 mt-3 d-inline-flex align-items-center">
      Book Now <i class="ri-arrow-right-line ms-2"></i>
    </a>
  </div>
</section>

{{-- EXPLORE SECTION --}}
<section class="bg-white py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h5 class="display-5 section-title">Serenity Awaits You</h5>
      <p class="section-subtitle mt-2">"Where every stay feels like a retreat, and every room tells a story."</p>
    </div>
    <div class="row">
      @foreach ($exploreRooms as $index => $room)
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div id="carouselExplore{{ $index }}" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              @foreach ($room['images'] as $imgIndex => $img)
              <div class="carousel-item {{ $imgIndex === 0 ? 'active' : '' }}">
                <img class="d-block w-100" src="{{ $img }}" alt="Room {{ $imgIndex + 1 }}">
              </div>
              @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExplore{{ $index }}" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExplore{{ $index }}" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          </div>
          <div class="card-body">
            <h5 class="card-title">{{ $room['title'] }}</h5>
            <p class="card-text text-muted">{{ $room['description'] }}</p>
            <div>
              @foreach ($room['aminities'] as $amenity)
                <span class="badge bg-primary me-1 mb-1">{{ $amenity }}</span>
              @endforeach
            </div>
          </div>
          <div class="card-footer text-center bg-white border-0">
            <a href="{{ route('booking') }}" class="text-primary fw-semibold text-decoration-none">Book Now</a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section class="testimonial-section py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <h3 class="display-5 fw-bold section-title mb-3">Guest Testimonials</h3>
      <p class="section-subtitle">"Hear from our happy guests who made memories with us."</p>
    </div>

    <div class="row justify-content-center">
      
      <!-- Testimonial 1 -->
      @forelse ($ratingData as $item)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-lg border-0 testimonial-card p-4 text-center h-100">
                <img src="{{ asset('assets/img/profile/profile.png') }}" 
                    alt="Guest Photo" 
                    class="rounded-circle mx-auto mb-3" 
                    width="80" height="80" style="object-fit: cover;">

                <p class="fst-italic text-muted mb-4">
                    {{ $item->comments }}
                </p>

                <div class="rating-stars mb-3">
                    @php
                        $full = floor($item->rating);
                        $half = ($item->rating - $full) >= 0.5 ? 1 : 0;
                        $empty = 5 - $full - $half;
                    @endphp

                    @for ($i = 0; $i < $full; $i++)
                        <i class="ri-star-fill"></i>
                    @endfor

                    @if ($half)
                        <i class="ri-star-half-fill"></i>
                    @endif

                    @for ($i = 0; $i < $empty; $i++)
                        <i class="ri-star-line"></i>
                    @endfor
                </div>

                <h6 class="fw-semibold mb-0">
                    {{ $item->firstname }} {{ $item->middlename ?? "" }} {{ $item->lastname }}
                </h6>
                <small class="text-muted">Guest</small>
            </div>
        </div>

    @empty
        {{-- Show 3 skeleton boxes --}}
        @for ($i = 0; $i < 3; $i++)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-lg border-0 p-4 text-center h-100">
                    
                    {{-- Avatar skeleton --}}
                    <div class="rounded-circle mx-auto mb-3 bg-light"
                        style="width: 80px; height: 80px; opacity: 0.5;">
                    </div>

                    {{-- Comment skeleton --}}
                    <div class="mb-4">
                        <div class="bg-light mx-auto mb-2" style="height: 12px; width: 80%; opacity: 0.5;"></div>
                        <div class="bg-light mx-auto mb-2" style="height: 12px; width: 70%; opacity: 0.5;"></div>
                        <div class="bg-light mx-auto" style="height: 12px; width: 60%; opacity: 0.5;"></div>
                    </div>

                    {{-- Stars skeleton --}}
                    <div class="d-flex justify-content-center mb-3">
                        @for ($j = 0; $j < 5; $j++)
                            <div class="bg-light mx-1" style="width: 20px; height: 20px; opacity: 0.5;"></div>
                        @endfor
                    </div>

                    {{-- Name skeleton --}}
                    <div class="bg-light mx-auto mb-2" style="height: 14px; width: 50%; opacity: 0.5;"></div>
                    <div class="bg-light mx-auto" style="height: 12px; width: 30%; opacity: 0.5;"></div>

                </div>
            </div>
        @endfor
    @endforelse


    </div>
  </div>
</section>
{{-- ⭐ RATING & TESTIMONIAL SECTION --}}
<section class="rating-section">
  <div class="container">
    <div class="row align-items-center">

      <!-- Rating Card -->
      <div class="col-md-5 mb-4 mb-md-0">
        <div class="card rating-card text-center p-4 h-100">
          <div class="card-body">
            <h5 class="fw-bold text-uppercase text-muted mb-2" style="letter-spacing: 1px;">Overall Rating</h5>
            <div class="mb-2 rating-stars">
              @php
                $fullStars = floor($rating); // number of full stars
                $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0; // half star if needed
                $emptyStars = 5 - $fullStars - $halfStar;
              @endphp

              @for ($i = 0; $i < $fullStars; $i++)
                <i class="ri-star-fill"></i>
              @endfor

              @if($halfStar)
                <i class="ri-star-half-fill"></i>
              @endif

              @for ($i = 0; $i < $emptyStars; $i++)
                <i class="ri-star-line"></i>
              @endfor
            </div>
            <h1 class="rating-number mb-2 text-dark">{{ number_format($rating, 1)}} / 5.0</h1>
            <p class="text-muted small">Based on {{ $count }} verified guest reviews</p>
            <hr class="my-3 w-75 mx-auto opacity-50">
            <p class="text-secondary fst-italic mb-0">
              “Your happiness is our horizon.”
            </p>
          </div>
        </div>
      </div>

      <!-- Testimonial Text -->
      <div class="col-md-7">
        <h2 class="fw-bold mb-3">What Our Guests Say</h2>
        <p class="medium text-muted mb-4">
          “Blue Oasis Beach Resort isn’t just a destination—it’s an experience.  
          From crystal-clear waters to breathtaking sunsets, every corner whispers serenity.  
          Guests love the peaceful atmosphere, friendly staff, and the way time seems to slow down by the shore.”
        </p>
        <div class="d-flex align-items-center mt-4">
          <img src="{{ asset('assets/img/profile/profile.png')}}" 
               alt="Guest Photo" 
               class="rounded-circle me-3" 
               width="60" height="60" style="object-fit: cover;">
          <div>
            <h6 class="mb-0 fw-semibold">John Doe</h6>
            <small class="text-muted">Guest from Cebu City</small>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
@endsection
