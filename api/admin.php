<?php
require_once __DIR__ . '/../config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) { http_response_code(401); echo json_encode(['error'=>'Non autorisé']); exit; }

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── Products ──────────────────────────────────────
if ($action === 'add' && $method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $db->prepare('INSERT INTO products (name,description,price_from,price_to,category_id,image_url,active) VALUES (?,?,?,?,?,?,?)')
       ->execute([$d['name'],$d['description']??'',$d['price_from']??null,$d['price_to']??null,$d['category_id']??null,$d['image_url']??'', $d['active']??1]);
    echo json_encode(['ok'=>true,'id'=>$db->lastInsertId()]); exit;
}

if ($action === 'edit' && $method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true);
    $db->prepare('UPDATE products SET name=?,description=?,price_from=?,price_to=?,category_id=?,image_url=?,active=? WHERE id=?')
       ->execute([$d['name'],$d['description']??'',$d['price_from']??null,$d['price_to']??null,$d['category_id']??null,$d['image_url']??'',$d['active']??1,$d['id']]);
    echo json_encode(['ok'=>true]); exit;
}

if ($action === 'delete' && $method === 'DELETE') {
    $id = (int)($_GET['id']??0);
    if ($id) $db->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    echo json_encode(['ok'=>true]); exit;
}

if ($action === 'toggle' && $method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $db->prepare('UPDATE products SET active=? WHERE id=?')->execute([(int)$d['active'],(int)$d['id']]);
    echo json_encode(['ok'=>true]); exit;
}

if ($action === 'reorder' && $method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $ids = array_map('intval', $d['ids']??[]);
    $s = $db->prepare('UPDATE products SET display_order=? WHERE id=?');
    foreach ($ids as $ord => $id) $s->execute([$ord, $id]);
    echo json_encode(['ok'=>true]); exit;
}

// ── Categories ────────────────────────────────────
if ($action === 'add_cat' && $method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $db->prepare('INSERT INTO categories (name,icon,color,display_order) VALUES (?,?,?,?)')
       ->execute([$d['name'],$d['icon']??'🍽️',$d['color']??'#d4a853',(int)($d['display_order']??0)]);
    echo json_encode(['ok'=>true]); exit;
}

if ($action === 'edit_cat' && $method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true);
    $db->prepare('UPDATE categories SET name=?,icon=?,color=?,display_order=? WHERE id=?')
       ->execute([$d['name'],$d['icon']??'🍽️',$d['color']??'#d4a853',(int)($d['display_order']??0),(int)$d['id']]);
    echo json_encode(['ok'=>true]); exit;
}

if ($action === 'delete_cat' && $method === 'DELETE') {
    $id = (int)($_GET['id']??0);
    if ($id) $db->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
    echo json_encode(['ok'=>true]); exit;
}

echo json_encode(['error'=>'Action inconnue']);
