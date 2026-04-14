<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Enquiry Has Been Approved</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; color: #333; }
        .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #7c3aed; padding: 30px 36px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: #e5d9ff; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px 36px; font-size: 15px; line-height: 1.7; color: #444; }
        .body p { margin: 0 0 16px; }
        .highlight-box { background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 20px 24px; margin: 24px 0; }
        .highlight-box p { margin: 0; font-size: 14px; color: #555; }
        .highlight-box strong { color: #7c3aed; }
        .footer { background: #fafafa; border-top: 1px solid #eee; padding: 20px 36px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Congratulations, {{ $lead->name }}!</h1>
            <p>Your enquiry has been reviewed and approved.</p>
        </div>
        <div class="body">
            <p>Thank you for your interest in <strong>ElifLammeem</strong>. We're delighted to let you know that your enquiry has been approved and our team is ready to move forward with setting up your school on the platform.</p>

            <p>Here's what happens next:</p>
            <ul style="margin:0 0 16px;padding-left:20px;">
                <li style="margin-bottom:8px;">Our team will be in touch shortly to walk you through the onboarding process.</li>
                <li style="margin-bottom:8px;">We will collect payment details and activate your subscription.</li>
                <li style="margin-bottom:8px;">Once activated, we will provision your school's environment and guide you through initial setup.</li>
            </ul>

            <p>If you have any questions in the meantime, please don't hesitate to reach out to us directly.</p>

            @if($contactEmail)
            <div class="highlight-box">
                <p>You can reach us at: <strong><a href="mailto:{{ $contactEmail }}" style="color:#7c3aed;text-decoration:none;">{{ $contactEmail }}</a></strong></p>
            </div>
            @endif

            <p>We look forward to working with you and {{ $lead->school_name ? $lead->school_name : 'your school' }}.</p>

            <p style="margin-top:24px;">Warm regards,<br><strong>The ElifLammeem Team</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} ElifLammeem. All rights reserved.
        </div>
    </div>
</body>
</html>
