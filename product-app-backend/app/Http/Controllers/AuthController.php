<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Helpers\TokenHelper;

// 1st September 2025 - Implemented the auth controller - Yashira 9.30pm
class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'dob' => 'required|date',
            'address' => 'required|string',
            'profile_picture' => 'nullable|string',
            'parent_email' => 'nullable|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $dob = Carbon::parse($data['dob']);
        $age = $dob->age;
        if ($age < 16 && empty($data['parent_email'])) {
            return response()->json(['error' => 'Parent email is required for users under 16.'], 422);
        }
        $otp = rand(100000, 999999);
        $otpExpires = Carbon::now()->addMinutes(10);
        $parentOtp = null;
        $parentOtpExpires = null;
        $isParentVerified = null;
        if ($age < 16) {
            $parentOtp = rand(100000, 999999);
            $parentOtpExpires = Carbon::now()->addMinutes(10);
            $isParentVerified = false;
        }
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'dob' => $data['dob'],
            'address' => $data['address'],
            'profile_picture' => $data['profile_picture'] ?? null,
            'parent_email' => $data['parent_email'] ?? null,
            'otp' => $otp,
            'otp_expires_at' => $otpExpires,
            'is_verified' => false,
            'parent_otp' => $parentOtp,
            'parent_otp_expires_at' => $parentOtpExpires,
            'is_parent_verified' => $isParentVerified,
        ]);
        return response()->json([
            'message' => 'Signup successful. Please verify your email with the OTP sent.',
            'user_id' => $user->id,
            'otp' => $otp
        ], 201);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = TokenHelper::createToken($user);
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->where('otp', $request->otp_code)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid OTP or email.'], 400);
        }
        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}