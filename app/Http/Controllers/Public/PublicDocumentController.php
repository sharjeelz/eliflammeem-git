<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicDocumentController extends Controller
{
    /**
     * Stream a publicly-downloadable document via signed URL.
     * No authentication required — the signed URL is the only gate.
     */
    public function download(Document $document)
    {
        abort_unless($document->allow_public_download, 404);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        $filename = Str::slug($document->title) . '.' . pathinfo($document->path, PATHINFO_EXTENSION);

        return Storage::disk($document->disk)->download($document->path, $filename);
    }
}
