<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueAiAnalysis extends Model
{
    use HasFactory;

    protected $table = 'issue_ai_analysis';

    protected $fillable = [
        'tenant_id',
        'issue_id',
        'analysis_type',
        'result',
        'confidence',
        'model_version',
    ];

    protected $casts = [
        'result' => 'array', // JSON to array conversion
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
