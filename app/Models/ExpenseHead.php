<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseHead extends Model
{
    protected $table = 'expense_heads';
    protected $primaryKey = 'head_id';
    protected $fillable = [
        'name', 
        
    ];
   
}
