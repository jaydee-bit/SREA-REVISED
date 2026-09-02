<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ResponderPageController extends Controller
{
    public function index()
    {
        $responders = User::where('role', 'responder')
            ->with(['responderProfile', 'incidentsAssigned' => function ($query) {
                $query->whereIn('status', ['Responding', 'Escalated']);
            }])
            ->get();

        return view('admin.responders.index', compact('responders'));
    }
}