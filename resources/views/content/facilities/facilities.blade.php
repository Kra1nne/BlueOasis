@extends('layouts/contentNavbarLayout')

@section('title', 'Facilities')

@section('page-script')
@vite('resources/assets/js/facilities.js')
@endsection

@section('content')
<section class="row" id="facilitiesssection">
  <div class="col card">
    <header class="mb-3 navbar-nav-right d-flex flex-wrap align-items-center px-3 mt-4">
      <!-- Search input -->
      <div class="navbar-nav align-items-start flex-grow-1 mb-2 mb-sm-0">
        <div class="nav-item d-flex align-items-center w-100">
          <i class="ri-search-line ri-22px me-1_5"></i>
          <input type="text" id="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 ms-50 w-100" placeholder="Search..." aria-label="Search...">
        </div>
      </div>

      <!-- Buttons -->
      <div class="navbar-nav d-flex flex-row align-items-center gap-2 ms-auto">
        @if(!$isFiltering)
              <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filtering">
                  <i class="ri-filter-line ri-22px me-1"></i>Filter
              </button>
          @endif
            {{-- Dropdown appears ONLY if user applied filters --}}
          @if($isFiltering)
              <div class="dropdown">
                  <a class="btn btn-outline-primary" href="{{ route('product-facilities') }}">
                      <i class="ri-loader-line"></i> Reload
                  </a>
              </div>
          @endif
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#AddProduct">
          <span class="tf-icons ri-add-circle-line ri-16px me-1_5"></span>Add Facilities
        </button>
      </div>
    </header>


    <div class="nav-align-top mb-6">
      <ul class="nav nav-pills mb-4 nav-fill" role="tablist">
        <li class="nav-item mb-1 mb-sm-0">
          <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-home" aria-controls="navs-pills-justified-home" aria-selected="true"><i class="tf-icons ri-door-line me-1_5"></i> Rooms</button>
        </li>
        <li class="nav-item mb-1 mb-sm-0">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-profile" aria-controls="navs-pills-justified-profile" aria-selected="false"><i class="tf-icons ri-home-5-line me-1_5"></i> Cottages</button>
        </li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="navs-pills-justified-home" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-3" id="rooms">

          </div>
        </div>
        <div class="tab-pane fade" id="navs-pills-justified-profile" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-3" id="cottages">

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
{{-- add product modal --}}
<div class="modal fade" id="AddProduct" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Add Facilities</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="ProductData" enctype="multipart/form-data">
          @csrf
          <div class="row">
            <div class="col mb-3">
              <div class="input-group">
                <input type="file" name="imagesData[]" multiple accept="image/*" class="form-control" id="DataImages">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter Name">
                <label for="name">Name</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="price" name="price" class="form-control" placeholder="Enter Price">
                <label for="Price">Price</label>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="addprice_perhour" name="addprice_perhour" class="form-control" placeholder="Enter Price">
                <label for="Price">Additional Price (hour)</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="max_person" name="max_person" class="form-control" placeholder="Enter the number of pax">
                <label for="Max_Person">Number of Pax</label>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="additional_price" name="additional_price" class="form-control" placeholder="Enter addtional price">
                <label for="Additional_Price">Additional Price (person)</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="limit_add" name="limit_add" class="form-control" placeholder="Enter the number allowed for additional person">
                <label for="limit_add">Additional Person</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mb-2 mt-2">
              <div class="form-floating form-floating-outline mb-4">
                <select class="form-select" id="category" name="category" aria-label="Default select example">
                  <option value="" selected>Select Category</option>
                  <option value="room">Room</option>
                  <option value="cottage">Cottage</option>
                </select>
                <label for="exampleFormControlSelect1">Category</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col ">
              <div class="form-floating form-floating-outline mb-4">
                <textarea class="form-control h-px-100" id="amenities" placeholder="Enter amenities example ( Pool Access, Wifi)..." name="amenities"></textarea>
                <label for="exampleFormControlTextarea1">Amenities</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-floating form-floating-outline mb-2">
                <textarea class="form-control h-px-100" id="description" placeholder="Comments here..." name="description"></textarea>
                <label for="exampleFormControlTextarea1">Description</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="SaveProduct">Save</button>
      </div>
    </div>
  </div>
</div>
{{-- edit product modal--}}
<div class="modal fade" id="EditProduct" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Update Facilities</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="ProductDataUpdate" enctype="multipart/form-data">
          @csrf
          <input type="hidden" id="Edit_id" name="id" class="form-control" placeholder="Id">
          <input type="hidden" name="removed_images[]" id="removed_images" multiple accept="image/*">
          <div class="row">
            <div class="col mb-3" id="Edit_ImagePreview" style="display: flex; flex-wrap: wrap; gap: 10px;">
              <!-- Images will appear here -->
            </div>
          </div>
          <div class="row">
            <div class="col mb-3">
              <div class="input-group">
                <input type="file" name="imagesData[]" multiple accept="image/*" class="form-control" id="Edit_DataImages">
              </div>
            </div>
        </div>
          <div class="row">
            <div class="col mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="Edit_name" name="name" class="form-control" placeholder="Enter Name">
                <label for="name">Name</label>
              </div>
            </div>
          </div>
           <div class="row">
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="Edit_price" name="price" class="form-control" placeholder="Enter Price">
                <label for="Price">Price</label>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-2 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="Edit_addprice_perhour" name="addprice_perhour" class="form-control" placeholder="Enter Price">
                <label for="Price">Additional Price (hour)</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-md-6 mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="Edit_maxPerson" name="max_person" class="form-control" placeholder="Enter the number of pax">
                <label for="Max_Person">Number of Pax</label>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="number" id="Edit_additionalPrice" name="additional_price" class="form-control" placeholder="Enter addtional price">
                <label for="Additional_Price">Additional Price</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mb-3 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="Edit_limit_add" name="limit_add" class="form-control" placeholder="Enter the number allowed for additional person">
                <label for="limit_add">Additional Person</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mt-2">
              <div class="form-floating form-floating-outline mb-4">
                <select class="form-select" id="Edit_category" name="category" aria-label="Default select example">
                  <option value="" selected>Select Category</option>
                  <option value="room">Room</option>
                  <option value="cottage">Cottage</option>
                </select>
                <label for="exampleFormControlSelect1">Category</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-floating form-floating-outline mb-4">
                <textarea class="form-control h-px-100" id="Edit_amenities" placeholder="Enter amenities example ( Pool Access, Wifi)..." name="amenities"></textarea>
                <label for="exampleFormControlTextarea1">Amenities</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col ">
              <div class="form-floating form-floating-outline mb-2">
                <textarea class="form-control h-px-100" id="Edit_description" placeholder="Comments here..." name="description"></textarea>
                <label for="exampleFormControlTextarea1">Description</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="UpdateProduct">Save Changes</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="filtering" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title text-center w-100">Filter for venues</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="filteringData" action="{{ route('product-facilities')}}" method="GET">
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

<a href="#facilitiesssection"
  class="btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle d-flex align-items-center justify-content-center"
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
      'amenities' => $venue->amenities,
      'additional_price_time' => $venue->additional_price_time,
      'limit_add' => $venue->limit_add
    ];
  })->values()->toArray();
@endphp

<script>
  window.venues = @json($venues);
</script>
@if(session('show_modal'))
    <a id="pdfDownloadBtn"
       href="{{ route('generatePDF', ['booking' => session('booking_id')]) }}"
       target="_blank"
       class="btn btn-primary"
       style="display:none !important;">
       Download PDF
    </a>
    <script>
        // Show your modal here
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
@endsection
