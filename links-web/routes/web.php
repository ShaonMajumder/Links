<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\PeopleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::prefix('links')->name('links.')->group(function(){
    Route::get('', [LinkController::class, 'listIndex'])->name('index');
    Route::get('create', [PeopleController::class, 'create'])->name('create');
    Route::post('store', [LinkController::class, 'store'])->name('store');
    Route::get('show/random', [LinkController::class, 'randomPage']);
    Route::post('pick/random', [LinkController::class, 'randomChoose'])->name('show-random');
    // Route::get('update/{link}', [LinkController::class, 'edit'])->name('edit');
    Route::get('tags/count-total', [LinkController::class, 'tagCount']); // make it efficient 

    // checked till now

    Route::post('bulk-input', [LinkController::class, 'bulkInput']);
    Route::post('check-unique', [LinkController::class, 'checkUniqueLink']);
    
    Route::get('listtags', [LinkController::class, 'listTags']);
    

    

    Route::prefix('tags')->name('tags.')->group(function(){
        Route::get('/', [LinkController::class, 'tagsIndex']);    
        Route::get('/{tag}', [LinkController::class, 'tagEditPage']);
        Route::post('/{tag}/update', [LinkController::class, 'tagUpdate']);
        Route::post('/{tag}/select-all-parent-tags', [LinkController::class, 'selectAllParents']);
    });
    

});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::prefix('users')->group(function(){
    Route::get('{id}', [LinkController::class, 'showUser']);
});