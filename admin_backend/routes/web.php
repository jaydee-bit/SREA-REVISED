<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('admin.login.show');
});


Route::get('admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login.show');

Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login');