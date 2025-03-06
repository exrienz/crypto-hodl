<?php
// Get the value from the GET request
$input = $_GET['hodl_id'] ?? '';

// Sanitize the input to remove unwanted characters
$sanitizedInput = preg_replace('/[^a-zA-Z0-9]/', '', $input);

// Execute the Python script with the sanitized input
$command = 'python3 ../script/tp2txt.py ' . escapeshellarg($sanitizedInput);
$output = shell_exec($command);

// Display the output
//echo $output;
echo nl2br($output);
?>
