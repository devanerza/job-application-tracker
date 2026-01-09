<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'role_title',
        'job_url',
        'status',
        'applied_at',
    ];

    #[Scope]
    protected function filter(Builder $query): void
    {
        $query->when($filters['status'] ?? null, function ($query, $status){
            $query->where('status', $status);
        });
    }   
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
