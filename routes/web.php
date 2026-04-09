<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatHistoryController;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if (! $user instanceof User) {
        Auth::logout();

        return redirect()->route('login');
    }

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('chat.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatHistoryController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatHistoryController::class, 'askBot'])->name('chat.send');
    Route::get('/chat/sessions', [ChatHistoryController::class, 'sessions'])->name('chat.sessions');
    Route::get('/chat/sessions/{sessionId}/messages', [ChatHistoryController::class, 'sessionMessages'])->name('chat.session.messages');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/folders', [AdminController::class, 'storeFolder'])->name('folders.store');
    Route::patch('/folders/{folderKey}', [AdminController::class, 'updateFolder'])->name('folders.update');
    Route::delete('/folders/{folderKey}/files', [AdminController::class, 'deleteFolderFile'])->name('folders.files.delete');
    Route::post('/upload-document', [AdminController::class, 'uploadDocument'])->name('upload-document');
});

Route::middleware(['auth', 'admin'])->get('/setup-db', function () {
    abort_unless(app()->environment('local'), 404);

    try {
        Artisan::call('optimize:clear');
        Artisan::call('migrate', ['--force' => true]);

        return 'Thanh cong! Da don cache va tao bang DB. <br> <a href="/">Bam vao day de ve trang chu dang nhap</a>';
    } catch (\Exception $e) {
        return 'Co loi xay ra: ' . $e->getMessage();
    }
})->name('setup-db');

require __DIR__ . '/auth.php';
