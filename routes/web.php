<?php

use App\Http\Controllers\Admin\AdminCrudController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\AttributionAuditoireController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Decanat\DemandeAuditoireController;
use App\Http\Controllers\Decanat\ExportController;
use App\Http\Controllers\Decanat\ProgrammationDecanatController;
use App\Http\Controllers\Enseignant\EcEnseignantController;
use App\Http\Controllers\Enseignant\ProgrammationEnseignantController;
use App\Http\Controllers\Etudiant\ProgrammationEtudiantController;
use App\Http\Controllers\Etudiant\PromotionEtudiantController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->middleware('role:Administrateur')->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/attributions', [AttributionAuditoireController::class, 'index'])->name('attributions.index');
        Route::patch('/attributions/{demande}/accepter', [AttributionAuditoireController::class, 'accepter'])->name('attributions.accepter');
        Route::patch('/attributions/{demande}/refuser', [AttributionAuditoireController::class, 'refuser'])->name('attributions.refuser');
        Route::post('/attributions', [AttributionAuditoireController::class, 'store'])->name('attributions.store');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/programmations/export', [NotificationController::class, 'exportProgramations'])->name('programmations.export');
        Route::patch('/users/{user}/confirmer', [AdminCrudController::class, 'confirmer'])->name('users.confirmer');
        Route::get('/{resource}', [AdminCrudController::class, 'index'])->name('crud.index');
        Route::get('/{resource}/create', [AdminCrudController::class, 'create'])->name('crud.create');
        Route::post('/{resource}', [AdminCrudController::class, 'store'])->name('crud.store');
        Route::get('/{resource}/{id}/edit', [AdminCrudController::class, 'edit'])->name('crud.edit');
        Route::put('/{resource}/{id}', [AdminCrudController::class, 'update'])->name('crud.update');
        Route::delete('/{resource}/{id}', [AdminCrudController::class, 'destroy'])->name('crud.destroy');
    });

    Route::prefix('decanat')->name('decanat.')->middleware('role:Decanat')->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/demandes', [DemandeAuditoireController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/create', [DemandeAuditoireController::class, 'create'])->name('demandes.create');
        Route::post('/demandes', [DemandeAuditoireController::class, 'store'])->name('demandes.store');
        Route::get('/demandes/{demande}', [DemandeAuditoireController::class, 'show'])->name('demandes.show');
        Route::patch('/demandes/{demande}/status', [DemandeAuditoireController::class, 'updateStatus'])->name('demandes.status');
        Route::get('/programmations', [ProgrammationDecanatController::class, 'index'])->name('programmations.index');
        Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
        Route::get('/export/excel', [ExportController::class, 'excel'])->name('export.excel');
    });

    Route::prefix('enseignant')->name('enseignant.')->middleware('role:Enseignant')->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/ecs', [EcEnseignantController::class, 'index'])->name('ecs.index');
        Route::get('/programmations', [ProgrammationEnseignantController::class, 'index'])->name('programmations.index');
    });

    Route::prefix('etudiant')->name('etudiant.')->middleware('role:Etudiant')->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/programmations', [ProgrammationEtudiantController::class, 'index'])->name('programmations.index');
        Route::get('/promotion', [PromotionEtudiantController::class, 'show'])->name('promotions.show');
    });
});
