<?php
/**
 * PCIP SMS Integration Examples
 * 
 * Common use cases for sending SMS from PCIP
 */

require_once 'BulkSmsHelper.php';

// ============================================
// Example 1: Send Welcome SMS After Registration
// ============================================
function sendWelcomeSms($userName, $userPhone)
{
    $sms = new BulkSmsHelper();
    
    $message = "Welcome to PCIP, $userName! Your account has been created successfully. For support, contact us at support@pcip.com";
    
    $result = $sms->sendSms($userPhone, $message);
    
    if ($result['success']) {
        // Log to your database
        error_log("Welcome SMS sent to $userPhone - Message ID: " . $result['message_id']);
        return true;
    } else {
        // Log error
        error_log("Failed to send welcome SMS to $userPhone: " . $result['error']);
        return false;
    }
}

// Usage:
// sendWelcomeSms('John Doe', '0728883160');


// ============================================
// Example 2: Send Payment Confirmation
// ============================================
function sendPaymentConfirmation($phone, $amount, $reference, $date)
{
    $sms = new BulkSmsHelper();
    
    $message = "Payment confirmed! Amount: KSH $amount, Ref: $reference, Date: $date. Thank you for your payment.";
    
    return $sms->sendSms($phone, $message);
}

// Usage:
// sendPaymentConfirmation('0728883160', '5000', 'PAY-12345', date('Y-m-d'));


// ============================================
// Example 3: Send OTP for Verification
// ============================================
function sendOtpCode($phone)
{
    $sms = new BulkSmsHelper();
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));
    
    // Store in session (or database)
    $_SESSION['pcip_otp'] = $otp;
    $_SESSION['pcip_otp_phone'] = $phone;
    $_SESSION['pcip_otp_expiry'] = time() + 300; // 5 minutes
    
    $message = "Your PCIP verification code is: $otp. Valid for 5 minutes. Do not share with anyone.";
    
    $result = $sms->sendSms($phone, $message);
    
    return [
        'sent' => $result['success'],
        'otp' => $otp,  // Don't return this in production!
        'message_id' => $result['message_id'] ?? null
    ];
}

// Verify OTP
function verifyOtp($phone, $enteredOtp)
{
    if (!isset($_SESSION['pcip_otp'])) {
        return ['valid' => false, 'error' => 'No OTP found'];
    }
    
    if (time() > $_SESSION['pcip_otp_expiry']) {
        return ['valid' => false, 'error' => 'OTP expired'];
    }
    
    if ($_SESSION['pcip_otp_phone'] !== $phone) {
        return ['valid' => false, 'error' => 'Phone mismatch'];
    }
    
    if ($_SESSION['pcip_otp'] !== $enteredOtp) {
        return ['valid' => false, 'error' => 'Invalid OTP'];
    }
    
    // Clear OTP after successful verification
    unset($_SESSION['pcip_otp']);
    unset($_SESSION['pcip_otp_phone']);
    unset($_SESSION['pcip_otp_expiry']);
    
    return ['valid' => true];
}

// Usage:
// $otpResult = sendOtpCode('0728883160');
// if ($otpResult['sent']) { /* Show OTP input form */ }
// $verify = verifyOtp('0728883160', $_POST['otp']);


// ============================================
// Example 4: Send Appointment Reminder
// ============================================
function sendAppointmentReminder($phone, $patientName, $appointmentDate, $appointmentTime)
{
    $sms = new BulkSmsHelper();
    
    $message = "Reminder: $patientName, you have an appointment on $appointmentDate at $appointmentTime. PCIP";
    
    return $sms->sendSms($phone, $message);
}

// Usage:
// sendAppointmentReminder('0728883160', 'John Doe', '2025-10-20', '10:00 AM');


// ============================================
// Example 5: Send Bulk Notifications
// ============================================
function sendBulkNotification($recipients, $message)
{
    $sms = new BulkSmsHelper();
    
    // Check balance first
    $balance = $sms->checkBalance();
    $estimatedCost = count($recipients) * 0.75; // Assuming KSH 0.75 per SMS
    
    if ($balance && $balance['balance'] < $estimatedCost) {
        return [
            'success' => false,
            'error' => 'Insufficient balance. Required: ' . $estimatedCost . ', Available: ' . $balance['balance']
        ];
    }
    
    $results = $sms->sendBulkSms($recipients, $message);
    
    $successCount = count(array_filter($results, function($r) { return $r['success']; }));
    $failCount = count($recipients) - $successCount;
    
    return [
        'success' => true,
        'total' => count($recipients),
        'sent' => $successCount,
        'failed' => $failCount,
        'results' => $results
    ];
}

// Usage:
// $recipients = ['0728883160', '0712345678', '0723456789'];
// $result = sendBulkNotification($recipients, 'Important announcement from PCIP...');


// ============================================
// Example 6: Send Password Reset Link
// ============================================
function sendPasswordResetSms($phone, $resetToken)
{
    $sms = new BulkSmsHelper();
    
    $resetLink = "https://pcip.com/reset?token=$resetToken";
    
    $message = "Reset your PCIP password: $resetLink. Link expires in 1 hour.";
    
    return $sms->sendSms($phone, $message);
}

// Usage:
// $token = bin2hex(random_bytes(16));
// sendPasswordResetSms('0728883160', $token);


// ============================================
// Example 7: Send Account Status Update
// ============================================
function sendAccountStatusUpdate($phone, $userName, $status, $reason = '')
{
    $sms = new BulkSmsHelper();
    
    $statusMessages = [
        'approved' => "Congratulations $userName! Your PCIP account has been approved. You can now access all features.",
        'suspended' => "Your PCIP account has been suspended. Reason: $reason. Contact support for assistance.",
        'activated' => "Your PCIP account is now active. Welcome aboard, $userName!",
    ];
    
    $message = $statusMessages[$status] ?? "Your PCIP account status has been updated to: $status";
    
    return $sms->sendSms($phone, $message);
}

// Usage:
// sendAccountStatusUpdate('0728883160', 'John Doe', 'approved');


// ============================================
// Example 8: Send Low Balance Alert (Admin)
// ============================================
function checkAndAlertLowBalance($adminPhone, $threshold = 50)
{
    $sms = new BulkSmsHelper();
    
    $balance = $sms->checkBalance();
    
    if ($balance && $balance['balance'] < $threshold) {
        $message = "ALERT: PCIP SMS balance is low! Current balance: KSH " . $balance['balance'] . ". Please top up.";
        
        return $sms->sendSms($adminPhone, $message);
    }
    
    return ['success' => false, 'error' => 'Balance is sufficient'];
}

// Usage (run as a cron job):
// checkAndAlertLowBalance('0728883160', 50);


// ============================================
// Example 9: Check Balance Before Sending
// ============================================
function sendSmsWithBalanceCheck($phone, $message, $adminPhone)
{
    $sms = new BulkSmsHelper();
    
    // Check balance
    $balance = $sms->checkBalance();
    
    if (!$balance || $balance['balance'] < 1) {
        // Alert admin
        $sms->sendSms($adminPhone, "URGENT: Cannot send SMS. PCIP account balance is KSH 0!");
        
        return [
            'success' => false,
            'error' => 'Insufficient balance'
        ];
    }
    
    // Send SMS
    return $sms->sendSms($phone, $message);
}

// Usage:
// sendSmsWithBalanceCheck('0728883160', 'Your message', '0712345678');


