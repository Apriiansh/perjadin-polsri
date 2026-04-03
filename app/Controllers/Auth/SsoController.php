<?php

namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

class SsoController extends Controller
{
    /**
     * Endpoint for Perjadin to jump to PolsriPay.
     * Accessible by logged in users.
     */
    public function toPolsripay()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(config('Auth')->views['login'])->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        $identifier = $user->email ?? $user->username ?? null;

        if (!$identifier) {
            return redirect()->back()->with('error', 'Identitas akun tidak ditemukan untuk SSO.');
        }

        // We use PolsriPay's DB connection for the tokens
        $db = db_connect('polsripay');
        $token = bin2hex(random_bytes(32));

        // Save token to DB
        $db->table('sso_tokens')->insert([
            'token'      => $token,
            'identifier' => $identifier,
            'target_app' => 'polsripay',
            'created_at' => Time::now()->toDateTimeString(),
            'expires_at' => Time::now()->addSeconds(60)->toDateTimeString(),
        ]);

        // Redirect to PolsriPay's consume URL
        // Karena Perjadin di /perjadin, Polsripay ada di root directory ( / )
        $host = parse_url(base_url(), PHP_URL_SCHEME) . '://' . parse_url(base_url(), PHP_URL_HOST);
        return redirect()->to($host . '/sso/consume?token=' . $token);
    }

    /**
     * Endpoint for Perjadin to consume a token sent by PolsriPay.
     */
    public function consume()
    {
        $token = $this->request->getGet('token');
        
        if (!$token) {
            return redirect()->to(config('Auth')->views['login'])->with('error', 'Token SSO tidak ditemukan.');
        }

        $db = db_connect('polsripay');
        
        // Find the token
        $ssoRecord = $db->table('sso_tokens')
            ->where('token', $token)
            ->where('target_app', 'perjadin')
            ->where('expires_at >=', Time::now()->toDateTimeString())
            ->get()
            ->getRow();

        if (!$ssoRecord) {
            return redirect()->to(config('Auth')->views['login'])->with('error', 'Sesi SSO Anda tidak valid atau sudah kadaluarsa. Silakan coba lagi.');
        }

        // Delete token immediately to prevent replay attacks
        $db->table('sso_tokens')->where('id', $ssoRecord->id)->delete();

        // Log in using Shield
        $identifier = $ssoRecord->identifier;
        $userProvider = auth()->getProvider(); // Shield UserModel
        
        // Use Shield's findByCredentials instead of manual where() to avoid 'Unknown column email' error
        $perjadinUser = $userProvider->findByCredentials(['email' => $identifier]);
        
        if (!$perjadinUser) {
            $perjadinUser = $userProvider->findByCredentials(['username' => $identifier]);
        }

        if (!$perjadinUser) {
            return redirect()->to(config('Auth')->views['login'])->with('error', 'Sayangnya, Akun Anda sah di PolsriPay tapi belum didaftarkan di dalam Perjadin. Harap lapor Admin.');
        }

        // Penting: Logout dulu untuk membersihkan sesi lama agar tidak muncul error "Already logged in"
        auth()->logout();

        // Login menggunakan Shield
        auth('session')->login($perjadinUser);

        /** @var \CodeIgniter\Shield\Authentication\Authenticators\Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show');
        }

        return redirect()->to(config('Auth')->loginRedirect());
    }
}
