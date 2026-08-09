<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockResponders;

class ResponderPageController extends Controller
{
    public function index()
    {
        $responders = MockResponders::all();

        return view('admin.responders.index', compact('responders'));
    }
}