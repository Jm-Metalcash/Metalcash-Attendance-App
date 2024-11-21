<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\HistoricalController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\BordereauHistoriqueController;
use App\Http\Controllers\BordereauInformationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Middleware\RestrictIP;

// Route de connexion (page de login), accessible à tout le monde
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('index')
        : Inertia::render('Auth/Login');
})->name('home');


// Route Index après connexion
Route::middleware(['auth', 'verified', RestrictIP::class])->group(function () {
    Route::get('/index', function () {
        return Inertia::render('Index');
    })->name('index');
});


// Route de dashboard
Route::middleware(['auth', 'verified', RestrictIP::class])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Autres routes nécessitant la restriction d'IP après authentification
    Route::get('/historique', [HistoricalController::class, 'index'])->name('historique');

    // Envoi des données des jours dans la base de données
    Route::post('/days/store', [DayController::class, 'store'])->name('days.store');
    Route::get('/dashboard/days', [DayController::class, 'index']);
});

// Gestion des timers des entrées et sorties
Route::post('/time-entries/store', [TimeEntryController::class, 'store'])->name('time-entries.store');
Route::post('/days/update-total', [DayController::class, 'updateTotal']);



// Routes protégées par l'authentification et les rôles Admin, Informatique, ou Comptabilité
Route::middleware(['auth', 'role:Admin,Informatique,Comptabilité', RestrictIP::class])->group(function () {
    Route::get('/liste-des-employes', [EmployeController::class, 'index'])->name('employes');
    Route::post('/ajouter-un-employe', [EmployeController::class, 'store'])->name('employees.store')->middleware('role:Admin,Informatique');
    Route::get('/employes/{id}/profil', [EmployeController::class, 'edit'])->name('employees.profile');
    Route::patch('/employes/{id}/profil', [EmployeController::class, 'update'])->name('employees.update')->middleware('role:Admin,Informatique');
    Route::patch('/employes/{id}/password', [EmployeController::class, 'updatePassword'])->name('employees.password.update')->middleware('role:Admin,Informatique');
    Route::delete('/employes/{id}', [EmployeController::class, 'destroy'])->name('employees.destroy')->middleware('role:Admin,Informatique');
    Route::post('/update-day/{id}', [HistoricalController::class, 'updateDay'])->name('update-day')->middleware('role:Admin,Informatique');
    Route::post('/add-day', [HistoricalController::class, 'addDay'])->name('add-day')->middleware('role:Admin,Informatique');

    // Route pour afficher les pointages d'un employé spécifique
    Route::get('/employe/{id}/historique', [HistoricalController::class, 'show'])->name('users.pointages');

    //Management appel téléphonique et clients
    Route::get('/gestion-appels-telephoniques', [ClientController::class, 'index'])->name('managementCall');
    Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/gestion-appels-telephoniques/{user?}', [ClientController::class, 'index'])->name('management-call');
    Route::post('/clients/log-view', [ClientController::class, 'logView'])->name('clients.log-view');
    Route::get('/recent-views', [ClientController::class, 'getRecentViews'])->name('recent-views');


    //gestion des notes des clients
    Route::put('/clients/{client}/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::post('/clients/{client}/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::delete('/clients/{client}/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Routes pour les bordereaux historiques
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('clients/{client}/bordereau_historique', [BordereauHistoriqueController::class, 'store']);
});


// Routes pour le reset du mot de passe
Route::post('/send-reset-link', [PasswordResetController::class, 'sendResetLink'])->name('password.send-link');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');

// Routes pour la gestion de profil, accessibles uniquement après authentification
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Inclusion des routes d'authentification
require __DIR__ . '/auth.php';
