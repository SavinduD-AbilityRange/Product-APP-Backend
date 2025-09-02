<?php
//[02/09/2025 |Asmitha T| 11.11] - User Interest Controller
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInterest;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserInterestController extends Controller
{
    //[02/09/2025 |Asmitha T| 11.11] - Store User Interests
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'interests' => 'required|array|size:10',
            'interests.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->input('user_id');
        $interestIds = $request->input('interests');

        
        UserInterest::where('user_id', $userId)->delete();

        
        $data = collect($interestIds)->map(function ($id) use ($userId) {
            return [
                'user_id' => $userId,
                'interest_id' => $id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        })->toArray();

        UserInterest::insert($data);

        return response()->json(['message' => 'Interests saved successfully.']);
    }

    //[02/09/2025 |Asmitha T| 11.11] - Get User Interests
    public function show($userId)
    {
        $interests = \DB::table('user_interests')
            ->join('interests', 'user_interests.interest_id', '=', 'interests.id')
            ->where('user_interests.user_id', $userId)
            ->select('interests.id as interest_id', 'interests.name', 'interests.icon')
            ->get();

        return response()->json(['interests' => $interests]);
    }
}
