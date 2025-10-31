<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password as PasswordFacade;
use App\Traits\ApiResponse;

class ApiAuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
       $val =  $request->validated();

        $user = User::create([
            'name' => $val['name'],
            'email' => $val['email'],
            'password' => Hash::make($val['password']),
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

      
        return $this->success(['user' => $user,'token' => $token,'email_verified' => false] ,  'تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني.' ,  201);
        
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = PasswordFacade::sendResetLink(
            $request->only('email')
        );

        if ($status === PasswordFacade::RESET_LINK_SENT) {
            return $this->success(
                [],
                'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.',
                200
            );
        }

        return $this->success(
            [],
            'إذا كان البريد الإلكتروني موجوداً، سيتم إرسال رابط إعادة التعيين.',
            200
        );
    }

        public function forgotPasswordConfirm(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('لم يتم العثور على مستخدم بهذا البريد الإلكتروني.' , [] , 404);
        }

        $isValidToken = app('auth.password.broker')->tokenExists($user, $request->token);

        if (!$isValidToken) {
            return $this->error('رمز إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.' , [] , 400);
        }

        return $this->success([],'رمز إعادة تعيين كلمة المرور صالح.' , 200);
    }

    
 
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = app('auth.password.broker')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->tokens()->delete();
                 
                
            }
        );


        if ($status == app('auth.password.broker')::PASSWORD_RESET) {
            return $this->success([],'تم إعادة تعيين كلمة المرور بنجاح.' , 200);
        } else {
            return $this->error('فشل في إعادة تعيين كلمة المرور.' , ['status' => __($status)] , 500);
        }
    }



     


    public function login(LoginRequest $request)
    {
        $val = $request->validated();

        $user = User::where('email', $val['email'] )->first();

        if (!$user || !Hash::check($val['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البيانات المدخلة غير صحيحة.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success(['user' => $user, 'token' => $token,'email_verified' => $user->hasVerifiedEmail()], 'تم تسجيل الدخول بنجاح', 200);
        
    }



    public function user(Request $request)
    {
       
        return $this->success(["user"=>$request->user() , 'email_verified' => $request->user()->hasVerifiedEmail()], 'تم جلب بيانات المستخدم بنجاح', 200);

    }




    public function sendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
          
             return $this->success([],'البريد الإلكتروني مؤكد بالفعل.' , 400);    
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success([],'تم إرسال رابط التحقق إلى بريدك الإلكتروني.' , 200);
    }



    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
          
            return $this->error('رابط التحقق غير صالح.' , [] , 403);
        }

        if ($user->hasVerifiedEmail()) {
           
            return $this->success([],'البريد الإلكتروني مؤكد بالفعل.' , 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

       
        return $this->success([],'تم تأكيد البريد الإلكتروني بنجاح!', 200);
    }

    

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $request->user()->tokens()->where('id', $token->id)->delete();
        }
        return $this->success([],'تم تسجيل الخروج بنجاح',200);
    }
}