<?php

namespace App\Http\Controllers;

use App\Actions\Auth\ChangeUserPassword;
use App\Actions\Auth\LoginOrRegisterSocialUser;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Actions\Auth\ResetUserPassword;
use App\Actions\Auth\SendPasswordResetOtp;
use App\Actions\Auth\SendVerificationOtp;
use App\Actions\Auth\VerifyAccount;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Requests\Auth\VerifyAccountRequest;
use App\Http\Resources\UserResource;
use App\Services\Social\SocialAuthManager;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * APIs for user authentication and account management
 *
 * @unauthenticated
 */
class AuthController extends Controller
{
    public function __construct(
        protected SocialAuthManager $socialAuth,
        protected LoginOrRegisterSocialUser $loginOrRegisterSocialUser,
    ) {}

    /**
     * Login
     *
     * Authenticate a user with their credentials (email/phone number and password).
     *
     * @bodyParam identifier string required The user's email or phone number. Example: user@example.com
     * @bodyParam password string required The user's password. Example: Password123!
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "phone": null,
     *     "is_active": true,
     *     "is_verified": true,
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "roles": [],
     *     "direct_permissions": [],
     *     "permissions": []
     *   },
     *   "token": "1|abc123...",
     *   "token_type": "Bearer"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $request, LoginUser $loginUser)
    {
        $user = $loginUser->execute(
            $request->validated('identifier'),
            $request->validated('password'),
        );

        $token = $user->createToken('stockmate')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Register
     *
     * Create a new user account. After registration, a verification code will be sent to the provided email or phone.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string The user's email address. Example: user@example.com
     * @bodyParam phone string The user's phone number. Example: +1234567890
     * @bodyParam password string required The user's password. Example: Password123!
     * @bodyParam password_confirmation string required Password confirmation. Example: Password123!
     *
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "phone": null,
     *     "is_active": true,
     *     "is_verified": false,
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "roles": [],
     *     "direct_permissions": [],
     *     "permissions": []
     *   },
     *   "token": "2|def456...",
     *   "token_type": "Bearer"
     * }
     */
    public function register(RegisterRequest $request, RegisterUser $registerUser)
    {
        $user = $registerUser->execute($request->validated());

        $token = $user->createToken('stockmate')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Social Login
     *
     * Authenticate or register a user using social provider tokens (Google or Microsoft).
     *
     * @bodyParam provider string required The social provider. Example: google
     * @bodyParam token string required The ID token from Google or access token from Microsoft. Example: eyJhbGciOiJSUzI1NiIs...
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "phone": null,
     *     "is_active": true,
     *     "is_verified": true,
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "roles": [],
     *     "direct_permissions": [],
     *     "permissions": []
     *   },
     *   "token": "3|ghi789...",
     *   "token_type": "Bearer"
     * }
     * @response 401 {
     *   "message": "Invalid social token"
     * }
     */
    public function socialLogin(SocialLoginRequest $request)
    {
        $provider = $request->provider();
        $verifier = $this->socialAuth->driver($provider->value);

        $data = $verifier->resolve($request->string('token')->toString());
        $user = $this->loginOrRegisterSocialUser->execute($provider, $data);

        $token = $user->createToken('stockmate')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout
     *
     * Revoke the current access token and log out the authenticated user.
     *
     * @authenticated
     *
     * @response 204 scenario="Success"
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * Verify Account
     *
     * Verify a user account using the OTP code sent to their email or phone.
     *
     * @bodyParam identifier string required The user's email or phone number. Example: user@example.com
     * @bodyParam code string required The verification code. Example: 123456
     *
     * @response 200 {
     *   "message": "Account verified successfully"
     * }
     * @response 400 {
     *   "message": "Invalid or expired verification code"
     * }
     */
    public function verify(VerifyAccountRequest $request, VerifyAccount $verifyOtp)
    {
        $verifyOtp->execute(
            $request->validated('identifier'),
            $request->validated('code'),
        );

        return [
            'message' => 'Account verified successfully',
        ];
    }

    /**
     * Resend Verification Code
     *
     * Resend the verification code to the user's email or phone.
     *
     * @bodyParam identifier string required The user's email or phone number. Example: user@example.com
     *
     * @response 200 {
     *   "message": "Code resent."
     * }
     */
    public function resendVerification(ResendOtpRequest $request, SendVerificationOtp $verification)
    {
        $verification->execute($request->validated('identifier'));

        return ['message' => 'Code resent.'];
    }

    /**
     * Change Password
     *
     * Change the password for the authenticated user.
     *
     * @authenticated
     *
     * @bodyParam password string required The new password. Example: NewPassword123!
     * @bodyParam password_confirmation string required Password confirmation. Example: NewPassword123!
     *
     * @response 200 {
     *   "message": "Password changed."
     * }
     */
    public function changePassword(ChangePasswordRequest $request, ChangeUserPassword $changeUserPassword)
    {
        $changeUserPassword->execute(
            $request->user(),
            $request->validated('password'),
        );

        return ['message' => 'Password changed.'];
    }

    /**
     * Forgot Password
     *
     * Request a password reset code to be sent to the user's email or phone.
     *
     * @bodyParam identifier string required The user's email or phone number. Example: user@example.com
     *
     * @response 202 {
     *   "message": "Password reset link sent to your email."
     * }
     */
    public function forgotPassword(ForgotPasswordRequest $request, SendPasswordResetOtp $sendPasswordResetOtp)
    {
        $sendPasswordResetOtp->execute($request->validated('identifier'));

        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ], 202);
    }

    /**
     * Reset Password
     *
     * Reset the user's password using the OTP code received.
     *
     * @bodyParam identifier string required The user's email or phone number. Example: user@example.com
     * @bodyParam code string required The password reset code. Example: 123456
     * @bodyParam password string required The new password. Example: NewPassword123!
     * @bodyParam password_confirmation string required Password confirmation. Example: NewPassword123!
     *
     * @response 200 {
     *   "message": "Password has been reset successfully."
     * }
     * @response 400 {
     *   "message": "Invalid or expired reset code"
     * }
     */
    public function resetPassword(ResetPasswordRequest $request, ResetUserPassword $resetUserPassword)
    {
        $resetUserPassword->execute($request->validated());

        return [
            'message' => 'Password has been reset successfully.',
        ];
    }
}
