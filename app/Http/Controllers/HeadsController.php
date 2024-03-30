<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseHead; // Import the Expense Head model

class HeadsController extends Controller
{
    public function addViewHeads(Request $request)
    {
        // Validate the request data
        $request->validate([
            'type' => 'required|in:add_exp_heads,view_exp_heads', // Validate that type is either 'add' or 'view'
        ]);

        // If type is 'add', create a new expense head and store it in the database
        if ($request->type === 'add_exp_heads') {
            // Validate the request for adding a new expense head
            $request->validate([
                'name' => 'required|string|max:250', // Assuming 'name' field is required and has maximum length of 250 characters
            ]);

            // Create a new ExpenseHead instance
            $head = new ExpenseHead();
            $head->name = $request->input('name');
            $head->save();

            // Return a response indicating success
            return response()->json(['status'=>'true','message' => 'Head added successfully', 'head' => $head], 201);
        } 
        // If type is 'view', fetch all expense heads from the database and return them in the response
        else {
            $heads = ExpenseHead::all();

            // Return a response with the fetched expense heads
            return response()->json(['status'=>'true','message' => 'Heads retrieved successfully', 'head' => $heads], 200);
        }
    }
}
