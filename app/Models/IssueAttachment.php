<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class IssueAttachment extends Model
{
    protected $fillable = [
        'tenant_id',
        'issue_id',
        'issue_message_id',
        'disk',
        'path',
        'mime',
        'size',
        'meta',
    ];

    /**
     * Return a URL to access this attachment.
     *
     * - Local disk (new, private): time-limited signed route that streams the file
     *   through an authenticated controller. The signature is the access credential.
     * - Public disk (legacy rows that weren't migrated): direct public URL.
     */
    public function getStorageUrlAttribute(): string
    {
        if ($this->disk === 'local') {
            return URL::temporarySignedRoute(
                'tenant.attachment.show',
                now()->addHours(6),
                ['attachment' => $this->id],
            );
        }

        // Legacy: file still on public disk
        return tenant_asset($this->path);
    }
}
