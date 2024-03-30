<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'expense_id';

    protected $fillable = [
        'emp_id', 'label_id', 'amount', 'head_id', 'note', 'image', 'payment_type','status','updated_at','created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'user_id');
    }

    public function label()
    {
        return $this->belongsTo(ExpenseLabel::class,'label_id','label_id');
    }

    public function head()
    {
        return $this->belongsTo(ExpenseHead::class,'head_id','head_id');
    }
}
