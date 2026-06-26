<?php

namespace App\Livewire\Books;

use App\Livewire\FullPageComponent;
use App\Models\Book;
use App\Models\Sector;
use App\Services\PdfCompressor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends FullPageComponent
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public $file       = null;
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

    public function saveBook(): void
    {
        $this->validate([
            'file'        => 'required|file|mimes:pdf|max:307200',
            'coverFile'   => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'title'       => 'required|string|max:255',
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sectorId'    => 'nullable|exists:sectors,id',
            'coverColor'  => 'required|string|max:7',
        ]);

        try {
            $mimeType  = $this->file->getMimeType();
            $localPath = $this->file->getRealPath();

            $compressedPath = PdfCompressor::compress($localPath);
            $uploadPath     = $compressedPath ?? $localPath;
            $fileSize       = filesize($uploadPath);

            $r2Key  = 'books/'.auth()->id().'/'.uniqid('', true).'.pdf';
            $stream = fopen($uploadPath, 'rb');
            Storage::disk('r2')->put($r2Key, $stream, 'private');
            fclose($stream);

            if ($compressedPath) {
                @unlink($compressedPath);
            }

            $book = Book::create([
                'user_id'     => auth()->id(),
                'sector_id'   => $this->sectorId ?: null,
                'title'       => $this->title,
                'author'      => $this->author ?: null,
                'description' => $this->description ?: null,
                'r2_key'      => $r2Key,
                'mime_type'   => $mimeType,
                'file_size'   => $fileSize,
                'cover_color' => $this->coverColor,
            ]);

            if ($this->coverFile) {
                $ext       = $this->coverFile->extension();
                $coverKey  = 'covers/'.auth()->id().'/'.uniqid('', true).'.'.$ext;
                $coverMime = $this->coverFile->getMimeType();
                $cs = fopen($this->coverFile->getRealPath(), 'rb');
                Storage::disk('r2')->put($coverKey, $cs, 'private');
                fclose($cs);
                $book->update(['cover_image' => $coverKey, 'cover_mime' => $coverMime]);
            }

            $this->reset(['file', 'coverFile', 'title', 'author', 'description', 'sectorId', 'showForm']);
            $this->coverColor = '#e85d26';
            $this->resetPage();

            $msg = $compressedPath
                ? 'Book uploaded and compressed (saved '.round((filesize($localPath) - $fileSize) / 1024 / 1024, 1).' MB).'
                : 'Book uploaded successfully.';
            session()->flash('success', $msg);

        } catch (\Exception $e) {
            $this->addError('file', 'Upload error: '.$e->getMessage());
            \Log::error('R2 book upload failed', ['error' => $e->getMessage(), 'user' => auth()->id()]);
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
