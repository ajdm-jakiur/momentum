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

    Route::get('tasks', TasksIndex::class)->name('tasks.index');

    Route::get('checkins/daily', CheckinsDaily::class)->name('checkins.daily');

    Route::get('reports/weekly', ReportsWeekly::class)->name('reports.weekly');
    Route::get('reports/monthly', ReportsMonthly::class)->name('reports.monthly');

    Route::post('push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
});

require __DIR__.'/auth.php';
