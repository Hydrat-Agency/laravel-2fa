<?php

namespace Hydrat\Laravel2FA\Models;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract;

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '2fa_login_attempts';

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'uid',
    ];

    /**
     * The user related to the attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Succeed the login attempt.
     *
     * @return void
     */
    public function succeed()
    {
        $this->succeed = true;
        $this->save();
    }

    /**
     * Create a new login attempt for the given user.
     *
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return static
     */
    public static function newForUser(TwoFactorAuthenticatableContract $user)
    {
        $ip  = $_SERVER['REMOTE_ADDR'];
        $url = 'https://freegeoip.app/json/' . $ip;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!($infos = json_decode($response))) {
            $infos = (object) [
                'country_name' => null,
                'country_code' => null,
                'region_name'  => null,
                'region_code'  => null,
                'city'         => null,
                'zip_code'     => null,
                'time_zone'    => null,
                'latitude'     => null,
                'longitude'    => null,
            ];
        }

        return static::create([
            'user_id'      => $user->id,
            'uid'          => Str::random(15),
            'ip'           => $ip,
            'country_name' => $infos->country_name,
            'country_code' => $infos->country_code,
            'region_name'  => $infos->region_name,
            'region_code'  => $infos->region_code,
            'city'         => $infos->city,
            'zip_code'     => $infos->zip_code,
            'time_zone'    => $infos->time_zone,
            'lat'          => $infos->latitude,
            'lng'          => $infos->longitude,
        ]);
    }
}
