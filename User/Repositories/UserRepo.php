<?php

namespace App\Modules\User\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepo
{
    public  function newUser(Request $request){
        $salt = Str::random('10');
        $password = Hash::make($request['password'].$salt);
        $new_user = DB::insert('INSERT INTO users (user_login, user_password, user_salt) values (?,?,?)',[$request['user_login'], $password, $salt]);
        return $new_user;
    }
}
