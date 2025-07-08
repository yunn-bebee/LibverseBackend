<?php 
// app/Http/Controllers/Api/V1/Profile/ProfileController.php
namespace App\Http\Controllers\API\V1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'username' => 'sometimes|string|unique:users,username,'.$user->id,
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'bio' => 'sometimes|string|max:500',
            'location' => 'sometimes|string|max:100',
            'website' => 'sometimes|url|max:255',
        ]);

        $user->update($request->only(['username', 'email']));
        
        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['bio', 'location', 'website'])
        );

        return response()->json($user->load('profile'));
    }


    public function delete(Request $request)
    {
        $user = $request->user();
        
        // Delete user profile
        $user->profile()->delete();
        
        // Delete user account
        $user->delete();

        return response()->json(['message' => 'User profile and account deleted successfully.']);
    }
}