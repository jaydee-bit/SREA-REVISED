<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ResponderPageController extends Controller
{
    public function index()
    {
        $admin = backpack_user();

        $query = User::where('role', 'responder')
            ->with(['responderProfile', 'incidentsAssigned' => function ($query) {
                $query->whereIn('status', ['Responding', 'Escalated']);
            }]);

        if (!$admin->is_super_admin) {
            $query->where('barangay', $admin->barangay);
        }

        $responders = $query->get();

        return view('admin.responders.index', compact('responders'));
    }
}