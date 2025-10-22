<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redirect users to their appropriate dashboard based on role
     */
    public function index()
    {
        $user = auth()->user();

        // Admin - goes to manager dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('manager.index');
        }

        // Manager - goes to manager dashboard
        if ($user->hasRole('manager')) {
            return redirect()->route('manager.index');
        }

        // Supervisor - goes to manager dashboard
        if ($user->hasRole('supervisor')) {
            return redirect()->route('manager.index');
        }

        // Front Desk - goes to front desk
        if ($user->hasRole('front_desk')) {
            return redirect()->route('frontdesk.index');
        }

        // Technician - goes to technician dashboard
        if ($user->hasRole('technician')) {
            return redirect()->route('technician.index');
        }

        // Client - goes to bookings
        if ($user->hasRole('client')) {
            return redirect()->route('bookings.index');
        }

        // Default fallback
        return redirect()->route('home');
    }
}
