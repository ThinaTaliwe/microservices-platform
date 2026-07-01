<?php

namespace App\Http\Controllers\Bfrn;

use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    public function index()
    {
        return view('bfrn.operations.addresses.index');
    }
}
