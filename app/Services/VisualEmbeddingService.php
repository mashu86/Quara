<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImageEmbedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VisualEmbeddingService
{
    private const SAMPLE_SIZE = 48;

    /**
     * Store or update vector embedding for a single product image.
     */
    public function storeOrUpdateEmbedding(ProductImage $productImage): ?ProductImageEmbedding
    {
        $localPath = $this->resolveLocalPath($productImage->image_path);

        if ($localPath === null || ! is_file($localPath) || ! is_readable($localPath)) {
            return null;
        }

        $checksum = md5_file($localPath);

        if ($checksum === false) {
            return null;
        }

        $existing = ProductImageEmbedding::where('product_image_id', $productImage->id)->first();

        if ($existing && $existing->checksum === $checksum && ! empty($existing->embedding)) {
            return $existing;
        }

        $vector = $this->extractVectorFromImageFile($localPath);

        if ($vector === null) {
            return null;
        }

        return ProductImageEmbedding::updateOrCreate(
            ['product_image_id' => $productImage->id],
            [
                'product_id' => $productImage->product_id,
                'embedding' => $vector,
                'checksum' => $checksum,
            ]
        );
    }

    /**
     * Generate missing vector embeddings for all active products in catalog.
     */
    public function generateEmbeddingsForCatalog(): array
    {
        $stats = ['processed' => 0, 'skipped' => 0, 'failed' => 0];

        $images = ProductImage::whereHas('product', function ($query) {
            $query->active();
        })->get();

        foreach ($images as $image) {
            $result = $this->storeOrUpdateEmbedding($image);
            if ($result) {
                $stats['processed']++;
            } else {
                $stats['failed']++;
            }
        }

        return $stats;
    }

    /**
     * Search products matching an uploaded query image using vector similarity.
     */
    public function searchSimilarProducts(string $queryImagePath, int $limit = 8, float $minThreshold = 52.0): array
    {
        $queryVector = $this->extractVectorFromImageFile($queryImagePath);

        if ($queryVector === null) {
            return [];
        }

        // Retrieve active products with their image embeddings
        $products = Product::active()
            ->with(['category', 'images.embedding'])
            ->get();

        $scoredProducts = [];

        foreach ($products as $product) {
            $maxScore = 0.0;

            foreach ($product->images as $image) {
                $embeddingModel = $image->embedding;

                // Auto-generate on demand if missing
                if (! $embeddingModel || empty($embeddingModel->embedding)) {
                    $embeddingModel = $this->storeOrUpdateEmbedding($image);
                }

                if (! $embeddingModel || empty($embeddingModel->embedding)) {
                    continue;
                }

                $catalogVector = $embeddingModel->embedding;
                $sim = $this->cosineSimilarity($queryVector, $catalogVector);

                if ($sim > $maxScore) {
                    $maxScore = $sim;
                }
            }

            // Calibrate raw similarity cosine dot product into percentage score (0-100)
            // Cosine dot product typically ranges 0.35 - 0.95 for clothing images
            if ($maxScore > 0.40) {
                $percentScore = $this->calibrateScore($maxScore);

                if ($percentScore >= $minThreshold) {
                    $scoredProducts[] = [
                        'product' => $product,
                        'score' => (int) round($percentScore),
                        'raw_sim' => $maxScore,
                    ];
                }
            }
        }

        // Sort descending by match score
        usort($scoredProducts, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scoredProducts, 0, $limit);
    }

    /**
     * Extract 216-dimensional normalized visual feature vector from an image file using GD.
     */
    public function extractVectorFromImageFile(string $filePath): ?array
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($filePath));

        if (! $image) {
            return null;
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth <= 0 || $origHeight <= 0) {
            imagedestroy($image);

            return null;
        }

        // Canvas 48x48
        $size = self::SAMPLE_SIZE;
        $resized = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $size, $size, $origWidth, $origHeight);
        imagedestroy($image);

        // Feature extractors
        $histogram = array_fill(0, 108, 0.0);
        $spatial = [];
        for ($i = 0; $i < 16; $i++) {
            $spatial[$i] = ['r' => 0, 'g' => 0, 'b' => 0, 'count' => 0];
        }
        $grayscale = array_fill(0, $size * $size, 0.0);
        $garmentMask = array_fill(0, $size * $size, 0);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $idx = ($y * $size) + $x;
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $gray = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                $grayscale[$idx] = $gray;

                // Ignore plain white/light background padding
                $isNearWhite = ($r > 242 && $g > 242 && $b > 242);
                $isGarmentZone = ($x >= 4 && $x <= 43 && $y >= 4 && $y <= 43);

                if ($isGarmentZone && ! $isNearWhite) {
                    $garmentMask[$idx] = 1;
                    [$h, $s, $v] = $this->rgbToHsv($r, $g, $b);

                    $hBin = min(11, (int) floor($h * 12));
                    $sBin = min(2, (int) floor($s * 3));
                    $vBin = min(2, (int) floor($v * 3));

                    $bin = ($hBin * 9) + ($sBin * 3) + $vBin;
                    $histogram[$bin] += 1.0;

                    $block = ((int) floor($y / 12) * 4) + (int) floor($x / 12);
                    $spatial[$block]['r'] += $r;
                    $spatial[$block]['g'] += $g;
                    $spatial[$block]['b'] += $b;
                    $spatial[$block]['count'] += 1;
                }
            }
        }

        imagedestroy($resized);

        // Normalize color histogram
        $histTotal = max(1.0, array_sum($histogram));
        foreach ($histogram as $k => $val) {
            $histogram[$k] = $val / $histTotal;
        }

        // Spatial color features (48 dims)
        $spatialVector = [];
        foreach ($spatial as $b) {
            $c = max(1, $b['count']);
            $spatialVector[] = ($b['r'] / $c) / 255.0;
            $spatialVector[] = ($b['g'] / $c) / 255.0;
            $spatialVector[] = ($b['b'] / $c) / 255.0;
        }

        // Edge & Texture features (Sobel 32 dims + LBP 16 dims = 48 dims)
        $edgeVector = array_fill(0, 16, 0.0);
        $textureVector = array_fill(0, 16, 0.0);
        $orientationVector = array_fill(0, 16, 0.0);

        for ($y = 1; $y < $size - 1; $y++) {
            for ($x = 1; $x < $size - 1; $x++) {
                $idx = ($y * $size) + $x;

                if (! $garmentMask[$idx]) {
                    continue;
                }

                $gx = $grayscale[$idx + 1] - $grayscale[$idx - 1];
                $gy = $grayscale[$idx + $size] - $grayscale[$idx - $size];
                $mag = min(255.0, sqrt(($gx * $gx) + ($gy * $gy)));

                $block = ((int) floor($y / 12) * 4) + (int) floor($x / 12);
                $edgeVector[$block] += $mag;

                if ($mag > 15.0) {
                    $angle = atan2($gy, $gx);
                    if ($angle < 0) {
                        $angle += M_PI;
                    }
                    $angBin = min(15, (int) floor(($angle / M_PI) * 16));
                    $orientationVector[$angBin] += 1.0;
                }

                // Simple LBP
                $code = 0;
                if ($grayscale[$idx - $size - 1] >= $gray) {
                    $code |= 1;
                }
                if ($grayscale[$idx - $size] >= $gray) {
                    $code |= 2;
                }
                if ($grayscale[$idx - $size + 1] >= $gray) {
                    $code |= 4;
                }
                if ($grayscale[$idx + 1] >= $gray) {
                    $code |= 8;
                }
                if ($grayscale[$idx + $size + 1] >= $gray) {
                    $code |= 16;
                }
                if ($grayscale[$idx + $size] >= $gray) {
                    $code |= 32;
                }
                if ($grayscale[$idx + $size - 1] >= $gray) {
                    $code |= 64;
                }
                if ($grayscale[$idx - 1] >= $gray) {
                    $code |= 128;
                }
                $patternBin = min(15, (int) floor($code / 16));
                $textureVector[$patternBin] += 1.0;
            }
        }

        $edgeMax = max(1.0, max($edgeVector));
        foreach ($edgeVector as $k => $v) {
            $edgeVector[$k] = $v / $edgeMax;
        }

        $orientSum = max(1.0, array_sum($orientationVector));
        foreach ($orientationVector as $k => $v) {
            $orientationVector[$k] = $v / $orientSum;
        }

        $textSum = max(1.0, array_sum($textureVector));
        foreach ($textureVector as $k => $v) {
            $textureVector[$k] = $v / $textSum;
        }

        // Combine into 216-dim vector
        $vector = array_merge(
            $histogram,           // 108 dims
            $spatialVector,       // 48 dims
            $edgeVector,          // 16 dims
            $orientationVector,   // 16 dims
            $textureVector        // 16 dims
        );

        // Normalize vector length (Unit Vector for Fast Cosine Dot Product)
        $norm = 0.0;
        foreach ($vector as $v) {
            $norm += $v * $v;
        }
        $norm = sqrt($norm);

        if ($norm > 0) {
            foreach ($vector as $k => $v) {
                $vector[$k] = round($v / $norm, 6);
            }
        }

        return $vector;
    }

    /**
     * Compute Cosine Similarity (Dot Product of unit vectors) between two vectors.
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = min(count($vecA), count($vecB));

        if ($count === 0) {
            return 0.0;
        }

        $dot = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $dot += $vecA[$i] * $vecB[$i];
        }

        return max(0.0, min(1.0, $dot));
    }

    /**
     * Calibrate raw Cosine similarity (0.45 - 0.98) into realistic display percentage (55% - 98%).
     */
    private function calibrateScore(float $rawSimilarity): float
    {
        if ($rawSimilarity >= 0.95) {
            return 95.0 + (($rawSimilarity - 0.95) / 0.05) * 4.0; // 95% - 99%
        }
        if ($rawSimilarity >= 0.82) {
            return 85.0 + (($rawSimilarity - 0.82) / 0.13) * 9.0; // 85% - 94%
        }
        if ($rawSimilarity >= 0.65) {
            return 72.0 + (($rawSimilarity - 0.65) / 0.17) * 12.0; // 72% - 84%
        }
        if ($rawSimilarity >= 0.50) {
            return 60.0 + (($rawSimilarity - 0.50) / 0.15) * 11.0; // 60% - 71%
        }

        return 50.0 + (($rawSimilarity - 0.40) / 0.10) * 9.0; // 50% - 59%
    }

    private function rgbToHsv(int $r, int $g, int $b): array
    {
        $rf = $r / 255.0;
        $gf = $g / 255.0;
        $bf = $b / 255.0;

        $max = max($rf, $gf, $bf);
        $min = min($rf, $gf, $bf);
        $delta = $max - $min;

        $h = 0.0;
        if ($delta > 0) {
            if ($max === $rf) {
                $h = fmod(($gf - $bf) / $delta, 6.0);
            } elseif ($max === $gf) {
                $h = (($bf - $rf) / $delta) + 2.0;
            } else {
                $h = (($rf - $gf) / $delta) + 4.0;
            }
            $h /= 6.0;
            if ($h < 0) {
                $h += 1.0;
            }
        }

        $s = ($max > 0) ? ($delta / $max) : 0.0;
        $v = $max;

        return [$h, $s, $v];
    }

    private function resolveLocalPath(?string $imagePath): ?string
    {
        if (! is_string($imagePath) || $imagePath === '') {
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return null;
        }

        $cleanPath = ltrim(str_replace('storage/', '', str_replace('\\', '/', $imagePath)), '/');
        $storagePath = storage_path('app/public/'.$cleanPath);

        if (is_file($storagePath)) {
            return $storagePath;
        }

        $publicPath = public_path($cleanPath);

        if (is_file($publicPath)) {
            return $publicPath;
        }

        return null;
    }
}
