@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')
<div class="row gy-6">

  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="avatar">
          <div class="avatar-initial bg-primary rounded-circle shadow-xs">
            <i class="ri-hotel-line ri-24px text-white"></i>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="mb-1 mt-4">Rooms & Cottages</h6>
        <div class="d-flex flex-wrap mb-4 align-items-center">
          <h4 class="mb-0 me-2 fw-semibold" id="roomCount">{{ $facilitycount }}</h4>
        </div>
        <div class="mt-4">
          <ul class="p-0 m-0">
            <li class="d-flex mb-3">
              <div class="avatar flex-shrink-0 bg-light-primary rounded me-3">
                <i class="ri-home-smile-line ri-xl text-primary"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Total Facilities</h6>
                <div class="progress bg-label-primary" style="height: 4px;">
                  <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Overall Rating -->
  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="avatar">
          <div class="avatar-initial bg-warning rounded-circle shadow-xs">
            <i class="ri-star-smile-line ri-24px text-white"></i>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="mb-1 mt-4">Overall Rating</h6>
        <div class="d-flex flex-wrap mb-4 align-items-center">
          <h4 class="mb-0 me-2 fw-semibold" id="overallRating">{{ number_format($ratingavg, 1) ?? 0 }} / 5</h4>
        </div>
        <div class="mt-4">
          <ul class="p-0 m-0">
            <li class="d-flex mb-3">
              <div class="avatar flex-shrink-0 bg-light-warning rounded me-3">
                <i class="ri-user-smile-line ri-xl text-warning"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Guest Satisfaction</h6>
                <div class="progress bg-label-warning" style="height: 4px;">
                  <div class="progress-bar bg-warning" style="width: 100%"></div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Items in Menu -->
  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="avatar">
          <div class="avatar-initial bg-success rounded-circle shadow-xs">
            <i class="ri-restaurant-line ri-24px text-white"></i>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="mb-1 mt-4">Food Menu</h6>
        <div class="d-flex flex-wrap mb-4 align-items-center">
          <h4 class="mb-0 me-2 fw-semibold" id="menuItemCount">{{ $foodCount }}</h4>
        </div>
        <div class="mt-4">
          <ul class="p-0 m-0">
            <li class="d-flex mb-3">
              <div class="avatar flex-shrink-0 bg-light-success rounded me-3">
                <i class="ri-bread-line ri-xl text-success"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Available Today</h6>
                <div class="progress bg-label-success" style="height: 4px;">
                  <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="avatar">
          <div class="avatar-initial bg-info rounded-circle shadow-xs">
            <i class="ri-cash-line ri-24px text-white"></i>
          </div>
        </div>
      </div>
      <div class="card-body">
        <h6 class="mb-1 mt-4">Total Earnings</h6>
        <div class="d-flex flex-wrap mb-4 align-items-center">
          <h4 class="mb-0 me-2 fw-semibold" id="totalEarnings">₱{{ number_format($amount, 2)}}</h4>
        </div>
        <div class="mt-4">
          <ul class="p-0 m-0">
            <li class="d-flex mb-3">
              <div class="avatar flex-shrink-0 bg-light-info rounded me-3">
                <i class="ri-wallet-2-line ri-xl text-info"></i>
              </div>
              <div class="flex-grow-1">
               <h6 class="mb-1">{{ now()->format('F j, Y') }}</h6>
                <div class="progress bg-label-info" style="height: 4px;">
                  <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- facilities -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-item-center">
        <h4>Facilities</h4>
         <form method="GET">
              <label for="month" class="form-label">Select Month:</label>
              <div class="d-flex gap-2">
                <input type="month" id="monthFacilities" name="monthFacilities" class="form-control" value="{{ $monthFacility }}" onchange="this.form.submit()">
              </div>
          </form>
      </div>
      <div class="card-body">
        <div id="facilitiesChart" style="height: 400px;"></div>
        <span id="facility-revenue-data" style="display:none;">
            {{ json_encode($facilitiesData->map(fn($f) => ['name' => $f->name, 'total' => $f->total])) }}
        </span>
      </div>
    </div>
  </div>
  <!-- foods -->
  
      <div class="col-12">
          <div class="card">
              <div class="card-header">
                  <div class="d-flex justify-content-between">
                    <h4>Foods Revenue</h4>
                    <form method="GET">
                        <label for="month" class="form-label">Select Month:</label>
                        <div class="d-flex gap-2">
                          <input type="month" id="monthFood" name="month" class="form-control" value="{{ $monthFood }}" onchange="this.form.submit()">
                        </div>
                    </form>
                  </div>
              </div>
              <div class="card-body">
                  <div id="foodsChart" style="height: 400px;"></div>
                  <span id="foods-revenue-data" style="display:none;">
                      {{ json_encode($foodData->map(fn($f) => ['name' => $f->name, 'total' => $f->total])) }}
                  </span>
              </div>
          </div>
      </div>

    <!-- Additional Analytics Section -->
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Customer Satisfaction Score</h4>
      </div>
      <div class="card-body">
        <div id="customerSatisfactionGauge" style="height: 345px;"></div>
        <span id="rating-counts-data" style="display:none;">{{ json_encode($ratingCounts ?? []) }}</span>        
      </div>
    </div>
  </div>

  


  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">Revenue Reservation</h4>
      </div>
      <div class="card-body text-center">
        <h2 class="fw-semibold text-success mb-2">
          ₱{{ number_format($averageRevenue, 2) }}
        </h2>
        <p class="text-muted mb-3">Average per booking</p>
        <div id="avgRevenueSparkline" style="height: 250px;"></div>

        <!-- Hidden data for trend -->
        <span id="avg-revenue-data" style="display:none;">
          {{ json_encode($monthlyRevenue) }}
        </span>
      </div>
    </div>
  </div>

  


  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">Cancellations</h4>
      </div>
      <div class="card-body">
        <div id="refundsChart" style="height: 300px;"></div>
        <span id="refunds-data" style="display:none;">
          {{ json_encode($refundsData) }}
        </span>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Revenue by Category</h4>
        <form method="GET">
          <select name="year" class="form-select" onchange="this.form.submit()">
              @forelse ($availableYearsBooking as $y)
                  <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
              @empty
                  <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
              @endforelse
          </select>
      </form>
      </div>
      <div class="card-body">
        <div id="revenueByCategoryChart" style="height: 305px;"></div>

        <!-- Hidden JSON data -->
        <span id="revenue-category-data" style="display:none;">
          {{ json_encode($revenueByCategory ?? [
            'Cottages' => $cottageData,
            'Rooms' => $roomData
          ]) }}
        </span>
      </div>
    </div>
  </div>
  <div class="row gy-3">
    <div class="col-xl-4 col-md-12">
        <div class="card h-100 position-relative">
            <!-- Badge -->
            <span class="badge bg-success text-dark position-absolute top-0 start-0 m-2">Most Booked</span>
            <div class="card-body p-2">
                <img class="d-block w-100" src="{{ $RoomBest->path }}" alt="Room Best" style="height: 300px; object-fit: cover;">
            </div>
            <div class="card-body">
              <h6>{{ $RoomBest->name}}</h3>
              <span>Total Sales of ₱{{ number_format($RoomBest->total, 2)}}</span>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-12">
        <div class="card h-100 position-relative">
            <!-- Badge -->
            <span class="badge bg-success text-dark position-absolute top-0 start-0 m-2">Most Booked</span>
            <div class="card-body p-2">
                <img class="d-block w-100" src="{{ $CottageBest->path }}" alt="Room Best" style="height: 300px; object-fit: cover;">
            </div>
             <div class="card-body">
              <h6>{{ $CottageBest->name}}</h3>
              <span>Total Sales of ₱{{ number_format($CottageBest->total, 2)}}</span>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-12">
        <div class="card h-100 position-relative">
            <!-- Badge -->
            <span class="badge bg-success text-dark position-absolute top-0 start-0 m-2">Best Seller</span>
            <div class="card-body p-2">
                <img class="d-block w-100" src="{{ $foodBest->path }}" alt="Room Best" style="height: 300px; object-fit: cover;">
            </div>
             <div class="card-body">
              <h6>{{ $foodBest->name}}</h3>
              <span>Total Sales of ₱{{ number_format($foodBest->total, 2)}}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-12">
      <div class="card">
        <div class="table-responsive text-nowrap overflow-auto" style="max-height: 500px;">
            <table class="table table-hover">
              <thead class="position-sticky top-0 bg-body">
                <tr>
                  <th>Name</th>
                  <th>Book Count</th>
                  <th>Sales</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                @foreach($CountOfBookings as $item)
                <tr>
                  <td>{{ $item->name }}</td>
                  <td>{{ $item->total_bookings }}</td>
                  <td>₱{{ number_format($item->total_sales, 2) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-12">
      <div class="card">
        <div class="table-responsive text-nowrap overflow-auto" style="max-height: 500px;">
            <table class="table table-hover">
              <thead class="position-sticky top-0 bg-body">
                <tr>
                  <th>Name</th>
                  <th>Order Count</th>
                  <th>Sales</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                @foreach($CountOfFoodBookings as $item)
                <tr>
                  <td>{{ $item->name }}</td>
                  <td>{{ $item->total_orders }}</td>
                  <td>₱{{ number_format($item->total_sales, 2) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
    </div>
</div>
 


<!-- Pass the amounts data as a hidden JSON element -->
<span id="amounts-data" style="display:none;">{{ json_encode($amountReservation->pluck('amount')) }}</span>

@endsection