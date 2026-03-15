<?php

namespace App\Controllers;

class SettingsController extends BaseController
{
    private string $envPath;

    public function __construct()
    {
        $this->envPath = ROOTPATH . '.env';
    }

    private function rejectInvalidAccess()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/settings')
                ->with('error', 'Jangan akses rute ini (/load-databases, /test-connection, /save) bila tidak mengirimkan parameter apa-apa.');
        }

        if (empty($this->request->getPost()) && empty($_FILES)) {
            return redirect()->to('/settings')
                ->with('error', 'Jangan akses rute ini (/load-databases, /test-connection, /save) bila tidak mengirimkan parameter apa-apa.');
        }

        return null;
    }

    private function getCurrentDbConfig(): array
    {
        $dbConfig = config('Database');

        return [
            'hostname' => $dbConfig->default['hostname'] ?? '',
            'username' => $dbConfig->default['username'] ?? '',
            'password' => $dbConfig->default['password'] ?? '',
            'database' => $dbConfig->default['database'] ?? '',
            'port'     => $dbConfig->default['port'] ?? 1433,
            'query_file' => (string) env('app.queryFilePath', ''),
        ];
    }

    private function getPostedConfig(): array
    {
        $current = $this->getCurrentDbConfig();

        return [
            'hostname'   => trim((string) $this->request->getPost('hostname')),
            'username'   => trim((string) $this->request->getPost('username')),
            'password'   => (string) $this->request->getPost('password'),
            'database'   => trim((string) $this->request->getPost('database')),
            'port'       => (int) ($this->request->getPost('port') ?: 1433),
            'query_file' => $current['query_file'] ?? '',
        ];
    }

    private function handleQueryFileUpload(array $config): array
    {
        $file = $this->request->getFile('query_file');

        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $config;
        }

        if (!$file->isValid()) {
            throw new \RuntimeException('Upload file query gagal.');
        }

        $ext = strtolower((string) $file->getExtension());

        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            throw new \RuntimeException('File query harus berformat .xlsx atau .xls');
        }

        $targetDir = WRITEPATH . 'uploads/query_files';

        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Folder upload file query gagal dibuat.');
        }

        $newName = 'listquery.' . $ext;
        $file->move($targetDir, $newName, true);

        $finalPath = $targetDir . DIRECTORY_SEPARATOR . $newName;

        if (!is_file($finalPath)) {
            throw new \RuntimeException('File query Excel gagal dipindahkan ke server.');
        }

        $config['query_file'] = str_replace('\\', '/', $finalPath);

        return $config;
    }

    private function buildServerName(string $hostname, int $port): string
    {
        if ($port > 0 && strpos($hostname, '\\') === false && strpos($hostname, ',') === false) {
            return $hostname . ',' . $port;
        }

        return $hostname;
    }

    private function connectSqlServer(array $config, string $database = 'master')
    {
        $serverName = $this->buildServerName($config['hostname'], (int) $config['port']);

        $connectionInfo = [
            'UID'                    => $config['username'],
            'PWD'                    => $config['password'],
            'Database'               => $database,
            'CharacterSet'           => 'UTF-8',
            'Encrypt'                => true,
            'TrustServerCertificate' => true,
        ];

        return sqlsrv_connect($serverName, $connectionInfo);
    }

    public function index()
    {
        return view('settings', [
            'config'       => $this->getCurrentDbConfig(),
            'databases'    => [],
            'message'      => session()->getFlashdata('message'),
            'error'        => session()->getFlashdata('error'),
            'success'      => session()->getFlashdata('success'),
            'save_success' => session()->getFlashdata('save_success'),
        ]);
    }

    public function loadDatabases()
    {
        if ($response = $this->rejectInvalidAccess()) {
            return $response;
        }

        $config = $this->getPostedConfig();
        try {
            $config = $this->handleQueryFileUpload($config);
        } catch (\Throwable $e) {
            return view('settings', [
                'config'       => $config,
                'databases'    => [],
                'error'        => $e->getMessage(),
                'success'      => null,
                'message'      => null,
                'save_success' => null,
            ]);
        }

        if ($config['hostname'] === '' || $config['username'] === '') {
            return redirect()->to('/settings')
                ->with('error', 'Hostname dan Username wajib diisi.');
        }

        $conn = $this->connectSqlServer($config, 'master');

        if ($conn === false) {
            return view('settings', [
                'config'       => $config,
                'databases'    => [],
                'error'        => $this->formatSqlsrvErrors(),
                'success'      => null,
                'message'      => null,
                'save_success' => null,
            ]);
        }

        $sql = "SELECT name FROM sys.databases WHERE name NOT IN ('master','tempdb','model','msdb') ORDER BY name";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            sqlsrv_close($conn);

            return view('settings', [
                'config'       => $config,
                'databases'    => [],
                'error'        => $this->formatSqlsrvErrors(),
                'success'      => null,
                'message'      => null,
                'save_success' => null,
            ]);
        }

        $databases = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $databases[] = $row['name'];
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        return view('settings', [
            'config'       => $config,
            'databases'    => $databases,
            'error'        => null,
            'success'      => 'Berhasil terhubung ke server SQL Server dan memuat daftar database.',
            'message'      => null,
            'save_success' => null,
        ]);
    }

    public function testConnection()
    {
        if ($response = $this->rejectInvalidAccess()) {
            return $response;
        }

        $config = $this->getPostedConfig();
        try {
            $config = $this->handleQueryFileUpload($config);
        } catch (\Throwable $e) {
            return view('settings', [
                'config'       => $config,
                'databases'    => $config['database'] !== '' ? [$config['database']] : [],
                'error'        => $e->getMessage(),
                'success'      => null,
                'message'      => null,
                'save_success' => null,
            ]);
        }

        if ($config['hostname'] === '' || $config['username'] === '') {
            return redirect()->to('/settings')
                ->with('error', 'Hostname dan Username wajib diisi.');
        }

        $databaseToUse = $config['database'] !== '' ? $config['database'] : 'master';
        $conn = $this->connectSqlServer($config, $databaseToUse);

        if ($conn === false) {
            return view('settings', [
                'config'       => $config,
                'databases'    => $config['database'] !== '' ? [$config['database']] : [],
                'error'        => $this->formatSqlsrvErrors(),
                'success'      => null,
                'message'      => null,
                'save_success' => null,
            ]);
        }

        sqlsrv_close($conn);

        return view('settings', [
            'config'       => $config,
            'databases'    => $config['database'] !== '' ? [$config['database']] : [],
            'error'        => null,
            'success'      => 'Koneksi berhasil.',
            'message'      => null,
            'save_success' => null,
        ]);
    }

    public function save()
    {
        if ($response = $this->rejectInvalidAccess()) {
            return $response;
        }

        $config = $this->getPostedConfig();
        try {
            $config = $this->handleQueryFileUpload($config);
        } catch (\Throwable $e) {
            return redirect()->to('/settings')
                ->with('error', $e->getMessage());
        }

        if ($config['hostname'] === '' || $config['username'] === '' || $config['database'] === '') {
            return redirect()->to('/settings')
                ->with('error', 'Hostname, Username, dan Database wajib diisi sebelum menyimpan.');
        }

        if ($config['query_file'] === '') {
            return redirect()->to('/settings')
                ->with('error', 'File query Excel wajib diupload terlebih dahulu.');
        }

        if (!is_file($this->envPath)) {
            return redirect()->to('/settings')
                ->with('error', 'File .env tidak ditemukan.');
        }

        $envContent = file_get_contents($this->envPath);

        if ($envContent === false) {
            return redirect()->to('/settings')
                ->with('error', 'Gagal membaca file .env');
        }

        $updates = [
            'database.default.hostname' => $config['hostname'],
            'database.default.username' => $config['username'],
            'database.default.password' => $config['password'],
            'database.default.database' => $config['database'],
            'database.default.DBDriver' => 'SQLSRV',
            'database.default.port'     => (string) $config['port'],
            'app.queryFilePath'         => $config['query_file'],
        ];

        foreach ($updates as $key => $value) {
            $envContent = $this->setEnvValue($envContent, $key, $value);
        }

        if (file_put_contents($this->envPath, $envContent) === false) {
            return redirect()->to('/settings')
                ->with('error', 'Gagal menulis perubahan ke file .env');
        }

        return redirect()->to('/settings')
            ->with('success', 'Konfigurasi database berhasil disimpan ke .env')
            ->with('save_success', true);
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $escapedKey = preg_quote($key, '/');
        $quotedValue = $this->quoteEnvValue($value);

        $pattern = "/^\\s*#?\\s*{$escapedKey}\\s*=.*$/m";

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, "{$key} = {$quotedValue}", $content, 1);
        }

        $trimmed = rtrim($content);
        return $trimmed . PHP_EOL . "{$key} = {$quotedValue}" . PHP_EOL;
    }

    private function quoteEnvValue(string $value): string
    {
        $escaped = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
        return "'{$escaped}'";
    }

    private function formatSqlsrvErrors(): string
    {
        $errors = sqlsrv_errors();

        if (!$errors) {
            return 'Koneksi gagal tanpa detail error.';
        }

        $messages = [];

        foreach ($errors as $error) {
            $messages[] = '[' . ($error['SQLSTATE'] ?? '') . '] ' . ($error['message'] ?? 'Unknown error');
        }

        $fullMessage = implode(' | ', $messages);
        $lowerMessage = strtolower($fullMessage);

        if (
            str_contains($lowerMessage, 'error locating server/instance specified') ||
            str_contains($lowerMessage, 'server is not found or not accessible') ||
            str_contains($lowerMessage, 'login timeout expired') ||
            str_contains($lowerMessage, 'network-related or instance-specific error')
        ) {
            return 'Koneksi gagal. Cek apakah ada kesalahan penulisan IP Address atau Instance SQL Server Anda.';
        }

        if (str_contains($lowerMessage, 'login failed for user')) {
            return 'Username atau Password anda salah.';
        }

        if (
            str_contains($lowerMessage, 'certificate chain was issued by an authority that is not trusted') ||
            str_contains($lowerMessage, 'client unable to establish connection')
        ) {
            return 'Koneksi gagal. Sertifikat SQL Server tidak dipercaya oleh client.';
        }

        return $fullMessage;
    }
}
