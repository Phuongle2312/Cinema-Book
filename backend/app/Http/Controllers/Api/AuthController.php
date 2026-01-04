<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

/**
 * Controller: AuthController
 * Mục đích: Xử lý authentication (đăng ký, đăng nhập, social login)
 */
class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     * POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'date_of_birth' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Đăng nhập
     * POST /api/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Đăng xuất
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     * GET /api/user
     */
    public function getUser(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Cập nhật thông tin user
     * PUT /api/user
     */
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:15',
            'date_of_birth' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'date_of_birth']));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công',
            'data' => $user
        ]);
    }

    /**
     * Google Login - Redirect
     * GET /api/auth/google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Google Login - Callback
     * GET /api/auth/google/callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập Google thành công',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập Google thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Facebook Login - Redirect
     * GET /api/auth/facebook
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Facebook Login - Callback
     * GET /api/auth/facebook/callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            $user = User::where('email', $facebookUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'provider' => 'facebook',
                    'provider_id' => $facebookUser->id,
                    'avatar' => $facebookUser->avatar,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'provider' => 'facebook',
                    'provider_id' => $facebookUser->id,
                    'avatar' => $facebookUser->avatar,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập Facebook thành công',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập Facebook thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quên mật khẩu - Gửi email reset
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Tạo token reset password
        $token = Str::random(64);

        // Xóa token cũ nếu có
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Lưu token mới
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // Gửi email (giả định - log ra console)
        \Log::info('Password Reset Token for ' . $request->email . ': ' . $token);
        \Log::info('Reset URL: ' . url('/reset-password?token=' . $token . '&email=' . $request->email));

        return response()->json([
            'success' => true,
            'message' => 'Email khôi phục mật khẩu đã được gửi',
            'debug_token' => config('app.debug') ? $token : null, // Chỉ hiển thị khi debug
        ]);
    }

    /**
     * Đặt lại mật khẩu
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ'
            ], 400);
        }

        // Kiểm tra token đã quá 60 phút chưa
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn'
            ], 400);
        }

        // Cập nhật mật khẩu
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công'
        ]);
    }
}

