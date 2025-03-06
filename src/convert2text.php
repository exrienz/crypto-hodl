<?php
// Get the value from the GET request
$input = $_GET['hodl_id'] ?? '';
$full_domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

// Sanitize the input to remove unwanted characters
$sanitizedInput = preg_replace('/[^a-zA-Z0-9]/', '', $input);

// Execute the Python script with the sanitized input
$command = 'python3 ../script/tp2txt.py ' . escapeshellarg($sanitizedInput) . ' ' . escapeshellarg($full_domain);
$output = shell_exec($command);

// Check if output is null and handle it
if ($output === null) {
    echo "No output returned from the script.";
} else {
    echo nl2br(htmlspecialchars($output));
}
?>