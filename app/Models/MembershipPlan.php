<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'application',
        'description',
        'price',
        'duration_days',
        'status'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}