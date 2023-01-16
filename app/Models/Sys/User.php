<?php
namespace App\Models\Sys;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Hash;

/**
 * Class User
 *
 * @package App
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
*/
class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use HasFactory;
    use HasApiTokens;

    protected $guard_name = 'web';

    protected $hidden = ['password', 'id', 'remember_token'];

    protected $fillable = ['name', 'email', 'password', 'remember_token'];
    
    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return \UrlHash::encodeId('cirgobanggocir', $this->attributes['id'], 50);
    }
    
    /**
     * Hash password
     * @param $input
     */
    public function setPasswordAttribute($input)
    {
        if ($input)
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
    }
    
    
    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
}
