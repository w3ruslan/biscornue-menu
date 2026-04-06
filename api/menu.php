<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$db = getDB();
$rows = $db->query('
    SELECT p.*,
           c.name  AS category_name,
           c.icon  AS category_icon,
           c.color AS category_color
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.active = 1
    ORDER BY c.display_order, p.display_order, p.name
')->fetchAll();

// Attach options to each product
$products = [];
foreach ($rows as $p) {
    $pid = $p['id'];
    // Load option groups + items
    $groups = $db->prepare('SELECT * FROM options_groups WHERE product_id=? ORDER BY display_order');
    $groups->execute([$pid]);
    $grpRows = $groups->fetchAll();
    $opts = [];
    foreach ($grpRows as $g) {
        $items = $db->prepare('SELECT * FROM options_items WHERE group_id=? ORDER BY display_order');
        $items->execute([$g['id']]);
        $opts[] = [
            'id'    => $g['id'],
            'title' => $g['title'],
            'items' => $items->fetchAll()
        ];
    }
    $p['options'] = $opts;
    $products[] = $p;
}

echo json_encode($products);
