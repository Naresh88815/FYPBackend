<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusUpdate extends Model
{
    protected $table = 'status_update';
    protected $primaryKey = 'exp_id'; 
    public $incrementing = false;
    protected $fillable = [
        'exp_id',
        'emp_id',
        'note',
        'status',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'user_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expenses::class, 'exp_id', 'expense_id');
    }
}
