<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Socialite;
use Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;

use App\Mail\UserRegistered;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        //arahkan ke halaman pemberitahuan untuk melihat email
        return redirect('/login');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'token' => str_random(20),
            'password' => bcrypt($data['password']),
        ]);

        //mengirim email imagesetbrush
        Mail::to($user -> email) -> send(new UserRegistered($user));

    }

    public function verify($token, $id){
      $user = User::findOrFail($id);
      if($token == $user -> token){
        if($user -> status != '1')
            $user -> update([
                'status' => 1
            ]);
        else dd('Anda Sudah Melakukan Verify');
      } else dd('Token Anda Salah');


      return redirect('/login');
    }

    //funday add
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->User();
        } catch (Exception $e) {
            return redirect('/social/login/facebook');
        }

        $authUser = $this->findOrCreateUser($user,$provider);

        Auth::login($authUser, true);

        return redirect()->route('home');
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $provider
     * @return User
     */
    private function findOrCreateUser($provideruser,$providername)
    {
        $authUser = User::where('email', $provideruser->getEmail())->first();

        if ($authUser){
            return $authUser;
        }

        return User::create([
            'name' => $provideruser->name,
            'email' => $provideruser->getEmail(),
            'social_id' => $provideruser->getId(),
	    'social_provider' => $providername,
            'avatar' => $provideruser->getAvatar(),
            'password' => md5(rand(1,10000)),
            'token' => str_random(20),
            'status' => '1',
        ]);
    }
}
