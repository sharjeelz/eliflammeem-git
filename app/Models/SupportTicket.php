<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    // Stored on the central DB — not tenant-isolated
    protected $connection = 'pgsql_admin';

    protected $table = 'support_tickets';

    protected $fillable = [
        'tenant_id',
        'tenant_name',
        'user_id',
        'user_name',
        'user_email',
        'subject',
        'message',
        'type',
        'priority',
        'status',
        'admin_notes',
    ];
}
