<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Home extends BaseController
{
    private function getQueries()
    {
        // $file = 'C:/Users/ACER/Downloads/listquery.xlsx';
        $file = (string) (env('app.queryFilePath') ?? getenv('app.queryFilePath') ?? $_ENV['app.queryFilePath'] ?? '');

        $file = trim($file, " \t\n\r\0\x0B'\"");

        if ($file === '') {
            throw new \RuntimeException('Path file query belum diatur di settings.');
        }

        // if (!is_file($file)) {
        //     throw new \RuntimeException('File query Excel tidak ditemukan di server.');
        // }

        if (!is_file($file)) {
            throw new \RuntimeException('File query tidak ditemukan di server: ' . $file);
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $queries = [];

        if ($ext === 'csv') {
            if (($handle = fopen($file, 'r')) !== false) {
                $isHeader = true;
                while (($data = fgetcsv($handle)) !== false) {
                    if ($isHeader) {
                        $isHeader = false;
                        continue;
                    }
                    if (!empty($data[0]) && isset($data[1])) {
                        $queries[] = [
                            'label' => trim($data[0]),
                            'query' => trim($data[1]),
                        ];
                    }
                }
                fclose($handle);
            }
        } else {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheet as $i => $row) {
                if ($i == 0) continue;
                if (!empty($row[0]) && isset($row[1])) {
                    $queries[] = [
                        'label' => trim((string) $row[0]),
                        'query' => trim((string) $row[1]),
                    ];
                }
            }
        }

        return $queries;
    }

    public function index()
    {
        $licenseMgr = new \App\Libraries\LicenseManager();
        $licenseStatus = $licenseMgr->verify();

        if (empty($licenseStatus['valid'])) {
            $submitted = session()->getFlashdata('submitted');
            $errorMessage = session()->getFlashdata('license_error');

            if (!$errorMessage && $submitted) {
                $errorMessage = $licenseStatus['message'] ?? 'Lisensi belum aktif.';
            }

            return response()->setStatusCode(403)->setBody(view('license_locked', [
                'message'      => $errorMessage,
                'satker'       => (string) env('app.licenseSatker', ''),
                'token'        => (string) env('app.licenseToken', ''),
                'hwid'         => $licenseMgr->getHardwareId(),
            ]));
        }

        try {
            $data['queries'] = $this->getQueries();
        } catch (\Throwable $e) {
            $data['queries'] = [];
            $data['error'] = $e->getMessage();
        }

        $data['error']   = $data['error'] ?? session()->getFlashdata('error');
        $data['success'] = session()->getFlashdata('success');

        return view('welcome_message', $data);
    }

    public function runquery()
    {
        $licenseMgr = new \App\Libraries\LicenseManager();
        $licenseStatus = $licenseMgr->verify();

        if (empty($licenseStatus['valid'])) {
            return response()->setStatusCode(403)->setBody(view('license_locked', [
                'message' => $licenseStatus['message'] ?? 'Lisensi belum aktif.',
            ]));
        }

        if (!$this->request->is('post')) {
            return redirect()->to(site_url(''))
                ->with('error', 'Jangan akses rute ini (/runquery) secara langsung.');
        }

        $idx = $this->request->getPost('q');

        if ($idx === null || $idx === '') {
            return redirect()->to(site_url(''))
                ->with('error', 'Jangan akses rute ini (/runquery) bila tidak mengirimkan parameter apa-apa.');
        }

        $queries = $this->getQueries();
        $idx = (int) $idx;

        if (!isset($queries[$idx])) {
            return redirect()->to(site_url(''))
                ->with('error', 'Query yang dipilih tidak valid.');
        }

        try {
            $db = \Config\Database::connect();
            $sql = $queries[$idx]['query'];
            $query = $db->query($sql);

            $data['queries']  = $queries;
            $data['selected'] = $idx;
            $data['judul']    = $queries[$idx]['label'];
            $data['sql']      = $sql;
            $data['hasil']    = $query->getResult();
            $data['error']    = session()->getFlashdata('error');
            $data['success']  = session()->getFlashdata('success');

            return view('welcome_message', $data);
        } catch (\Throwable $e) {
            return redirect()->to(site_url(''))
                ->with('error', 'Terjadi kesalahan saat menjalankan query.');
        }
    }

    public function activateLicense()
    {
        $satker = trim((string) $this->request->getPost('satker'));
        $token  = trim((string) $this->request->getPost('token'));

        if ($satker === '' || $token === '') {
            return redirect()->to(site_url(''))
                ->with('license_error', 'Kode Satker dan Token Lisensi wajib diisi.');
        }

        $envPath = ROOTPATH . '.env';
        if (!is_file($envPath)) {
            return redirect()->to(site_url(''))
                ->with('license_error', 'File .env tidak ditemukan.');
        }

        $envContent = (string) file_get_contents($envPath);

        // Update atau tambahkan app.licenseSatker & app.licenseToken di .env
        $updates = [
            'app.licenseSatker' => $satker,
            'app.licenseToken'  => $token,
        ];

        foreach ($updates as $key => $value) {
            $escapedKey = preg_quote($key, '/');
            $escapedVal = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
            $quotedVal  = "'{$escapedVal}'";
            $pattern    = "/^\\s*#?\\s*{$escapedKey}\\s*=.*$/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key} = {$quotedVal}", $envContent, 1);
            } else {
                $envContent = rtrim($envContent) . PHP_EOL . "{$key} = {$quotedVal}" . PHP_EOL;
            }
        }

        file_put_contents($envPath, $envContent);

        // Hapus cache lisensi lama agar langsung memverifikasi token baru
        $cacheFile = WRITEPATH . 'cache/license_cache.json';
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }

        return redirect()->to(site_url(''))->with('submitted', true);
    }

}
