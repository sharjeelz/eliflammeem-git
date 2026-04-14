<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SupportTicketReceivedMail;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class SupportTicketController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $tickets = SupportTicket::where('tenant_id', tenant('id'))
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.admin.support.index', compact('tickets'));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403, 'Only school admins can submit support tickets.');

        $data = $request->validate([
            'subject'  => ['required', 'string', 'min:5', 'max:150'],
            'type'     => ['required', 'in:bug,question,billing,feature_request,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message'  => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'tenant_id'   => tenant('id'),
            'tenant_name' => tenant('school')?->name ?? tenant('id'),
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'user_email'  => $user->email,
            'subject'     => $data['subject'],
            'message'     => $data['message'],
            'type'        => $data['type'],
            'priority'    => $data['priority'],
            'status'      => 'open',
        ]);

        $supportAddress = config('mail.support_address', config('mail.from.address'));

        Mail::to($supportAddress)->queue(new SupportTicketReceivedMail($ticket));

        return response()->json([
            'message'   => 'Your support ticket has been submitted.',
            'ticket_id' => $ticket->id,
        ]);
    }
}
