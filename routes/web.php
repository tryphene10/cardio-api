<?php

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


Route::get('/vendor-publish-mail', function() {
    $exitCode = Artisan::call('vendor:publish --tag=laravel-mail');
    return "Vendor published";
});

Route::get('/passport', function() {
    $exitCode = Artisan::call('passport:install');
    return "tables seeded";
});

Route::get('/seeders', function() {
    $exitCode = Artisan::call('db:seed');
    return "tables seeded";
});

Route::get('/migrate', function() {
    $exitCode = Artisan::call('migrate:fresh');
    return "migrations is done";
});

Route::get('/storage-link', function() {
    $exitCode = Artisan::call('storage:link');
    return "storage is linked";
});

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::get('/route-cache', function() {
    $exitCode =Artisan::call('route:cache');
    return "route is cleared";
});

Route::get('/config-cache', function() {
    $exitCode =Artisan::call('config:cache');
    return "Cache is cleared";
});

Route::get('/config-clear', function() {
    $exitCode =Artisan::call('config:clear');
    return "Cache is cleared";
});

//Clear View cache:
Route::get('/view-cache', function() {
    $exitCode = Artisan::call('view:cache');
    return '<h1>View cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear View cache:
Route::get('/key-generate', function() {
    $exitCode = Artisan::call('key:generate');
    return '<h1>key generated</h1>';
});




