<?php


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Modules\User\Middlewares\Authenticated;
use App\Modules\User\Middlewares\NotAuthenticated;


Route::middleware([Authenticated::class])->namespace('App\Modules\User\Controllers')->group(function(){
    Route::match(['GET','POST'],'/login', 'UserController@login')->name('user.login');
    Route::match(['GET','POST'],'/register', 'UserController@register')->name('user.register');
    Route::match(['GET','POST'],'/forgot','UserController@forgot')->name('user.forgot');
    Route::match(['GET','POST'],'/verify', 'UserController@verify')->name('verification.verify');



    Route::get('/social-auth/{provider}','SocialController@redirectToProvider')->name('user.social');
    Route::get('/social-auth/{provider}/callback', 'SocialController@handleProviderCallback')->name('user.social.callback');
});
Route::namespace('App\Modules\User\Controllers')->group(function(){
    Route::match(['GET','POST'],'/verify{id}/{hash}', 'UserController@verify')->name('user.verify');
});
Route::middleware([NotAuthenticated::class])->namespace('App\Modules\User\Controllers')->group(function(){

    Route::get('/logout', 'UserController@logout')->name('user.logout');
});
