<?php
/**
 * إضافة منتجات مع صور من Unsplash (مجاني بدون API)
 */

$dbPath = __DIR__ . '/database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$imagesDir = __DIR__ . '/public/assets/images/products';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

echo "بدء إضافة المنتجات مع الصور...\n\n";

// منتجات لكل تصنيف مع روابط صور Unsplash
$productsData = [
    1 => [ // تلفزيونات
        ['تلفزيون سامسونج 55 بوصة سمارت 4K', 15000, 17250, 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=400'],
        ['تلفزيون LG 50 بوصة Ultra HD', 12000, 13800, 'https://images.unsplash.com/photo-1567690187548-f07b1d7bf5a9?w=400'],
        ['تلفزيون سوني 65 بوصة OLED', 25000, 28750, 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=400'],
        ['تلفزيون توشيبا 43 بوصة سمارت', 8000, 9200, 'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=400'],
        ['تلفزيون TCL 55 بوصة اندرويد', 9500, 10925, 'https://images.unsplash.com/photo-1558888401-3cc1de77652d?w=400'],
        ['تلفزيون هايسنس 58 بوصة', 11000, 12650, 'https://images.unsplash.com/photo-1571415060716-baff5f717c37?w=400'],
        ['تلفزيون فيليبس 50 بوصة امبيلايت', 10500, 12075, 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=400'],
        ['تلفزيون شارب 45 بوصة LED', 7500, 8625, 'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=400'],
    ],
    2 => [ // ثلاجات
        ['ثلاجة توشيبا 18 قدم نوفروست سيلفر', 18000, 20700, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
        ['ثلاجة شارب 16 قدم نوفروست', 15000, 17250, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
        ['ثلاجة LG 20 قدم انفرتر', 22000, 25300, 'https://images.unsplash.com/photo-1536353284924-9220c464e262?w=400'],
        ['ثلاجة سامسونج 24 قدم فرنش دور', 28000, 32200, 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400'],
        ['ثلاجة كريازي 14 قدم ديفروست', 12000, 13800, 'https://images.unsplash.com/photo-1619624683930-5c12c2500c6c?w=400'],
        ['ثلاجة الاسكا 16 قدم نوفروست', 14000, 16100, 'https://images.unsplash.com/photo-1606787366850-de6330128bfc?w=400'],
        ['ثلاجة فريش 18 قدم ديجيتال', 16000, 18400, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['ثلاجة وايت ويل 20 قدم', 19000, 21850, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
    ],
    3 => [ // غسالات
        ['غسالة سامسونج 8 كيلو فول أوتوماتيك', 12000, 13800, 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=400'],
        ['غسالة LG 10 كيلو انفرتر ستيم', 15000, 17250, 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=400'],
        ['غسالة توشيبا 7 كيلو تحميل أمامي', 9000, 10350, 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?w=400'],
        ['غسالة زانوسي 8 كيلو ستيم', 11000, 12650, 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400'],
        ['غسالة وايت بوينت 9 كيلو', 10500, 12075, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['غسالة شارب 7 كيلو فول ديجيتال', 8500, 9775, 'https://images.unsplash.com/photo-1567690187548-f07b1d7bf5a9?w=400'],
        ['غسالة بوش 9 كيلو انفرتر', 18000, 20700, 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400'],
        ['غسالة إيديال 7 كيلو اوتوماتيك', 7000, 8050, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
    ],
    4 => [ // تكييفات
        ['تكييف شارب 1.5 حصان بارد ساخن انفرتر', 12000, 13800, 'https://images.unsplash.com/photo-1631545806609-4b1c1c9b0e4a?w=400'],
        ['تكييف كاريير 2.25 حصان اوبتيماكس', 18000, 20700, 'https://images.unsplash.com/photo-1580595999172-787970a962d9?w=400'],
        ['تكييف يونيون اير 3 حصان انفرتر', 22000, 25300, 'https://images.unsplash.com/photo-1622974909575-9f8878c51c7a?w=400'],
        ['تكييف LG 1.5 حصان دوال انفرتر', 15000, 17250, 'https://images.unsplash.com/photo-1585767934604-5b7e8f9e7f56?w=400'],
        ['تكييف سامسونج 2.25 حصان ديجيتال', 16000, 18400, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['تكييف جري 1.5 حصان بلازما', 11000, 12650, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
        ['تكييف ميديا 2.25 حصان انفرتر', 14000, 16100, 'https://images.unsplash.com/photo-1558888401-3cc1de77652d?w=400'],
        ['تكييف تورنيدو 1.5 حصان بارد', 10000, 11500, 'https://images.unsplash.com/photo-1571415060716-baff5f717c37?w=400'],
    ],
    5 => [ // بوتاجازات
        ['بوتاجاز يونيفرسال 5 شعلة ستانلس', 8000, 9200, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400'],
        ['بوتاجاز فريش 4 شعلة عيون نحاس', 6000, 6900, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
        ['بوتاجاز اي كوك 5 شعلة بلت ان', 9000, 10350, 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?w=400'],
        ['بوتاجاز لاجيرمانيا 5 شعلة ايطالي', 12000, 13800, 'https://images.unsplash.com/photo-1565538810643-b5bdb714032a?w=400'],
        ['بوتاجاز جليم غاز 4 شعلة', 7500, 8625, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400'],
        ['بوتاجاز تكنوجاز 5 شعلة الماني', 8500, 9775, 'https://images.unsplash.com/photo-1556909172-54557c7e4fb7?w=400'],
        ['بوتاجاز وايت ويل 5 شعلة شواية', 7000, 8050, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
        ['بوتاجاز كريازي 4 شعلة اقتصادي', 5500, 6325, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
    ],
    6 => [ // سخانات
        ['سخان غاز اوليمبيك 10 لتر ديجيتال', 3500, 4025, 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400'],
        ['سخان كهرباء تورنيدو 50 لتر', 4000, 4600, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['سخان غاز فريش 6 لتر امان', 2500, 2875, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
        ['سخان كهرباء اريستون 80 لتر ايطالي', 6000, 6900, 'https://images.unsplash.com/photo-1567690187548-f07b1d7bf5a9?w=400'],
        ['سخان غاز وايت بوينت 10 لتر', 3000, 3450, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
        ['سخان كهرباء يونيون 50 لتر', 3500, 4025, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
        ['سخان غاز كريازي 6 لتر اقتصادي', 2200, 2530, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['سخان فوري اريستون كهرباء', 5000, 5750, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
    ],
    7 => [ // ميكروويف
        ['ميكروويف سامسونج 40 لتر كونفكشن', 4500, 5175, 'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?w=400'],
        ['ميكروويف LG 25 لتر سمارت انفرتر', 3500, 4025, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
        ['ميكروويف شارب 30 لتر جريل', 4000, 4600, 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?w=400'],
        ['ميكروويف باناسونيك 27 لتر انفرتر', 4200, 4830, 'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?w=400'],
        ['ميكروويف بلاك اند ديكر 20 لتر', 2500, 2875, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
        ['ميكروويف تورنيدو 25 لتر ديجيتال', 3000, 3450, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
        ['ميكروويف كينوود 30 لتر جريل', 3800, 4370, 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?w=400'],
        ['ميكروويف فريش 23 لتر عادي', 2800, 3220, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
    ],
    8 => [ // أجهزة منزلية
        ['خلاط براون 1000 وات متعدد السرعات', 2500, 2875, 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=400'],
        ['خلاط مولينكس 800 وات فرنسي', 2000, 2300, 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=400'],
        ['مكنسة كهربائية باناسونيك 2000 وات', 4000, 4600, 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=400'],
        ['مكنسة كهربائية فيليبس 1800 وات', 3500, 4025, 'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=400'],
        ['مكواة بخار تيفال 2400 وات', 1500, 1725, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=400'],
        ['مكواة بخار فيليبس باور لايف', 1800, 2070, 'https://images.unsplash.com/photo-1562913844-31e29e3e6e58?w=400'],
        ['ماكينة قهوة ديلونجي اسبريسو', 5000, 5750, 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=400'],
        ['فرن كهرباء تورنيدو 48 لتر تربو', 3000, 3450, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=400'],
        ['ديب فريزر كريازي 5 درج نوفروست', 8000, 9200, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400'],
        ['شفاط مطبخ فريش 60 سم ستانلس', 2500, 2875, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
    ],
    9 => [ // موبايلات
        ['آيفون 15 Pro Max 256GB تيتانيوم', 55000, 63250, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400'],
        ['آيفون 15 128GB أزرق', 42000, 48300, 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400'],
        ['سامسونج Galaxy S24 Ultra 512GB', 50000, 57500, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400'],
        ['سامسونج Galaxy A54 128GB', 15000, 17250, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400'],
        ['شاومي 14 Pro 256GB', 25000, 28750, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400'],
        ['شاومي Redmi Note 13 128GB', 8000, 9200, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400'],
        ['أوبو Reno 11 256GB', 18000, 20700, 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=400'],
        ['ريلمي GT 5 256GB', 16000, 18400, 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=400'],
        ['هواوي Nova 12 256GB', 14000, 16100, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400'],
        ['فيفو V30 256GB', 17000, 19550, 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400'],
    ],
    10 => [ // لابتوبات
        ['لابتوب HP Pavilion 15 Core i7 16GB', 28000, 32200, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400'],
        ['لابتوب Dell Inspiron 14 Core i5', 25000, 28750, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=400'],
        ['لابتوب Lenovo IdeaPad 3 Ryzen 5', 18000, 20700, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400'],
        ['لابتوب ASUS VivoBook 15 Core i5', 20000, 23000, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400'],
        ['لابتوب Acer Aspire 5 Core i5', 16000, 18400, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400'],
        ['لابتوب MacBook Air M2 256GB', 55000, 63250, 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=400'],
        ['لابتوب MacBook Pro 14 M3 Pro', 75000, 86250, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400'],
        ['لابتوب HP Victus Gaming RTX 3050', 35000, 40250, 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?w=400'],
        ['لابتوب Dell G15 Gaming RTX 4060', 38000, 43700, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=400'],
        ['لابتوب MSI GF63 Gaming RTX 3050', 32000, 36800, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400'],
    ],
];

function downloadImage($url, $filepath) {
    $context = stream_context_create([
        'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0'],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    
    $imageContent = @file_get_contents($url, false, $context);
    
    if ($imageContent && strlen($imageContent) > 1000) {
        file_put_contents($filepath, $imageContent);
        return true;
    }
    return false;
}

// حذف المنتجات القديمة
$pdo->exec("DELETE FROM invoice_items");
$pdo->exec("DELETE FROM products");
echo "تم حذف المنتجات القديمة\n\n";

$totalProducts = 0;
$totalImages = 0;

foreach ($productsData as $categoryId => $products) {
    // الحصول على اسم التصنيف
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    $categoryName = $cat ? $cat['name'] : "التصنيف $categoryId";
    
    echo "📁 $categoryName (" . count($products) . " منتج)\n";
    
    foreach ($products as $product) {
        $name = $product[0];
        $cashPrice = $product[1];
        $installmentPrice = $product[2];
        $imageUrl = $product[3];
        $costPrice = $cashPrice * 0.7;
        $quantity = rand(10, 50);
        $barcode = '30' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        // تحميل الصورة
        $imageFilename = 'product_' . md5($name) . '.jpg';
        $imagePath = $imagesDir . '/' . $imageFilename;
        
        echo "   ⏳ $name... ";
        
        if (downloadImage($imageUrl, $imagePath)) {
            echo "✅\n";
            $totalImages++;
            $dbImagePath = $imageFilename;
        } else {
            echo "❌\n";
            $dbImagePath = null;
        }
        
        // إضافة المنتج
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, cash_price, installment_price, cost_price, quantity, min_quantity, barcode, image, is_active) VALUES (?, ?, ?, ?, ?, ?, 5, ?, ?, 1)");
        $stmt->execute([$name, $categoryId, $cashPrice, $installmentPrice, $costPrice, $quantity, $barcode, $dbImagePath]);
        $totalProducts++;
        
        usleep(100000); // 0.1 ثانية
    }
    
    echo "\n";
}

echo "\n✅ اكتمل!\n";
echo "📦 إجمالي المنتجات: $totalProducts\n";
echo "🖼️ الصور المحملة: $totalImages\n";
