<?php
function short_text($text, $limit = 20) {
    $text = (string) $text;
    return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '...' : $text;
}

function move_pml_first($row) {
    $arr = (array) $row;
    $pmlKey = null;

    foreach ($arr as $key => $value) {
        if (strtoupper(trim((string) $key)) === 'PML') {
            $pmlKey = $key;
            break;
        }
    }

    if ($pmlKey === null) {
        return $arr;
    }

    $newRow = [$pmlKey => $arr[$pmlKey]];
    foreach ($arr as $key => $value) {
        if ($key !== $pmlKey) {
            $newRow[$key] = $value;
        }
    }

    return $newRow;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EvalSSN Helper - Tools Eval Anomali Susenas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/main.css') ?>" rel="stylesheet">
</head>
<body>

    <header class="app-header py-3 shadow-sm">
        <div class="page-wrap">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div style="width: 120px;"></div>

                <div class="text-center flex-grow-1">
                    <h1 class="h1 mb-0 fw-bold app-title">EvalSSN Helper</h1>
                    <h2 class="h4 mb-0 fw-bold app-title">Untuk Evaluasi Anomali Susenas Maret 2026</h2>
                </div>

                <div class="d-flex justify-content-end" style="width: 120px;">
                    <a href="<?= base_url('settings') ?>" class="settings-btn" title="Settings">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                </div>

            </div>
        </div>
    </header>
    <main class="py-4">
        <div class="page-wrap">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="/runquery" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <select name="q" class="form-select" style="max-width: 320px;">
                    <?php foreach ($queries as $i => $q): ?>
                        <option value="<?= $i ?>" <?= (isset($selected) && $selected == $i) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary">Jalankan</button>
            </form>

            <?php if (isset($hasil) && count($hasil) > 0): ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h3 class="h5 mb-1"><?= htmlspecialchars($judul) ?></h3>
                        <div id="recordCount" class="text-muted small">
                            Jumlah record: <?= count($hasil) ?>
                        </div>
                    </div>
                </div>

                <div id="pmlFilterBox" class="mb-3" style="display:none; max-width:260px;">
                    <label for="pmlFilter" class="form-label mb-1">Filter PML:</label>
                    <select id="pmlFilter" class="form-select form-select-sm">
                        <option value="">Semua</option>
                    </select>
                </div>

                <div class="table-responsive bg-white shadow-sm rounded">
                    <table id="dataTable" class="table table-bordered table-hover table-striped table-sm align-middle mb-0 text-nowrap custom-table">
                        <thead>
                            <tr>
                                <?php
                                $firstRowOrdered = move_pml_first($hasil[0]);
                                $headers = array_keys($firstRowOrdered);
                                foreach ($headers as $colIndex => $col):
                                ?>
                                    <th class="sortable" onclick="sortTable(<?= $colIndex ?>)">
                                        <?= htmlspecialchars($col) ?> ↑↓
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hasil as $row): ?>
                                <?php $orderedRow = move_pml_first($row); ?>
                                <tr>
                                    <?php $cellIndex = 0; ?>
                                    <?php foreach ($orderedRow as $key => $v): ?>
                                        <td title="<?= htmlspecialchars((string) $v) ?>">
                                            <?=
                                                htmlspecialchars(
                                                    $cellIndex === 0 ? short_text($v, 20) : (string) $v
                                                )
                                            ?>
                                        </td>
                                        <?php $cellIndex++; ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif (isset($hasil)): ?>

                <div class="alert alert-warning shadow-sm">
                    <h3 class="h5 mb-0">Tidak ada data</h3>
                </div>

            <?php endif; ?>

            <?php if (isset($selected)): ?>
                <div class="card shadow-sm mt-4 query-card">
                    <div class="card-body">
                        <h3 class="h6 mb-3 query-title">Query SQL</h3>
                        <pre class="query-content"><?= htmlspecialchars($queries[$selected]['query'] ?? '') ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="app-footer py-3 mt-4">
        <div class="page-wrap text-center small footer-text">
            Made with ❤️<br>
            Gilang Wahyu Prasetyo © BPS Kabupaten Tabalong 2025
        </div>
    </footer>

    <script src="<?= base_url('js/main.js') ?>"></script>
</body>
</html>
