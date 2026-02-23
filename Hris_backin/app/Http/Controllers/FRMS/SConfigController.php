<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SConfigController extends Controller
{
    public function index()
    {
        return Inertia::render('FRMS/Maintenance/Sconfig/index');
    }
}
