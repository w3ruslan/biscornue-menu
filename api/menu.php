<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$db = getDB();

// Categories
$cats = $db->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();

// Products
$rows = $db->query('
    SELECT p.*,
           c.name  AS cat_name,
           c.icon  AS cat_icon,
           c.color AS cat_color
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.active = 1
    ORDER BY c.display_order, p.display_order, p.name
')->fetchAll();

// Attach options
$products = [];
foreach ($rows as $p) {
    $pid    = $p['id'];
    $groups = $db->prepare('SELECT * FROM options_groups WHERE product_id=? ORDER BY display_order');
    $groups->execute([$pid]);
    $opts = [];
    foreach ($groups->fetchAll() as $g) {
        $items = $db->prepare('SELECT * FROM options_items WHERE group_id=? ORDER BY display_order');
        $items->execute([$g['id']]);
        $itemRows = [];
        foreach ($items->fetchAll() as $item) {
            $itemRows[] = [
                'id'          => $item['id'],
                'name'        => $item['label'],
                'price_extra' => $item['price'] ?? 0,
            ];
        }
        $opts[] = [
            'id'    => $g['id'],
            'name'  => $g['title'],
            'items' => $itemRows,
        ];
    }
    $p['options'] = $opts;
    $products[]   = $p;
}

echo json_encode(['products' => $products, 'categories' => $cats]);
