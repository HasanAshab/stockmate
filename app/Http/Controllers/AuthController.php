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

class AuthController extends Controller
{
    public function __construct(
        protected SocialAuthManager $socialAuth,
        protected LoginOrRegisterSocialUser $loginOrRegisterSocialUser,
    ) {}

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

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

    public function resendVerification(ResendOtpRequest $request, SendVerificationOtp $verification)
    {
        $verification->execute($request->validated('identifier'));

        return ['message' => 'Code resent.'];
    }

    public function changePassword(ChangePasswordRequest $request, ChangeUserPassword $changeUserPassword)
    {
        $changeUserPassword->execute(
            $request->user(),
            $request->validated('password'),
        );

        return ['message' => 'Password changed.'];
    }

    public function forgotPassword(ForgotPasswordRequest $request, SendPasswordResetOtp $sendPasswordResetOtp)
    {
        $sendPasswordResetOtp->execute($request->validated('identifier'));

        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ], 202);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetUserPassword $resetUserPassword)
    {
        $resetUserPassword->execute($request->validated());

        return [
            'message' => 'Password has been reset successfully.',
        ];
    }
}
