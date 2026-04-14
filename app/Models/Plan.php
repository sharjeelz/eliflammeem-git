<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key', 'label', 'price_monthly', 'tagline',
        'max_branches', 'max_users', 'max_contacts', 'max_issues_per_month',
        'feat_ai_analysis', 'feat_ai_trends',
        'feat_chatbot', 'feat_chatbot_daily',
        'feat_broadcasting', 'feat_whatsapp',
        'feat_document_library', 'feat_custom_smtp',
        'feat_reports_full', 'feat_csv_export',
        'feat_csat', 'feat_two_factor', 'feat_api_access',
        'feat_api_daily_limit',
    ];

    protected $casts = [
        'price_monthly'         => 'integer',
        'max_branches'          => 'integer',
        'max_users'             => 'integer',
        'max_contacts'          => 'integer',
        'max_issues_per_month'  => 'integer',
        'feat_ai_analysis'     => 'boolean',
        'feat_ai_trends'       => 'boolean',
        'feat_chatbot'         => 'boolean',
        'feat_chatbot_daily'   => 'integer',
        'feat_broadcasting'    => 'boolean',
        'feat_whatsapp'        => 'boolean',
        'feat_document_library'=> 'boolean',
        'feat_custom_smtp'     => 'boolean',
        'feat_reports_full'    => 'boolean',
        'feat_csv_export'      => 'boolean',
        'feat_csat'            => 'boolean',
        'feat_two_factor'      => 'boolean',
        'feat_api_access'      => 'boolean',
        'feat_api_daily_limit' => 'integer',
    ];

    /**
     * Returns formatted price string: "$0", "$49", or null (meaning "contact us / custom").
     * Prices stored in cents to avoid floating-point issues.
     */
    /**
     * Returns "$0", "$49", etc., or null meaning "Contact us / custom".
     * price_monthly is stored as whole dollars (49 = $49/month).
     */
    public function formattedPrice(): ?string
    {
        if ($this->price_monthly === null) {
            return null;
        }
        return '$' . number_format((int) $this->price_monthly);
    }

    /** Fetch a plan row with a 10-minute cache — avoids per-request DB hits. */
    public static function findCached(string $key): ?static
    {
        return Cache::remember("plan:{$key}", 600, fn () => static::find($key));
    }

    /** Clear the cached plan row — called by Nova after save. */
    public static function clearCache(string $key): void
    {
        Cache::forget("plan:{$key}");
    }
}
