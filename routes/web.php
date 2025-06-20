<?php

use App\Models\Booking;
use App\Models\Customer;
use NunoMaduro\Collision\Provider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\PetController;
use App\Http\Controllers\web\BlogController;
use App\Http\Controllers\web\CartController;
use App\Http\Controllers\web\ChatController;
use App\Http\Controllers\web\PostController;
use App\Http\Controllers\web\TestController;
use App\Http\Controllers\web\UserController;
use App\Http\Controllers\web\WishController;
use App\Http\Controllers\web\AdminController;
use App\Http\Controllers\web\BreedController;
use App\Http\Controllers\web\ImageController;
use App\Http\Controllers\web\LocaleController;
use App\Http\Controllers\web\OrderController;
use App\Http\Controllers\web\AnimalController;
use App\Http\Controllers\web\RecordController;
use App\Http\Controllers\web\BookingController;
use App\Http\Controllers\web\CommentController;
use App\Http\Controllers\web\PartnerController;
use App\Http\Controllers\web\PaymentController;
use App\Http\Controllers\web\ProductController;
use App\Http\Controllers\web\ServiceController;
use App\Http\Controllers\web\CustomerController;
use App\Http\Controllers\web\VeterinarianController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


//auth
Route::get('/login', [UserController::class, 'index'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');
Route::get('/register', [UserController::class, 'register'])->name('register'); 
Route::post('/register', [UserController::class, 'store'])->name('user.store'); 

//no auth routes

//users (vets and partners show , this is not not profile)
Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');

//products
Route::get('/product', [ProductController::class, 'index'])->name('product.index');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');

//animals
Route::get('/animal', [AnimalController::class, 'index'])->name('animal.index');
Route::get('/animal/{id}', [AnimalController::class, 'show'])->name('animal.show');

//Breeds
Route::get('/breed/{id}', [BreedController::class, 'show'])->name('breed.show'); 

//vets
Route::get('/vets', [VeterinarianController::class, 'index'])->name('vet.index');

//partners
Route::get('/partners', [PartnerController::class, 'index'])->name('partner.index'); 

//services
Route::get('/service', [ServiceController::class, 'index'])->name('service.index'); 
Route::get('/service/{id}', [ServiceController::class, 'show'])->name('service.show'); 

//blogs
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{id}', [BLogController::class, 'show'])->name('blog.show'); 

//posts
Route::get('/post/{id}', [PostController::class, 'show'])->name('post.show'); 
 

Route::middleware(['auth'])->group(function () {

    //users
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    Route::get('/user-edit', [UserController::class, 'edit'])->name('user.edit');
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::put('/user/update', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

    

    
    //animals
    Route::group(['can' => 'edit media'], function () {
        Route::get('/animal-create', [AnimalController::class, 'create'])->name('animal.create'); 
        Route::put('/animal/{id}', [AnimalController::class, 'update'])->name('animal.update');
        Route::get('/animal/edit/{id}', [AnimalController::class, 'edit'])->name('animal.edit');
        Route::post('/animal', [AnimalController::class, 'store'])->name('animal.store'); 
        Route::delete('/animal/{id}', [AnimalController::class, 'destroy'])->name('animal.destroy');
    });

    //breeds
    
    Route::group(['can' => 'edit media'], function () {
        Route::get('/breed/create/{animal_id}', [BreedController::class, 'create'])->name('breed.create');
        Route::get('/breed/edit/{animal_id}', [BreedController::class, 'edit'])->name('breed.edit'); 
        Route::put('/breed/{id}', [BreedController::class, 'update'])->name('breed.update');
        Route::delete('/breed/{id}', [BreedController::class, 'destroy'])->name('breed.destroy')->can('edit media');
        Route::post('/breed', [breedController::class, 'store'])->name('breed.store'); 
    });


    //pets
    Route::get('/pet', [PetController::class, 'index'])->name('pet.index'); 
    Route::get('/pet/create', [PetController::class, 'create'])->name('pet.create'); 
    Route::post('/pet/store', [PetController::class, 'store'])->name('pet.store'); 
    Route::get('/pet/edit/{id}', [PetController::class, 'edit'])->name('pet.edit'); 
    Route::put('/pet/{id}', [PetController::class, 'update'])->name('pet.update'); 
    Route::get('/pet/{id}', [PetController::class, 'show'])->name('pet.show'); 
    Route::delete('/pet/{id}', [PetController::class, 'destroy'])->name('pet.destroy'); 
    
    //bookings 
    //for starting a nooking 
    Route::get('/booking-start', [BookingController::class, 'start'])->name('booking.start'); 
    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create'); 
    Route::get('/booking/petIndex', [BookingController::class, 'PetIndex'])->name('booking.PetIndex'); 
    Route::get('/booking/serviceIndex', [BookingController::class, 'ServiceIndex'])->name('booking.ServiceIndex'); 

                //general
                Route::get('/booking', [BookingController::class, 'index'])->name('booking.index'); 
                Route::get('/booking/reschedule/{id}', [BookingController::class, 'reschedule'])->name('booking.reschedule');
    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/edit/{id}', [BookingController::class, 'edit'])->name('booking.edit');
    Route::put('/booking/{id}', [BookingController::class, 'update'])->name('booking.update');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{id}', [BookingController::class, 'show'])->name('booking.show'); 
    Route::delete('/booking/{id}', [BookingController::class, 'destroy'])->name('booking.destroy'); 
    
    //payments
    Route::get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

    //services
    Route::get('/service-create', [ServiceController::class, 'create'])->name('service.create'); 
    Route::post('/service', [ServiceController::class, 'store'])->name('service.store'); 
    Route::put('/service/{id}', [ServiceController::class, 'update'])->name('service.update'); 
    Route::delete('/service/{id}', [ServiceController::class, 'destroy'])->name('service.destroy'); 
    Route::get('/service/edit/{id}', [ServiceController::class, 'edit'])->name('service.edit'); 
    
    
    //products
    //adding to cart
    Route::post('/product/add-to-cart', [ProductController::class, 'AddToCart'])->name('product.addToCart');
    
    //general
    Route::get('/product-create', [ProductController::class, 'create'])->name('product.create');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    
    //orders
    Route::get('/order', [OrderController::class, 'index'])->name('order.index'); 
    Route::post('/order/{id}/restore', [OrderController::class, 'restore'])->name('order.restore'); 
    Route::get('/order/{id}', [OrderController::class, 'edit'])->name('order.edit'); 

    Route::delete('/order/{id}', [OrderController::class, 'destroy'])->name('order.destroy'); 

    //carts
                // carts checkout to order
    Route::get('/cart/place-order', [CartController::class, 'placeOrder'])->name('cart.placeOrder');
    
                //general
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::put('/cart/updateMany', [CartController::class, 'updateMany'])->name('cart.updateMany');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');


    //chats
                //only ai chats
    Route::get('/chat/ai/open', [ChatController::class, 'openAIChat']); 
    
                //general
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store'); //open normal chats
    Route::get('/chat/open/{id}', [ChatController::class, 'openChat'])->name('chat.open'); //open normal chats
    Route::get('/chat/{id?}', [ChatController::class, 'index'])->name('chat.index');
    Route::delete('/chat/{id}/clear', [ChatController::class, 'clearMessages'])->name('chat.clear');
    Route::post('/chat/message/send', [ChatController::class, 'sendMessage'])->name('chat.send');


    //records 
    Route::get('/record/{id}', [RecordController::class, 'index'])->name('record.index');  //this is the pet id for the records for that pet 
    Route::post('/record', [RecordController::class, 'store'])->name('record.store');
    Route::get('/record-show/{id}', [RecordController::class, 'show'])->name('record.show');
    Route::delete('/record/{id}', [RecordController::class, 'destroy'])->name('record.destroy');

    //wishes
    Route::post('/wish', [WishController::class, 'store'])->name('wish.store');
    Route::put('/wish', [WishController::class, 'update'])->name('wish.update');
    

    
    Route::get('/blog-create', [BLogController::class, 'create'])->name('blog.create'); 
    Route::get('/blog/edit/{id}', [BLogController::class, 'edit'])->name('blog.edit');  
    Route::post('/blog', [BLogController::class, 'store'])->name('blog.store');
    Route::put('/blog', [BLogController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{id}', [BLogController::class, 'destroy'])->name('blog.destroy');
    
    Route::get('/post-create/{blog_id}', [PostController::class, 'create'])->name('post.create'); 
    Route::post('/post', [PostController::class, 'store'])->name('post.store');
    Route::get('/post/edit/{id}', [PostController::class, 'edit'])->name('post.edit');  
    Route::put('/post', [PostController::class, 'update'])->name('post.update');
    Route::delete('/post/{id}', [PostController::class, 'destroy'])->name('post.destroy');
    
    Route::post('/comment', [CommentController::class, 'store'])->name('comment.store');
    Route::put('/comment', [CommentController::class, 'update'])->name('comment.update');
    Route::delete('/comment/{id}', [CommentController::class, 'destroy'])->name('comment.destroy');

    Route::get('/lang/{locale}', [LocaleController::class, 'setLocale'])->name('set.locale');



    //images
    Route::middleware('myAsset')->group(function () {
        Route::post('/addImage', [ImageController::class, 'addImage'])->name('image.add');
        Route::put('/updateImage', [ImageController::class, 'updateImage'])->name('image.update');
    }); 

    //admins
    Route::middleware('can:edit users')->group(function () {
        Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store'); 
        Route::get('/admin/create', [AdminController::class, 'create'])->name('admin.create'); 
        Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.index'); 
    });
});

