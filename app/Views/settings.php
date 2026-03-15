<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
        }

        .page-wrap {
            width: 95%;
            margin: 0 auto;
            max-width: 900px;
        }

        .app-header {
            background: linear-gradient(90deg, #ff8008, #ffc837);
            color: #fff;
        }

        .app-title {
            text-shadow: 0 1px 1px rgba(0,0,0,.35), 0 2px 4px rgba(0,0,0,.28);
        }
    </style>
</head>
<body>
    <header class="app-header py-3 shadow-sm mb-4">
        <div class="page-wrap text-center">
            <h1 class="h3 mb-0 fw-bold app-title">Pengaturan Koneksi Database</h1>
        </div>
    </header>

    <main class="page-wrap pb-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= esc($success) ?>
                <?php if (!empty($save_success)): ?>
                    <br>
                    Anda akan diarahkan ke halaman utama dalam
                    <span id="redirectCountdown">3</span> detik.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Hostname / Server</label>
                        <input
                            type="text"
                            name="hostname"
                            class="form-control"
                            value="<?= esc($config['hostname'] ?? '') ?>"
                            placeholder="127.0.0.1\new_sqlipds"
                        >
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?= esc($config['username'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                value="<?= esc($config['password'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Port</label>
                            <input
                                type="number"
                                name="port"
                                class="form-control"
                                value="<?= esc($config['port'] ?? 1433) ?>"
                            >
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Database</label>
                            <select name="database" class="form-select">
                                <option value="">-- Pilih database --</option>

                                <?php foreach ($databases as $db): ?>
                                    <option value="<?= esc($db) ?>" <?= (($config['database'] ?? '') === $db) ? 'selected' : '' ?>>
                                        <?= esc($db) ?>
                                    </option>
                                <?php endforeach; ?>

                                <?php if (empty($databases) && !empty($config['database'])): ?>
                                    <option value="<?= esc($config['database']) ?>" selected>
                                        <?= esc($config['database']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File Query Excel</label>
                        <input
                            type="file"
                            name="query_file"
                            class="form-control"
                            accept=".xlsx,.xls"
                        >
                        <?php if (!empty($config['query_file'])): ?>
                            <div class="form-text">
                                File tersimpan: <?= esc(basename($config['query_file'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" formaction="<?= base_url('settings/load-databases') ?>" class="btn btn-warning">
                            Muat Database
                        </button>

                        <button type="submit" formaction="<?= base_url('settings/test-connection') ?>" class="btn btn-primary">
                            Tes Koneksi
                        </button>

                        <button type="submit" formaction="<?= base_url('settings/save') ?>" class="btn btn-success">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="modal fade" id="adminWarningModal" tabindex="-1" aria-labelledby="adminWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title" id="adminWarningModalLabel">Peringatan</h5>
                </div>
                <div class="modal-body">
                    Sebaiknya anda jangan akses halaman ini bila anda bukan administrator dan/atau tidak menggunakan PC Server.
                </div>
                <div class="modal-footer">
                <a href="<?= base_url('/') ?>" class="btn btn-success">Back ke Home</a>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (empty($save_success)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const warningModalEl = document.getElementById('adminWarningModal');
                if (!warningModalEl) return;

                const warningModal = new bootstrap.Modal(warningModalEl);
                warningModal.show();
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($save_success)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let seconds = 3;
                const countdownEl = document.getElementById('redirectCountdown');

                const interval = setInterval(function () {
                    seconds--;

                    if (countdownEl && seconds >= 0) {
                        countdownEl.textContent = seconds;
                    }

                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = "<?= base_url('/') ?>";
                    }
                }, 1000);
            });
        </script>
    <?php endif; ?>
</body>
</html>
