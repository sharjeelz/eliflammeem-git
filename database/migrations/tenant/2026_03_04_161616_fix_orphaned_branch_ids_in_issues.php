<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix issues whose branch_id points to a branch belonging to a different tenant.
 * Reset them to the first branch (by id) owned by the issue's own tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        $orphaned = DB::table('issues as i')
            ->join('branches as b', 'b.id', '=', 'i.branch_id')
            ->whereRaw('b.tenant_id != i.tenant_id')
            ->select('i.id', 'i.tenant_id')
            ->get();

        foreach ($orphaned as $issue) {
            $correctBranch = DB::table('branches')
                ->where('tenant_id', $issue->tenant_id)
                ->orderBy('id')
                ->value('id');

            if ($correctBranch) {
                DB::table('issues')
                    ->where('id', $issue->id)
                    ->update(['branch_id' => $correctBranch]);
            }
        }
    }

    public function down(): void
    {
        // Data correction — not reversible
    }
};
