<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use App\Http\Controllers\Controller;

class ShipmentWebController extends Controller
{
    public function create()
    {
        // dd("here");

        return view('bfrn.shipcreate');
        
    }

    public function store(Request $request)
    {
        dd("now sending");        
    } 
}