<?php

namespace App\Http\Controllers;

use App\Actions\Auth\ChangeUserPassword;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Actions\Auth\ResendOtp;
use App\Actions\Auth\ResetUserPassword;
use App\Actions\Auth\SendPasswordResetLink;
use App\Actions\Auth\VerifyAccount;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyAccountRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginUser $loginUser)
    {
        $user = $loginUser->execute(
            $request->validated("identifier"),
            $request->validated("password"),
        );

        $token = $user->createToken('stockmate')->plainTextToken;

        return [
            'user' => $user->toResource(),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function register(RegisterRequest $request, RegisterUser $registerUser)
    {
        $user = $registerUser->execute($request->validated());

        $token = $user->createToken('stockmate')->plainTextToken;

        return response()->json([
            'user' => $user->toResource(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
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

    public function resendOtp(ResendOtpRequest $request, ResendOtp $resendOtp)
    {
        $resendOtp->execute($request->validated('identifier'));  

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

    public function forgotPassword(ForgotPasswordRequest $request, SendPasswordResetLink $sendPasswordResetLink)
    {
        $sendPasswordResetLink->execute($request->validated('email'));

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
