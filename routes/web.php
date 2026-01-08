<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\food\FoodController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\home\BookingController;
use App\Http\Controllers\home\FeatureController;
use App\Http\Controllers\home\PricingController;
use App\Http\Controllers\promos\PromosController;
use App\Http\Controllers\pages\MiscTooManyRequest;
use App\Http\Controllers\payment\PaymentController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\home\LandingPageController;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\feedback\FeedbackController;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\facilities\FacilitiesController;
use App\Http\Controllers\reservation\ReservationController;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\report\ReportControllers;
use App\Http\Controllers\evaluation\EvaluationControllers;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\email\EmailsController;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/booking', [BookingController::class, 'index'])->name('booking');
Route::get('/features', [FeatureController::class, 'index'])->name('features');
Route::get('/foods-menu', [FoodController::class, 'display'])->name('foods');

Route::get('/booking/pdf/{id}', [BookingController::class, 'displayPDF'])->name('pdfView')->middleware(['auth']);

Route::get('/reservations-list', [ReservationController::class, 'userReservationDisplay'])->name('user-reservation-list')->middleware('auth');
Route::post('/reservations-list/rating', [ReservationController::class, 'rating'])->name('reservation-rating')->middleware('auth');
Route::post('/reservations-list/cancel', [ReservationController::class, 'cancel'])->name('reservation-cancel')->middleware('auth');
Route::post('/reservations-list/update', [ReservationController::class, 'updateReservation'])->name('reservation-update')->middleware('auth');
Route::post('/reservations-list/paid', [PaymentController::class, 'fullpaid'])->name('reservation-paid')->middleware('auth');
Route::get('/reservations-list/success', [PaymentController::class, 'fullyPaidSuccess'])->name('reservation-paid.success')->middleware('auth');

Route::get('/booking/generate-pdf', [PaymentController::class, 'displayPDF'])->name('generatePDF')->middleware(['auth']);

Route::post('/paymongo/checkout', [PaymentController::class, 'checkout'])->name('paymongo.checkout')->middleware(['throttle:web']);
Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success')->middleware(['throttle:web']);

Route::get('/booking/foods', [BookingController::class, 'viewFoods'])->name('booking-foods')->middleware(['throttle:web']);
Route::get('/booking/{id}', [BookingController::class, 'viewDetails'])->name('booking-details')->middleware(['throttle:web']);


Route::middleware(['guest', 'throttle:web'])->group(function () {
  Route::get('/register', [RegisterBasic::class, 'index'])->name('auth-register')->middleware(['throttle:web']);
  Route::post('/register/add', [RegisterBasic::class, 'store'])->name('auth-register-add');

  Route::get('/login', [LoginBasic::class, 'index'])->name('login');
  Route::post('/login/process', [LoginBasic::class, 'login'])->name('login-process')->middleware(['throttle:login']);

  Route::get('/register', [RegisterBasic::class, 'index'])->name('auth-register');
  Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password');


  Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
  Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'role:Admin,Employee', 'throttle:web'])->group(function () {

  Route::get('/report', [ReportControllers::class, 'index'])->name('reports-page');
  Route::get('/report/generate', [ReportControllers::class, 'generate'])->name('reports-page-generate');
  
  Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard-analytics');

  Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback-list');

  Route::post('/user/add', [UserController::class, 'store'])->name('user-add');
  Route::post('/user/update', [UserController::class, 'update'])->name('user-update');
  Route::post('/user/delete', [UserController::class, 'delete'])->name('user-delete');

  Route::get('/facilities', [FacilitiesController::class, 'index'])->name('product-facilities');
  Route::post('/facilities/add', [FacilitiesController::class, 'store'])->name('product-facilities-add');
  Route::post('/facilities/delete', [FacilitiesController::class, 'delete'])->name('product-facilities-delete');
  Route::post('/facilities/update', [FacilitiesController::class, 'update'])->name('product-facilities-update');

  Route::post('/facilities/success', [PaymentController::class, 'paymentSuccessAdmin'])->name('admin-payment.success');
  
  Route::get('/facilities/details/foods', [FacilitiesController::class, 'viewFoods'])->name('product-facilities-booking-foods');
  Route::get('/facilities/details/{id}', [FacilitiesController::class, 'viewDetail'])->name('product-facilities-booking');

  Route::get('/promos', [PromosController::class, 'index'])->name('promos-facilities');
  Route::post('/promos/add', [PromosController::class, 'store'])->name('promos-facilities-add');
  Route::post('/promos/delete', [PromosController::class, 'delete'])->name('promos-facilities-delete');
  Route::post('/promos/update', [PromosController::class, 'update'])->name('promos-facilities-update');

  Route::get('/foods', [FoodController::class, 'index'])->name('food-menu');
  Route::post('/foods/add', [FoodController::class, 'store'])->name('food-menu-add');
  Route::post('/foods/delete', [FoodController::class, 'delete'])->name('food-menu-delete');
  Route::post('/foods/update', [FoodController::class, 'update'])->name('food-menu-update');

  Route::get('/reservations', [ReservationController::class, 'index'])->name('reservation-list');
  Route::post('/reservations/done', [ReservationController::class, 'done'])->name('reservation-done');

  Route::post('/reservations/extend', [ReservationController::class, 'ExtendTime'])->name('reservation-extend');
  Route::get('/reservations/add_food/{id}', [ReservationController::class, 'AddFood'])->name('reservation-add-food');
  Route::post('/reservations/add_food/process', [ReservationController::class, 'AddFoodProcess'])->name('reservation-add-food-process');

  Route::post('/reservations/guest', [ReservationController::class, 'AddGuest'])->name('reservation-add-guest');

  Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar-list');
  Route::middleware(['role:Admin'])->group(function () {

    Route::get('/user', [UserController::class, 'index'])->name('user-accounts');
    Route::post('/user/add', [UserController::class, 'store'])->name('user-add');
    Route::post('/user/update', [UserController::class, 'update'])->name('user-update');
    Route::post('/user/delete', [UserController::class, 'delete'])->name('user-delete');
    Route::get('/user', [UserController::class, 'index'])->name('user-accounts');

    Route::get('/logs', [UserController::class, 'logs'])->name('user-logs');
    

    Route::get('/evaluation', [EvaluationControllers::class, 'display'])->name('evaluate-display');
  });

  Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
});

Route::get('/logout', [LoginBasic::class, 'logoutAccount'])->name('logout-process')->middleware(['throttle:web']);
Route::get('/evaluation/question', [EvaluationControllers::class, 'index'])->name('evaluate');
Route::post('/evaluation/question/add', [EvaluationControllers::class, 'store'])->name('evaluate-store');


Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');
Route::get('/pages/misc-too-many-request', [MiscTooManyRequest::class, 'index'])->name('pages-misc-too-many-request');

Route::post('/email/success', [EmailsController::class, 'successEmail'])->name('email.success');