<?php


namespace App\Modules\User\Controllers;


use App\Http\Controllers\Controller;
use App\Modules\User\Models\SocialAccount;
use App\Modules\User\Models\UserModel as User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;


class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }



    public function handleProviderCallback($provider)
    {
        $userSocial = Socialite::driver($provider)->user();
        $user = $this->findOrCreateUser($provider, $userSocial);


        Auth::login($user, true);
        return redirect('/');
    }
    public function findOrCreateUser($provider, $socialiteUser)
    {

        if ($user = $this->findUserBySocialId($provider, $socialiteUser->getId())) {
            return $user;
        }

        if ($user = $this->findUserByEmail($provider, $socialiteUser->getEmail())) {
            $this->addSocialAccount($provider, $user, $socialiteUser);
            return $user;
        }
        $user = User::create([
                                 'login' => $socialiteUser->id,
                                 'email' => $socialiteUser->id,
                                 'password' => bcrypt(Str::random(25)),
                                 'salt' => Str::random(9),
                             ]);

        $this->addSocialAccount($provider, $user, $socialiteUser);

        return $user;
    }
    public function findUserBySocialId($provider, $id)
    {
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $id)
            ->first();

        return $socialAccount ? $socialAccount->user : false;
    }

    public function findUserByEmail($provider, $email)
    {
        return User::where('email', $email)->first();
    }

    public function addSocialAccount($provider, $user, $socialiteUser)
    {
        SocialAccount::create([
                                  'user_id' => $user->id,
                                  'provider' => $provider,
                                  'provider_id' => $socialiteUser->getId(),
                                  'token' => $socialiteUser->token,
                              ]);
    }
}
