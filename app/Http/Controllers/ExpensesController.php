<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;

class ExpensesController extends Controller
{
    /**
     * Add or retrieve expenses based on type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addViewExpenses(Request $request)
    {
        // Validate the 'type' parameter first
        $request->validate([
            'type' => 'required|in:create_expense,expense_view,expense_status_update,expense_detail',
        ]);

        // Based on the type, proceed with further validation and processing
        if ($request->type === 'create_expense') {
            // Validate other parameters for creating an expense
            $request->validate([
                'emp_id' => 'required|exists:users,user_id',
                'head_id' => 'required|exists:expense_heads,head_id',
                'label_id' => 'required|exists:expense_label,label_id',
                'amount' => 'required|string|max:10',
                'note' => 'nullable|string|max:500',
                'payment_type' => 'nullable|string|max:50',
                'status' => 'required|string',
                'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            ]);

            // If type is 'create_expense', create a new expense
            $expense = new Expenses($request->except('image'));

            // Handle image upload
            if ($request->hasFile('image')) {
                $images = [];
                foreach ($request->file('image') as $image) {
                    $image_name = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('images'), $image_name);
                    $images[] = $image_name;
                }
                $expense->image = json_encode($images); // Store image names as JSON
            }

            // Save the expense record to the database
            $expense->save();

            // Return a response indicating success
            return response()->json(['status' => true, 'message' => 'Expense added successfully'], 201);
        } elseif ($request->type === 'expense_view') {
            $request->validate([
                'type' => 'required|in:create_expense,expense_view,expense_search',
                'page' => 'required_if:type,expense_view|integer|min:1',
                'search' => 'nullable|string|max:255',
                'amount_filter' => 'nullable|string',
                'sort_filter' => 'nullable|string',
                'datefilter' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);
            $query = Expenses::query();
            $query->with('user', 'head', 'label');

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereHas('user', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    })
                        ->orWhereHas('head', function ($query) use ($searchTerm) {
                            $query->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('label', function ($query) use ($searchTerm) {
                            $query->where('label_name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            if ($request->has('amount_filter')) {
                $query->orderBy('amount', $request->amount_filter === 'low_to_high' ? 'asc' : 'desc');
            }

            if ($request->has('sort_filter') && $request->has('status')) {
                $query->orderBy('status', 'asc');
            }

            if ($request->has('sort_filter') && $request->has('exp_heads')) {
                $query->leftJoin('expense_heads', 'expenses.head_id', '=', 'expense_heads.head_id')
                    ->orderBy('expense_heads.name', 'asc');
            }


            if ($request->has('datefilter') && $request->datefilter === 'date') {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            if ($request->has('datefilter')) {
                $query->orderBy('created_at', $request->datefilter === 'asc' ? 'asc' : 'desc');
            }

            // if ($request->has('sort_filter')) {
            //     $query->orderBy('user_name', $request->sort_filter === 'request_by' ? 'asc' : 'desc');
            // }

            if ($request->has('sort_filter') && $request->sort_filter === 'request_by') {
                $query->orderBy('user_name', 'asc');
            }

            $expenses = $query->paginate(10); // Adjust pagination limit as needed

            $transformedExpenses = $expenses->map(function ($expense) {
                return [
                    'expense_id' => $expense->expense_id,
                    'user_name' => $expense->user->name,
                    'label_name' => $expense->label->label_name,
                    'heads_name' => $expense->head->name,
                    'amount' => $expense->amount,
                    'status' => $expense->status,
                    'date_added' => $expense->created_at->format('Y-m-d H:i:s'),
                    'image' => $expense->image,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Expenses retrieved successfully',
                'expenses' => $transformedExpenses,
                'pagination' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'total' => $expenses->total(),
                ],
            ], 200);
            // } elseif ($request->type === 'expense_search') {
            //     $searchTerm = $request->input('search');

            //     $query = Expenses::query();
            //     $query->with('user', 'head', 'label');

            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->whereHas('user', function ($query) use ($searchTerm) {
            //             $query->where('name', 'like', '%' . $searchTerm . '%');
            //         })
            //             ->orWhereHas('head', function ($query) use ($searchTerm) {
            //                 $query->where('name', 'like', '%' . $searchTerm . '%');
            //             })
            //             ->orWhereHas('label', function ($query) use ($searchTerm) {
            //                 $query->where('label_name', 'like', '%' . $searchTerm . '%');
            //             });
            //     });

            //     $expenses = $query->distinct()->paginate(10);

            //     $transformedExpenses = $expenses->map(function ($expense) {
            //         return [
            //             'expense_id' => $expense->expense_id,
            //             'user_name' => $expense->user->name,
            //             'label_name' => $expense->label->label_name,
            //             'heads_name' => $expense->head->name,
            //             'amount' => $expense->amount,
            //             'status' => $expense->status,
            //             'date_added' => $expense->created_at->format('Y-m-d H:i:s'),
            //             'image' => $expense->image,
            //         ];
            //     });

            //     return response()->json([
            //         'status' => true,
            //         'message' => 'Expenses retrieved successfully',
            //         'expenses' => $transformedExpenses,
            //     ], 200);
        } elseif ($request->type === 'expense_status_update') {
            $request->validate([
                'emp_id' => 'required|exists:users,user_id',
                'exp_id' => 'required|exists:expenses,expense_id',
                'status_id' => 'required',
            ]);
            if ($request->has(['emp_id', 'exp_id', 'status_id'])) {
                $status = $request->input('status_id');
                $expense_id = $request->input('exp_id');
                $this->updateStatus($status, $expense_id); // Corrected the function call
            }
        } elseif ($request->type === 'expense_detail') {
            $request->validate([
                'exp_id' => 'required|exists:expenses,expense_id',
            ]);

            // If the validation passes, retrieve the expense details
            $expense = Expenses::with(['user', 'head', 'label'])->find($request->exp_id);

            // Check if the expense record exists
            if ($expense) {
                $transformedExpenses = $expense->map(function ($expense) {
                    return [
                        'expense_id' => $expense->expense_id,
                        'emp_id'=> $expense->emp_id,
                        'user_name' => $expense->user->name,
                        'label_name' => $expense->label->label_name,
                        'heads_name' => $expense->head->name,
                        'amount' => $expense->amount,
                        'status' => $expense->status,
                        'note' => $expense ->note,
                        'date_added' => $expense->created_at->format('Y-m-d H:i:s'),
                        'image' => $expense->image,
                    ];
                });
    
                return response()->json(['status' => true, 'expense' => $transformedExpenses], 200);
            } else {
                // Return a response indicating that the expense was not found
                return response()->json(['status' => false, 'message' => 'Expense not found'], 404);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Invalid request type'], 400);
        }
    }

    public function updateStatus($status, $expense_id)
    {
        // $status = $request->input('status');
        // $expense_id = $request->input('expense_id');

        // Retrieve the expense record from the database
        $expense = Expenses::find($expense_id);

        // Check if the expense record exists
        if ($expense) {
            // Update the status of the expense
            $expense->status = $status;

            // Save the changes to the database
            $expense->save();

            return response()->json(['message' => 'Expense status updated successfully'], 200);
        } else {
            return response()->json(['message' => 'Expense not found'], 404);
        }
    }
}