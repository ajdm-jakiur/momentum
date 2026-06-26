<?php

namespace App\Livewire\Books;

use App\Livewire\FullPageComponent;
use App\Models\Book;
use App\Models\Sector;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends FullPageComponent
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    // Direct R2 upload fields (r2Key set by JS after browser→R2 upload)
    public string $r2Key     = '';
    public int    $fileSize  = 0;

    // Cover still goes through Livewire (small file, under Cloudflare limit)
    public $coverFile  = null;

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

    // Called by JS after successful browser→R2 upload
    public function saveBook(string $r2Key, int $fileSize): void
    {
        \Log::info('[saveBook] called via direct R2 upload', [
            'user'      => auth()->id(),
            'r2Key'     => $r2Key,
            'fileSize'  => $fileSize,
        ]);

        $this->r2Key    = $r2Key;
        $this->fileSize = $fileSize;

        $this->validate([
            'r2Key'       => 'required|string',
            'fileSize'    => 'required|integer|min:1',
            'coverFile'   => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'title'       => 'required|string|max:255',
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sectorId'    => 'nullable|exists:sectors,id',
            'coverColor'  => 'required|string|max:7',
        ]);

        // Verify key belongs to this user (prefix check — prevents tampering)
        if (! str_starts_with($r2Key, 'books/'.auth()->id().'/')) {
            $this->addError('title', 'Invalid upload key.');
            return;
        }

        try {
            $book = Book::create([
                'user_id'     => auth()->id(),
                'sector_id'   => $this->sectorId ?: null,
                'title'       => $this->title,
                'author'      => $this->author ?: null,
                'description' => $this->description ?: null,
                'r2_key'      => $r2Key,
                'mime_type'   => 'application/pdf',
                'file_size'   => $fileSize,
                'cover_color' => $this->coverColor,
            ]);

            if ($this->coverFile) {
                $ext      = $this->coverFile->extension();
                $coverKey = 'covers/'.auth()->id().'/'.uniqid('', true).'.'.$ext;
                $cs = fopen($this->coverFile->getRealPath(), 'rb');
                Storage::disk('r2')->put($coverKey, $cs, 'private');
                fclose($cs);
                $book->update([
                    'cover_image' => $coverKey,
                    'cover_mime'  => $this->coverFile->getMimeType(),
                ]);
            }

            $this->reset(['r2Key', 'fileSize', 'coverFile', 'title', 'author', 'description', 'sectorId', 'showForm']);
            $this->coverColor = '#e85d26';
            $this->resetPage();

            session()->flash('success', 'Book uploaded successfully.');

        } catch (\Exception $e) {
            $this->addError('title', 'Save error: '.$e->getMessage());
            \Log::error('[saveBook] DB save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user'  => auth()->id(),
            ]);
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
