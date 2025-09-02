<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\UserAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Helpers\TokenHelper;

// 1st September 2025 - Implemented the auth controller - Yashira 9.30pm
class AuthController extends Controller
{
    private function generateApiKey()
    {
        do {
            $apiKey = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        } while (UserAuth::where('user_api_key', $apiKey)->exists());
        
        return $apiKey;
    }
    private function handleFileUpload($file, $directory = 'uploads/profiles')
    {
        if (!$file) {
            return null;
        }
        $uploadPath = public_path($directory);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $filename);

        return $directory . '/' . $filename;
    }
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|string|min:6',
            'dob' => 'required|date',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        $profilePicturePath = $this->handleFileUpload($request->file('profile_picture'));
        $profileImagePath = $this->handleFileUpload($request->file('profile_image'));

        $user = Customer::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'dob' => $data['dob'],
            'address' => $data['address'],
            'description' => $data['description'] ?? null,
            'profile_picture' => $profilePicturePath,
            'profile_image' => $profileImagePath,
            'parent_email' => $data['parent_email'] ?? null,
            'otp' => $otp,
            'otp_expires_at' => $otpExpires,
            'is_verified' => false,
            'parent_otp' => $parentOtp,
            'parent_otp_expires_at' => $parentOtpExpires,
            'is_parent_verified' => $isParentVerified,
        ]);
        $apiKey = $this->generateApiKey();
        UserAuth::create([
            'user_id' => $user->id,
            'user_role' => $age < 16 ? 'child' : 'adult',
            'user_api_key' => $apiKey,
            'user_status' => 'active'
        ]);

        return response()->json([
            'message' => 'Signup successful. Please verify your email with the OTP sent.',
            'user_id' => $user->id,
            'api_key' => $apiKey,
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

        $user = Customer::where('email', $request->email)->first();
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
            'otp_code' => 'required|digits:4',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Customer::where('email', $request->email)->where('otp', $request->otp_code)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid OTP or email.'], 400);
        }
        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
    public function getProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
        $userAuth = $user->userAuth;
        
        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'dob' => $user->dob,
                'address' => $user->address,
                'description' => $user->description,
                'profile_image' => $user->profile_image,
                'profile_picture' => $user->profile_picture,
                'parent_email' => $user->parent_email,
                'is_verified' => $user->is_verified,
                'status' => $user->status,
                'role' => $user->role,
                'api_key' => $userAuth ? $userAuth->user_api_key : null,
                'user_role' => $userAuth ? $userAuth->user_role : null,
                'user_status' => $userAuth ? $userAuth->user_status : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }
    public function editProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'middle_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'dob' => 'sometimes|date',
            'address' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
            'profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_picture' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_email' => 'sometimes|nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $this->handleFileUpload($request->file('profile_picture'));
        }
        
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->handleFileUpload($request->file('profile_image'));
        }
        foreach ($data as $key => $value) {
            if ($value !== null) { 
                $user->$key = $value;
            }
        }

        $user->save();
        $userAuth = $user->userAuth;

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'dob' => $user->dob,
                'address' => $user->address,
                'description' => $user->description,
                'profile_image' => $user->profile_image,
                'profile_picture' => $user->profile_picture,
                'parent_email' => $user->parent_email,
                'is_verified' => $user->is_verified,
                'status' => $user->status,
                'role' => $user->role,
                'api_key' => $userAuth ? $userAuth->user_api_key : null,
                'user_role' => $userAuth ? $userAuth->user_role : null,
                'user_status' => $userAuth ? $userAuth->user_status : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
        return response()->json([
            'message' => 'Logout successful.',
            'user_id' => $user->id
        ], 200);
    }
}