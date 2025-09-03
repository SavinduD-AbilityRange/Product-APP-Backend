<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\UserAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Helpers\TokenHelper;

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
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Invalid file type. Only JPG, JPEG, PNG, GIF, and WebP files are allowed.");
        }

        if ($file->getSize() > 2048 * 1024) {
            throw new \Exception("File too large. Maximum size is 2MB.");
        }

        $uploadPath = dirname(dirname(dirname(__DIR__))) . '/public/' . $directory;
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $file->move($uploadPath, $filename);

        return $directory . '/' . $filename;
    }

    private function deleteOldProfilePicture($profilePicturePath)
    {
        if (!$profilePicturePath) {
            return;
        }

        $fullPath = dirname(dirname(dirname(__DIR__))) . '/public/' . $profilePicturePath;
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
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
            'children' => 'nullable|integer|min:0',
            'profile_picture' => 'nullable|file|max:2048',
            'parent_email' => 'nullable|email',
            'role' => 'nullable|in:admin,user,manager,creator',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        
        \Log::info('Signup data received:', ['middle_name' => $data['middle_name'] ?? 'NOT_SET']);
        
        $dob = Carbon::parse($data['dob']);
        $age = $dob->age;
        if ($age < 16 && empty($data['parent_email'])) {
            return response()->json(['error' => 'Parent email is required for users under 16.'], 422);
        }
        $otp = rand(1000, 9999);
        $otpExpires = Carbon::now()->addMinutes(10);
        $parentOtp = null;
        $parentOtpExpires = null;
        $isParentVerified = null;
        if ($age < 16) {
            $parentOtp = rand(1000, 9999);
            $parentOtpExpires = Carbon::now()->addMinutes(10);
            $isParentVerified = false;
        }

        try {
            $profilePicturePath = $this->handleFileUpload($request->file('profile_picture'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $role = $data['role'] ?? 'user';

        $user = Customer::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'dob' => $data['dob'],
            'address' => $data['address'],
            'description' => $data['description'] ?? null,
            'children' => $data['children'] ?? 0,
            'profile_picture' => $profilePicturePath,
            'parent_email' => $data['parent_email'] ?? null,
            'role' => $role,
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
            'user_role' => $role, // Use the same role as the user
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
        
        \Log::info('User from database:', ['middle_name' => $user->middle_name]);
        
        $userAuth = $user->userAuth;
        
        $token = TokenHelper::createToken($user);
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'dob' => $user->dob,
                'address' => $user->address,
                'description' => $user->description,
                'children' => $user->children,
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

    public function verifyEmail(Request $request)
    {
        \Log::info('Verify Email Request Data:', $request->all());
        \Log::info('OTP Code Value:', [
            'value' => $request->otp_code,
            'type' => gettype($request->otp_code),
            'length' => strlen((string)$request->otp_code),
            'is_numeric' => is_numeric($request->otp_code)
        ]);
        
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

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $otp = rand(1000, 9999);
        $customer->otp = $otp;
        $customer->otp_expires_at = Carbon::now()->addMinutes(10);
        $customer->save();

        return response()->json([
            'message' => 'OTP sent successfully',
            'customer_id' => $customer->id,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'otp' => 'required|digits:4',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        if ($customer->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        if ($customer->otp_expires_at && Carbon::now()->greaterThan($customer->otp_expires_at)) {
            return response()->json(['error' => 'OTP has expired'], 400);
        }

        $customer->is_verified = true;
        $customer->otp = null;
        $customer->otp_expires_at = null;
        $customer->save();

        return response()->json([
            'message' => 'OTP verified successfully',
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'is_verified' => $customer->is_verified,
            ]
        ], 200);
    }

    public function debugCustomer($id)
    {
        try {
            $customer = Customer::find($id);
            
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $rawData = \DB::table('customers')->where('id', $id)->first();
            
            return response()->json([
                'customer_model' => $customer->toArray(),
                'raw_database' => (array) $rawData,
                'middle_name_from_model' => $customer->middle_name,
                'middle_name_from_raw' => $rawData->middle_name ?? 'null',
                'all_attributes' => $customer->getAttributes()
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
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
                'children' => $user->children,
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

        $allowedFields = [
            'first_name', 'middle_name', 'last_name', 'dob', 'address', 
            'description', 'children', 'parent_email', 'role'
        ];
        
        $updateData = [];
        foreach ($allowedFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        $rules = [];
        if (isset($updateData['first_name'])) $rules['first_name'] = 'string|max:255';
        if (isset($updateData['middle_name'])) $rules['middle_name'] = 'nullable|string|max:255';
        if (isset($updateData['last_name'])) $rules['last_name'] = 'string|max:255';
        if (isset($updateData['dob'])) $rules['dob'] = 'date';
        if (isset($updateData['address'])) $rules['address'] = 'string';
        if (isset($updateData['description'])) $rules['description'] = 'nullable|string';
        if (isset($updateData['children'])) $rules['children'] = 'nullable|integer|min:0';
        if (isset($updateData['parent_email'])) $rules['parent_email'] = 'nullable|email';
        if (isset($updateData['role'])) $rules['role'] = 'nullable|in:admin,user,manager,creator';

        if ($request->hasFile('profile_picture')) {
            $rules['profile_picture'] = 'file|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        if ($request->hasFile('profile_picture')) {
            try {
                $oldProfilePicture = $user->profile_picture;
                
                $profilePicturePath = $this->handleFileUpload($request->file('profile_picture'));
                $user->update(['profile_picture' => $profilePicturePath]);
                
                if ($oldProfilePicture) {
                    $this->deleteOldProfilePicture($oldProfilePicture);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
        }
        
        $user->refresh();
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
                'children' => $user->children,
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
    
    public function debugProfileUpdate(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        return response()->json([
            'message' => 'Debug profile update',
            'user_id' => $user->id,
            'request_all' => $request->all(),
            'request_input_first_name' => $request->input('first_name'),
            'request_has_first_name' => $request->has('first_name'),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);
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