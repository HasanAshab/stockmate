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
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

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
    #[Response(
        status: 401,
        description: 'Invalid credentials.',
        examples: [
            'message' => 'These credentials do not match our records.',
        ]
    )]
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
    public function resetPassword(ResetPasswordRequest $request, ResetUserPassword $resetUserPassword)
    {
        $resetUserPassword->execute($request->validated());

        return [
            'message' => 'Password has been reset successfully.',
        ];
    }
}
