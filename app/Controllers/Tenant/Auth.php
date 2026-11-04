<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        // Jika sudah login, langsung lempar ke dashboard
        if (session()->get('logged_in_tenant')) {
            return redirect()->to('/tenant/dashboard');
        }
        return view('tenant/auth/login');
    }

    public function prosesLogin()
    {
        $session = session();
        $db = \Config\Database::connect();
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $tenant = $db->table('tenants')->where('username', $username)->get()->getRowArray();

        if ($tenant) {
            // Verifikasi password (mendukung password_hash maupun teks biasa untuk tes)
            if (password_verify($password, $tenant['password']) || $password === $tenant['password']) {
                
                $session->set([
                    'tenant_id'        => $tenant['id'],
                    'nama_tenant'      => $tenant['nama_tenant'],
                    'logged_in_tenant' => true
                ]);

                return redirect()->to('/tenant/dashboard');
            }
        }

        return redirect()->back()->with('error', 'Username atau Password salah!');
    }

    public function logout()
    {
        session()->remove(['tenant_id', 'nama_tenant', 'logged_in_tenant']);
        return redirect()->to('/tenant/login');
    }
}