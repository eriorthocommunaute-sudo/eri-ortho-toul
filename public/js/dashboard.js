(() => {
    const table = document.getElementById('activityTable');
    const body = document.getElementById('activityBody');
    const search = document.getElementById('activitySearch');
    const exportButton = document.getElementById('exportActivity');
    const count = document.getElementById('activityCount');
    const empty = document.getElementById('activityEmpty');

    if (!table || !body) {
        return;
    }

    const rows = () => Array.from(body.querySelectorAll('tr[data-row]'));

    const updateCount = (visible) => {
        if (count) {
            count.textContent = visible + ' ' + (count.dataset.label || '');
        }
    };

    const filter = () => {
        const term = (search ? search.value : '').trim().toLowerCase();
        let visible = 0;

        rows().forEach((row) => {
            const match = !term || row.textContent.toLowerCase().includes(term);
            row.style.display = match ? '' : 'none';
            if (match) {
                visible += 1;
            }
        });

        if (empty) {
            empty.style.display = visible === 0 ? '' : 'none';
        }

        updateCount(visible);
    };

    if (search) {
        search.addEventListener('input', filter);
    }

    if (exportButton) {
        exportButton.addEventListener('click', () => {
            const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
            const lines = [headers];

            rows().forEach((row) => {
                if (row.style.display === 'none') {
                    return;
                }
                const cells = Array.from(row.querySelectorAll('td')).map((td) => td.textContent.replace(/\s+/g, ' ').trim());
                lines.push(cells);
            });

            const escape = (value) => '"' + String(value).replace(/"/g, '""') + '"';
            const csv = lines.map((line) => line.map(escape).join(';')).join('\r\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'activite-financiere-' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        });
    }
})();
