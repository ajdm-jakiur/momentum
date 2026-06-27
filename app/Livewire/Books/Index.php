<?php

namespace App\Livewire\Books;

use App\Livewire\FullPageComponent;
use App\Models\Book;
use App\Models\Sector;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Index extends FullPageComponent
{
    use WithPagination;

    public bool $showForm = false;

    public string $title       = '';
    public string $author      = '';
    public string $description = '';
    public string $sectorId    = '';
    public string $coverColor  = '#e85d26';

    #[Computed]
    public function sectors()
    {
        return Sector::orderBy('sort_order')->get();
    }

    // Called by JS after both PDF and cover are uploaded directly to R2
    public function saveBook(string $r2Key, int $fileSize, string $coverKey = '', string $coverMime = ''): void
    {
        if (! str_starts_with($r2Key, 'books/'.auth()->id().'/')) {
            $this->addError('title', 'Invalid upload key.');
            return;
        }

        $this->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sectorId'    => 'nullable|exists:sectors,id',
            'coverColor'  => 'required|string|max:7',
        ]);

        try {
            $data = [
                'user_id'     => auth()->id(),
                'sector_id'   => $this->sectorId ?: null,
                'title'       => $this->title,
                'author'      => $this->author ?: null,
                'description' => $this->description ?: null,
                'r2_key'      => $r2Key,
                'mime_type'   => 'application/pdf',
                'file_size'   => $fileSize,
                'cover_color' => $this->coverColor,
            ];

            if ($coverKey && str_starts_with($coverKey, 'covers/'.auth()->id().'/')) {
                $data['cover_image'] = $coverKey;
                $data['cover_mime']  = $coverMime ?: 'image/jpeg';
            }

            Book::create($data);

            $this->reset(['title', 'author', 'description', 'sectorId', 'showForm']);
            $this->coverColor = '#e85d26';
            $this->resetPage();

            session()->flash('success', 'Book uploaded successfully.');

        } catch (\Exception $e) {
            $this->addError('title', 'Save error: '.$e->getMessage());
            \Log::error('[saveBook] failed', ['error' => $e->getMessage(), 'user' => auth()->id()]);
        }
    }

    public function delete(int $id): void
    {
        $book = Book::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        Storage::disk('r2')->delete($book->r2_key);
        if ($book->cover_image) {
            Storage::disk('r2')->delete($book->cover_image);
        }
        $book->delete();
    }

    public function render()
    {
        $books = Book::where('user_id', auth()->id())
            ->with(['sector', 'readingPosition'])
            ->latest()
            ->paginate(12);

        return view('livewire.books.index', compact('books'));
    }
}
