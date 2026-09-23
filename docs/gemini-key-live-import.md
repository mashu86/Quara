# Import Gemini API keys into the live database

Do not copy the encrypted `gemini_api_keys.api_key` values from local SQL into live. Laravel encryption uses `APP_KEY`, so a local encrypted value cannot be decrypted by a live server with a different app key.

Before deploying, add this one-time value to the **live** `.env` file. Use the actual local raw key values; do not commit this line to Git.

```dotenv
GEMINI_API_KEYS_JSON='[{"name":"Main key","api_key":"AIza...","is_active":true},{"name":"Backup key","api_key":"AIza...","is_active":false}]'
```

Then run on the live server. The data migration inserts/updates the key table automatically as part of `migrate`:

```bash
php artisan config:clear
php artisan migrate --force
php artisan config:clear
```

The migration upserts by key name, encrypts each raw API key with the live `APP_KEY`, and leaves exactly one key active. After a successful import, remove `GEMINI_API_KEYS_JSON` from the live `.env` file and run `php artisan config:clear` once more. `GeminiApiKeySeeder` remains available only if a manual re-import is needed later.
