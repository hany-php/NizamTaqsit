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
        
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?= $product['id'] ?>" 
                 data-name="<?= htmlspecialchars($product['name']) ?>"
                 data-price="<?= $product['cash_price'] ?>"
                 data-category="<?= $product['category_id'] ?>"
                 data-barcode="<?= $product['barcode'] ?>"
                 data-quantity="<?= $product['quantity'] ?>">
                <div class="product-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= upload('products/' . $product['image']) ?>" alt="<?= $product['name'] ?>">
                    <?php else: ?>
                        <span class="material-icons-round">inventory_2</span>
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
    </div>
    
    <div class="pos-cart">
        <div class="cart-header">
            <h3><span class="material-icons-round">shopping_cart</span> السلة</h3>
            <button class="btn-clear" onclick="clearCart()">
                <span class="material-icons-round">delete_sweep</span>
            </button>
        </div>
        
        <div class="cart-items" id="cartItems">
            <p class="empty-cart">السلة فارغة</p>
        </div>
        
        <div class="cart-summary">
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
        
        <div class="cart-customer">
            <label>العميل (اختياري):</label>
            <select id="customerSelect">
                <option value="">زبون نقدي</option>
                <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['id'] ?>"><?= $customer['full_name'] ?> - <?= $customer['phone'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="cart-actions">
            <button class="btn btn-success btn-block" onclick="completeSale()">
                <span class="material-icons-round">check_circle</span>
                إتمام البيع
            </button>
            <a href="<?= url('/pos/installment') ?>" class="btn btn-primary btn-block">
                <span class="material-icons-round">credit_score</span>
                بيع بالتقسيط
            </a>
        </div>
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
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

.product-card.out-of-stock {
    opacity: 0.5;
    pointer-events: none;
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
</style>

<script>
let cart = [];
const currency = '<?= $settings['currency'] ?? 'ج.م' ?>';

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
        return;
    }
    
    container.innerHTML = cart.map((item, index) => `
        <div class="cart-item">
            <div class="cart-item-info">
                <h5>${item.name}</h5>
                <small>${item.price.toFixed(2)} ${currency}</small>
            </div>
            <div class="cart-item-qty">
                <button onclick="changeQty(${index}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="changeQty(${index}, 1)">+</button>
            </div>
            <button class="cart-item-remove" onclick="removeItem(${index})">
                <span class="material-icons-round">close</span>
            </button>
        </div>
    `).join('');
    
    updateTotals();
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
}

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

// تصفية بالتصنيف
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        
        document.querySelectorAll('.product-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// البحث
document.getElementById('productSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const barcode = card.dataset.barcode || '';
        
        if (name.includes(query) || barcode.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
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
