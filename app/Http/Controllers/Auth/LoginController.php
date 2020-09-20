<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\User;
use Illuminate\Support\Str;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the provider authentication page.
     * nos va a dirigir a la pagina de google para darle permisos a la aplicación
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider and log in the user.
     * nos ayudara a guardar la información obtenida de google en nuestra base de datos
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        try{
            $user = Socialite::driver($provider)->user();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            abort(403, 'Unauthorized action.');
            return redirect()->to('/');
        }
        $attributes = [
            'provider' => $provider,
            'provider_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => isset($attributes['password']) ? $attributes['password'] : bcrypt(str_random(16))

        ];

        $user = User::where('provider_id', $user->getId() )->first();
        if (!$user){
            try{
                $user=  User::create($attributes);
            }catch (ValidationException $e){
              return redirect()->to('/auth/login');
            }
        }

        $this->guard()->login($user);
       return redirect()->to($this->redirectTo);

    }

}
