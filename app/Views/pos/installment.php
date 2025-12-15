<div class="pos-container">
    <div class="pos-products">
        <div class="pos-header">
            <div class="search-wrapper">
                <span class="material-icons-round">search</span>
                <input type="text" id="productSearch" placeholder="بحث عن منتج..." autocomplete="off">
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
                 data-price="<?= $product['installment_price'] ?: $product['cash_price'] ?>"
                 data-category="<?= $product['category_id'] ?>">
                <div class="product-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= upload('products/' . $product['image']) ?>" alt="" loading="lazy" decoding="async">
                    <?php else: ?>
                        <img src="<?= upload('products/default.png') ?>" alt="" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h4><?= $product['name'] ?></h4>
                    <p class="product-price"><?= formatMoney($product['installment_price'] ?: $product['cash_price']) ?></p>
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
    
    <div class="pos-cart installment-cart" id="posCart">
        <!-- Handle للسحب (يظهر فقط على الموبايل) -->
        <div class="cart-handle" id="cartHandle">
            <div class="handle-bar"></div>
        </div>
        
        <div class="cart-header" id="cartHeader">
            <div class="cart-title">
                <h3><span class="material-icons-round">shopping_cart</span> السلة</h3>
                <span class="cart-count" id="cartCount">0</span>
            </div>
            <div class="cart-header-actions">
                <span class="cart-mini-total" id="cartMiniTotal">0.00 <?= $settings['currency'] ?? 'ج.م' ?></span>
                <button class="btn-expand" id="btnExpandCart" onclick="toggleCart()">
                    <span class="material-icons-round">expand_less</span>
                </button>
                <button class="btn-clear" onclick="clearCart()" title="تفريغ السلة">
                    <span class="material-icons-round">delete</span>
                </button>
            </div>
        </div>
        
        <div class="cart-body" id="cartBody">
            <div class="customer-select">
                <label>العميل *</label>
                <select id="customerSelect" required>
                    <option value="">اختر العميل</option>
                    <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>"><?= $customer['full_name'] ?> - <?= $customer['phone'] ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="<?= url('/customers/create?return_to=installment') ?>" class="btn-add-customer" onclick="saveCartBeforeAddCustomer()">+ إضافة عميل جديد</a>
            </div>
            
            <div class="cart-items" id="cartItems">
                <p class="empty-cart">السلة فارغة</p>
            </div>
        
        <div class="installment-config">
            <div class="form-group">
                <label>خطة التقسيط</label>
                <select id="planSelect" onchange="calculateInstallment()">
                    <?php foreach ($plans as $plan): ?>
                    <option value="<?= $plan['id'] ?>" 
                            data-months="<?= $plan['months'] ?>" 
                            data-increase="<?= $plan['increase_percent'] ?>"
                            data-min-down="<?= $plan['min_down_payment_percent'] ?>">
                        <?= $plan['name'] ?> (<?= $plan['months'] ?> شهر - زيادة <?= $plan['increase_percent'] ?>%)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>الدفعة المقدمة</label>
                <input type="number" id="downPayment" step="0.01" value="0" onchange="calculateInstallment()">
                <small id="minDownPayment"></small>
            </div>
        </div>
        
        <div class="cart-summary installment-summary">
            <div class="total-items-badge">
                <span class="material-icons-round">shopping_basket</span>
                <span>إجمالي المنتجات:</span>
                <strong id="totalItemsCount">0</strong>
            </div>
            <div class="summary-row"><span>السعر النقدي:</span><strong id="cashPrice">0.00</strong></div>
            <div class="summary-row"><span>سعر التقسيط:</span><strong id="installmentPrice">0.00</strong></div>
            <div class="summary-row"><span>الزيادة:</span><strong id="increaseAmount" class="text-warning">0.00</strong></div>
            <div class="summary-row"><span>الدفعة المقدمة:</span><strong id="downPaymentDisplay">0.00</strong></div>
            <div class="summary-row"><span>المتبقي:</span><strong id="remainingAmount">0.00</strong></div>
            <div class="summary-row total"><span>القسط الشهري:</span><strong id="monthlyInstallment">0.00</strong></div>
            <div class="summary-row"><span>عدد الأقساط:</span><strong id="monthsCount">0</strong> شهر</div>
        </div>
        
        <div class="cart-actions">
            <button class="btn btn-success btn-block" onclick="completeInstallmentSale()">
                <span class="material-icons-round">check_circle</span>
                إنشاء عقد التقسيط
            </button>
        </div>
        </div><!-- end cart-body -->
    </div>
</div>

<style>
.pos-container { display: grid; grid-template-columns: 1fr 420px; gap: 25px; height: calc(100vh - var(--header-height) - 60px); }
.pos-products { background: var(--bg-card); border-radius: var(--radius); overflow: hidden; display: flex; flex-direction: column; }
.pos-header { padding: 20px; border-bottom: 1px solid var(--border-color); }
.search-wrapper { display: flex; align-items: center; background: var(--bg-main); border-radius: 12px; padding: 0 15px; margin-bottom: 15px; }
.search-wrapper input { flex: 1; padding: 12px; border: none; background: transparent; font-family: inherit; font-size: 15px; color: var(--text-primary); }
.search-wrapper input:focus { outline: none; }
.category-filter { display: flex; gap: 8px; flex-wrap: wrap; }
.cat-btn { padding: 8px 16px; border: none; background: var(--bg-main); border-radius: 20px; font-family: inherit; font-size: 13px; cursor: pointer; }
.cat-btn:hover, .cat-btn.active { background: var(--primary); color: white; }
.products-grid { flex: 1; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; overflow-y: auto; align-content: start; }
.product-card { background: var(--bg-main); border-radius: var(--radius); padding: 15px; cursor: pointer; text-align: center; transition: all 0.2s; }
.product-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.product-image { width: 70px; height: 70px; margin: 0 auto 10px; background: var(--bg-card); border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
.product-image .material-icons-round { font-size: 32px; color: var(--text-muted); }
.product-info h4 { font-size: 13px; margin-bottom: 5px; }
.product-price { color: var(--primary); font-weight: 700; font-size: 14px; }
.installment-cart { background: var(--bg-card); border-radius: var(--radius); display: flex; flex-direction: column; }
.cart-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.cart-header h3 { display: flex; align-items: center; gap: 10px; font-size: 16px; margin: 0; }
.btn-clear { background: none; border: none; color: var(--danger); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-clear:hover { background: rgba(229, 57, 53, 0.1); }
.customer-select { padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
.customer-select label { display: block; margin-bottom: 8px; font-weight: 600; }
.customer-select select { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; }
.btn-add-customer { display: block; margin-top: 10px; font-size: 13px; color: var(--primary); }
.cart-items { flex: 1; padding: 15px; overflow-y: auto; min-height: 100px; }
.empty-cart { text-align: center; color: var(--text-muted); padding: 20px 0; }
.cart-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--bg-main); border-radius: 8px; margin-bottom: 10px; }
.item-number { display: flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1565C0)); color: white; border-radius: 50%; font-size: 12px; font-weight: 700; flex-shrink: 0; box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3); }
.cart-item-info { flex: 1; }
.cart-item-info h5 { font-size: 13px; margin-bottom: 3px; }
.cart-item-info small { color: var(--text-muted); }
.cart-item-qty { display: flex; align-items: center; gap: 5px; }
.cart-item-qty button { width: 28px; height: 28px; border: none; background: var(--bg-card); border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; }
.cart-item-qty button:hover { background: var(--primary); color: white; }
.cart-item-qty span { width: 30px; text-align: center; font-weight: 600; }
.cart-item-remove { background: none; border: none; color: var(--danger); cursor: pointer; }
.total-items-badge { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
.total-items-badge .material-icons-round { font-size: 22px; opacity: 0.9; }
.total-items-badge span { font-size: 13px; }
.total-items-badge strong { margin-right: auto; font-size: 18px; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 16px; min-width: 36px; text-align: center; }
.installment-config { padding: 15px 20px; border-top: 1px solid var(--border-color); background: var(--bg-main); }
.installment-config .form-group { margin-bottom: 15px; }
.installment-config .form-group:last-child { margin-bottom: 0; }
.installment-config label { display: block; margin-bottom: 5px; font-size: 13px; }
.installment-config select, .installment-config input { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; }
.installment-config small { display: block; margin-top: 5px; color: var(--text-muted); font-size: 11px; }
.installment-summary { padding: 15px 20px; border-top: 1px solid var(--border-color); }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
.summary-row.total { font-size: 18px; padding-top: 10px; border-top: 2px solid var(--border-color); margin-top: 10px; color: var(--primary); }
.cart-actions { padding: 20px; }

/* Pagination Controls */
.pagination-controls { display: flex; align-items: center; justify-content: center; gap: 15px; padding: 15px; background: var(--bg-card); border-radius: var(--radius); margin-top: 15px; box-shadow: var(--shadow); }
.pagination-btn { display: flex; align-items: center; gap: 5px; padding: 10px 20px; border: none; background: var(--bg-main); border-radius: var(--radius-sm); font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; color: var(--text-primary); }
.pagination-btn:hover:not(:disabled) { background: var(--primary); color: white; }
.pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-info { display: flex; align-items: center; gap: 5px; font-size: 14px; font-weight: 600; color: var(--text-secondary); padding: 10px 20px; background: var(--bg-main); border-radius: var(--radius-sm); }
.pagination-info #currentPage { color: var(--primary); font-weight: 700; }
.product-card.hidden { display: none !important; }

/* ═══════════════════════════════════════════════════════════════
   Pull-up Cart Styles - للموبايل فقط
   ═══════════════════════════════════════════════════════════════ */

/* Handle للسحب - مخفي على الديسكتوب */
.cart-handle { display: none; }

/* عناصر الهيدر - تنسيق الديسكتوب */
.cart-title { display: flex; align-items: center; gap: 10px; }
.cart-title h3 { margin: 0; }
.cart-header-actions { display: flex; align-items: center; gap: 8px; }

/* عناصر مخفية على الديسكتوب */
.cart-count, .cart-mini-total, .btn-expand { display: none; }

/* Cart Body wrapper */
.cart-body { display: contents; }

@media (max-width: 768px) {
    /* السلة القابلة للسحب */
    .installment-cart {
        position: fixed !important;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 24px 24px 0 0 !important;
        box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.2);
        z-index: 100;
        background: var(--bg-card);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 70px;
        overflow: hidden;
    }
    
    .installment-cart.expanded {
        max-height: 90vh;
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
    
    .handle-bar {
        width: 48px;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-light), var(--primary));
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .installment-cart.expanded .handle-bar {
        background: var(--text-muted);
        width: 36px;
    }
    
    /* هيدر السلة المحسن */
    .installment-cart .cart-header {
        padding: 8px 16px 12px !important;
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .cart-title, .cart-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .installment-cart .cart-header h3 {
        font-size: 14px !important;
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
    
    /* الإجمالي المصغر */
    .cart-mini-total {
        display: inline-block;
        font-size: 13px;
        font-weight: 700;
        color: var(--success);
        background: rgba(76, 175, 80, 0.1);
        padding: 4px 10px;
        border-radius: 8px;
    }
    
    /* زر التوسيع */
    .btn-expand {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: none;
        background: var(--bg-main);
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-expand .material-icons-round {
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--primary);
        font-size: 20px;
    }
    
    .installment-cart.expanded .btn-expand .material-icons-round {
        transform: rotate(180deg);
    }
    
    /* Cart Body */
    .cart-body {
        display: block;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .installment-cart.expanded .cart-body {
        max-height: calc(90vh - 70px);
        overflow-y: auto;
    }
    
    /* عناصر السلة */
    .installment-cart .cart-items {
        padding: 10px 16px !important;
        max-height: 20vh;
        overflow-y: auto;
        border-top: 1px solid var(--border-color);
    }
    
    /* اختيار العميل */
    .customer-select {
        padding: 10px 16px !important;
    }
    
    /* إعدادات التقسيط */
    .installment-config {
        padding: 10px 16px !important;
    }
    
    /* الملخص */
    .installment-summary {
        padding: 10px 16px !important;
    }
    
    .installment-summary .summary-row {
        font-size: 12px !important;
        margin-bottom: 5px !important;
    }
    
    .installment-summary .summary-row.total {
        font-size: 15px !important;
    }
    
    /* أزرار السلة */
    .installment-cart .cart-actions {
        padding: 12px 16px 20px !important;
    }
    
    .installment-cart .cart-actions .btn {
        padding: 14px !important;
        font-size: 15px !important;
        font-weight: 600;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #4CAF50, #43A047);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    /* إضافة padding للمنتجات */
    .pos-container {
        padding-bottom: 90px !important;
    }
}

/* الشاشات الصغيرة جداً */
@media (max-width: 480px) {
    .installment-cart {
        max-height: 65px;
    }
    
    .installment-cart .cart-header h3 {
        font-size: 12px !important;
    }
    
    .cart-mini-total {
        font-size: 11px;
        padding: 3px 6px;
    }
    
    .cart-count {
        min-width: 20px;
        height: 20px;
        font-size: 11px;
    }
    
    .installment-cart .cart-items {
        max-height: 15vh;
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
let currentCategory = 'all';

// تحديث عند تغيير حجم الشاشة
window.addEventListener('resize', function() {
    const newPerPage = getProductsPerPage();
    if (newPerPage !== PRODUCTS_PER_PAGE) {
        PRODUCTS_PER_PAGE = newPerPage;
        showPage(1);
    }
});

function initPagination() {
    const allProducts = document.querySelectorAll('.product-card');
    filteredProducts = Array.from(allProducts);
    showPage(1);
}

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

function changePage(direction) {
    const totalPages = Math.ceil(filteredProducts.length / PRODUCTS_PER_PAGE);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        showPage(newPage);
    }
}

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

function filterProducts(category, searchTerm = '') {
    const allProducts = document.querySelectorAll('.product-card');
    filteredProducts = [];
    
    allProducts.forEach(product => {
        const productCategory = product.dataset.category;
        const productName = product.dataset.name.toLowerCase();
        
        const matchesCategory = category === 'all' || productCategory === category;
        const matchesSearch = searchTerm === '' || productName.includes(searchTerm.toLowerCase());
        
        if (matchesCategory && matchesSearch) {
            filteredProducts.push(product);
            product.style.display = '';
        } else {
            product.classList.add('hidden');
            product.style.display = 'none';
        }
    });
    
    showPage(1);
}

// Category filter
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentCategory = this.dataset.category;
        const searchTerm = document.getElementById('productSearch')?.value || '';
        filterProducts(currentCategory, searchTerm);
    });
});

// Search filter
const searchInput = document.getElementById('productSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        filterProducts(currentCategory, this.value);
    });
}

document.addEventListener('DOMContentLoaded', initPagination);

document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const price = parseFloat(this.dataset.price);
        
        const existing = cart.find(item => item.id === id);
        if (existing) { existing.quantity++; } 
        else { cart.push({ id, name, price, quantity: 1 }); }
        
        renderCart();
    });
});

function renderCart() {
    const container = document.getElementById('cartItems');
    if (cart.length === 0) {
        container.innerHTML = '<p class="empty-cart">السلة فارغة</p>';
        calculateInstallment();
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
    
    calculateInstallment();
}

// تغيير الكمية
function changeQty(index, change) {
    const item = cart[index];
    const newQty = item.quantity + change;
    
    if (newQty <= 0) {
        removeItem(index);
    } else {
        item.quantity = newQty;
        renderCart();
    }
}

function removeItem(index) { cart.splice(index, 1); renderCart(); }

// تفريغ السلة
function clearCart() {
    if (cart.length === 0) return;
    // استخدام window.confirm للتوافق مع جميع المتصفحات
    var confirmed = window.confirm('هل تريد تفريغ السلة؟');
    if (confirmed) {
        cart = [];
        localStorage.removeItem('posCartItems');
        renderCart();
    }
}

// تحميل السلة والعميل من نقطة البيع (إذا كانت موجودة)
function loadCartFromStorage() {
    // تحميل السلة
    const savedCart = localStorage.getItem('posCartItems');
    if (savedCart) {
        try {
            const items = JSON.parse(savedCart);
            if (Array.isArray(items) && items.length > 0) {
                cart = items;
                localStorage.removeItem('posCartItems');
                renderCart();
            }
        } catch (e) {
            console.error('Error loading cart from storage:', e);
        }
    }
    
    // تحميل العميل المختار
    const savedCustomer = localStorage.getItem('posSelectedCustomer');
    if (savedCustomer) {
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect) {
            // البحث عن العميل في القائمة
            for (let i = 0; i < customerSelect.options.length; i++) {
                if (customerSelect.options[i].value === savedCustomer) {
                    customerSelect.selectedIndex = i;
                    break;
                }
            }
        }
        localStorage.removeItem('posSelectedCustomer');
    }
}

// تحميل السلة والعميل عند بدء الصفحة
loadCartFromStorage();

function calculateInstallment() {
    const cashTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const plan = document.getElementById('planSelect');
    const selected = plan.options[plan.selectedIndex];
    
    const months = parseInt(selected.dataset.months) || 0;
    const increase = parseFloat(selected.dataset.increase) || 0;
    const minDownPercent = parseFloat(selected.dataset.minDown) || 0;
    
    const installmentTotal = cashTotal * (1 + increase / 100);
    const minDown = installmentTotal * (minDownPercent / 100);
    
    let downPayment = parseFloat(document.getElementById('downPayment').value) || 0;
    if (downPayment < minDown) downPayment = minDown;
    
    const remaining = installmentTotal - downPayment;
    const monthly = months > 0 ? remaining / months : 0;
    
    document.getElementById('minDownPayment').textContent = 'الحد الأدنى: ' + minDown.toFixed(2) + ' ' + currency;
    document.getElementById('cashPrice').textContent = cashTotal.toFixed(2) + ' ' + currency;
    document.getElementById('installmentPrice').textContent = installmentTotal.toFixed(2) + ' ' + currency;
    document.getElementById('increaseAmount').textContent = (installmentTotal - cashTotal).toFixed(2) + ' ' + currency;
    document.getElementById('downPaymentDisplay').textContent = downPayment.toFixed(2) + ' ' + currency;
    document.getElementById('remainingAmount').textContent = remaining.toFixed(2) + ' ' + currency;
    document.getElementById('monthlyInstallment').textContent = monthly.toFixed(2) + ' ' + currency;
    document.getElementById('monthsCount').textContent = months;
}

async function completeInstallmentSale() {
    if (cart.length === 0) { alert('السلة فارغة'); return; }
    if (!document.getElementById('customerSelect').value) { alert('يجب اختيار العميل'); return; }
    
    const formData = new FormData();
    formData.append('items', JSON.stringify(cart.map(i => ({ id: i.id, quantity: i.quantity, price: i.price }))));
    formData.append('customer_id', document.getElementById('customerSelect').value);
    formData.append('plan_id', document.getElementById('planSelect').value);
    formData.append('down_payment', document.getElementById('downPayment').value);
    
    const response = await fetch('<?= url('/pos/installment') ?>', { method: 'POST', body: formData });
    if (response.redirected) window.location.href = response.url;
}

document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const category = this.dataset.category;
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (category === 'all' || card.dataset.category === category) ? '' : 'none';
        });
    });
});

document.getElementById('productSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = card.dataset.name.toLowerCase().includes(query) ? '' : 'none';
    });
});

// ═══════════════════════════════════════════════════════════════
// تحديث عداد السلة والإجمالي المصغر
// ═══════════════════════════════════════════════════════════════

function updateCartBadge() {
    // عدد المنتجات المختلفة (وليس مجموع الكميات)
    const uniqueProductsCount = cart.length;
    const cartCountEl = document.getElementById('cartCount');
    const cartMiniTotalEl = document.getElementById('cartMiniTotal');
    const totalItemsCountEl = document.getElementById('totalItemsCount');
    
    // حساب الإجمالي
    const cashTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const plan = document.getElementById('planSelect');
    const selected = plan.options[plan.selectedIndex];
    const increase = parseFloat(selected.dataset.increase) || 0;
    const installmentTotal = cashTotal * (1 + increase / 100);
    
    if (cartCountEl) {
        cartCountEl.textContent = uniqueProductsCount;
        cartCountEl.style.display = uniqueProductsCount > 0 ? 'inline-flex' : 'none';
    }
    
    if (cartMiniTotalEl) {
        cartMiniTotalEl.textContent = installmentTotal.toFixed(2) + ' ' + currency;
    }
    
    // تحديث شارة إجمالي المنتجات في الملخص (عدد المنتجات المختلفة)
    if (totalItemsCountEl) {
        totalItemsCountEl.textContent = uniqueProductsCount;
    }
}

// استدعاء تحديث العداد عند كل تغيير
const originalRenderCart = renderCart;
renderCart = function() {
    originalRenderCart();
    updateCartBadge();
};

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
    
    // أحداث الماوس للـ Handle
    cartHandle.addEventListener('mousedown', handleDragStart);
    document.addEventListener('mousemove', handleDragMove);
    document.addEventListener('mouseup', handleDragEnd);
    
    // النقر على الهيدر
    cartHeader.addEventListener('click', function(e) {
        if (e.target.closest('button') || e.target.closest('select')) return;
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
    
    if (Math.abs(deltaY) > 50) {
        if (deltaY > 0) {
            expandCart();
        } else {
            collapseCart();
        }
    }
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initDragGesture();
    updateCartBadge();
    
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
    else if (localStorage.getItem('selectNewestCustomer') === 'installment') {
        localStorage.removeItem('selectNewestCustomer');
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect && customerSelect.options.length > 1) {
            customerSelect.selectedIndex = customerSelect.options.length - 1;
        }
    }
    
    document.addEventListener('click', function(e) {
        if (window.innerWidth > 768) return;
        
        const posCart = document.getElementById('posCart');
        if (!posCart) return;
        
        if (isCartExpanded && !posCart.contains(e.target)) {
            collapseCart();
        }
    });
});

// حفظ السلة قبل الانتقال لإضافة عميل جديد
function saveCartBeforeAddCustomer() {
    if (cart.length > 0) {
        localStorage.setItem('posCartItems', JSON.stringify(cart));
    }
    localStorage.setItem('selectNewestCustomer', 'installment');
}

calculateInstallment();
</script>
