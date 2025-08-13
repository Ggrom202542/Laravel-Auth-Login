<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type = null): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('login');
        }

        if ($type && $user->user_type !== $type) {
            // Redirect ไปยังหน้าที่เหมาะสมตาม user_type
            switch ($user->user_type) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'super_admin':
                    return redirect()->route('super_admin.dashboard');
                case 'user':
                    return redirect()->route('home');
                default:
                    return redirect('home');
            }
        }
        return $next($request);
    }
}
