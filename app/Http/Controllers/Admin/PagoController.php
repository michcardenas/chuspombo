<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyPayment;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = PropertyPayment::orderByDesc('created_at')->paginate(20);
        return view('admin.pagos.index', compact('pagos'));
    }
}
