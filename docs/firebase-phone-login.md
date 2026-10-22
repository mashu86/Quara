# Firebase phone login

Customer phone login uses Firebase Authentication for SMS OTP and Firebase's signed ID token to prove the verified phone number to Laravel. Laravel then logs in the matching existing `users` row whose `role` is `customer`; no customer row is created. Matching is done against `users.phone`, accepting common Indian formats such as `9876543210`, `+919876543210`, and `+91 98765-43210`.

The app uses the existing Laravel `web` session guard. Firebase ID tokens are checked with `firebase/php-jwt` against Google's rotating Secure Token X.509 certificates, with project audience, issuer, time claims, phone provider, and Indian E.164 number validation. No Firebase service-account private key is needed or stored. The server must be able to reach Google's certificate endpoint over HTTPS.

## Environment

Add these values to each environment's private `.env` file. The Firebase web API key is public client configuration, but keep deployment configuration in environment variables as shown.

```dotenv
FIREBASE_PROJECT_ID=quara-wardrobe
FIREBASE_API_KEY=AIzaSyBIqUqWWpfCSyb6SuqVq1W0hY_11zDIDW9Y
FIREBASE_AUTH_DOMAIN=quara-wardrobe.firebaseapp.com
FIREBASE_STORAGE_BUCKET=quara-wardrobe.firebasestorage.app
FIREBASE_MESSAGING_SENDER_ID=988515449547
FIREBASE_APP_ID=1:988515449547:web:d44c6e35ed2bf82f79794e
FIREBASE_MEASUREMENT_ID=G-PD8RG78KWX
```

These variables are read through `config/services.php`; do not add service-account JSON, private keys, or Firebase secrets to the repository or public directory. After setting production variables, rebuild Laravel's config cache.

## Firebase console

1. In project `quara-wardrobe`, open **Authentication → Sign-in method** and enable **Phone**.
2. Under **Authentication → Settings → Authorized domains**, add `quarawardrobe.shop` (and `www.quarawardrobe.shop` if the site uses that host). The login requires HTTPS on the live site.
3. Under the SMS region policy, allow India (`IN`) and deny regions the business does not serve.
4. Link a Cloud Billing account if required by Firebase Phone Authentication for the project. Firebase may enforce SMS quotas and per-number rate limits; the UI reports those failures and the server route is throttled.
5. Under **Phone numbers for testing**, configure fictional numbers and fixed codes for development. These do not send real SMS or consume the normal SMS quota. Do not use real customer phone numbers as test numbers.

Firebase currently disallows `localhost` as a hosted domain for web phone authentication. For local browser testing, use an HTTPS tunnel on a domain added to Authorized domains together with Firebase fictional test numbers; use the live domain for production testing.

## Deploy and verify

Install PHP dependencies with `composer install --no-dev --optimize-autoloader`, deploy the application, set the environment values above, then run `php artisan config:cache`. No database migration is required. Clear/rebuild cached views if the deployment process caches Blade templates.

Run the focused automated checks with:

```sh
php artisan test --filter=FirebasePhoneLoginTest
```

The feature tests cover missing OTP/token payloads, invalid and expired Firebase ID tokens, unknown customer accounts, successful login, and session ID rotation. A real SMS sign-in still needs a Firebase test number or a phone number you control on an authorized HTTPS domain.
