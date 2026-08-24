<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class VisualSearchController extends Controller
{
    private const MAX_RESULTS = 8;

    private const AI_BATCH_SIZE = 12;

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        try {
            $products = Product::active()
                ->with(['category', 'images'])
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'detected_colors' => [],
                    'total_matches' => 0,
                    'products' => [],
                ]);
            }

            $apiKey = (string) config('services.openai.api_key', '');

            if ($apiKey !== '') {
                $aiResult = $this->matchCatalogWithVision(
                    $request->file('image')->getRealPath(),
                    $products,
                    $apiKey
                );

                if ($aiResult !== null) {
                    if (($aiResult['is_clothing'] ?? true) === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No dress or outfit was detected. Please upload a clear clothing photo.',
                        ], 422);
                    }

                    return response()->json($this->formatAiResult($aiResult, $products));
                }
            }

            // The browser compares the uploaded photo with these actual product
            // images. This works even when GD/Imagick or an AI key is unavailable.
            return response()->json([
                'success' => true,
                'matching_mode' => 'browser_visual',
                'client_visual_verification' => true,
                'match_threshold' => 56,
                'detected_colors' => [],
                'total_matches' => 0,
                'products' => $products
                    ->filter(fn (Product $product) => $product->images->isNotEmpty())
                    ->take(60)
                    ->map(fn (Product $product) => $this->formatProduct($product))
                    ->values(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Visual Search Error', [
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to analyze this image. Please try another clear outfit photo.',
            ], 500);
        }
    }

    /**
     * Compare the uploaded outfit against the catalog's actual images. Products
     * are sent in small batches so the prompt remains reliable as the catalog grows.
     */
    protected function matchCatalogWithVision(string $queryPath, Collection $products, string $apiKey): ?array
    {
        $queryDataUrl = $this->fileToDataUrl($queryPath);

        if ($queryDataUrl === null) {
            return null;
        }

        $allMatches = [];
        $detectedColors = [];
        $detectedPattern = null;
        $clothingWasDetected = false;
        $receivedValidResult = false;

        foreach ($products->chunk(self::AI_BATCH_SIZE) as $batch) {
            $content = [
                [
                    'type' => 'text',
                    'text' => implode("\n", [
                        'You are the strict visual-search engine for a fashion shop.',
                        'The first image is the customer query. Remaining images are labeled catalog products.',
                        'A match MUST be the same garment class (for example top vs top, saree vs saree, gown vs gown).',
                        'Ignore the person, face, skin, pose, body shape, accessories, room and background. Judge only the clothing.',
                        'Compare garment type, silhouette/cut, dominant and secondary colors, pattern/print, neckline, sleeves and visible material.',
                        'A different color is allowed when the garment design, print or embroidery layout, pattern, cut and silhouette strongly match.',
                        'Do not reject an otherwise strong same-design product only because its color is different.',
                        'Reject products that merely share a generic word or a background color. Do not return weak matches.',
                        'Use scores 90-99 only for the same/near-identical item, 80-89 for a strong visual match, 72-79 for a credible similar item.',
                        'Omit every product below 72. It is correct to return an empty matches array.',
                        'Return only JSON: {"is_clothing":boolean,"detected_colors":["color"],"detected_pattern":"solid/floral/striped/checked/embroidered/printed/other","matches":[{"product_id":integer,"score":integer}]}.',
                    ]),
                ],
                [
                    'type' => 'text',
                    'text' => 'CUSTOMER_QUERY_IMAGE',
                ],
                [
                    'type' => 'image_url',
                    'image_url' => ['url' => $queryDataUrl, 'detail' => 'high'],
                ],
            ];

            $includedIds = [];

            foreach ($batch as $product) {
                $imageUrl = $this->productImageForVision($product);

                if ($imageUrl === null) {
                    continue;
                }

                $includedIds[] = (int) $product->id;
                $content[] = [
                    'type' => 'text',
                    'text' => sprintf(
                        'CATALOG_PRODUCT_ID: %d | NAME: %s | CATEGORY: %s',
                        $product->id,
                        $this->cleanPromptText($product->name),
                        $this->cleanPromptText($product->category?->name ?? 'Fashion')
                    ),
                ];
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $imageUrl, 'detail' => 'high'],
                ];
            }

            if ($includedIds === []) {
                continue;
            }

            $payload = [
                'model' => (string) config('services.openai.vision_model', 'gpt-4o-mini'),
                'messages' => [[
                    'role' => 'user',
                    'content' => $content,
                ]],
                'max_tokens' => 500,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
            ];

            $result = $this->sendVisionRequest($payload, $apiKey);

            if ($result === null) {
                continue;
            }

            $receivedValidResult = true;
            $batchMatches = is_array($result['matches'] ?? null) ? $result['matches'] : [];
            $batchColors = is_array($result['detected_colors'] ?? null) ? $result['detected_colors'] : [];
            if ($detectedPattern === null && is_string($result['detected_pattern'] ?? null)) {
                $detectedPattern = trim($result['detected_pattern']);
            }
            $clothingWasDetected = $clothingWasDetected
                || ($result['is_clothing'] ?? false) === true
                || $batchMatches !== [];
            $detectedColors = array_merge($detectedColors, $batchColors);

            foreach ($batchMatches as $match) {
                if (! is_array($match)) {
                    continue;
                }

                $productId = filter_var($match['product_id'] ?? null, FILTER_VALIDATE_INT);
                $score = filter_var($match['score'] ?? null, FILTER_VALIDATE_INT);

                if ($productId === false || $score === false || ! in_array($productId, $includedIds, true)) {
                    continue;
                }

                if ($score < 72 || $score > 99) {
                    continue;
                }

                $allMatches[$productId] = max($allMatches[$productId] ?? 0, $score);
            }
        }

        if (! $receivedValidResult) {
            return null;
        }

        arsort($allMatches);

        return [
            'is_clothing' => $clothingWasDetected,
            'detected_colors' => array_values(array_unique(array_filter(
                array_map(fn ($color) => trim((string) $color), $detectedColors)
            ))),
            'detected_pattern' => $detectedPattern,
            'matches' => collect($allMatches)
                ->take(self::MAX_RESULTS)
                ->map(fn (int $score, int $id) => ['product_id' => $id, 'score' => $score])
                ->values()
                ->all(),
        ];
    }

    protected function sendVisionRequest(array $payload, string $apiKey): ?array
    {
        $curl = curl_init('https://api.openai.com/v1/chat/completions');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (! is_string($response) || $status < 200 || $status >= 300) {
            Log::warning('Visual search AI request failed', [
                'status' => $status,
                'curl_error' => $curlError,
            ]);

            return null;
        }

        $responseData = json_decode($response, true);
        $json = $responseData['choices'][0]['message']['content'] ?? null;

        if (! is_string($json)) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function formatAiResult(array $result, Collection $products): array
    {
        $productsById = $products->keyBy('id');
        $matches = collect($result['matches'] ?? [])
            ->map(function (array $match) use ($productsById) {
                $product = $productsById->get((int) ($match['product_id'] ?? 0));

                if (! $product) {
                    return null;
                }

                return array_merge($this->formatProduct($product), [
                    'match_score' => (int) $match['score'],
                ]);
            })
            ->filter()
            ->values()
            ->all();

        return [
            'success' => true,
            'matching_mode' => 'ai_catalog_visual',
            'detected_colors' => array_slice($result['detected_colors'] ?? [], 0, 4),
            'detected_pattern' => $result['detected_pattern'] ?? null,
            'total_matches' => count($matches),
            'products' => $matches,
        ];
    }

    protected function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => number_format((float) $product->price, 2),
            'final_price' => number_format((float) $product->final_price, 2),
            'has_discount' => (float) $product->final_price < (float) $product->price,
            'image' => $product->primary_image_url,
            'category_name' => $product->category?->name ?? 'Fashion',
            'url' => route('product.detail', $product->slug),
        ];
    }

    protected function productImageForVision(Product $product): ?string
    {
        $imagePath = $product->images->first()?->image_path;

        if (! is_string($imagePath) || $imagePath === '') {
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        $cleanPath = ltrim(str_replace('storage/', '', str_replace('\\', '/', $imagePath)), '/');
        $localPath = storage_path('app/public/'.$cleanPath);

        if (! is_file($localPath)) {
            $localPath = public_path($cleanPath);
        }

        return $this->fileToDataUrl($localPath);
    }

    protected function fileToDataUrl(string $path): ?string
    {
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    protected function cleanPromptText(?string $value): string
    {
        return mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? ''), 0, 120);
    }
}
