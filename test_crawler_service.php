<?php
require 'vendor/autoload.php';

use App\Services\ShopeeCrawlerService;

$service = new ShopeeCrawlerService();
$results = $service->search('samsung', 3);

echo "Results count: " . count($results) . "\n";
if (count($results) > 0) {
    echo "First product: " . json_encode($results[0]) . "\n";
} else {
    echo "No products returned\n";
}
?>
