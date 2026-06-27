<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    public function presign(Request $request): JsonResponse
    {
        $request->validate([
            'filename'  => 'required|string|max:255',
            'mime_type' => 'required|in:application/pdf,image/jpeg,image/png,image/webp',
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

            $mimeType = $request->input('mime_type');
            $ext      = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'pdf',
            };
            $folder   = str_starts_with($mimeType, 'image/') ? 'covers' : 'books';
            $r2Key    = $folder.'/'.auth()->id().'/'.uniqid('', true).'.'.$ext;

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
                'ContentType' => $mimeType,
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

    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required|file|mimes:jpeg,png,webp|max:5120',
        ]);

        try {
            $file = $request->file('cover');
            $ext  = match($file->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $key  = 'covers/'.auth()->id().'/'.uniqid('', true).'.'.$ext;

            Storage::disk('r2')->put($key, $file->get(), [
                'ContentType' => $file->getMimeType(),
            ]);

            return response()->json(['key' => $key, 'mime' => $file->getMimeType()]);

        } catch (\Throwable $e) {
            \Log::error('[uploadCover] failed: '.$e->getMessage());
            return response()->json(['message' => 'Cover upload failed: '.$e->getMessage()], 500);
        }
    }

    public function cover(Book $book): StreamedResponse
    {
        abort_unless((int) $book->user_id === (int) auth()->id(), 403);
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

    public function serve(Book $book): \Illuminate\Http\RedirectResponse
    {
        abort_unless((int) $book->user_id === (int) auth()->id(), 403);

        $s3 = new S3Client([
            'version'                 => 'latest',
            'region'                  => 'auto',
            'endpoint'                => config('filesystems.disks.r2.endpoint'),
            'credentials'             => [
                'key'    => config('filesystems.disks.r2.key'),
                'secret' => config('filesystems.disks.r2.secret'),
            ],
            'use_path_style_endpoint' => true,
        ]);

        $cmd = $s3->getCommand('GetObject', [
            'Bucket'                     => config('filesystems.disks.r2.bucket'),
            'Key'                        => $book->r2_key,
            'ResponseContentType'        => 'application/pdf',
            'ResponseContentDisposition' => 'inline',
        ]);

        $presigned = $s3->createPresignedRequest($cmd, '+60 minutes');

        return redirect((string) $presigned->getUri());
    }
}
