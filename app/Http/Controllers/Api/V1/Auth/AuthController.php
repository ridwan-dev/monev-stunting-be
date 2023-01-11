<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\UserResource;
use App\Models\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;
use App\Models\Sys\User;
use App\Models\Sys\PasswordReset;
use Validator;
use Illuminate\Support\Facades\Password;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(new JsonResponse([], 'login_error'), Response::HTTP_UNAUTHORIZED);
        }

        $user = $request->user();
        $user->token = $user->createToken('KGMTagging', [])->plainTextToken;

        $user->getPermissionsViaRoles();

        return response()->json(new JsonResponse(new UserResource($user)), Response::HTTP_OK);
    }

    public function ping(Request $request){
        return (Auth::check())?'ping':'pong';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = request()->user();
        if (request()->token_id) {
            $user->tokens()->where('id', request()->token_id)->delete();
        }
             
        $user->tokens()->delete();

        Auth::guard('web')->logout();
        return response()->json((new JsonResponse())->success([]), Response::HTTP_OK);
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $userid = request()->user()->id;
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $message = array(
            'old_password.required' => 'Password lama anda wajib diisi',
            'new_password.required' => 'Password baru anda wajib diisi',
            'confirm_password.required' => 'Konfirmasi password baru wajib diisi',
            'confirm_password.same' => 'Konfirmasi password baru tidak sama',
        );
        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            return $this->returnJsonError($validator->errors()->first(), 400);
        } else {
            try {
                if ((\Hash::check(request('old_password'), Auth::user()->password)) == false) {
                    return $this->returnJsonError("Silahkan cek password lama anda.", 400);
                } else if ((\Hash::check(request('new_password'), Auth::user()->password)) == true) {
                    return $this->returnJsonError("Silahkan masukan password baru yang berbeda dengan password anda saat ini.", 400);
                } else {
                    User::where('id', $userid)->update(['password' => \Hash::make($input['new_password'])]);
                    return $this->returnJsonSuccess("Password updated successfully.", array());
                }
            } catch (\Exception $ex) {
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                } else {
                    $msg = $ex->getMessage();
                }
                return $this->returnJsonError($msg, 400);
            }
        }
    }

    public function forgotPassword(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'email' => "required|email",
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->returnJsonError($validator->errors()->first(), 400);
        } else {
            $token = \Str::random(64);
    
            \DB::table('password_resets')->insert([
                'email' => $request->email, 
                'token' => $token, 
                'created_at' => \Carbon::now()
            ]);
    
            $details = [
                'title' => 'Reset Password',
                'body' => 'Silahkan klik link dibawah untuk reset password.',
                'token' => $token
            ];
           
            \Mail::to($request->email)->send(new \App\Mail\ForgotPasswordMail($details));
    
            return $this->returnJsonSuccess("Email reset password telah dikirim.", array());
        }
    }

    public function resetPassword(Request $request){
        $input = $request->all();

        $rules = array(
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
            'token' => 'required'
        );

        $message = array(
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email harus dalam format yang benar',
            'email.exist' => 'Email tidak ditemukan dengan user manapun',
            'password.required' => 'Password harus diisi',
            'password.confirm' => 'Konfirmasi password tidak sama',
            'password_confirmation.required' => 'Konfirmasi password harus diisi',
            'token.required' => 'Token harus diisi'
        );

        $validator = Validator::make($input, $rules, $message);

        if ($validator->fails()) {
            return $this->returnJsonError($validator->errors()->first(), 400);
        } else {
            $updatePassword = \DB::table('password_resets')
                                ->where([
                                    'email' => $request->email, 
                                    'token' => $request->token
                                ])
                                ->first();

            if(!$updatePassword){
                return $this->returnJsonError('Token tidak valid', 403);
            }

            $user = User::where('email', $request->email)
                        ->update(['password' => \Hash::make($request->password)]);

            \DB::table('password_resets')->where(['email'=> $request->email])->delete();

            return $this->returnJsonSuccess('Password anda telah berhasil diubah', array());
        }
    }

    public function emailConfirmation(Request $request){
        $input = $request->all();

        $rules = array(
            'email' => 'required|email|exists:users',
            'token' => 'required'
        );

        $message = array(
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email harus dalam format yang benar',
            'email.exist' => 'Email tidak ditemukan dengan user manapun',
            'token.required' => 'Token harus diisi'
        );

        $validator = Validator::make($input, $rules, $message);

        if ($validator->fails()) {
            return $this->returnJsonError($validator->errors()->first(), 400);
        } else {
            $id = \UrlHash::decodeId('cirgobanggocir', $request->token, 150);
            $user = User::where([
                'email' => $request->email,
                'id'    => $id
            ])->first();

            if(empty($user)){
                return $this->returnJsonError('Token tidak valid atau email tidak ditemukan', 403);
            }

            $user = User::where([
                            'email' => $request->email,
                            'id'    => $id
                        ])
                        ->update(['status_verifikasi_email' => 1]);

            return $this->returnJsonSuccess('Email anda telah berhasil dikonfirmasi', array());
        }
    }

}
