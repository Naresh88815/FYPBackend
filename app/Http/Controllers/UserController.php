<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Import the User model

class UserController extends Controller
{
    /**
     * Add a new user or retrieve existing users based on the request type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addViewUser(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'type' => 'required|in:add_user,view_user', // Validate that type is either 'add_user' or 'view_user'
        ]);

        try {
            if ($validatedData['type'] === 'add_user') {
                // Validate the request for adding a new user
                $validatedUserData = $request->validate([
                    'name' => 'required|string|max:50',
                    'email' => 'required|email|max:60',
                    'user_phone' => 'nullable|string|max:10',
                    'account_no' => 'nullable|string|max:500',
                    'khalti_id' => 'nullable|string|max:500',
                    'user_role' => 'nullable|string',
                    'super_user' => 'nullable|string',
                ]);

                // Create a new User instance
                $user = new User();
                $user->fill($validatedUserData);
                $user->save();

                // Return a response indicating success
                return response()->json(['status' => true, 'message' => 'User added successfully'], 201);
            } else {
                $users = User::all();

                // Return a response with the fetched users
                return response()->json(['status' => true, 'message' => 'Users retrieved successfully', 'users' => $users], 200);
            }
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
