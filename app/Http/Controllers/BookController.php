<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookController extends Controller
{
    public function presign(Request $request): JsonResponse
    {
        $request->validate([
            'filename'  => 'required|string|max:255',
            'mime_type' => 'required|in:application/pdf',
            'size'      => 'required|integer|min:1|max:314572800',
        ]);

        try {
            $endpoint = config('filesystems.disks.r2.endpoint');
            $key      = config('filesystems.disks.r2.key');
            $secret   = config('filesystems.disks.r2.secret');
            $bucket   = config('filesystems.disks.r2.bucket');

            if (! $endpoint || ! $key || ! $secret || ! $bucket) {
                \Log::error('[presign] Missing R2 config', compact('endpoint', 'bucket') + ['key_set' => (bool)$key, 'secret_set' => (bool)$secret]);
                return response()->json(['message' => 'R2 not configured on server.'], 500);
            }

            $r2Key = 'books/'.auth()->id().'/'.uniqid('', true).'.pdf';

            $s3 = new S3Client([
                'version'                 => 'latest',
                'region'                  => 'auto',
                'endpoint'                => $endpoint,
                'credentials'             => ['key' => $key, 'secret' => $secret],
                'use_path_style_endpoint' => true,
            ]);

            $cmd       = $s3->getCommand('PutObject', [
                'Bucket'      => $bucket,
                'Key'         => $r2Key,
                'ContentType' => 'application/pdf',
            ]);
            $presigned = $s3->createPresignedRequest($cmd, '+15 minutes');

            return response()->json([
                'url' => (string) $presigned->getUri(),
                'key' => $r2Key,
            ]);

        } catch (\Throwable $e) {
            \Log::error('[presign] failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Presign error: '.$e->getMessage()], 500);
        }
    }

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
