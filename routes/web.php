<?php

use App\Livewire\Admin\Sectors\Create as SectorCreate;
use App\Livewire\Admin\Sectors\Edit as SectorEdit;
use App\Livewire\Admin\Sectors\Index as SectorIndex;
use App\Livewire\Admin\Sectors\Users as SectorUsers;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard\Home;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboards');
Route::redirect('/dashboard', '/dashboards')->name('dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboards', Home::class)->name('dashboards.index');
    Route::view('/profile', 'profile.show')->name('profile');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function (): void {
            Route::view('/', 'admin.index')->name('index');
            Route::get('/setores', SectorIndex::class)->name('sectors.index');
            Route::get('/setores/criar', SectorCreate::class)->name('sectors.create');
            Route::get('/setores/{sector}/editar', SectorEdit::class)->name('sectors.edit');
            Route::get('/setores/{sector}/usuarios', SectorUsers::class)->name('sectors.users');
            Route::view('/usuarios', 'admin.users')->name('users.index');
        });
});
