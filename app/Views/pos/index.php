<div class="pos-container">
    <div class="pos-products">
        <div class="pos-header">
            <div class="search-wrapper">
                <span class="material-icons-round">search</span>
                <input type="text" id="productSearch" placeholder="بحث عن منتج بالاسم أو الباركود..." autocomplete="off">
                <button type="button" class="voice-btn" onclick="voiceSearchProduct()" title="بحث صوتي">
                    <span class="material-icons-round">mic</span>
                </button>
            </div>
            <div class="category-filter">
                <button class="cat-btn active" data-category="all">الكل</button>
                <?php foreach ($categories as $cat): ?>
                <button class="cat-btn" data-category="<?= $cat['id'] ?>"><?= $cat['name'] ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Pagination Controls - Top -->
        <div class="pagination-controls" id="paginationControlsTop">
            <button class="pagination-btn" id="prevPageTop" onclick="changePage(-1)">
                <span class="material-icons-round">chevron_right</span>
                السابق
            </button>
            <div class="pagination-info">
                <span class="currentPageNum">1</span> / <span class="totalPagesNum">1</span>
            </div>
            <button class="pagination-btn" id="nextPageTop" onclick="changePage(1)">
                التالي
                <span class="material-icons-round">chevron_left</span>
            </button>
        </div>
        
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?= $product['id'] ?>" 
                 data-name="<?= htmlspecialchars($product['name']) ?>"
                 data-price="<?= $product['cash_price'] ?>"
                 data-category="<?= $product['category_id'] ?>"
                 data-barcode="<?= $product['barcode'] ?>"
                 data-quantity="<?= $product['quantity'] ?>">
                <div class="product-qty-badge" style="display: none;">0</div>
                <div class="product-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= upload('products/' . $product['image']) ?>" alt="<?= $product['name'] ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                        <img src="<?= upload('products/default.png') ?>" alt="<?= $product['name'] ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h4><?= $product['name'] ?></h4>
                    <p class="product-price"><?= formatMoney($product['cash_price']) ?></p>
                    <small class="stock <?= $product['quantity'] <= $product['min_quantity'] ? 'low' : '' ?>">
                        المخزون: <?= $product['quantity'] ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination Controls - Bottom -->
        <div class="pagination-controls" id="paginationControlsBottom">
            <button class="pagination-btn" id="prevPageBottom" onclick="changePage(-1)">
                <span class="material-icons-round">chevron_right</span>
                السابق
            </button>
            <div class="pagination-info">
                <span class="currentPageNum">1</span> / <span class="totalPagesNum">1</span>
            </div>
            <button class="pagination-btn" id="nextPageBottom" onclick="changePage(1)">
                التالي
                <span class="material-icons-round">chevron_left</span>
            </button>
        </div>
    </div>
    
    <div class="pos-cart" id="posCart">
        <!-- Handle للسحب (يظهر فقط على الموبايل) -->
        <div class="cart-handle" id="cartHandle">
            <div class="handle-bar"></div>
        </div>
        
        <div class="cart-header" id="cartHeader">
            <div class="cart-header-left">
                <h3><span class="material-icons-round">shopping_cart</span> السلة</h3>
                <span class="cart-count" id="cartCount">0</span>
            </div>
            <div class="cart-header-right">
                <span class="cart-mini-total" id="cartMiniTotal">0.00 <?= $settings['currency'] ?? 'ج.م' ?></span>
                <button class="btn-expand" id="btnExpandCart" onclick="toggleCart()">
                    <span class="material-icons-round">expand_less</span>
                </button>
                <button class="btn-clear" onclick="clearCart()">
                    <span class="material-icons-round">delete_sweep</span>
                </button>
            </div>
        </div>
        
        <!-- اختيار العميل -->
        <div class="customer-select">
            <label>العميل (اختياري):</label>
            <select id="customerSelect">
                <option value="">زبون نقدي</option>
                <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['id'] ?>"><?= $customer['full_name'] ?> - <?= $customer['phone'] ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= url('/customers/create?return_to=pos') ?>" class="btn-add-customer" onclick="saveCartBeforeAddCustomer()">+ إضافة عميل جديد</a>
        </div>
        
        <div class="cart-body" id="cartBody">
            <div class="cart-items" id="cartItems">
                <p class="empty-cart">السلة فارغة</p>
            </div>
        
        <div class="cart-summary">
            <div class="total-items-badge">
                <span class="material-icons-round">shopping_basket</span>
                <span>إجمالي المنتجات:</span>
                <strong id="totalItemsCount">0</strong>
            </div>
            <div class="summary-row">
                <span>المجموع:</span>
                <strong id="subtotal">0.00 <?= $settings['currency'] ?? 'ج.م' ?></strong>
            </div>
            <div class="summary-row">
                <span>الخصم:</span>
                <input type="number" id="discountInput" value="0" min="0" step="0.01" onchange="updateTotals()">
            </div>
            <div class="summary-row total">
                <span>الإجمالي:</span>
                <strong id="grandTotal">0.00 <?= $settings['currency'] ?? 'ج.م' ?></strong>
            </div>
        </div>
        
        <div class="cart-actions">
            <button class="btn btn-success btn-block" onclick="completeSale()">
                <span class="material-icons-round">check_circle</span>
                إتمام البيع
            </button>
            <button class="btn btn-primary btn-block" onclick="goToInstallment()">
                <span class="material-icons-round">credit_score</span>
                بيع بالتقسيط
            </button>
        </div>
        </div><!-- end cart-body -->
    </div>
</div>

<style>
.pos-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 25px;
    height: calc(100vh - var(--header-height) - 60px);
}

.pos-products {
    background: var(--bg-card);
    border-radius: var(--radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.pos-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.search-wrapper {
    display: flex;
    align-items: center;
    background: var(--bg-main);
    border-radius: 12px;
    padding: 0 15px;
    margin-bottom: 15px;
}

.search-wrapper input {
    flex: 1;
    padding: 12px;
    border: none;
    background: transparent;
    font-family: inherit;
    font-size: 15px;
    color: var(--text-primary);
}

.search-wrapper input:focus { outline: none; }

.category-filter {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.cat-btn {
    padding: 8px 16px;
    border: none;
    background: var(--bg-main);
    border-radius: 20px;
    font-family: inherit;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.cat-btn:hover, .cat-btn.active {
    background: var(--primary);
    color: white;
}

.products-grid {
    flex: 1;
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 15px;
    overflow-y: auto;
    align-content: start;
}

.product-card {
    background: var(--bg-main);
    border-radius: var(--radius);
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    position: relative;
    border: 2px solid transparent;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

.product-card.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(30, 136, 229, 0.08), rgba(30, 136, 229, 0.03));
    box-shadow: 0 0 15px rgba(30, 136, 229, 0.3);
}

.product-card.out-of-stock {
    opacity: 0.5;
    pointer-events: none;
}

/* شارة الكمية على كارت المنتج */
.product-qty-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1565C0));
    color: white;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(30, 136, 229, 0.4);
    z-index: 10;
    animation: badge-pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes badge-pop {
    0% { transform: scale(0); }
    80% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.product-image {
    width: 80px;
    height: 80px;
    margin: 0 auto 10px;
    background: var(--bg-card);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.product-image .material-icons-round {
    font-size: 36px;
    color: var(--text-muted);
}

.product-info h4 {
    font-size: 13px;
    margin-bottom: 5px;
    line-height: 1.3;
}

.product-price {
    color: var(--primary);
    font-weight: 700;
    font-size: 14px;
}

.stock {
    color: var(--text-muted);
    font-size: 11px;
}

.stock.low {
    color: var(--warning);
}

/* Pagination Controls */
.pagination-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 15px;
    background: var(--bg-card);
    border-radius: var(--radius);
    margin-top: 15px;
    box-shadow: var(--shadow);
}

.pagination-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px 20px;
    border: none;
    background: var(--bg-main);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-primary);
}

.pagination-btn:hover:not(:disabled) {
    background: var(--primary);
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-info {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-secondary);
    padding: 10px 20px;
    background: var(--bg-main);
    border-radius: var(--radius-sm);
}

.pagination-info #currentPage {
    color: var(--primary);
    font-weight: 700;
}

/* Product card hidden by pagination */
.product-card.hidden {
    display: none !important;
}

/* السلة */
.pos-cart {
    background: var(--bg-card);
    border-radius: var(--radius);
    display: flex;
    flex-direction: column;
}

.cart-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cart-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
}

.btn-clear {
    background: none;
    border: none;
    color: var(--danger);
    cursor: pointer;
}

/* اختيار العميل */
.customer-select {
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color);
}

.customer-select label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.customer-select select {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-family: inherit;
    background: var(--bg-main);
    color: var(--text-primary);
}

.btn-add-customer {
    display: block;
    margin-top: 10px;
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    transition: all 0.2s;
}

.btn-add-customer:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.cart-items {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
}

.empty-cart {
    text-align: center;
    color: var(--text-muted);
    padding: 40px 0;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--bg-main);
    border-radius: var(--radius-sm);
    margin-bottom: 10px;
}

/* رقم المنتج في السلة */
.item-number {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1565C0));
    color: white;
    border-radius: 50%;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3);
}

.cart-item-info {
    flex: 1;
}

.cart-item-info h5 {
    font-size: 13px;
    margin-bottom: 3px;
}

.cart-item-info small {
    color: var(--text-muted);
}

.cart-item-qty {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cart-item-qty button {
    width: 28px;
    height: 28px;
    border: none;
    background: var(--bg-card);
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}

.cart-item-qty span {
    width: 30px;
    text-align: center;
    font-weight: 600;
}

.cart-item-remove {
    background: none;
    border: none;
    color: var(--danger);
    cursor: pointer;
}

/* شارة إجمالي المنتجات */
.total-items-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.total-items-badge .material-icons-round {
    font-size: 24px;
    opacity: 0.9;
}

.total-items-badge span {
    font-size: 14px;
}

.total-items-badge strong {
    margin-right: auto;
    font-size: 20px;
    background: rgba(255,255,255,0.2);
    padding: 4px 14px;
    border-radius: 20px;
    min-width: 40px;
    text-align: center;
}

.cart-summary {
    padding: 15px 20px;
    border-top: 1px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.summary-row.total {
    font-size: 18px;
    padding-top: 10px;
    border-top: 2px solid var(--border-color);
}

.summary-row input {
    width: 100px;
    padding: 8px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    text-align: center;
    font-family: inherit;
}

.cart-customer {
    padding: 15px 20px;
    border-top: 1px solid var(--border-color);
}

.cart-customer label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
}

.cart-customer select {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-family: inherit;
    background: var(--bg-main);
}

.cart-actions {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* ═══════════════════════════════════════════════════════════════
   Pull-up Cart Styles - للموبايل فقط
   ═══════════════════════════════════════════════════════════════ */

/* Handle للسحب - مخفي على الديسكتوب */
.cart-handle {
    display: none;
}

/* عناصر الهيدر الجديدة - مخفية على الديسكتوب */
.cart-header-left,
.cart-header-right {
    display: contents;
}

.cart-count,
.cart-mini-total,
.btn-expand {
    display: none;
}

/* Cart Body wrapper */
.cart-body {
    display: contents;
}

@media (max-width: 768px) {
    /* السلة القابلة للسحب */
    .pos-cart {
        position: fixed !important;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 24px 24px 0 0 !important;
        box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.2);
        z-index: 100;
        background: var(--bg-card);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 70px; /* الارتفاع المصغر */
        overflow: hidden;
    }
    
    .pos-cart.expanded {
        max-height: 85vh;
        overflow: visible;
    }
    
    /* Handle للسحب */
    .cart-handle {
        display: flex;
        justify-content: center;
        padding: 12px 0 4px;
        cursor: grab;
        touch-action: none;
    }
    
    .cart-handle:active {
        cursor: grabbing;
    }
    
    .handle-bar {
        width: 48px;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-light), var(--primary));
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .pos-cart.expanded .handle-bar {
        background: var(--text-muted);
        width: 36px;
    }
    
    /* هيدر السلة المحسن */
    .cart-header {
        padding: 8px 16px 12px !important;
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .cart-header-left,
    .cart-header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-header h3 {
        font-size: 15px !important;
        margin: 0;
    }
    
    /* عداد المنتجات */
    .cart-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1565C0));
        color: white;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        animation: pulse-badge 2s infinite;
    }
    
    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    
    .cart-count:empty,
    .cart-count:contains("0") {
        display: none;
    }
    
    /* الإجمالي المصغر */
    .cart-mini-total {
        display: inline-block;
        font-size: 14px;
        font-weight: 700;
        color: var(--success);
        background: rgba(76, 175, 80, 0.1);
        padding: 4px 12px;
        border-radius: 8px;
    }
    
    /* زر التوسيع */
    .btn-expand {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        background: var(--bg-main);
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-expand:hover {
        background: var(--primary-light);
    }
    
    .btn-expand .material-icons-round {
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--primary);
        font-size: 22px;
    }
    
    .pos-cart.expanded .btn-expand .material-icons-round {
        transform: rotate(180deg);
    }
    
    /* Cart Body */
    .cart-body {
        display: block;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .pos-cart.expanded .cart-body {
        max-height: calc(85vh - 70px);
        overflow-y: auto;
    }
    
    /* عناصر السلة */
    .cart-items {
        padding: 10px 16px !important;
        max-height: 30vh;
        overflow-y: auto;
        border-top: 1px solid var(--border-color);
    }
    
    .cart-item {
        padding: 12px !important;
        margin-bottom: 8px;
        background: linear-gradient(135deg, var(--bg-main), rgba(255,255,255,0.05));
        border: 1px solid var(--border-color);
        border-radius: 12px !important;
        transition: all 0.2s;
    }
    
    .cart-item:active {
        transform: scale(0.98);
    }
    
    .cart-item-info h5 {
        font-size: 13px !important;
        font-weight: 600;
    }
    
    .cart-item-qty {
        background: var(--bg-card);
        padding: 4px;
        border-radius: 8px;
    }
    
    .cart-item-qty button {
        width: 32px !important;
        height: 32px !important;
        font-size: 18px !important;
        font-weight: bold;
        border-radius: 8px !important;
        transition: all 0.15s;
    }
    
    .cart-item-qty button:active {
        background: var(--primary);
        color: white;
    }
    
    .cart-item-qty span {
        min-width: 36px;
        font-size: 15px !important;
    }
    
    /* ملخص السلة */
    .cart-summary {
        padding: 12px 16px !important;
        background: rgba(0,0,0,0.02);
    }
    
    .summary-row {
        margin-bottom: 8px !important;
    }
    
    .summary-row.total {
        font-size: 17px !important;
        color: var(--primary);
    }
    
    /* اختيار العميل */
    .customer-select {
        padding: 12px 16px !important;
    }
    
    /* أزرار السلة */
    .cart-actions {
        padding: 12px 16px 20px !important;
        gap: 10px !important;
    }
    
    .cart-actions .btn {
        padding: 14px !important;
        font-size: 15px !important;
        font-weight: 600;
        border-radius: 12px !important;
    }
    
    .cart-actions .btn-success {
        background: linear-gradient(135deg, #4CAF50, #43A047);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    .cart-actions .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1565C0));
        box-shadow: 0 4px 15px rgba(30, 136, 229, 0.3);
    }
    
    /* إضافة padding للمنتجات لتفادي تغطية السلة */
    .pos-container {
        padding-bottom: 90px !important;
    }
}

/* الشاشات الصغيرة جداً */
@media (max-width: 480px) {
    .pos-cart {
        max-height: 65px;
    }
    
    .cart-header h3 {
        font-size: 13px !important;
    }
    
    .cart-header h3 .material-icons-round {
        font-size: 18px;
    }
    
    .cart-mini-total {
        font-size: 12px;
        padding: 3px 8px;
    }
    
    .cart-count {
        min-width: 20px;
        height: 20px;
        font-size: 11px;
    }
    
    .btn-expand {
        width: 32px;
        height: 32px;
    }
    
    .cart-items {
        max-height: 25vh;
    }
}
</style>

<script>
let cart = [];
const currency = '<?= $settings['currency'] ?? 'ج.م' ?>';

// ═══════════════════════════════════════════════════════════════
// إعدادات الـ Pagination المتجاوبة
// ═══════════════════════════════════════════════════════════════
// عدد المنتجات حسب حجم الشاشة: موبايل = 8، سطح المكتب = 18
function getProductsPerPage() {
    return window.innerWidth <= 768 ? 8 : 18;
}

let PRODUCTS_PER_PAGE = getProductsPerPage();
let currentPage = 1;
let filteredProducts = [];

// تحديث عند تغيير حجم الشاشة
window.addEventListener('resize', function() {
    const newPerPage = getProductsPerPage();
    if (newPerPage !== PRODUCTS_PER_PAGE) {
        PRODUCTS_PER_PAGE = newPerPage;
        showPage(1); // إعادة العرض من الصفحة الأولى
    }
});

// تهيئة Pagination عند تحميل الصفحة
function initPagination() {
    const allProducts = document.querySelectorAll('.product-card');
    filteredProducts = Array.from(allProducts);
    showPage(1);
}

// عرض صفحة معينة
function showPage(page) {
    const start = (page - 1) * PRODUCTS_PER_PAGE;
    const end = start + PRODUCTS_PER_PAGE;
    
    filteredProducts.forEach((product, index) => {
        if (index >= start && index < end) {
            product.classList.remove('hidden');
        } else {
            product.classList.add('hidden');
        }
    });
    
    currentPage = page;
    updatePaginationControls();
}

// تغيير الصفحة
function changePage(direction) {
    const totalPages = Math.ceil(filteredProducts.length / PRODUCTS_PER_PAGE);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        showPage(newPage);
    }
}

// تحديث عناصر التحكم (الأعلى والأسفل)
function updatePaginationControls() {
    const totalPages = Math.max(1, Math.ceil(filteredProducts.length / PRODUCTS_PER_PAGE));
    
    // تحديث أرقام الصفحات في كل الأماكن
    document.querySelectorAll('.currentPageNum').forEach(el => el.textContent = currentPage);
    document.querySelectorAll('.totalPagesNum').forEach(el => el.textContent = totalPages);
    
    // تفعيل/تعطيل أزرار السابق
    document.getElementById('prevPageTop').disabled = currentPage <= 1;
    document.getElementById('prevPageBottom').disabled = currentPage <= 1;
    
    // تفعيل/تعطيل أزرار التالي
    document.getElementById('nextPageTop').disabled = currentPage >= totalPages;
    document.getElementById('nextPageBottom').disabled = currentPage >= totalPages;
    
    // إخفاء التحكمات إذا كان هناك صفحة واحدة أو أقل
    const hideControls = filteredProducts.length <= PRODUCTS_PER_PAGE;
    document.getElementById('paginationControlsTop').style.display = hideControls ? 'none' : 'flex';
    document.getElementById('paginationControlsBottom').style.display = hideControls ? 'none' : 'flex';
}

// تصفية المنتجات مع Pagination
function filterProducts(category, searchTerm = '') {
    const allProducts = document.querySelectorAll('.product-card');
    filteredProducts = [];
    
    allProducts.forEach(product => {
        const productCategory = product.dataset.category;
        const productName = product.dataset.name.toLowerCase();
        const productBarcode = product.dataset.barcode?.toLowerCase() || '';
        
        const matchesCategory = category === 'all' || productCategory === category;
        const matchesSearch = searchTerm === '' || 
            productName.includes(searchTerm.toLowerCase()) || 
            productBarcode.includes(searchTerm.toLowerCase());
        
        if (matchesCategory && matchesSearch) {
            filteredProducts.push(product);
            product.style.display = '';
        } else {
            product.classList.add('hidden');
            product.style.display = 'none';
        }
    });
    
    // إعادة تعيين للصفحة الأولى عند التصفية
    showPage(1);
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initPagination();
    
    // استعادة السلة من localStorage إذا كانت موجودة
    const savedCart = localStorage.getItem('posCartItems');
    if (savedCart) {
        try {
            const items = JSON.parse(savedCart);
            if (Array.isArray(items) && items.length > 0) {
                cart = items;
                renderCart();
            }
        } catch (e) {
            console.error('Error restoring cart:', e);
        }
        localStorage.removeItem('posCartItems');
    }
    
    // اختيار العميل الجديد إذا كنا عائدين من صفحة إضافة عميل
    const urlParams = new URLSearchParams(window.location.search);
    const newCustomerId = urlParams.get('new_customer_id');
    if (newCustomerId) {
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect) {
            // البحث عن العميل بالـ ID في القائمة
            for (let i = 0; i < customerSelect.options.length; i++) {
                if (customerSelect.options[i].value === newCustomerId) {
                    customerSelect.selectedIndex = i;
                    break;
                }
            }
        }
        // إزالة الـ parameter من URL بدون إعادة تحميل الصفحة
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
    // دعم الطريقة القديمة (localStorage) للتوافق
    else if (localStorage.getItem('selectNewestCustomer') === 'pos') {
        localStorage.removeItem('selectNewestCustomer');
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect && customerSelect.options.length > 1) {
            // اختيار آخر عميل في القائمة (الأحدث)
            customerSelect.selectedIndex = customerSelect.options.length - 1;
        }
    }
});

// إضافة منتج للسلة
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const price = parseFloat(this.dataset.price);
        const stock = parseInt(this.dataset.quantity);
        
        if (stock <= 0) {
            alert('المنتج غير متوفر في المخزون');
            return;
        }
        
        const existing = cart.find(item => item.id === id);
        
        if (existing) {
            if (existing.quantity < stock) {
                existing.quantity++;
            } else {
                alert('لا يمكن إضافة أكثر من الكمية المتاحة');
                return;
            }
        } else {
            cart.push({ id, name, price, quantity: 1, stock });
        }
        
        renderCart();
    });
});

// عرض السلة
function renderCart() {
    const container = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="empty-cart">السلة فارغة</p>';
        updateTotals();
        updateProductCardSelection();
        return;
    }
    
    container.innerHTML = cart.map((item, index) => `
        <div class="cart-item">
            <span class="item-number">${index + 1}</span>
            <div class="cart-item-info">
                <h5>${item.name}</h5>
                <small>${item.price.toFixed(2)} ${currency}</small>
            </div>
            <div class="cart-item-qty">
                <button onclick="event.stopPropagation(); changeQty(${index}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="event.stopPropagation(); changeQty(${index}, 1)">+</button>
            </div>
            <button class="cart-item-remove" onclick="event.stopPropagation(); removeItem(${index})">
                <span class="material-icons-round">close</span>
            </button>
        </div>
    `).join('');
    
    updateTotals();
    updateProductCardSelection();
}

// تحديث حالة تحديد كروت المنتجات وإظهار الكمية
function updateProductCardSelection() {
    // إزالة التحديد من كل الكروت أولاً
    document.querySelectorAll('.product-card').forEach(card => {
        card.classList.remove('selected');
        const badge = card.querySelector('.product-qty-badge');
        if (badge) {
            badge.style.display = 'none';
            badge.textContent = '0';
        }
    });
    
    // إضافة التحديد للمنتجات الموجودة في السلة
    cart.forEach(item => {
        const card = document.querySelector(`.product-card[data-id="${item.id}"]`);
        if (card) {
            card.classList.add('selected');
            const badge = card.querySelector('.product-qty-badge');
            if (badge) {
                badge.style.display = 'flex';
                badge.textContent = item.quantity;
            }
        }
    });
}

// تغيير الكمية
function changeQty(index, change) {
    const item = cart[index];
    const newQty = item.quantity + change;
    
    if (newQty <= 0) {
        removeItem(index);
    } else if (newQty <= item.stock) {
        item.quantity = newQty;
        renderCart();
    } else {
        alert('لا يمكن إضافة أكثر من الكمية المتاحة');
    }
}

// حذف عنصر
function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

// تفريغ السلة
function clearCart() {
    if (cart.length === 0) return;
    if (confirm('هل تريد تفريغ السلة؟')) {
        cart = [];
        renderCart();
    }
}

// تحديث الإجماليات
function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = subtotal - discount;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ' + currency;
    document.getElementById('grandTotal').textContent = total.toFixed(2) + ' ' + currency;
    
    // تحديث عداد السلة والإجمالي المصغر (للموبايل)
    // عدد المنتجات المختلفة (وليس مجموع الكميات)
    const uniqueProductsCount = cart.length;
    // مجموع الكميات (للعرض الاختياري)
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartCountEl = document.getElementById('cartCount');
    const cartMiniTotalEl = document.getElementById('cartMiniTotal');
    const totalItemsCountEl = document.getElementById('totalItemsCount');
    
    if (cartCountEl) {
        cartCountEl.textContent = uniqueProductsCount;
        cartCountEl.style.display = uniqueProductsCount > 0 ? 'inline-flex' : 'none';
    }
    
    // تحديث شارة إجمالي المنتجات في الملخص (عدد المنتجات المختلفة)
    if (totalItemsCountEl) {
        totalItemsCountEl.textContent = uniqueProductsCount;
    }
    
    if (cartMiniTotalEl) {
        cartMiniTotalEl.textContent = total.toFixed(2) + ' ' + currency;
    }
}

// ═══════════════════════════════════════════════════════════════
// Pull-up Cart Functions - وظائف السلة القابلة للسحب
// ═══════════════════════════════════════════════════════════════

let isCartExpanded = false;
let isDragging = false;
let startY = 0;
let currentY = 0;

// تبديل السلة (توسيع/تصغير)
function toggleCart() {
    const posCart = document.getElementById('posCart');
    if (!posCart) return;
    
    isCartExpanded = !isCartExpanded;
    posCart.classList.toggle('expanded', isCartExpanded);
    
    // اهتزاز خفيف للتغذية الراجعة (إذا كان مدعوماً)
    if (navigator.vibrate) {
        navigator.vibrate(10);
    }
}

// إغلاق السلة
function collapseCart() {
    const posCart = document.getElementById('posCart');
    if (!posCart) return;
    
    isCartExpanded = false;
    posCart.classList.remove('expanded');
}

// فتح السلة
function expandCart() {
    const posCart = document.getElementById('posCart');
    if (!posCart) return;
    
    isCartExpanded = true;
    posCart.classList.add('expanded');
}

// تهيئة السحب باللمس
function initDragGesture() {
    const cartHandle = document.getElementById('cartHandle');
    const cartHeader = document.getElementById('cartHeader');
    const posCart = document.getElementById('posCart');
    
    if (!cartHandle || !posCart) return;
    
    // أحداث اللمس للـ Handle
    cartHandle.addEventListener('touchstart', handleDragStart, { passive: true });
    cartHandle.addEventListener('touchmove', handleDragMove, { passive: false });
    cartHandle.addEventListener('touchend', handleDragEnd);
    
    // أحداث الماوس للـ Handle (للاختبار على الديسكتوب)
    cartHandle.addEventListener('mousedown', handleDragStart);
    document.addEventListener('mousemove', handleDragMove);
    document.addEventListener('mouseup', handleDragEnd);
    
    // النقر على الهيدر لفتح السلة
    cartHeader.addEventListener('click', function(e) {
        // تجاهل النقر على الأزرار
        if (e.target.closest('button')) return;
        toggleCart();
    });
}

function handleDragStart(e) {
    isDragging = true;
    startY = e.touches ? e.touches[0].clientY : e.clientY;
    currentY = startY;
    
    const posCart = document.getElementById('posCart');
    posCart.style.transition = 'none';
}

function handleDragMove(e) {
    if (!isDragging) return;
    
    currentY = e.touches ? e.touches[0].clientY : e.clientY;
    const deltaY = startY - currentY;
    
    // منع التمرير الافتراضي
    if (e.cancelable) {
        e.preventDefault();
    }
}

function handleDragEnd() {
    if (!isDragging) return;
    
    isDragging = false;
    const deltaY = startY - currentY;
    const posCart = document.getElementById('posCart');
    
    posCart.style.transition = '';
    
    // حد السحب للتبديل (50 بكسل)
    if (Math.abs(deltaY) > 50) {
        if (deltaY > 0) {
            // سحب لأعلى - فتح
            expandCart();
        } else {
            // سحب لأسفل - إغلاق
            collapseCart();
        }
    }
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initDragGesture();
    
    // إغلاق السلة عند النقر خارجها (على الموبايل فقط)
    document.addEventListener('click', function(e) {
        if (window.innerWidth > 768) return;
        
        const posCart = document.getElementById('posCart');
        if (!posCart) return;
        
        if (isCartExpanded && !posCart.contains(e.target)) {
            collapseCart();
        }
    });
});

// إتمام البيع
async function completeSale() {
    if (cart.length === 0) {
        alert('السلة فارغة');
        return;
    }
    
    const items = cart.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
    }));
    
    const formData = new FormData();
    formData.append('items', JSON.stringify(items));
    formData.append('discount', document.getElementById('discountInput').value);
    formData.append('customer_id', document.getElementById('customerSelect').value);
    formData.append('payment_method', 'cash');
    
    try {
        const response = await fetch('<?= url('/pos/cash') ?>', {
            method: 'POST',
            body: formData
        });
        
        if (response.redirected) {
            window.location.href = response.url;
        }
    } catch (error) {
        alert('حدث خطأ في العملية');
    }
}

// الانتقال لصفحة التقسيط مع نقل السلة والعميل
function goToInstallment() {
    // حفظ السلة في localStorage لنقلها لصفحة التقسيط
    if (cart.length > 0) {
        localStorage.setItem('posCartItems', JSON.stringify(cart));
    }
    
    // حفظ العميل المختار
    const customerId = document.getElementById('customerSelect').value;
    if (customerId) {
        localStorage.setItem('posSelectedCustomer', customerId);
    }
    
    window.location.href = '<?= url('/pos/installment') ?>';
}

// حفظ السلة قبل الانتقال لإضافة عميل جديد
function saveCartBeforeAddCustomer() {
    // حفظ السلة
    if (cart.length > 0) {
        localStorage.setItem('posCartItems', JSON.stringify(cart));
    }
    // علامة لاختيار أحدث عميل عند العودة
    localStorage.setItem('selectNewestCustomer', 'pos');
}

// تصفية بالتصنيف (مع Pagination)
let currentCategory = 'all';
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        currentCategory = this.dataset.category;
        const searchTerm = document.getElementById('productSearch').value;
        filterProducts(currentCategory, searchTerm);
    });
});

// البحث (مع Pagination)
document.getElementById('productSearch').addEventListener('input', function() {
    const query = this.value;
    filterProducts(currentCategory, query);
});

// البحث الصوتي
function voiceSearchProduct() {
    if (!('webkitSpeechRecognition' in window)) {
        alert('المتصفح لا يدعم البحث الصوتي');
        return;
    }
    
    const recognition = new webkitSpeechRecognition();
    recognition.lang = 'ar-SA';
    recognition.onresult = function(event) {
        document.getElementById('productSearch').value = event.results[0][0].transcript;
        document.getElementById('productSearch').dispatchEvent(new Event('input'));
    };
    recognition.start();
}
</script>
