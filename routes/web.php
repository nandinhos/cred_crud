<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

// Rota temporária para login automático do admin
Route::get('/login-admin', function () {
    $user = \App\Models\User::where('email', 'admin@admin.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        session()->regenerate();

        return redirect('/admin')->with('success', 'Login realizado com sucesso! Usuário: '.$user->email);
    }

    return redirect('/admin/login')->with('error', 'Usuário admin não encontrado!');
});

// Rota de teste para verificar permissões
Route::get('/test-permissions', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (! $user) {
        return 'Usuário não logado. <a href="/login-admin">Fazer login como admin</a>';
    }

    $canAccess = $user->canAccessPanel(app(\Filament\Panel::class));

    return [
        'user_id' => $user->id,
        'email' => $user->email,
        'roles' => $user->roles->pluck('name')->toArray(),
        'can_access_panel' => $canAccess,
        'is_authenticated' => \Illuminate\Support\Facades\Auth::check(),
    ];
});

Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rota de download de backup (apenas super_admin e admin)
    Route::get('/backup/download/{filename}', function (string $filename) {
        $user = auth()->user();

        if (! $user->hasRole('super_admin') && ! $user->hasRole('admin')) {
            abort(403, 'Acesso negado');
        }

        $service = new \App\Services\BackupService;
        $path = $service->getDownloadPath($filename);

        if (file_exists($path)) {
            return response()->download($path);
        }

        abort(404, 'Backup não encontrado');
    })->name('backup.download');
});

require __DIR__.'/auth.php';
