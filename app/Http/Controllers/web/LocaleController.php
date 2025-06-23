<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LocaleController extends Controller
{
    
    public function setLocale(string $locale)
    {
        // Ensure the locale is available in the configuration
        if (array_key_exists($locale, config('app.available_locales'))) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        // Redirect back to the previous page or to the application's base URL
        return Redirect::to(url()->previous() ?? config('app.url'));
    }
    
}
