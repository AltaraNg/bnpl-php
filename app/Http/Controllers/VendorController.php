<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{

    /**
     * @throws ValidationException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $this->validate($request, [
            'password' => ['required', 'string', 'min:4'],
            'confirm_password' => ['same:password'],
            'otp' => ['required', 'string', 'min:6'],
            'username' => ['required', 'string'],
        ]);
        $username = $request->username;
        $user = User::query()->where('api_token', $request->input('otp'))->where(function (Builder $query) use ($username) {
            $query->where('phone_number', $username)->orWhere('email', $username);
        })->first();
        if ($user == null) {
            return $this->respondError('Invalid otp supplied');
        }
        $user->password = Hash::make($request->input('password'));
        $user->api_token = null;
        $user->update();
        $token = $user->createToken('vendor-api-token')->plainTextToken;
        return $this->respondSuccess(['user' => $user, 'token' => $token], 'Password changed successfully');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'username' => ['required', 'string'],
            'password' => ['required', 'string']
        ]);
        $user = User::query()->where('email', $request->username)->orWhere('phone_number', $request->username)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->respondUnAuthorized('The provided credentials are incorrect');
        }
        if (!$user->portal_access) {
            return $this->respondUnAuthorized('Your account has been deactivated, contact admin');
        }
        $token = $user->createToken('vendor-api-token')->plainTextToken;
        return $this->respondSuccess(['user' => $user, 'token' => $token], 'Login successfully');
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return $this->respondSuccess([], 'Logged out successfully');
    }
}
