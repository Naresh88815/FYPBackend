<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseLabel;

class LabelController extends Controller
{
    /**
     * Store a newly created label in storage or view all labels.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addViewlabel(Request $request)
    {
        // Validate the request data
        $request->validate([
            'type' => 'required|in:add_label,view_label', // Validate that type is either 'add' or 'view'
            'label_name' => 'required_if:type,add_label', // Only required if type is 'add'
            'emp_id' => 'required_if:type,add_label', // Only required if type is 'add'
        ]);

        // If type is 'add', create a new label and store it in the database
        if ($request->type === 'add_label') {
            // Create a new ExpLabel instance
            $label = new ExpenseLabel();
            $label->label_name = $request->label_name;
            $label->emp_id = $request->emp_id;
            $label->status = 1; // Assuming default status is active
            $label->save();

            // Optionally, you can return a response indicating success
            return response()->json(['status'=>'true','message' => 'Label added successfully', 'label' => $label], 201);
        } 
        // If type is 'view', fetch all labels from the database and return them in the response
        else {
            $labels = ExpenseLabel::all();

            // Optionally, you can return a response with the fetched labels
            return response()->json(['status'=>'true','message' => 'Labels retrieved successfully','label' => $labels], 200);
        }
    }
}
