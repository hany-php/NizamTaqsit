<?php
require_once __DIR__ . '/../vendor/autoload.php';

// اختبار المسار
$testUri = '/categories/18/move-product';

// محاكاة Router
$routes = [
    ['path' => '/categories/{id}', 'segments' => 2],
    ['path' => '/categories/{id}/move-product', 'segments' => 3],
    ['path' => '/categories/{id}/delete', 'segments' => 3],
];

// ترتيب المسارات
usort($routes, function($a, $b) {
    return $b['segments'] - $a['segments'];
});

echo "Sorted routes:\n";
foreach ($routes as $route) {
    $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
    $pattern = '#^' . $pattern . '$#';
    
    echo "Path: {$route['path']}, Pattern: $pattern\n";
    
    if (preg_match($pattern, $testUri, $matches)) {
        echo "  -> MATCHES! Params: " . json_encode(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)) . "\n";
    }
}
