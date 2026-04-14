<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Move existing public-disk attachments to the local (private) disk.
     * Files are physically relocated from storage/app/public/ to storage/app/.
     * The 'disk' column is updated to 'local' on success.
     * If a file cannot be moved it stays on 'public' so nothing breaks.
     */
    public function up(): void
    {
        $rows = DB::table('issue_attachments')
            ->where('disk', 'public')
            ->get(['id', 'path']);

        foreach ($rows as $row) {
            try {
                if (! Storage::disk('public')->exists($row->path)) {
                    continue;
                }

                $contents = Storage::disk('public')->get($row->path);

                Storage::disk('local')->put($row->path, $contents);
                Storage::disk('public')->delete($row->path);

                DB::table('issue_attachments')
                    ->where('id', $row->id)
                    ->update(['disk' => 'local']);
            } catch (\Throwable) {
                // Leave the row on 'public' if anything goes wrong — the accessor handles both
            }
        }
    }

    public function down(): void
    {
        // Reverse: move local-disk attachments back to public disk
        $rows = DB::table('issue_attachments')
            ->where('disk', 'local')
            ->get(['id', 'path']);

        foreach ($rows as $row) {
            try {
                if (! Storage::disk('local')->exists($row->path)) {
                    continue;
                }

                $contents = Storage::disk('local')->get($row->path);

                Storage::disk('public')->put($row->path, $contents);
                Storage::disk('local')->delete($row->path);

                DB::table('issue_attachments')
                    ->where('id', $row->id)
                    ->update(['disk' => 'public']);
            } catch (\Throwable) {
                //
            }
        }
    }
};
