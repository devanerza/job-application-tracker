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
    protected function filterSort(Builder $query, array $filters): void
    {
        $query->when($filters['status'] ?? null, function ($query, $status){
            $query->where('status', $status);
        });

        $sort = $filters['sort'] ?? 'last_activity_at';
        $direction = $filters['direction'] ?? 'desc';
        
        $allowedSorts = ['last_activity_at', 'follow_up_at', 'applied_at'];

        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
    }   
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
