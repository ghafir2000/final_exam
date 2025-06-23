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
        dd($locale, config('app.available_locales')); // Debug line 1
        if (array_key_exists($locale, config('app.available_locales'))) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            dd(App::getLocale(), Session::get('locale')); // Debug line 2
        }
        return Redirect::back();
    }
    
}
