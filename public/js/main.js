let sortDirections = {};

function sortTable(colIndex) {
    const table = document.getElementById('dataTable');
    if (!table) return;

    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    let dir = sortDirections[colIndex] || 'asc';
    dir = dir === 'asc' ? 'desc' : 'asc';
    sortDirections[colIndex] = dir;

    rows.sort((a, b) => {
        const valA = a.cells[colIndex]?.textContent.trim() ?? '';
        const valB = b.cells[colIndex]?.textContent.trim() ?? '';

        const numA = parseFloat(valA);
        const numB = parseFloat(valB);

        if (!isNaN(numA) && !isNaN(numB)) {
            return dir === 'asc' ? numA - numB : numB - numA;
        }

        return dir === 'asc'
            ? valA.localeCompare(valB, 'id', { numeric: true, sensitivity: 'base' })
            : valB.localeCompare(valA, 'id', { numeric: true, sensitivity: 'base' });
    });

    rows.forEach(row => tbody.appendChild(row));
}

document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('dataTable');
    if (!table) return;

    const headers = Array.from(table.querySelectorAll('thead th'));
    const pmlIndex = headers.findIndex(th =>
        th.textContent.trim().toUpperCase().startsWith('PML')
    );

    if (pmlIndex === -1) return;

    const filterBox = document.getElementById('pmlFilterBox');
    const filter = document.getElementById('pmlFilter');
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    filter.innerHTML = '<option value="">Semua</option>';

    const values = [...new Set(
        rows.map(row => row.cells[pmlIndex]?.textContent.trim() ?? '')
    )]
        .filter(v => v !== '')
        .sort((a, b) => a.localeCompare(b, 'id', { sensitivity: 'base' }));

    values.forEach(value => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        filter.appendChild(option);
    });

    filterBox.style.display = 'block';

    filter.addEventListener('change', function () {
        const selectedValue = this.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const cellValue = row.cells[pmlIndex]?.textContent.trim() ?? '';
            const showRow = selectedValue === '' || cellValue === selectedValue;

            row.style.display = showRow ? '' : 'none';
            if (showRow) visibleCount++;
        });

        const recordCount = document.getElementById('recordCount');
        if (recordCount) {
            recordCount.textContent = 'Jumlah record: ' + visibleCount;
        }
    });
});
