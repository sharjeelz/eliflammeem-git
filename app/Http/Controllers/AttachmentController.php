<?php

namespace App\Http\Controllers;

use App\Models\IssueAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Stream a private attachment file.
     *
     * Access is granted via a time-limited signed URL generated in
     * IssueAttachment::getStorageUrlAttribute(). The ValidateSignature
     * middleware on the route verifies the URL hasn't been tampered with
     * or expired. Tenant isolation is enforced here explicitly.
     */
    public function show(Request $request, IssueAttachment $attachment): StreamedResponse
    {
        // Tenant isolation — the signed URL is still scoped to the tenant domain,
        // but we double-check to prevent cross-tenant leakage if IDs collide.
        abort_unless($attachment->tenant_id === tenant('id'), 404);

        // Only private-disk files go through this route
        abort_if($attachment->disk === 'public', 404);

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        $filename = basename($attachment->path);

        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ];
        $safeMime = in_array($attachment->mime, $allowedMimes, true)
            ? $attachment->mime
            : 'application/octet-stream';

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $filename,
            [
                'Content-Type'           => $safeMime,
                'Content-Disposition'    => 'attachment; filename="' . addslashes($filename) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
