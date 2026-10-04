<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HomeCarouselSlide;

$slide = HomeCarouselSlide::find(6);

file_put_contents('public/slide6_desktop.webp', $slide->image_blob);
file_put_contents('public/slide6_mobile.webp', $slide->mobile_image_blob);

$desktopSize = getimagesize('public/slide6_desktop.webp');
$mobileSize = getimagesize('public/slide6_mobile.webp');

echo "Desktop Image Dimensions: " . ($desktopSize ? "{$desktopSize[0]}x{$desktopSize[1]}" : "Failed") . PHP_EOL;
echo "Mobile Image Dimensions:  " . ($mobileSize ? "{$mobileSize[0]}x{$mobileSize[1]}" : "Failed") . PHP_EOL;
