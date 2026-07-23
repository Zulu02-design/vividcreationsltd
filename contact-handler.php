<?php
/**
 * Contact form handler for Vivid Creations Ltd
 * Sends submissions from contact.html to the business email.
 */

// ---- Settings ----
$recipient_email = "v.creations@aol.com";
$redirect_success = "contact.html?status=success";
$redirect_error   = "contact.html?status=error";

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $redirect_error);
    exit;
}

// ---- Collect & sanitize input ----
function clean_input($value) {
    $value = trim($value ?? "");
    $value = stripslashes($value);
    // Strip anything that looks like an email header injection attempt
    $value = preg_replace("/(\r\n|\r|\n|%0a|%0d|Content-Type:|bcc:|to:|cc:)/i", "", $value);
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

$name    = clean_input($_POST["name"] ?? "");
$email   = clean_input($_POST["email"] ?? "");
$subject = clean_input($_POST["subject"] ?? "");
$message = clean_input($_POST["message"] ?? "");

// ---- Basic validation ----
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    header("Location: " . $redirect_error . "&reason=missing");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . $redirect_error . "&reason=invalid_email");
    exit;
}

// ---- Build the email ----
$email_subject = "New Website Inquiry: " . $subject;

$email_body  = "You received a new message from the Vivid Creations Ltd website contact form.\r\n\r\n";
$email_body .= "Name: "    . $name    . "\r\n";
$email_body .= "Email: "   . $email   . "\r\n";
$email_body .= "Subject: " . $subject . "\r\n\r\n";
$email_body .= "Message:\r\n" . $message . "\r\n";

// Use a safe "From" address on your own domain; set Reply-To to the sender
// so replying from your inbox goes straight back to the customer.
$from_address = "website@vividcreations.example";

$headers  = "From: Vivid Creations Website <" . $from_address . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ---- Send ----
$sent = mail($recipient_email, $email_subject, $email_body, $headers);

if ($sent) {
    header("Location: " . $redirect_success);
} else {
    header("Location: " . $redirect_error . "&reason=send_failed");
}
exit;
