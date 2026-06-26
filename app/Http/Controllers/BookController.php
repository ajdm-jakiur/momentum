<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookController extends Controller
{
    public function cover(Book $book): StreamedResponse
    {
        abort_unless($book->user_id === auth()->id(), 403);
        abort_unless($book->cover_image, 404);

        return response()->stream(function () use ($book) {
            while (ob_get_level()) ob_end_clean();
            $stream = Storage::disk('r2')->readStream($book->cover_image);
            while (!feof($stream)) {
                echo fread($stream, 256 * 1024);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type'  => $book->cover_mime ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function serve(Book $book): StreamedResponse
    {
        abort_unless($book->user_id === auth()->id(), 403);

        return response()->stream(function () use ($book) {
            while (ob_get_level()) ob_end_clean();
            set_time_limit(0);

            $stream = Storage::disk('r2')->readStream($book->r2_key);
            while (!feof($stream)) {
                echo fread($stream, 512 * 1024); // 512 KB chunks
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type'        => $book->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($book->r2_key).'"',
            'Cache-Control'       => 'private, max-age=3600',
            'Accept-Ranges'       => 'none',
        ]);
    }
}
