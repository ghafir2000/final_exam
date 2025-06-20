<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
        $locale = Session::get('locale');
        // dd('Middleware: Session locale is', $locale, config('app.available_locales')); // For debugging
        if (array_key_exists($locale, config('app.available_locales', []))) {
            App::setLocale($locale);
            // dd('Middleware: App locale SET to', App::getLocale()); // For debugging
        }
    } else {
        // dd('Middleware: No locale in session, using default:', App::getLocale()); // For debugging
    }
    return $next($request);
    }
}
