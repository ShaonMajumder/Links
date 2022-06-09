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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::prefix('links')->group(function(){
    Route::get('list', [LinkController::class, 'listLinks'])->name('links.list');
    Route::get('new', [PeopleController::class, 'create']);
    Route::post('insert', [LinkController::class, 'insert']);
    Route::post('bulk-input', [LinkController::class, 'bulkInput']);
    Route::post('check-unique', [LinkController::class, 'checkUniqueLink']);
    Route::get('{people}/add', [PeopleController::class, 'showAddPeopleInformationForm']);
    Route::get('listtags', [LinkController::class, 'listTags']);
    Route::get('random', [LinkController::class, 'random']);
    // Route::post('addinfo', [PeopleController::class, 'addInfo']);
});