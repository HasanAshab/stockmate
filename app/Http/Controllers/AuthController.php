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
use App\Models\User;
use App\Services\Social\SocialAuthManager;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'APIs for user authentication and account management')]
#[Unauthenticated]
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
     */
    #[BodyParam('identifier', 'string', 'The user\'s email or phone number.', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', 'The user\'s password.', required: true, example: 'Password123!')]
    #[ResponseFromApiResource(UserResource::class, User::class, additional: [ 'token' => '2|def456...', 'token_type' => 'Bearer' ])]
    #[Response(['message' => 'Invalid credentials'], 401)]
    public function login(LoginRequest $request, LoginUser $loginUser)
    {
        $user = $loginUser->execute(
            $request->validated('identifier'),
            $request->validated('password'),
        );

        $token = $user->createToken('stockmate')->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
    }

    /**
     * Register
     *
     * Create a new user account. After registration, a verification code will be sent to the provided email or phone.
     */
    #[BodyParam('name', 'string', 'The user\'s full name.', required: true, example: 'John Doe')]
    #[BodyParam('email', 'string', 'The user\'s email address.', example: 'user@example.com', required: false)]
    #[BodyParam('phone', 'string', 'The user\'s phone number.', example: '+1234567890', required: false)]
    #[BodyParam('password', 'string', 'The user\'s password.', required: true, example: 'Password123!')]
    #[ResponseFromApiResource(UserResource::class, User::class, status: 201, additional: [ 'token' => '2|def456...', 'token_type' => 'Bearer' ])]
    public function register(RegisterRequest $request, RegisterUser $registerUser)
    {
        $user = $registerUser->execute($request->validated());

        $token = $user->createToken('stockmate')->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'token' => $token,
                'token_type' => 'Bearer',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Social Login
     *
     * Authenticate or register a user using social provider tokens (Google or Microsoft).
     */
    #[BodyParam('provider', 'string', 'The social provider.', required: true, example: 'google')]
    #[BodyParam('token', 'string', 'The ID token from Google or access token from Microsoft.', required: true, example: 'eyJhbGciOiJSUzI1NiIs...')]
    #[Response(['message' => 'Invalid social token'], 401)]
    #[ResponseFromApiResource(UserResource::class, User::class, status: 201, additional: [ 'token' => '2|def456...', 'token_type' => 'Bearer' ])]
    public function socialLogin(SocialLoginRequest $request)
    {
        $provider = $request->provider();
        $verifier = $this->socialAuth->driver($provider->value);

        $data = $verifier->resolve($request->string('token')->toString());
        $user = $this->loginOrRegisterSocialUser->execute($provider, $data);

        $token = $user->createAccessToken('stockmate')->plainTextToken;

        return (new UserResource($user))
            ->additional([
                'token' => $token,
                'token_type' => 'Bearer',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Logout
     *
     * Revoke the current access token and log out the authenticated user.
     */
    #[Authenticated]
    #[Response([], 204, 'Success')]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * Verify Account
     *
     * Verify a user account using the OTP code sent to their email or phone.
     */
    #[BodyParam('identifier', 'string', 'The user\'s email or phone number.', required: true, example: 'user@example.com')]
    #[BodyParam('code', 'string', 'The verification code.', required: true, example: '123456')]
    #[Response(['message' => 'Account verified successfully'], 200)]
    #[Response(['message' => 'Invalid or expired verification code'], 400)]
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
     */
    #[BodyParam('identifier', 'string', 'The user\'s email or phone number.', required: true, example: 'user@example.com')]
    #[Response(['message' => 'Code resent.'], 200)]
    public function resendVerification(ResendOtpRequest $request, SendVerificationOtp $verification)
    {
        $verification->execute($request->validated('identifier'));

        return ['message' => 'Code resent.'];
    }

    /**
     * Change Password
     *
     * Change the password for the authenticated user.
     */
    #[Authenticated]
    #[BodyParam('password', 'string', 'The new password.', required: true, example: 'NewPassword123!')]
    #[Response(['message' => 'Password changed.'], 200)]
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
     */
    #[BodyParam('identifier', 'string', 'The user\'s email or phone number.', required: true, example: 'user@example.com')]
    #[Response(['message' => 'Password reset link sent to your email.'], 202)]
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
     */
    #[BodyParam('identifier', 'string', 'The user\'s email or phone number.', required: true, example: 'user@example.com')]
    #[BodyParam('code', 'string', 'The password reset code.', required: true, example: '123456')]
    #[BodyParam('password', 'string', 'The new password.', required: true, example: 'NewPassword123!')]
    #[Response(['message' => 'Password has been reset successfully.'], 200)]
    #[Response(['message' => 'Invalid or expired reset code'], 400)]
    public function resetPassword(ResetPasswordRequest $request, ResetUserPassword $resetUserPassword)
    {
        $resetUserPassword->execute($request->validated());

        return [
            'message' => 'Password has been reset successfully.',
        ];
    }
}
