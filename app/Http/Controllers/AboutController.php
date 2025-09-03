<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Muestra la página de "Nosotros"
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
      
        
        return view('about');
    }
}