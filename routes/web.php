<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\HistoricalController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NoteProspectController;
use App\Http\Controllers\NoteClientController;
use App\Http\Controllers\BordereauHistoriqueController;
use App\Http\Controllers\BordereauInformationController;
use App\Http\Controllers\ContactRelanceController;
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
        : redirect()->route('login'); // Redirige explicitement vers la route nommée 'login'
})->name('home');


// Route Index après connexion
Route::middleware(['auth', 'verified', RestrictIP::class])->group(function () {
    Route::get('/index', function () {
        return Inertia::render('Index');
    })->name('index');
});


//Récupère les users pour le Menu de navigation
Route::get('/dashboard', [AuthenticatedSessionController::class, 'index'])->name('dashboard');



// Route de dashboard
Route::middleware(['auth', 'verified', RestrictIP::class])->group(function () {
    Route::get('/pointage', function () {
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
    Route::post('/update-day/{id}', [HistoricalController::class, 'updateDay'])->name('update-day')->middleware('role:Admin,Informatique');
    Route::post('/add-day', [HistoricalController::class, 'addDay'])->name('add-day')->middleware('role:Admin,Informatique');
    Route::put('/employees/{id}/deactivate', [EmployeController::class, 'deactivate'])->name('employees.deactivate')->middleware('role:Admin,Informatique');
    Route::put('/employees/{id}/reactivate', [EmployeController::class, 'reactivate'])->name('employees.reactivate');



    // Route pour afficher les pointages d'un employé spécifique
    Route::get('/employe/{id}/historique', [HistoricalController::class, 'show'])->name('users.pointages');


    // Gestion des appels téléphoniques pour les prospects
    Route::get('/gestion-appels-telephoniques', [ProspectController::class, 'index'])->name('managementCall');
    Route::put('/prospects/{id}', [ProspectController::class, 'update'])->name('prospects.update');
    Route::post('/prospects', [ProspectController::class, 'store']);
    Route::get('/gestion-appels-telephoniques/prospect/{prospect?}', [ProspectController::class, 'index'])->name('management-call');
    Route::post('/prospects/log-view', [ProspectController::class, 'logView'])->name('prospects.log-view');
    Route::get('/recent-views', [ProspectController::class, 'getRecentViews'])->name('recent-views');

    //Gestion des notes pour les prospects
    Route::put('/prospects/{prospect}/notes/{note}', [NoteProspectController::class, 'update'])->name('notes.update');
    Route::post('/prospects/{prospect}/notes', [NoteProspectController::class, 'store'])->name('notes.store');
    Route::get('/prospects/{id}', [ProspectController::class, 'showProspect'])->name('prospects.show');
    Route::delete('/prospects/{prospect}/notes/{note}', [NoteProspectController::class, 'destroy'])->name('notes.destroy');


    // Gestion des appels téléphoniques pour les clients
    Route::get('/gestion-appels-telephoniques/client/{id?}', [ClientController::class, 'show'])->name('management-call.client');
    Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');

    //Gestion des notes pour les clients
    Route::post('/clients/{client}/notes', [NoteClientController::class, 'store'])->name('clients.notes.store');
    Route::put('/clients/{clientId}/notes/{noteId}', [NoteClientController::class, 'updateNote']);
    Route::delete('/clients/{clientId}/notes/{noteId}', [NoteClientController::class, 'deleteNote']);


    // Routes pour les bordereaux historiques
    Route::post('prospects/{prospect}/bordereau_historique', [BordereauHistoriqueController::class, 'store']);
    Route::post('clients/{client}/bordereau_historique', [BordereauHistoriqueController::class, 'store']);
});

// Route pour la page de relance des contacts
Route::middleware(['auth', 'role:Admin,Informatique,Comptabilité', RestrictIP::class])->group(function () {
    Route::get('/demandes-de-rappel', [ContactRelanceController::class, 'index'])->name('contactRelance');
    Route::post('/update-action', [ContactRelanceController::class, 'updateAction'])->name('updateAction');
});

// Routes pour le reset du mot de passe
Route::post('/send-reset-link', [PasswordResetController::class, 'sendResetLink'])->name('password.send-link');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');

// Routes pour la gestion de profil, accessibles uniquement après authentification
Route::middleware(['auth'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Inclusion des routes d'authentification
require __DIR__ . '/auth.php';
