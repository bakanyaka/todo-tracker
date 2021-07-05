<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    public function username(): string
    {
        return 'username';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request): array
    {
        // The array keys you set here are important.
        // The password key must be present, as this is sent directly to your LDAP server for verification.
        // The other key must be the name of the LDAP attribute you want LdapRecord to locate the authenticating user with.
        // It must be an attribute that has a unique value per user in your directory.
        return [
            'sAMAccountName' => $request->input('username'),
            'password' => $request->input('password'),
            'fallback' => [
                'username' => $request->input('username'),
                'password' => $request->input('password')
            ]
        ];
    }
}
