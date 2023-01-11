<?php

namespace App\Libraries\Services\Core;

use Closure;
use Illuminate\Http\Request;
use App\Libraries\Services\{
    AuthService,
    UserService,
    Core\Exception as ServiceException
};
use Denagus\ArxService\Auth as AuthArxService;
use App\Models\User\User;

class Auth extends AuthArxService
{
    /**
     * Get auth user info
     *
     * @return mixed
     */
    public static function user()
    {
        if (self::currentRequestHas(self::REQUEST_AUTH_USER)) {
            return self::currentRequestGet(self::REQUEST_AUTH_USER);
        }

        $info = self::info();

        if (is_null($info)) :
            return null;
        endif;

        return User::find($info->user_id);
    }
}
