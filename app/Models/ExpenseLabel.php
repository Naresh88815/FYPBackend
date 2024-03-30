<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseLabel extends Model
{
    use HasFactory;

    protected $table = 'expense_label';
    protected $primaryKey = 'label_id';

    protected $fillable = [
        'label_name',
        'emp_id',
        'status',
        // Add other fillable fields here if needed
    ];

     // Define the relationship with the User model
     public function user()
     {
         return $this->belongsTo(User::class, 'emp_id','user_id');
     }
}
