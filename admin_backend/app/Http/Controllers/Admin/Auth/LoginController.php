<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->guard('backpack')->attempt($credentials)) {
            if (auth()->guard('backpack')->user()->role !== 'admin') {
                auth()->guard('backpack')->logout();
                return back()->withErrors(['email' => 'Access restricted to admin accounts only.']);
            }
            return redirect()->intended(backpack_url('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }
}