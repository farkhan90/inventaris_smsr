<?php

namespace App\Http\Middleware;

use App\Models\User; // Jangan lupa import User model
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === User::ROLE_ADMIN) {
            return $next($request);
        }

        abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI');
    }
}
