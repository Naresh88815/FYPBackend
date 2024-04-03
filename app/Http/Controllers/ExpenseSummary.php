<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;

class ExpenseSummary extends Controller
{
    public function getExpenseSummary(Request $request)
    {
        $request->validate([
            'type' => 'required|in:count_expense',
            'start_date'=> 'nullable|date',
            'end_date'=> 'nullable|date',
        ]);

        // Initialize an array to hold the summary data
        $summary = [
            'total_request' => 0,
            'total_approved' => 0,
            'total_cancel' => 0,
            'total_pending' => 0,
            'total_decline' => 0,
            'total_transfer' => 0,
        ];

        // Query the Expenses model to get the count of expenses by status
        $expenseCounts = Expenses::selectRaw('status, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();

        // Map the status codes to their corresponding keys in the summary array
        $statusMap = [
            1 => 'total_pending',
            2 => 'total_approved',
            3 => 'total_cancel',
            4 => 'total_decline',
            5 => 'total_transfer',
        ];

        // Populate the summary array with the data from the query
        foreach ($statusMap as $statusCode => $key) {
            if (isset($expenseCounts[$statusCode])) {
                $summary[$key] = $expenseCounts[$statusCode]['total_amount'];
            }
        }

        // Calculate the total expenses by summing up the amounts for each status
        $summary['total_request'] = array_sum($summary);

        return response()->json([
            'status' => true,
            'message' => 'Expense summary retrieved successfully',
            'summary' => $summary,
        ], 200);
    }
}
