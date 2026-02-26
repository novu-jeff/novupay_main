<?php

require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

// Get user input
$url = $_POST['url'] ?? null;

// Validate URL
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>QR Code Generator</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; }
            form { max-width: 500px; margin: auto; }
            input, button {
                width: 100%;
                padding: 10px;
                margin-top: 10px;
            }
            button { background: #0070f3; color: #fff; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h2>Generate QR Code</h2>
        <form method="POST">
            <label>Enter URL</label>
            <input
                type="url"
                name="url"
                placeholder="https://example.com"
                required
            >
            <button type="submit">Generate QR</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Generate QR
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($url)
    ->size(300)
    ->margin(10)
    ->build();

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="zabbby_cutie.png"');
header('Content-Length: ' . strlen($result->getString()));

echo $result->getString();
exit;
