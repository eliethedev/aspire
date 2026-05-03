<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\PHPMailerService;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing PHPMailer Configuration...\n\n";
    
    // Test the PHPMailer service
    $mailerService = new PHPMailerService();
    
    // Test connection
    echo "Testing SMTP connection...\n";
    $connectionTest = $mailerService->testConnection();
    
    if ($connectionTest) {
        echo "✅ SMTP connection successful!\n";
    } else {
        echo "❌ SMTP connection failed. Check your credentials.\n";
    }
    
    // Test email sending (optional - uncomment to test actual email)
    /*
    echo "\nTesting email sending...\n";
    $user = new User([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);
    
    $verificationUrl = 'http://localhost/test-verification';
    $emailSent = $mailerService->sendVerificationEmail($user, $verificationUrl);
    
    if ($emailSent) {
        echo "✅ Test email sent successfully!\n";
    } else {
        echo "❌ Test email failed to send.\n";
    }
    */
    
    echo "\nConfiguration details:\n";
    echo "Host: " . config('phpmailer.host') . "\n";
    echo "Port: " . config('phpmailer.port') . "\n";
    echo "Username: " . config('phpmailer.username') . "\n";
    echo "From: " . config('phpmailer.from.address') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
