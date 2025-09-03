<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\UserInterest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserInterestController extends Controller
{
    /**
     * Get all available interests (maximum 10)
     */
    public function getInterests()
    {
        $interests = Interest::where('is_active', true)->take(10)->get();
        return response()->json([
            'message' => 'Interests retrieved successfully.',
            'interests' => $interests
        ], 200);
    }

    /**
     * Get user's selected interests
     */
    public function getUserInterests(Request $request)
    {
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return response()->json(['message' => 'User ID is required.'], 400);
        }

        $userInterests = UserInterest::with('interest')
            ->where('user_id', $userId)
            ->get();

        $interests = $userInterests->pluck('interest');
        $totalCount = $interests->count();

        return response()->json([
            'message' => 'User interests retrieved successfully.',
            'interests' => $interests,
            'total_interests' => $totalCount,
            'max_allowed' => 3,
            'available_slots' => 3 - $totalCount
        ], 200);
    }

    /**
     * Add user interests (maximum 3 total)
     */
    public function addUserInterests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:customers,id',
            'interest_ids' => 'required|array|min:1|max:3',
            'interest_ids.*' => 'integer|exists:interests,id|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;
        $interestIds = array_unique($request->interest_ids); // Remove duplicates

        // Check current user interests count
        $existingCount = UserInterest::where('user_id', $userId)->count();
        $newCount = count($interestIds);
        
        if ($existingCount + $newCount > 3) {
            return response()->json([
                'message' => "Cannot add {$newCount} interests. You already have {$existingCount} interests. Maximum 3 interests allowed per user.",
                'current_count' => $existingCount,
                'max_allowed' => 3,
                'available_slots' => 3 - $existingCount
            ], 422);
        }

        // Check for duplicate interests
        $existingInterests = UserInterest::where('user_id', $userId)
            ->whereIn('interest_id', $interestIds)
            ->pluck('interest_id')
            ->toArray();

        if (!empty($existingInterests)) {
            return response()->json([
                'message' => 'Some interests are already added to your profile.',
                'duplicate_interest_ids' => $existingInterests
            ], 422);
        }

        // Add new interests
        $addedInterests = [];
        foreach ($interestIds as $interestId) {
            $userInterest = UserInterest::create([
                'user_id' => $userId,
                'interest_id' => $interestId
            ]);
            $addedInterests[] = $userInterest->load('interest');
        }

        return response()->json([
            'message' => 'Interests added successfully.',
            'added_interests' => $addedInterests,
            'total_interests' => $existingCount + $newCount
        ], 201);
    }

    /**
     * Update/Replace user interests (maximum 3 total)
     */
    public function updateUserInterests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:customers,id',
            'interest_ids' => 'required|array|min:1|max:3',
            'interest_ids.*' => 'integer|exists:interests,id|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;
        $interestIds = array_unique($request->interest_ids); // Remove duplicates

        // Validate maximum 3 interests
        if (count($interestIds) > 3) {
            return response()->json([
                'message' => 'Maximum 3 interests allowed per user.',
                'provided_count' => count($interestIds),
                'max_allowed' => 3
            ], 422);
        }

        // Remove existing interests
        UserInterest::where('user_id', $userId)->delete();

        // Add new interests
        $addedInterests = [];
        foreach ($interestIds as $interestId) {
            $userInterest = UserInterest::create([
                'user_id' => $userId,
                'interest_id' => $interestId
            ]);
            $addedInterests[] = $userInterest->load('interest');
        }

        return response()->json([
            'message' => 'Interests updated successfully.',
            'interests' => $addedInterests,
            'total_interests' => count($addedInterests)
        ], 200);
    }

    /**
     * Delete specific user interest
     */
    public function deleteUserInterest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:customers,id',
            'interest_id' => 'required|integer|exists:interests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deleted = UserInterest::where('user_id', $request->user_id)
            ->where('interest_id', $request->interest_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Interest deleted successfully.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Interest not found for this user.'
            ], 404);
        }
    }

    /**
     * Delete all user interests
     */
    public function deleteAllUserInterests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        UserInterest::where('user_id', $request->user_id)->delete();

        return response()->json([
            'message' => 'All user interests deleted successfully.'
        ], 200);
    }
}
