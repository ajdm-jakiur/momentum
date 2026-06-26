<?php

namespace App\Livewire\Books;

use App\Livewire\FullPageComponent;
use App\Models\Book;
use App\Models\ReadingPosition;

class Read extends FullPageComponent
{
    public Book $book;
    public string $pdfUrl = '';
    public int $currentPage = 1;
    public int $totalPages = 0;

    public function mount(Book $book): void
    {
        $this->book = $book;

        $position = ReadingPosition::firstOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $book->id],
            ['current_page' => 1]
        );
        $this->currentPage = $position->current_page;
        $this->totalPages = $book->page_count ?? 0;

        // Serve through our own route — avoids R2 CORS entirely
        $this->pdfUrl = route('books.serve', $book);
    }

    public function savePage(int $page, int $totalPages = 0): void
    {
        if ($page < 1) $page = 1;

        ReadingPosition::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $this->book->id],
            ['current_page' => $page, 'last_read_at' => now()]
        );

        $this->currentPage = $page;

        // totalPages comes from PDF.js numPages — always authoritative
        if ($totalPages > 0 && $totalPages !== ($this->book->page_count ?? 0)) {
            $this->book->update(['page_count' => $totalPages]);
        }
    }

    public function render()
    {
        return view('livewire.books.read');
    }
}
