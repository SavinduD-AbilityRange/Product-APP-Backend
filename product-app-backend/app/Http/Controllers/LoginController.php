<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\TokenHelper;

class LoginController extends Controller
{
    // [01/09/2025 |Asmitha T| 15.26] Request OTP for login
    //[02/09/2025 |Asmitha T| 11.46]  User submits email and password, OTP is sent if credentials are valid
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if (!$customer->email_verified) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }
        if (!Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $otp_code = rand(100000, 999999);
        $customer->otp_code = $otp_code;
        $customer->save();

        //[01/09/2025 |Asmitha T| 15.26] Send OTP to email 

        return response()->json([
            'message' => 'OTP sent to email.',
            'otp_code' => $otp_code 
        ], 200);
    }

    //[02/09/2025 |Asmitha T| 11.46]  User submits email and OTP, login is allowed if OTP matches
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if (!$customer->email_verified) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }
        if ($customer->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Invalid OTP code.'], 403);
        }

        //[01/09/2025 |Asmitha T| 15.26] Generate tokens
        $jwt = TokenHelper::createJWT($customer);
        $api_token = TokenHelper::createApiToken();
        $normal_token = TokenHelper::createNormalToken();

        $children = Customer::where('parent_id', $customer->id)->get();

        return response()->json([
            'message' => 'Login successful.',
            'jwt_token' => $jwt,
            'api_token' => $api_token,
            'normal_token' => $normal_token,
            'user_details' => [
                'id' => $customer->id,
                'profile_image' => $customer->profile_image,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'date_of_birth' => $customer->date_of_birth,
                'address' => $customer->address,
                'status' => $customer->status,
                'role' => $customer->role,
                'children' => $children,
            ],
        ], 200);
    }
}
