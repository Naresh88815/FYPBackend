<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;
use App\Models\User;
use App\Models\StatusUpdate;

class ExpensesController extends Controller
{
    public function addViewExpenses(Request $request)
    {
        $request->validate([
            'type' => 'required|in:create_expense,expense_view,expense_status_update,expense_search,expense_detail',
        ]);

        switch ($request->type) {
            case 'create_expense':
                return $this->createExpense($request);
            case 'expense_view':
                return $this->viewExpenses($request);
            case 'expense_detail':
                return $this->getExpenseDetail($request);
            case 'expense_search':
                return $this->searchExpenses($request);
            default:
                return response()->json(['status' => false, 'message' => 'Invalid request type'], 400);
        }
    }

    private function createExpense(Request $request)
    {
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

        $expense = new Expenses($request->except('image'));

        if ($request->hasFile('image')) {
            $images = [];
            foreach ($request->file('image') as $image) {
                $image_name = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $image_name);
                $images[] = $image_name;
            }
            $expense->image = json_encode($images);
        }

        $expense->save();

        return response()->json(['status' => true, 'message' => 'Expense added successfully'], 201);
    }

    private function viewExpenses(Request $request)
    {
        $request->validate([
            'page' => 'required_if:type,expense_view|integer|min:1',
            'emp_id' => 'nullable|string',
            'search' => 'nullable|string|max:255',
            'amount_filter' => 'nullable|string',
            'sort_filter' => 'nullable|string',
            'datefilter' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = Expenses::query();
        $query->with('user', 'head', 'label');

        // Filtering and sorting logic
        if ($request->has('amount_filter')) {
            $query->orderBy('amount', $request->amount_filter === 'low_to_high' ? 'asc' : 'desc');
        }

        if ($request->has('datefilter') && $request->datefilter === 'date') {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->has('sort_filter')) {
            switch ($request->sort_filter) {
                case 'status':
                    $query->orderBy('status', 'asc');
                    break;
                case 'request_by':
                    $query->join('users', 'expenses.emp_id', '=', 'users.user_id')
                        ->orderBy('users.name', 'asc');
                    break;
                case 'exp_heads':
                    $query->join('expense_heads', 'expenses.head_id', '=', 'expense_heads.head_id')
                        ->orderBy('expense_heads.name', 'asc');
                    break;
            }
        }

        $emp_id = $request->emp_id;
        $user = User::where('user_id', $emp_id)->first();
        if ($user) {
            $super_user_value = $user->super_user;
            if ($super_user_value === 0) {
                $query->where('emp_id', $emp_id);
            }
        }

        $expenses = $query->paginate(10);

        $transformedExpenses = $expenses->map(function ($expense) {
            $status = '';
            switch ($expense->status) {
                case 1:
                    $status = 'Pending';
                    break;
                case 2:
                    $status = 'Approved';
                    break;
                case 3:
                    $status = 'Cancelled';
                    break;
                case 4:
                    $status = 'Declined';
                    break;
                case 5:
                    $status = 'Transfered';
                    break;
                default:
                    $status = $expense->status;
            }
            return [
                'expense_id' => $expense->expense_id,
                'user_name' => $expense->user->name,
                'label_name' => $expense->label->label_name,
                'heads_name' => $expense->head->name,
                'amount' => $expense->amount,
                'status' => $status,
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
    }


    private function getExpenseDetail(Request $request)
    {
        $request->validate([
            'exp_id' => 'required|exists:expenses,expense_id',
        ]);

        $expense = Expenses::with(['user', 'head', 'label'])->find($request->exp_id);

        if ($expense) {
            $status = '';
            switch ($expense->status) {
                case 1:
                    $status = 'Pending';
                    break;
                case 2:
                    $status = 'Approved';
                    break;
                case 3:
                    $status = 'Cancelled';
                    break;
                case 4:
                    $status = 'Declined';
                    break;
                case 5:
                    $status = 'Transferred';
                    break;
                default:
                    $status = $expense->status;
            }

            $is_approve_button = $status == 'Pending';

            $approvalData = $expense->statusUpdate()->where('status', 2)->where('exp_id', $expense->expense_id)->with('user')->first();
            $transformedapprove = $approvalData ? [
                'user_name' => $approvalData->user->name,
                'date_added' => $approvalData->created_at->format('Y-m-d H:i:s'),
            ] : null;

            $cancelData = $expense->statusUpdate()->where('status', 3)->where('exp_id', $expense->expense_id)->with('user')->first();
            $transformedCancel = $cancelData ? [
                'user_name' => $cancelData->user->name,
                'note' => $cancelData->note,
                'date_added' => $cancelData->created_at->format('Y-m-d H:i:s'),
            ] : null;

            $rejectData = $expense->statusUpdate()->where('status', 4)->where('exp_id', $expense->expense_id)->with('user')->first();
            $transformedReject = $rejectData ? [
                'user_name' => $rejectData->user->name,
                'note' => $rejectData->note,
                'date_added' => $rejectData->created_at->format('Y-m-d H:i:s'),
            ] : null;

            $transferredData = $expense->statusUpdate()->where('status', 5)->where('exp_id', $expense->expense_id)->with('user')->first();
            $transformedTransfer = $transferredData ? [
                'user_name' => $transferredData->user->name,
                'note' => $transferredData->note,
                'date_added' => $transferredData->created_at->format('Y-m-d H:i:s'),
            ] : null;

            $transformedExpense = [
                'expense_id' => $expense->expense_id,
                'emp_id' => $expense->emp_id,
                'user_name' => $expense->user->name,
                'label_name' => $expense->label->label_name,
                'heads_name' => $expense->head->name,
                'amount' => $expense->amount,
                'status' => $status,
                'note' => $expense->note,
                'date_added' => $expense->created_at->format('Y-m-d H:i:s'),
                'image' => $expense->image,
                'payment_type' => $expense->payment_type,
                'is_approve_button' => $is_approve_button,
            ];

            // Include additional details for approval, rejection, transfer, and cancellation based on status
            switch ($expense->status) {
                case 2: // Approved
                    $transformedExpense['approve'] = $transformedapprove;
                    break;
                case 3: // Cancelled
                    $transformedExpense['cancelled'] = $transformedCancel;
                    break;
                case 4: // Declined
                    $transformedExpense['reject'] = $transformedReject;
                    break;
                case 5: // Transferred
                    $transformedExpense['transfer'] = $transformedTransfer;
                    break;
            }

            return response()->json(['status' => true, 'expense' => $transformedExpense], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Expense not found'], 404);
        }
    }


    private function searchExpenses(Request $request)
    {
        $searchTerm = $request->input('search');

        $searchResults = collect();

        $userSearchResults = Expenses::whereHas('user', function ($query) use ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        })->get();

        $headSearchResults = Expenses::whereHas('head', function ($query) use ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        })->get();

        $labelSearchResults = Expenses::whereHas('label', function ($query) use ($searchTerm) {
            $query->where('label_name', 'like', '%' . $searchTerm . '%');
        })->get();

        $searchResults = $searchResults->merge($userSearchResults)
            ->merge($headSearchResults)
            ->merge($labelSearchResults);

        $uniqueExpenses = $searchResults->unique('id');

        $paginatedExpenses = $uniqueExpenses->paginate(10);

        $transformedExpenses = $paginatedExpenses->map(function ($expense) {
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
                'current_page' => $paginatedExpenses->currentPage(),
                'last_page' => $paginatedExpenses->lastPage(),
                'per_page' => $paginatedExpenses->perPage(),
                'total' => $paginatedExpenses->total(),
            ],
        ], 200);
    }

   

}


