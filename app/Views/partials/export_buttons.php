<?php if (isset($exportUrl)): ?>
<div class="export-buttons">
    <div class="export-dropdown">
        <button class="btn btn-sm btn-secondary dropdown-toggle" onclick="toggleExportMenu(this)">
            <span class="material-icons-round">download</span> تصدير
        </button>
        <div class="export-menu">
            <div class="export-menu-section">
                <label>عدد السجلات:</label>
                <select id="exportCount" class="form-control form-control-sm">
                    <option value="10">10 سجل</option>
                    <option value="25">25 سجل</option>
                    <option value="50">50 سجل</option>
                    <option value="100">100 سجل</option>
                    <option value="all" selected>الكل</option>
                </select>
            </div>
            <div class="export-menu-buttons">
                <a href="#" class="btn btn-sm btn-danger export-btn" data-format="pdf" onclick="doExport(this, 'pdf')">
                    <span class="material-icons-round">picture_as_pdf</span> PDF
                </a>
                <a href="#" class="btn btn-sm btn-success export-btn" data-format="excel" onclick="doExport(this, 'excel')">
                    <span class="material-icons-round">table_chart</span> Excel
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.export-buttons { display: flex; gap: 8px; }
.export-dropdown { position: relative; }
.export-menu { 
    display: none; position: absolute; top: 100%; left: 0; z-index: 1000;
    background: var(--bg-card); border-radius: var(--radius-sm); box-shadow: var(--shadow-lg);
    padding: 15px; min-width: 200px; margin-top: 5px;
}
.export-dropdown.open .export-menu { display: block; }
.export-menu-section { margin-bottom: 12px; }
.export-menu-section label { display: block; margin-bottom: 5px; font-size: 12px; color: var(--text-muted); }
.export-menu-buttons { display: flex; gap: 8px; }
.export-menu-buttons .btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; }
.dropdown-toggle { display: flex; align-items: center; gap: 5px; }
</style>

<script>
function toggleExportMenu(btn) {
    btn.closest('.export-dropdown').classList.toggle('open');
}
function doExport(btn, format) {
    const count = document.getElementById('exportCount').value;
    let baseUrl = '<?= $exportUrl ?>/' + format;
    let queryString = '<?= !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '' ?>';
    
    if (count !== 'all') {
        queryString += (queryString ? '&' : '') + 'export_limit=' + count;
    }
    
    window.open(baseUrl + (queryString ? '?' + queryString : ''), '_blank');
    btn.closest('.export-dropdown').classList.remove('open');
    return false;
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        document.querySelectorAll('.export-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
</script>
<?php endif; ?>

