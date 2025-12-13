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
        
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?= $product['id'] ?>" 
                 data-name="<?= htmlspecialchars($product['name']) ?>"
                 data-price="<?= $product['installment_price'] ?: $product['cash_price'] ?>"
                 data-category="<?= $product['category_id'] ?>">
                <div class="product-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= upload('products/' . $product['image']) ?>" alt="">
                    <?php else: ?>
                        <span class="material-icons-round">inventory_2</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h4><?= $product['name'] ?></h4>
                    <p class="product-price"><?= formatMoney($product['installment_price'] ?: $product['cash_price']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="pos-cart installment-cart">
        <div class="cart-header">
            <h3><span class="material-icons-round">credit_score</span> بيع بالتقسيط</h3>
        </div>
        
        <div class="customer-select">
            <label>العميل *</label>
            <select id="customerSelect" required>
                <option value="">اختر العميل</option>
                <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['id'] ?>"><?= $customer['full_name'] ?> - <?= $customer['phone'] ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= url('/customers/create') ?>" class="btn-add-customer">+ إضافة عميل جديد</a>
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
.cart-header { padding: 20px; border-bottom: 1px solid var(--border-color); }
.cart-header h3 { display: flex; align-items: center; gap: 10px; font-size: 16px; }
.customer-select { padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
.customer-select label { display: block; margin-bottom: 8px; font-weight: 600; }
.customer-select select { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; }
.btn-add-customer { display: block; margin-top: 10px; font-size: 13px; color: var(--primary); }
.cart-items { flex: 1; padding: 15px; overflow-y: auto; min-height: 100px; }
.empty-cart { text-align: center; color: var(--text-muted); padding: 20px 0; }
.cart-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--bg-main); border-radius: 8px; margin-bottom: 10px; }
.cart-item-info { flex: 1; }
.cart-item-info h5 { font-size: 13px; margin-bottom: 3px; }
.cart-item-info small { color: var(--text-muted); }
.cart-item-remove { background: none; border: none; color: var(--danger); cursor: pointer; }
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
</style>

<script>
let cart = [];
const currency = '<?= $settings['currency'] ?? 'ج.م' ?>';

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
            <div class="cart-item-info">
                <h5>${item.name}</h5>
                <small>${item.price.toFixed(2)} ${currency} × ${item.quantity}</small>
            </div>
            <strong>${(item.price * item.quantity).toFixed(2)}</strong>
            <button class="cart-item-remove" onclick="removeItem(${index})">
                <span class="material-icons-round">close</span>
            </button>
        </div>
    `).join('');
    
    calculateInstallment();
}

function removeItem(index) { cart.splice(index, 1); renderCart(); }

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

calculateInstallment();
</script>
