<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Get user profile data for the widget
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'data' => $user->getProfileData(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
    
    /**
     * Manual refresh endpoint
     */
    public function refresh()
    {
        $user = Auth::user();
        $user->broadcastProfileUpdate();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile data refreshed',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
