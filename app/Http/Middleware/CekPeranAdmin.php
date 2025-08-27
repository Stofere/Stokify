<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeranAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user sudah login DAN perannya adalah 'admin'
        if ($request->user() && $request->user()->peran === 'admin') {
            return $next($request); // Lanjutkan ke halaman yang dituju
        }

        // Jika tidak, tendang ke halaman dashboard dengan pesan error
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki hak akses.');
    }
}