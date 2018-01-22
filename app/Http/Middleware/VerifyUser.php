<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class VerifyUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      if ((Auth::user() -> status) != '1')
        return redirect('/login')->with('msg-warning', 'Anda Tidak Bisa Mengakses Halaman Ini Sebelum Terverifikasi');

      return $next($request);
    }
}
