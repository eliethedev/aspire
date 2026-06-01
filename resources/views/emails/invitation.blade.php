<x-mail::message>
# Welcome to ASPIRE

Hello {{ $invitation->user->name }},

You have been invited to join **ASPIRE**, the Department of Education's school supervision platform.

## Invitation Details

- **Your Role:** {{ ucfirst($invitation->role) }}
- **School:** {{ $invitation->school?->name ?? 'Not assigned' }}
- **Invited by:** {{ $invitation->invitedBy->name }}
- **Expires:** {{ $expiresAt }}

## Get Started

To activate your account, please click the button below to set your password:

<x-mail::button :url="$setPasswordUrl">
Set Your Password
</x-mail::button>

## Important Information

- This invitation link will expire in 7 days
- If you don't set your password before then, you'll need to request a new invitation
- The link can only be used once

If you did not expect this invitation, you can safely ignore this email.

---

Best regards,

The ASPIRE Team

<x-mail::subcopy>
If you're having trouble clicking the button, copy and paste the URL below into your web browser:
{{ $setPasswordUrl }}
</x-mail::subcopy>
</x-mail::message>
