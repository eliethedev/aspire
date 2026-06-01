# Invitation-Only Registration System

## Overview

The ASPIRE platform implements a secure, invitation-only registration system for DepEd school supervision. Only system administrators can create user accounts, and users must accept an invitation email to set their password and activate their account.

## Features

- **No Public Self-Registration**: Users cannot register themselves
- **Admin-Only Account Creation**: Only administrators can create user accounts
- **Secure Email Invitations**: Professional email with magic link for password setup
- **Token-Based Security**: Secure, single-use, time-limited invitation tokens (7 days)
- **Multi-Tenancy Support**: Each user belongs to a specific school/tenant
- **Resend Functionality**: Admins can resend invitations if needed
- **Audit Logging**: Complete audit trail for all invitation-related actions
- **Rate Limiting**: Protection against abuse on invitation endpoints

## Database Schema

### Invitations Table

```php
Schema::create('invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
    $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
    $table->string('token')->unique();
    $table->string('email');
    $table->enum('role', ['teacher', 'supervisor', 'school_head', 'admin']);
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->boolean('is_used')->default(false);
    $table->integer('resend_count')->default(0);
    $table->timestamp('last_sent_at')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
});
```

### Users Table (Additions)

```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('status', ['invited', 'active', 'suspended'])->default('active')->after('role');
    $table->timestamp('password_set_at')->nullable()->after('password');
    $table->index('status');
});
```

### Audit Logs Table

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('invitation_id')->nullable()->constrained('invitations')->onDelete('set null');
    $table->string('action');
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
});
```

## API Endpoints

### Admin Invitation Management

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| GET | `/admin/invitations` | List all invitations | auth, admin |
| GET | `/admin/invitations/create` | Show invitation creation form | auth, admin |
| POST | `/admin/invitations` | Create new invitation | auth, admin |
| GET | `/admin/invitations/{id}` | Show invitation details | auth, admin |
| POST | `/admin/invitations/{id}/resend` | Resend invitation email | auth, admin |
| POST | `/admin/invitations/{id}/cancel` | Cancel invitation | auth, admin |
| DELETE | `/admin/invitations/{id}` | Delete invitation | auth, admin |

### Public Invitation Endpoints

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| GET | `/auth/set-password/{token}` | Show set password page | throttle:5,1 |
| POST | `/auth/set-password` | Submit password | throttle:5,1 |

## User Flow

### 1. Admin Creates Invitation

1. Admin navigates to `/admin/invitations/create`
2. Fills in user details:
   - Full Name
   - Email Address
   - Role (Teacher, Supervisor, School Head, Admin)
   - School (optional)
   - Teacher-specific fields (if role is teacher)
3. System creates user with `status = 'invited'`
4. System generates secure token (64-character hex string)
5. System creates invitation record with 7-day expiry
6. System sends invitation email
7. System logs the action in audit logs

### 2. User Receives Email

Email includes:
- Personal greeting
- Role and school information
- Invitation expiry date
- Secure link to set password
- Instructions for use

### 3. User Sets Password

1. User clicks email link
2. System validates token (not expired, not used)
3. User sees set password page with account details
4. User enters and confirms password (minimum 8 characters, mixed case, number, symbol)
5. System validates password strength
6. System hashes password and saves to user record
7. System updates user status to `active`
8. System marks invitation as used
8. System logs user in automatically
9. User is redirected to dashboard

### 4. Admin Manages Invitations

Admin can:
- View all invitations with status filters
- Resend invitations (max 3 resends)
- Cancel pending invitations
- Delete invitation records
- View audit logs for each invitation

## Security Features

### Token Security

- **Length**: 64-character hex string (32 bytes of random data)
- **Uniqueness**: Database-level unique constraint
- **Expiry**: 7 days from creation
- **Single-use**: Marked as used after password set
- **Validation**: Server-side validation before use

### Password Requirements

- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character
- Password strength indicator on frontend

### Rate Limiting

- Set password endpoint: 5 attempts per minute per IP
- Resend limit: Maximum 3 resends per invitation
- IP-based tracking for abuse prevention

### Audit Logging

All invitation-related actions are logged:
- Invitation creation
- Invitation acceptance
- Invitation resend
- Invitation cancellation
- User login after invitation acceptance

## Services

### InvitationService

```php
use App\Services\InvitationService;

$service = new InvitationService();

// Create invitation
$invitation = $service->createInvitation($data, $adminUser);

// Validate token
$invitation = $service->validateToken($token);

// Accept invitation
$user = $service->acceptInvitation($token, $password);

// Resend invitation
$invitation = $service->resendInvitation($invitation);

// Cancel invitation
$service->cancelInvitation($invitation);

// Get statistics
$stats = $service->getStatistics();
```

### AuditLogService

```php
use App\Services\AuditLogService;

$service = new AuditLogService();

// Log invitation created
$service->logInvitationCreated($invitation, $adminUser);

// Log invitation accepted
$service->logInvitationAccepted($invitation, $user);

// Log invitation resent
$service->logInvitationResent($invitation, $adminUser);

// Log invitation cancelled
$service->logInvitationCancelled($invitation, $adminUser);

// Get invitation logs
$logs = $service->getInvitationLogs($invitation);
```

## Email Template

Location: `resources/views/emails/invitation.blade.php`

The email template includes:
- Professional branding
- User's name and role
- School assignment
- Invitation expiry date
- Call-to-action button
- Security notice
- Plain text fallback

## Frontend Components

### Admin Components

- `Admin/Invitations/Index.tsx` - List and manage invitations
- `Admin/Invitations/Create.tsx` - Create new invitation

### Auth Components

- `Auth/SetPassword.tsx` - Set password page for invited users
- `Auth/InvitationError.tsx` - Error page for invalid/expired invitations

## Configuration

### Environment Variables

Ensure these are set in `.env`:

```env
APP_URL=https://your-domain.com
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Middleware Registration

Register the rate limiting middleware in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'invitation.throttle' => \App\Http\Middleware\RateLimitInvitation::class,
];
```

## Testing

### Manual Testing Steps

1. **Create Invitation**
   - Login as admin
   - Navigate to `/admin/invitations/create`
   - Fill in user details
   - Submit form
   - Verify email is sent
   - Verify invitation appears in list

2. **Accept Invitation**
   - Click email link
   - Verify set password page loads
   - Enter weak password (should fail validation)
   - Enter strong password
   - Submit form
   - Verify user is logged in
   - Verify user status is 'active'
   - Verify invitation is marked as used

3. **Resend Invitation**
   - Navigate to invitations list
   - Click resend on pending invitation
   - Verify new email is sent
   - Verify resend count increments

4. **Cancel Invitation**
   - Navigate to invitations list
   - Click cancel on pending invitation
   - Verify invitation is marked as used
   - Verify associated user is deleted

5. **Error Handling**
   - Try to use expired invitation (should show error)
   - Try to use already used invitation (should show error)
   - Try invalid token (should show error)

## Troubleshooting

### Email Not Sending

- Check mail configuration in `.env`
- Verify SMTP credentials
- Check mail logs
- Ensure queue worker is running if using queue

### Token Validation Fails

- Check token hasn't expired
- Verify token hasn't been used
- Check database for token existence
- Verify token format (64 hex characters)

### Password Not Setting

- Check password validation rules
- Verify password hashing is working
- Check user status update
- Verify database transaction completed

## Migration Steps

To implement this system in an existing ASPIRE installation:

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Register middleware in `app/Http/Kernel.php`

3. Update User model fillable fields (already done in migration)

4. Clear caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

5. Test email configuration:
   ```bash
   php artisan tinker
   >>> Mail::raw('Test', fn($msg) => $msg->to('your@email.com')->subject('Test'));
   ```

## Future Enhancements

- Bulk invitation creation
- Invitation templates for different roles
- SMS-based invitations (optional)
- Invitation reminder emails before expiry
- Customizable invitation expiry periods
- Two-factor authentication for password setup
- Invitation analytics dashboard
