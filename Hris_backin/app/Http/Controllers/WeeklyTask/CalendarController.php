<?php

namespace App\Http\Controllers\WeeklyTask;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('WeeklyTask/Calendar/index');
    }
}
