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
            throw new \RuntimeException('File query Excel tidak ditemukan di server: ' . $file);
        }

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet()->toArray();

        $queries = [];

        foreach ($sheet as $i => $row) {
            if ($i == 0) continue;

            $queries[] = [
                'label' => $row[0],
                'query' => $row[1]
            ];
        }

        return $queries;
    }

    public function index()
    {
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
        if (!$this->request->is('post')) {
            return redirect()->to('/')
                ->with('error', 'Jangan akses rute ini (/runquery) secara langsung.');
        }

        $idx = $this->request->getPost('q');

        if ($idx === null || $idx === '') {
            return redirect()->to('/')
                ->with('error', 'Jangan akses rute ini (/runquery) bila tidak mengirimkan parameter apa-apa.');
        }

        $queries = $this->getQueries();
        $idx = (int) $idx;

        if (!isset($queries[$idx])) {
            return redirect()->to('/')
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
            return redirect()->to('/')
                ->with('error', 'Terjadi kesalahan saat menjalankan query.');
        }
    }

}
