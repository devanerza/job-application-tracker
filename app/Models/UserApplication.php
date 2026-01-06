<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApplication extends Model
{
    protected $fillable = [
        'company_name',
        'role_title',
        'job_url',
        'status',
        'applied_at',
    ]
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
