<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pagina = Pagina::with('meta')->first() ?? new Pagina();

        return view('admin.dashboard', compact('pagina'));
    }
}