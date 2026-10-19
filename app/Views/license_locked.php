<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi Lisensi - EvalSSN Helper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/main.css') ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card shadow border-0" style="max-width: 540px; width: 100%;">
        <div class="card-header bg-danger text-white py-3 text-center">
            <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Akses Aplikasi Terkunci</h4>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-x-octagon-fill me-2"></i>
                    <strong>Status Lisensi:</strong> <?= esc($message) ?>
                </div>
            <?php endif; ?>

            <div class="card bg-white border p-3 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-key-fill text-primary me-1"></i> Form Aktivasi Lisensi</h6>
                <form method="post" action="<?= site_url('activate-license') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label small mb-1 fw-semibold">Kode Satker (4 Digit)</label>
                        <input type="text" name="satker" class="form-control form-control-sm" placeholder="Contoh: 6309" value="<?= esc($satker ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-semibold">Token Lisensi</label>
                        <input type="text" name="token" class="form-control form-control-sm" placeholder="Masukkan token resmi dari BPS Tabalong" value="<?= esc($token ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-check-circle-fill me-1"></i> Aktifkan Lisensi
                    </button>
                </form>
            </div>

            <?php if (!empty($hwid) && (string) ($satker ?? '') === '6309'): ?>
                <div class="p-2 bg-body-secondary rounded mb-3 small">
                    <div class="text-muted" style="font-size: 0.75rem;">Hardware ID PC Ini:</div>
                    <code class="user-select-all d-block text-truncate" style="font-size: 0.8rem;"><?= esc($hwid) ?></code>
                </div>
            <?php endif; ?>

            <div class="text-secondary small mb-3">
                <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">Tata Cara Permohonan Token:</h6>
                <ol class="mb-0 ps-3" style="font-size: 0.8rem;">
                    <li>Kirimkan Surat Permohonan Resmi Penggunaan Aplikasi EvalSSN ke <strong>BPS Kabupaten Tabalong</strong>.</li>
                    <li>Sertakan Kode Satker dan Kontak Admin Satker Anda.</li>
                    <li>Setelah diverifikasi, inputkan Kode Satker dan Token di atas.</li>
                </ol>
            </div>

            <div class="text-center text-muted small mt-3">
                EvalSSN Helper &copy; BPS Kabupaten Tabalong 2026
            </div>
        </div>
    </div>
</body>
</html>

