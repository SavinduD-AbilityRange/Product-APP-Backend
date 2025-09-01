<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

// 1st September 2025 - Implemented the auth controller - Ashini 19:36
class AuthController extends Controller
{
    public function signup(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'required|email|unique:customers,email',
        'password' => 'required|string|min:6|confirmed',
        'date_of_birth' => 'required|date',
        'role' => 'required|integer|in:1,2,3',
        'parent_id' => 'nullable|integer|exists:customers,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

        $dob = Carbon::parse($request->date_of_birth);
        $age = $dob->age;

        if ($age < 16 && !$request->parent_id) {
            return response()->json([
                'message' => 'Parent account is required for users under 16.'
            ], 422);
        }

        $otp_code = rand(100000, 999999);
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'role' => $request->role,
            'parent_id' => $request->parent_id,
            'otp_code' => $otp_code,
            'status' => 'inactive',
        ]);

       
        return response()->json([
            'message' => 'Signup successful. Please verify your email with the OTP sent.',
            'user_id' => $customer->id,
            'otp_code' => $otp_code
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

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

       
    $token = \App\Helpers\TokenHelper::createToken($customer);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $customer
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

        $customer = Customer::where('email', $request->email)->where('otp_code', $request->otp_code)->first();
        if (!$customer) {
            return response()->json(['message' => 'Invalid OTP or email.'], 400);
        }

        $customer->email_verified = true;
        $customer->status = 'active';
        $customer->otp_code = null;
        $customer->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
