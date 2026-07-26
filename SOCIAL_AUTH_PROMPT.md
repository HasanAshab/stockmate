# Laravel Social Login (Google + Microsoft) — Implementation Spec

## Goal

Add Google and Microsoft social login to the existing `AuthController`. An email is always required, but it does **not** have to already be verified by the provider — `email_verified_at` is set to `now()` when the provider confirms verification, or left `null` when it doesn't, and Laravel's standard `Registered` event fires for newly-created users so the normal verification-notification flow picks it up. One user may link **multiple** social providers to the same account when the email matches an existing user.

---

## 1. Packages

```bash
composer require google/apiclient
```

No package needed for Microsoft — verification is a plain authenticated call to Microsoft Graph via Laravel's `Http` facade. No Socialite needed for either provider.

---

## 2. Database

### New table: `social_accounts`

```php
Schema::create('social_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider');       // 'google' | 'microsoft'
    $table->string('provider_id');    // the provider's unique subject/user id
    $table->timestamps();

    $table->unique(['provider', 'provider_id']); // one identity -> one user, ever
    $table->unique(['user_id', 'provider']);     // a user can't link the same provider twice
});
```

### `users` table

No new columns needed for social login specifically. `password` is already nullable, since social-only users won't have one.

---

## 3. Enum

`app/Enums/SocialProvider.php`
```php
enum SocialProvider: string
{
    case Google = 'google';
    case Microsoft = 'microsoft';
}
```

---

## 4. Model

`app/Models/SocialAccount.php`
```php
class SocialAccount extends Model
{
    protected $fillable = ['provider', 'provider_id'];

    protected $casts = [
        'provider' => SocialProvider::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Add to `User.php`:
```php
public function socialAccounts(): HasMany
{
    return $this->hasMany(SocialAccount::class);
}
```
---

## 5. DTO

`app/DTOs/SocialUserData.php`
```php
readonly class SocialUserData
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $name,
        public bool $emailVerified,
    ) {}
}
```

No `avatar`. `emailVerified` reflects whatever the provider actually reports — it no longer gates the login (email is still required, but unverified is allowed through), it just decides what `email_verified_at` gets set to.

---

## 6. Contract

`app/Contracts/SocialTokenVerifier.php`
```php
interface SocialTokenVerifier
{
    public function resolve(string $token): SocialUserData;
}
```

---

## 7. Google verifier

`app/Services/Social/GoogleTokenVerifier.php`
- Constructor-inject `Google\Client` (bound in a service provider with `client_id` from `config('services.google.client_id')`).
- `resolve($idToken)`: call `$client->verifyIdToken($idToken)`. If falsy, throw `ValidationException` ("Invalid Google token.").
- Extract `email` from the payload. **If missing, throw `ValidationException`** ("Your Google account must have an email address.").
- Read `email_verified` from the payload as a bool (`$payload['email_verified'] ?? false`) and pass it straight through on the DTO — do **not** reject the login if it's false, just record it.
- Map `sub` → `id`, `name` → `name`.

---

## 8. Microsoft verifier

`app/Services/Social/MicrosoftTokenVerifier.php`
- `resolve($accessToken)`: call `Http::withToken($accessToken)->get('https://graph.microsoft.com/v1.0/me')`. On failure, throw `ValidationException` ("Invalid Microsoft token.").
- Email comes from `mail`, falling back to `userPrincipalName`. **If neither is a valid email address (`filter_var(..., FILTER_VALIDATE_EMAIL)`), throw `ValidationException`.**
- Map `id` → `id`, `displayName` → `name`.
- Set `emailVerified: true` unconditionally. Graph's `/me` endpoint (v1.0) doesn't expose an explicit verification flag: personal Microsoft accounts are verified at signup, and work/school (Azure AD) accounts are provisioned by an admin, so treating both as verified is a reasonable default. The one edge case this misses is an Azure AD guest user with an unconfirmed invite — acceptable to ignore unless you're targeting enterprise tenants specifically.

---

## 9. Manager (driver resolution)

`app/Services/Social/SocialAuthManager.php` extends `Illuminate\Support\Manager`.
- `getDefaultDriver()` throws — provider must always be explicit.
- `createGoogleDriver()` → resolve `GoogleTokenVerifier` from the container.
- `createMicrosoftDriver()` → resolve `MicrosoftTokenVerifier` from the container.

Register as a singleton in `AppServiceProvider::register()`, alongside the `Google\Client` binding (client_id from config).

---

## 10. Account linking action

`app/Actions/Auth/LoginOrRegisterSocialUser.php`
```php
use Illuminate\Auth\Events\Registered;

class LoginOrRegisterSocialUser
{
    public function execute(SocialProvider $provider, SocialUserData $data): User
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $data->id)
            ->first();

        if ($account) {
            return $account->user;
        }

        $user = User::where('email', $data->email)->first();
        $isNewUser = false;

        if (!$user) {
            $user = User::create([
                'name' => $data->name ?? 'User',
                'email' => $data->email,
                'password' => null,
                'email_verified_at' => $data->emailVerified ? now() : null,
            ]);

            $isNewUser = true;
        }

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $data->id,
        ]);

        if ($isNewUser) {
            event(new Registered($user));
        }

        return $user;
    }
}
```

Two things to note about this version:

- **`email_verified_at` now mirrors the provider's own signal**, rather than always being stamped `now()`. Google reports this accurately (`email_verified`); Microsoft is treated as always-verified per the assumption above.
- **`Registered` only fires for a brand-new user row**, not when an existing account gains a second linked provider — firing it again on every subsequent login would be wrong (it's meant to mean "this account was just created").

This action still covers multi-provider linking: a second provider login with a matching email attaches a new `social_accounts` row to the same existing user rather than creating a duplicate account.

---

## 11. Request Form

`app/Http/Requests/Auth/SocialLoginRequest.php`
- `provider`: required, `Rule::enum(SocialProvider::class)`
- `token`: required, string
- Helper: `provider(): SocialProvider` → `SocialProvider::from($this->input('provider'))`

---

## 12. Controller — add to existing `AuthController`, no new controller

Add these dependencies to `AuthController`'s existing constructor (alongside whatever it already has), and add one new public method:

```php
public function __construct(
    // ...existing dependencies...
    protected SocialAuthManager $socialAuth,
    protected LoginOrRegisterSocialUser $loginOrRegisterSocialUser,
) {}

public function socialLogin(SocialLoginRequest $request)
{
    $provider = $request->provider();
    $verifier = $this->socialAuth->driver($provider->value);

    $data = $verifier->resolve($request->string('token')->toString());
    $user = $this->loginOrRegisterSocialUser->execute($provider, $data);

    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken,
        'user' => new UserResource($user),
    ]);
}
```

---

## 13. Route

Add to the existing unauthenticated routes group in `routes/api.php`:

```php
Route::post('/auth/social', [AuthController::class, 'socialLogin'])->middleware('throttle:10,1');
```

---

## 14. Config

```php
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
],
```

No Microsoft entry needed — Graph validates the access token itself.

---

## What the frontend sends

- **Google:** an ID token from Google Identity Services / native SDK, scopes `openid email profile`.
- **Microsoft:** an **access token** from MSAL with scope `User.Read` (not the ID token) — this is what's forwarded as the Bearer token to Graph's `/me`.