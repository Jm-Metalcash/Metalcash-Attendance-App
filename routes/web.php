<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HistoricalController;
use App\Http\Controllers\EmployeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : Inertia::render('Auth/Login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->get('/dashboard', [RoleController::class, 'index'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/historique', [HistoricalController::class, 'index'])->name('historique');
});

Route::get('/calendrier', function () {
    return Inertia::render('Calendrier');
})->middleware(['auth', 'verified'])->name('calendrier');


//Envoi les données des jours dans la bd
Route::post('/days/store', [DayController::class, 'store'])->name('days.store');

//Récupère les données des jours du user dans la db
Route::get('/dashboard/days', [DayController::class, 'index']);


Route::middleware(['auth'])->get('/dashboard', [RoleController::class, 'index'])->name('dashboard');



Route::middleware(['auth'])->get('/liste-des-employes', [EmployeController::class, 'index'])->name('employes');






Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
