<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ErrorController extends Controller
{
    public function notFound()
    {
        return redirect()->to('/')->with('error', 'Halaman yang Anda akses tidak ditemukan.');
    }
}
