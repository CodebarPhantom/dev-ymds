<?php

namespace App\Services\Auth;

use Auth;

class AuthService
{
    public function loginUser(array $data)
    {
        // Check if credentials are correct
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            // Return a custom exception for unauthorized access
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get the authenticated user
        $user = Auth::user();
        // Create a personal access token for the authenticated user
        $tokenResult = $user->createToken($data['access_token_type'],$user->getPermissionsViaRoles()->pluck('name')->toArray(), now()->addMonth());
        $token = $tokenResult->plainTextToken;

        // Return token and other necessary information
        return [
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'user' => $user, // If you want to return the user details as well
        ];

    }

}
