<?php

namespace App\Services\Firebase;

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class FirebaseIdTokenVerifier
{
    private const CERTIFICATES_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /** @return array{uid: string, phone_number: string} */
    public function verifyPhoneIdToken(string $idToken): array
    {
        $projectId = (string) config('services.firebase.project_id');
        if ($projectId === '') {
            throw new RuntimeException('Firebase project ID is not configured.');
        }

        $certificates = $this->getPublicKeys();

        try {
            $keys = array_map(fn (string $certificate) => new Key($certificate, 'RS256'), $certificates);
            $claims = (array) JWT::decode($idToken, $keys);
        } catch (Throwable $exception) {
            throw new InvalidFirebaseIdToken('Firebase ID token is invalid or expired.', previous: $exception);
        }

        $now = time();
        if (($claims['aud'] ?? null) !== $projectId
            || ($claims['iss'] ?? null) !== 'https://securetoken.google.com/'.$projectId
            || ! is_string($claims['sub'] ?? null)
            || trim($claims['sub']) === ''
            || ! is_numeric($claims['exp'] ?? null)
            || ! is_numeric($claims['iat'] ?? null)
            || (int) $claims['iat'] > $now + 60
            || ! is_numeric($claims['auth_time'] ?? null)
            || (int) $claims['auth_time'] > $now + 60) {
            throw new InvalidFirebaseIdToken('Firebase ID token claims do not match this project.');
        }

        $firebase = (array) ($claims['firebase'] ?? []);
        $phoneNumber = $claims['phone_number'] ?? null;
        if (($firebase['sign_in_provider'] ?? null) !== 'phone'
            || ! is_string($phoneNumber)
            || ! preg_match('/^\+91[6-9][0-9]{9}$/', $phoneNumber)) {
            throw new InvalidFirebaseIdToken('The token is not for a verified Indian phone sign-in.');
        }

        return ['uid' => $claims['sub'], 'phone_number' => $phoneNumber];
    }

    private function getPublicKeys(): array
    {
        $cacheKey = 'firebase.securetoken.x509.'.config('services.firebase.project_id');
        $certificates = Cache::get($cacheKey);
        if (is_array($certificates) && $certificates !== []) {
            return $certificates;
        }

        $response = Http::acceptJson()->timeout(10)->get(self::CERTIFICATES_URL);
        if (! $response->successful() || ! is_array($response->json()) || $response->json() === []) {
            throw new RuntimeException('Could not retrieve Firebase token verification certificates.');
        }

        $certificates = $response->json();
        foreach ($certificates as $certificate) {
            if (! is_string($certificate)
                || (! str_contains($certificate, 'BEGIN CERTIFICATE') && ! str_contains($certificate, 'BEGIN PUBLIC KEY'))) {
                throw new RuntimeException('Firebase returned invalid token verification certificates.');
            }
        }

        $cacheControl = (string) $response->header('Cache-Control');
        $ttl = preg_match('/max-age=(\d+)/i', $cacheControl, $matches) ? (int) $matches[1] : 3600;
        Cache::put($cacheKey, $certificates, max(60, min($ttl, 86400)));

        return $certificates;
    }
}
