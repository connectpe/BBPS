<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchemeController extends Controller
{
    public function index()
    {
        return view('scheme.index');
    }
}
