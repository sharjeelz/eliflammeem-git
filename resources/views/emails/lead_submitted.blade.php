<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Lead Submission</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; color: #333; }
        .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #7c3aed; padding: 30px 36px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: #e5d9ff; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px 36px; }
        .body h2 { font-size: 16px; font-weight: 700; color: #111; margin: 0 0 18px; }
        table.details { width: 100%; border-collapse: collapse; font-size: 14px; }
        table.details tr td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        table.details tr td:first-child { font-weight: 600; color: #555; white-space: nowrap; width: 38%; }
        table.details tr:last-child td { border-bottom: none; }
        .message-box { background: #f8f8fb; border-left: 4px solid #7c3aed; border-radius: 4px; padding: 14px 16px; margin-top: 24px; font-size: 14px; color: #444; line-height: 1.6; }
        .cta { margin-top: 28px; text-align: center; }
        .cta a { display: inline-block; background: #7c3aed; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 14px; }
        .footer { background: #fafafa; border-top: 1px solid #eee; padding: 20px 36px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>New Lead Submission</h1>
            <p>Someone just filled out the contact form on ElifLammeem.</p>
        </div>
        <div class="body">
            <h2>Lead Details</h2>
            <table class="details">
                <tr>
                    <td>Full Name</td>
                    <td>{{ $lead->name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><a href="mailto:{{ $lead->email }}" style="color:#7c3aed;">{{ $lead->email }}</a></td>
                </tr>
                @if($lead->phone)
                <tr>
                    <td>Phone</td>
                    <td>{{ $lead->phone }}</td>
                </tr>
                @endif
                @if($lead->school_name)
                <tr>
                    <td>School Name</td>
                    <td>{{ $lead->school_name }}</td>
                </tr>
                @endif
                @if($lead->city)
                <tr>
                    <td>City</td>
                    <td>{{ $lead->city }}</td>
                </tr>
                @endif
                @if($lead->package)
                <tr>
                    <td>Package Interest</td>
                    <td>{{ ucfirst($lead->package) }}</td>
                </tr>
                @endif
                <tr>
                    <td>IP Address</td>
                    <td>{{ $lead->ip_address ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Submitted At</td>
                    <td>{{ $lead->created_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>

            @if($lead->message)
            <div class="message-box">
                <strong style="display:block;margin-bottom:8px;color:#333;">Message:</strong>
                {{ $lead->message }}
            </div>
            @endif

            <div class="cta">
                <a href="{{ config('app.url') . '/nova/resources/leads/' . $lead->id }}">View in Nova &rarr;</a>
            </div>
        </div>
        <div class="footer">
            This is an automated notification from ElifLammeem. Do not reply to this email.
        </div>
    </div>
</body>
</html>
