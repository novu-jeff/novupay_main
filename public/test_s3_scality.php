<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function ok($msg) {
    echo "<div class='ok'>✔ {$msg}</div>";
}
function fail($msg) {
    echo "<div class='fail'>✖ {$msg}</div>";
}
function section($title) {
    echo "<h2>{$title}</h2>";
}

$s3 = new S3Client([
    'version'  => 'latest',
    'region'   => 'us-east-1',
    'endpoint' => 'http://s3.asean.scality.io',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'YN1CHSX2K9WTCHHJ1LS7',
        'secret' => 'kYkD+o3B=r1bUHkwqr7Rae9Q2msTodiBc8Q/AoSK',
        'token'  => null,
    ],
    'http' => ['verify' => false],
]);

$bucket = 'novupay';
$key    = 'test/scality-test.txt';
$body   = 'NovuPay Scality S3 integration test';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Scality S3 Test</title>
<style>
body { font-family: system-ui, sans-serif; background:#f6f7f9; padding:20px; }
h1 { margin-bottom:5px; }
h2 { margin-top:30px; }
.ok { color:#0a7a0a; margin:6px 0; }
.fail { color:#b00020; margin:6px 0; }
table { border-collapse: collapse; background:#fff; margin-top:10px; }
td,th { border:1px solid #ddd; padding:8px 12px; }
th { background:#f0f0f0; text-align:left; }
pre { background:#111; color:#0f0; padding:10px; }
</style>
</head>
<body>

<h1>Scality S3 Integration Test</h1>
<p><strong>Bucket:</strong> <?= $bucket ?></p>

<?php
/* 1. LIST BUCKETS */
section('1. List Buckets');
try {
    $buckets = $s3->listBuckets();
    echo "<table><tr><th>Name</th><th>Created</th></tr>";
    foreach ($buckets['Buckets'] as $b) {
        echo "<tr><td>{$b['Name']}</td><td>{$b['CreationDate']}</td></tr>";
    }
    echo "</table>";
    ok('Credentials valid');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 2. HEAD BUCKET */
section('2. Check Bucket Access');
try {
    $s3->headBucket(['Bucket' => $bucket]);
    ok("Bucket {$bucket} is accessible");
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 3. PUT OBJECT */
section('3. Upload Test File');
try {
    $s3->putObject([
        'Bucket' => $bucket,
        'Key'    => $key,
        'Body'   => $body,
        'ContentType' => 'text/plain',
    ]);
    ok('Upload successful');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 4. LIST OBJECTS */
section('4. List Files in Bucket');
try {
    $objects = $s3->listObjectsV2(['Bucket' => $bucket]);
    if (empty($objects['Contents'])) {
        echo "<p>No files found.</p>";
    } else {
        echo "<table><tr><th>Key</th><th>Size</th><th>Last Modified</th></tr>";
        foreach ($objects['Contents'] as $o) {
            echo "<tr>
                <td>{$o['Key']}</td>
                <td>{$o['Size']} bytes</td>
                <td>{$o['LastModified']}</td>
            </tr>";
        }
        echo "</table>";
    }
    ok('Object listing works');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 5. GET OBJECT */
section('5. Download & Display File');
try {
    $obj = $s3->getObject([
        'Bucket' => $bucket,
        'Key'    => $key,
    ]);
    $content = $obj['Body']->getContents();
    echo "<pre>{$content}</pre>";
    ok('Download successful');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 6. SAVE TO DISK */
section('6. Save File to Server');
try {
    $s3->getObject([
        'Bucket' => $bucket,
        'Key'    => $key,
        'SaveAs' => '/tmp/scality-download.txt',
    ]);
    ok('Saved to /tmp/scality-download.txt');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}

/* 7. METADATA */
section('7. File Metadata');
try {
    $meta = $s3->headObject([
        'Bucket' => $bucket,
        'Key'    => $key,
    ]);
    echo "<table>";
    echo "<tr><th>Size</th><td>{$meta['ContentLength']}</td></tr>";
    echo "<tr><th>ETag</th><td>{$meta['ETag']}</td></tr>";
    echo "<tr><th>Last Modified</th><td>{$meta['LastModified']}</td></tr>";
    echo "</table>";
    ok('Metadata retrieved');
} catch (AwsException $e) {
    fail($e->getAwsErrorMessage());
}
?>

</body>
</html>
