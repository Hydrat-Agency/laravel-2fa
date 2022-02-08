<?php

namespace Hydrat\Laravel2FA\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Token extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '2fa_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The user the 2FA Token belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get expired codes only.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
   
    /**
     * Get unexpired codes only.
     */
    public function scopeUnexpired($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
