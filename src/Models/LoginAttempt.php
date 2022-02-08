<?php

namespace Hydrat\Laravel2FA\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        //
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        //
    ];

    /**
     * The user related to the attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * the scope
     */
    // public function scopeSuperAdmin($query)
    // {
    //     return $query->where('role', '>=', 20);
    // }
}
