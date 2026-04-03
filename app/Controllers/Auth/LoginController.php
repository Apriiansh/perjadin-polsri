<?php

namespace App\Controllers\Auth;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

class LoginController extends ShieldLoginController
{
    /**
     * Attempts to log the user in via Hybrid Auth:
     * 1. Checks PolsriPay database first.
     * 2. If valid, force logs in the associated Perjadin user (by email/username).
     * 3. If invalid or not found, fallbacks to Shield's default session login (Perjadin local DB).
     */
    public function loginAction(): RedirectResponse
    {
        // 1. Initial validation using Shield's built-in rules
        $rules = $this->getValidationRules();

        if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        /** @var array $credentials */
        $credentials             = $this->request->getPost(setting('Auth.validFields')) ?? [];
        $credentials             = array_filter($credentials);
        $password                = $this->request->getPost('password');
        $credentials['password'] = $password;
        $remember                = (bool) $this->request->getPost('remember');

        // Extract identifier which could be either 'email' or 'username'
        $identifier = $credentials['email'] ?? $credentials['username'] ?? null;

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if ($identifier && $password) {
            // 2. Hybrid Auth Logic: Check PolsriPay database first
            $dbPolsri = \Config\Database::connect('polsripay');
            
            // Assume Polsripay users table uses standard 'email' or 'username' field
            $polsripayUser = $dbPolsri->table('users')
                ->where('email', $identifier)
                ->orWhere('username', $identifier)
                ->get()
                ->getRow();

            // Detect hash column name (usually 'password_hash' in CI4, or 'password')
            $hash = $polsripayUser->password_hash ?? $polsripayUser->password ?? '';

            if ($polsripayUser && password_verify($password, $hash)) {
                // PolsriPay password is correct!
                
                // 3. Find this user in Perjadin's Shield Auth Provider
                $userProvider = auth()->getProvider(); // Shield UserModel
                
                // Use Shield's findByCredentials which correctly handles email/username in auth_identities
                $perjadinUser = $userProvider->findByCredentials(['email' => $identifier]) 
                               ?? $userProvider->findByCredentials(['username' => $identifier]);
                
                if ($perjadinUser) {
                    // Success! They exist in Perjadin. Force login programmatically
                    $authenticator->remember($remember)->login($perjadinUser);

                    // If an action has been defined for login, start it up.
                    if ($authenticator->hasAction()) {
                        return redirect()->route('auth-action-show')->withCookies();
                    }

                    return redirect()->to(config('Auth')->loginRedirect())->withCookies();
                } else {
                    // Password correct BUT no Perjadin local account!
                    return redirect()->route('login')->withInput()->with('error', 'Akun PolsriPay Anda valid, namun belum diregistrasikan di Sistem Perjadin. Silakan hubungi Admin Perjadin.');
                }
            }
        }

        // 4. Fallback: If not found in Polsripay OR incorrect password there, try locally (Shield default)
        // Attempt to login using Perjadin's local auth identities natively
        $result = $authenticator->remember($remember)->attempt($credentials);
        
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show')->withCookies();
        }

        return redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }
}
