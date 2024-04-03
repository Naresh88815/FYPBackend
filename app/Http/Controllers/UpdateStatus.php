<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;
use App\Models\StatusUpdate;

class UpdateStatus extends Controller
{
    public function updateExpenseStatus(Request $request)
    {
        $request->validate([
            'type' => 'required|in:expense_status_update',
            'emp_id' => 'required|exists:users,user_id',
            'exp_id' => 'required|exists:expenses,expense_id',
            'status_id' => 'required|string',
            'note' => 'nullable|string',
            'image' => 'nullable',
        ]);

        $expense = Expenses::find($request->exp_id);

        if ($expense) {
            $expense->status = $request->status_id;
            $expense->save();

            // Handle status_id 3 and 4 separately to save the note and emp_id to the 'status_update' table
            if ((string) $request->status_id === "3" || (string) $request->status_id === "4" || (string) $request->status_id === "2" || (string) $request->status_id === "5" ) {
                try {
                    $statusUpdate = new StatusUpdate;
                    $statusUpdate->exp_id = $request->exp_id;
                    $statusUpdate->emp_id = $request->emp_id;
                    $statusUpdate->note = $request->note;
                    $statusUpdate->status = $request->status_id;
                    $statusUpdate->save();
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
                }
            }
                return response()->json([
                    'status' => true,
                    'message' => 'Expense status updated successfully',
                ], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Expense not found'], 404);
        }
    }
}
