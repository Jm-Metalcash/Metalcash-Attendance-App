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
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Auth\NewPasswordController;


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


// Gestion des routes pour les employés - accessibles uniquement aux rôles Admin et Informatique
Route::middleware(['auth', 'role:Admin,Informatique'])->group(function () {
    Route::get('/liste-des-employes', [EmployeController::class, 'index'])->name('employes');
    Route::post('/ajouter-un-employe', [EmployeController::class, 'store'])->name('employees.store');
    Route::get('/employes/{id}/profil', [EmployeController::class, 'edit'])->name('employees.profile');
    Route::patch('/employes/{id}/profil', [EmployeController::class, 'update'])->name('employees.update');
    Route::patch('/employes/{id}/password', [EmployeController::class, 'updatePassword'])->name('employees.password.update');
    Route::delete('/employes/{id}', [EmployeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/update-day/{id}', [HistoricalController::class, 'updateDay'])->name('update-day');
    Route::post('/add-day', [HistoricalController::class, 'addDay'])->name('add-day');


    
    // Route pour afficher les pointages d'un employé spécifique, accessible uniquement pour "Admin" ou "Informatique"
    Route::get('/employe/{id}/historique', [HistoricalController::class, 'show'])->name('users.pointages');
});



//Reset du mot de passe par e-mail
Route::post('/send-reset-link', [PasswordResetController::class, 'sendResetLink'])->name('password.send-link');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
