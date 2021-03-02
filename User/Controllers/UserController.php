<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\UserModel as User;
use App\Modules\User\Models\UsersHashes;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Modules\User\Repositories\UserRepo;
use Illuminate\Support\Str;


class UserController extends Controller
{
    protected $repo;
    public function __construct(Request $request)
    {
        $this->repo = new UserRepo();
    }

    public function login(Request $request)
    {
        if ($request->all())
        {
            $validator = Validator::make( $request->all(),[
                'login' => 'required',
                'password'=>'required',],
                                  ['required'=> 'Поле :attribute обязательно для заполнения',],
                                  ['login' => 'Логин', 'password'=>'Пароль']);
            if ($validator->fails())
            {
                return response()->json($validator->errors());
            }


            $user = User::where('login',$request->login)->first();
            if (!$user){
                $user = User::where('email',$request->login)->first();
                if (!$user){
                    return response()->json(["Пользователь с таким логином не найден в системе"]);
                }
            }



            if(Hash::check(request()->password.$user->salt, $user->password))
            {
                if (request()->remember){
                    $remember = true;
                }
                else{
                    $remember = false;
                }

                Auth::login($user, $remember);

                $request->session()->regenerate();

                if (!$user->active){
                    return response()->json(['verify'=>'Вы должны подтвердить вашу электронную почту!']);
                }
                return redirect()->to('/');
            }
            else{
                return response()->json("Введенные данные не верны");
            }
        }

    return view('User::login');
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function register(Request $request)
    {

        if ($request->all()){

            $validator = Validator::make( $request->all(),[
                'login' => 'required|unique:users|max:25',
                'email' => 'required|email|unique:users|max:100',
                'password'=>'required|max:16',
                'password_confirm'=>'required|max:16',],
                  [
                      'required'=> 'Поле :attribute обязательно для заполнения',
                      'unique'=> 'Такой :attribute уже зарегистрирован в системе',
                      'max'=> 'Такой :attribute уже зарегистрирован в системе',
                      'email'=> 'Проверьте корректность :attribute',
                  ],
                    [
                        'login' => 'Логин', 'password'=>'Пароль', 'email'=> 'Email', 'password_confirm'=>'Подтвердите пароль'
                    ]);
            if ($validator->fails())
            {
                return response()->json($validator->errors());
            }
            else{
                if ($request['password'] !== $request['password_confirm'])
                {
                    return response()->json(['Введенные пароли не совпадают']);
                }
                else{
                    $salt = Str::random('10');

                    $user = new User;
                    $user->login = request()->login;
                    $user->email = request()->email;
                    $user->password = Hash::make(request()->password.$salt);
                    $user->salt = $salt;
                    $user->save();
//                    Auth::login($user);
//                    $request->session()->regenerate();
                    event(new Registered($user));

                    return redirect()->to('/');
                }
            }
        }
        return view('User::register');
    }

    public function forgot(Request $request)
    {
        return view('User::forgot');

    }
    public function verify(Request $request)
    {
        $user = User::where('id', request()->id)->firstOrFail();

        if ($user->active){
            return response()->json(['Ваш email уже подтвержден.']);
        }

        if ($user->getKey() != $user->id) {
            return response()->json(['Ошибка проверки данных, запросите код верификации еще раз'], 401);
        }

        $hash_check = UsersHashes::where('user_id', $user->id)->first();
        if (!$hash_check or $hash_check->data != request()->hash){
            return response()->json(['Контрольная сумма не прошла проверку, запросите код верификации еще раз'], 401);
        }

        $user->active = true;
        $user->email_verified_at = Carbon::now();
        $user->save();
        $hash_check->delete();
        return redirect()->to('/');
    }
}
