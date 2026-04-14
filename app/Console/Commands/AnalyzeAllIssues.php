<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeIssueSentiment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeAllIssues extends Command
{
    protected $signature = 'analyze:all-issues';

    protected $description = 'Dispatch AI analysis jobs for all issues (upgrades to full multi-dimension analysis)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $issues = DB::table('issues')
            ->where('is_spam', false)
            ->where('is_anonymous', false)
            ->select('id', 'tenant_id', 'description', 'title', 'issue_category_id')
            ->get();

        foreach ($issues as $issue) {
            dispatch(new AnalyzeIssueSentiment(
                $issue->id,
                $issue->tenant_id,
                $issue->description ?? '',
                $issue->title ?? '',
                $issue->issue_category_id,
            ));
        }

        $this->info("Dispatched AI analysis jobs for {$issues->count()} issues (excluding spam and anonymous).");
    }
}
