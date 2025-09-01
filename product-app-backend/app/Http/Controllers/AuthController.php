<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class AuthController extends Controller
{
	public function register(Request $request)
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
			return response()->json(['error' => $validator->errors()], 422);
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
			'password' => app('hash')->make($data['password']),
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
			'message' => 'User registered. OTP sent to email.' . ($age < 16 ? ' Parent OTP sent to parent email.' : ''),
			'user' => $user,
		], 201);
	}
}
