<?php if (isset($pagination) && $pagination !== null): ?>
<div class="pagination-container">
    <div class="pagination-info">
        عرض <?= $pagination->getStartItem() ?> - <?= $pagination->getEndItem() ?> من <?= $pagination->getTotalItems() ?>
    </div>
    
    <div class="per-page-selector">
        <label>عرض:</label>
        <select onchange="window.location.href=this.value">
            <?php foreach ([10, 25, 50, 100] as $option): ?>
            <option value="<?= \Core\Pagination::buildUrl(1, $option) ?>" <?= $pagination->getPerPage() == $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <?php if ($pagination->getTotalPages() > 1): ?>
    <div class="pagination">
        <?php if ($pagination->hasPrev()): ?>
        <a href="<?= \Core\Pagination::buildUrl($pagination->getCurrentPage() - 1, $pagination->getPerPage()) ?>" class="page-btn">
            <span class="material-icons-round">chevron_right</span>
        </a>
        <?php endif; ?>
        
        <?php foreach ($pagination->getPageNumbers() as $page): ?>
            <?php if ($page === '...'): ?>
                <span class="page-dots">...</span>
            <?php else: ?>
                <a href="<?= \Core\Pagination::buildUrl($page, $pagination->getPerPage()) ?>" 
                   class="page-btn <?= $page == $pagination->getCurrentPage() ? 'active' : '' ?>">
                    <?= $page ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if ($pagination->hasNext()): ?>
        <a href="<?= \Core\Pagination::buildUrl($pagination->getCurrentPage() + 1, $pagination->getPerPage()) ?>" class="page-btn">
            <span class="material-icons-round">chevron_left</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.pagination-container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-top: 25px; padding: 15px; background: var(--bg-card); border-radius: var(--radius); }
.pagination-info { color: var(--text-muted); font-size: 14px; }
.per-page-selector { display: flex; align-items: center; gap: 10px; }
.per-page-selector label { color: var(--text-muted); font-size: 14px; }
.per-page-selector select { padding: 8px 15px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-main); color: var(--text-primary); }
.pagination { display: flex; align-items: center; gap: 5px; }
.page-btn { display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; border-radius: 8px; background: var(--bg-main); color: var(--text-primary); text-decoration: none; transition: all 0.2s; font-weight: 500; }
.page-btn:hover { background: var(--primary); color: white; }
.page-btn.active { background: var(--primary); color: white; }
.page-btn .material-icons-round { font-size: 20px; }
.page-dots { padding: 0 8px; color: var(--text-muted); }
@media (max-width: 768px) { .pagination-container { justify-content: center; } }
</style>
<?php endif; ?>

