<?php
/**
 * كلاس الـ Pagination للتعدد الصفحات
 */

namespace Core;

class Pagination
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;
    
    public function __construct(int $totalItems, int $perPage = 10, int $currentPage = 1)
    {
        $this->totalItems = $totalItems;
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = max(1, (int) ceil($totalItems / $this->perPage));
        
        // تصحيح الصفحة الحالية
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
    }
    
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }
    
    public function getLimit(): int
    {
        return $this->perPage;
    }
    
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
    
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }
    
    public function getPerPage(): int
    {
        return $this->perPage;
    }
    
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
    
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }
    
    public function getStartItem(): int
    {
        if ($this->totalItems === 0) return 0;
        return $this->getOffset() + 1;
    }
    
    public function getEndItem(): int
    {
        $end = $this->getOffset() + $this->perPage;
        return min($end, $this->totalItems);
    }
    
    /**
     * الحصول على أرقام الصفحات للعرض
     */
    public function getPageNumbers(int $range = 2): array
    {
        $pages = [];
        
        $start = max(1, $this->currentPage - $range);
        $end = min($this->totalPages, $this->currentPage + $range);
        
        // إضافة الصفحة الأولى
        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }
        
        // إضافة الصفحات في النطاق
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        
        // إضافة الصفحة الأخيرة
        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = $this->totalPages;
        }
        
        return $pages;
    }
    
    /**
     * إنشاء رابط مع الصفحة المحددة
     */
    public static function buildUrl(int $page, int $perPage, array $extraParams = []): string
    {
        $params = array_merge($_GET, ['page' => $page, 'per_page' => $perPage], $extraParams);
        return '?' . http_build_query($params);
    }
}
