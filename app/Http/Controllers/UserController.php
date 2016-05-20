<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use App\App;
use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\Helpers\DevlessHelper as DLH;

class UserController extends Controller
{
  // TODO: Session store needs to authenticate with a session table for security
  public function getLogin()
  {
    if(\Session::has('user_id'))
    {
      // DB::table('users')->where('id', \Session::get('user_id'))->first();
      return redirect('/services');
    } else {
      return view('auth.index');
    }
  }

  public function postLogin(Request $request)
  {
    $loginCredentials = array(
      'email' => $request->input('email'),
      'password' => $request->input('password')
    );

    $user = DB::table('users')->where('email', $request->input('email'))->first();
    if (Hash::check($request->input('password'), $user->password))
    {
      $request->session()->put('user_id', $user->id);
      DLH::flash('You are logged in', 'success');
      return redirect('services');
    } else {
      DLH::flash('Incorrect login credentials', 'error');
      return back();
    }
  }

  public function getLogout()
  {
    \Session::forget('user_id');
    \Session::flush();
    return redirect('/');
  }

  public function getRegister()
  {
    $app = array(
      'app_key' => $_SERVER['SERVER_NAME'],
      'app_token' => md5(uniqid(1, true))
     );
    return view('auth.create', compact('app'));
  }

  public function postRegister(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:255',
      'email' => 'required|email|max:255|unique:users',
      'password' => 'required|confirmed|min:6',
      'password_confirmation' => 'required|min:6',
      'app_name' => 'required|max:255',
      'app_description' => 'required|max:255',
    ]);

    $user = new User;
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->password = bcrypt($request->input('password'));

    $app = new App;
    $app->name = $request->input('app_name');
    $app->description = $request->input('app_description');
    $app->api_key = $request->input('app_key');
    $app->token = $request->input('app_token');

    if ($user->save() && $app->save()) {
      $request->session()->put('user_id', $user->id);
      DLH::flash('Setup successful. Welcome to Devless', 'success');
      return redirect('services');
    }

    return back()->withInput();
    DLH::flash('Error setting up', 'error');
  }
}