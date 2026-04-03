<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatHistoryController;
use Illuminate\Support\Facades\Artisan;
Route::get('/', function () {
    return redirect('/chat');
});
Route::post('/chat/send', [ChatHistoryController::class, 'askBot'])->name('chat.send');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatHistoryController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatHistoryController::class, 'askBot'])->name('chat.send');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/setup-db', function() {
    try {
        // Xóa cache cũ
        Artisan::call('optimize:clear');
        
        // Chạy lệnh tạo bảng trong database
        Artisan::call('migrate', ['--force' => true]);
        
        return 'Thành công! Đã dọn cache và tạo bảng DB. <br> <a href="/">Bấm vào đây để về trang chủ đăng nhập</a>';
    } catch (\Exception $e) {
        return 'Có lỗi xảy ra: ' . $e->getMessage();
    }
});
require __DIR__.'/auth.php';
