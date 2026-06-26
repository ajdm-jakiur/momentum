<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\PushController;
use App\Livewire\Books\Index as BooksIndex;
use App\Livewire\Books\Read as BooksRead;
use App\Livewire\Checkins\Daily as CheckinsDaily;
use App\Livewire\Dashboard;
use App\Livewire\ProfilePage;
use App\Livewire\Reports\Monthly as ReportsMonthly;
use App\Livewire\Reports\Weekly as ReportsWeekly;
use App\Livewire\Roadmaps\Edit as RoadmapsEdit;
use App\Livewire\Roadmaps\Import as RoadmapsImport;
use App\Livewire\Roadmaps\Show as RoadmapsShow;
use App\Livewire\Roadmaps\Trash as RoadmapsTrash;
use App\Livewire\Sectors\Index as SectorsIndex;
use App\Livewire\Tasks\Index as TasksIndex;
use Illuminate\Support\Facades\Route;

// TEMP DEBUG — remove after diagnosing upload issue
Route::get('/debug/upload-info', function () {
    abort_unless(app()->environment('local') || auth()->check(), 403);
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size'       => ini_get('post_max_size'),
        'memory_limit'        => ini_get('memory_limit'),
        'max_execution_time'  => ini_get('max_execution_time'),
        'max_input_time'      => ini_get('max_input_time'),
        'php_version'         => PHP_VERSION,
        'server_software'     => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'tmp_dir'             => sys_get_temp_dir(),
        'tmp_writable'        => is_writable(sys_get_temp_dir()),
        'storage_writable'    => is_writable(storage_path('app')),
        'livewire_tmp'        => storage_path('app/livewire-tmp'),
        'livewire_tmp_exists' => is_dir(storage_path('app/livewire-tmp')),
        'livewire_tmp_write'  => is_dir(storage_path('app/livewire-tmp')) && is_writable(storage_path('app/livewire-tmp')),
        'htaccess_active'     => ini_get('upload_max_filesize') !== '128M',
        'last_laravel_log'    => @file_get_contents(storage_path('logs/laravel.log'), false, null, -3000) ?: 'no log',
    ]);
})->name('debug.upload');

Route::redirect('/', 'login');

Route::get('profile', ProfilePage::class)
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('sectors', SectorsIndex::class)->name('sectors.index');

    Route::get('roadmaps/import', RoadmapsImport::class)->name('roadmaps.import');
    Route::get('roadmaps/trash', RoadmapsTrash::class)->name('roadmaps.trash');
    Route::get('roadmaps/{roadmap}/edit', RoadmapsEdit::class)->name('roadmaps.edit');
    Route::get('roadmaps/{roadmap}', RoadmapsShow::class)->name('roadmaps.show');

    Route::get('books', BooksIndex::class)->name('books.index');
    Route::get('books/{book}/read', BooksRead::class)->name('books.read');
    Route::get('books/{book}/serve', [BookController::class, 'serve'])->name('books.serve');
    Route::get('books/{book}/cover', [BookController::class, 'cover'])->name('books.cover');
    Route::post('books/presign', [BookController::class, 'presign'])->name('books.presign');

    Route::get('tasks', TasksIndex::class)->name('tasks.index');

    Route::get('checkins/daily', CheckinsDaily::class)->name('checkins.daily');

    Route::get('reports/weekly', ReportsWeekly::class)->name('reports.weekly');
    Route::get('reports/monthly', ReportsMonthly::class)->name('reports.monthly');

    Route::post('push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
});

require __DIR__.'/auth.php';
