<?php
require_once 'config.php';
$db = getDB();

$db->exec("CREATE TABLE IF NOT EXISTS categories (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    icon         VARCHAR(10)  DEFAULT '🍽️',
    color        VARCHAR(20)  DEFAULT '#d4a853',
    display_order INT DEFAULT 0
)");

$db->exec("CREATE TABLE IF NOT EXISTS products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT,
    name          VARCHAR(200) NOT NULL,
    description   TEXT,
    price_from    DECIMAL(8,2) DEFAULT NULL,
    price_to      DECIMAL(8,2) DEFAULT NULL,
    image_url     VARCHAR(500) DEFAULT '',
    active        TINYINT(1)   DEFAULT 1,
    display_order INT          DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS options_groups (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    title      VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)");

$db->exec("CREATE TABLE IF NOT EXISTS options_items (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    label    VARCHAR(200) NOT NULL,
    price    DECIMAL(8,2) DEFAULT 0.00,
    display_order INT DEFAULT 0,
    FOREIGN KEY (group_id) REFERENCES options_groups(id) ON DELETE CASCADE
)");

// Seed categories
$cats = [
    [1, 'Sandwichs',    '🥙', '#c0392b', 1],
    [2, 'Tacos',        '🌯', '#e67e22', 2],
    [3, 'Burgers',      '🍔', '#27ae60', 3],
    [4, 'Menu Enfant',  '👶', '#2980b9', 4],
    [5, 'Petit Faim',   '🍟', '#8e44ad', 5],
];
foreach ($cats as [$id, $name, $icon, $color, $order]) {
    $db->prepare("INSERT IGNORE INTO categories (id,name,icon,color,display_order) VALUES (?,?,?,?,?)")
       ->execute([$id,$name,$icon,$color,$order]);
}

// Seed products
$products = [
    // Sandwichs
    [1,1,'Kebab',         'Viande kebab marinée, crudités, sauce au choix dans pain pita ou galette.', 10.00, 12.00, 1],
    [2,1,'La Curryosite', 'Poulet curry maison, légumes frais, sauce au choix dans pain pita ou galette.', 10.00, 12.00, 2],
    [3,1,'Végétarien',    'Légumes grillés, feta, sauce au choix dans pain pita ou galette.', 10.00, 12.00, 3],
    [4,1,'Berlineur',     'Notre sandwich signature avec viande et garnitures maison.', 12.00, 14.00, 4],

    // Tacos
    [5,2,'Tacos 1 Viande', 'Galette grillée, viande au choix, frites, fromage fondu, sauce fromagère.', 10.00, 12.00, 1],
    [6,2,'Tacos 2 Viandes','Galette grillée, 2 viandes au choix, frites, fromage fondu, sauce fromagère.', 12.00, 14.00, 2],
    [7,2,'Tacos 3 Viandes','Galette grillée, 3 viandes au choix, frites, fromage fondu, sauce fromagère.', 14.00, 16.00, 3],

    // Burgers
    [8, 3,'La Biquette',   'Steak haché maison, cheddar, salade, tomate, oignons, sauce au choix.', 12.00, 14.00, 1],
    [9, 3,'Le Majestueux', 'Double steak haché maison, double cheddar, bacon, sauce spéciale maison.', 12.00, 14.00, 2],
    [10,3,'TOTORO',        'Notre burger signature, recette secrète. Sauce spéciale non modifiable.', 13.00, 15.00, 3],

    // Menu Enfant
    [11,4,'Cheeseburger Menu','Cheeseburger + frites. Avec ou sans boisson.', 7.90, 8.90, 1],
    [12,4,'Nuggets Menu',    '5 nuggets + frites. Avec ou sans boisson.', 7.90, 8.90, 2],

    // Petit Faim
    [13,5,'Frites',    'Petite portion €3 · Grande portion €6. Croustillantes, dorées à souhait.', 3.00, 6.00, 1],
    [14,5,'Tenders',   '3 tenders €5 · 6 tenders €10. Filets de poulet croustillants, sauce au choix.', 5.00, 10.00, 2],
    [15,5,'Nuggets',   '6 nuggets €4 · 12 nuggets €8. Moelleux à l\'intérieur, croustillants dehors.', 4.00, 8.00, 3],
];
foreach ($products as [$id,$cat,$name,$desc,$pf,$pt,$order]) {
    $db->prepare("INSERT IGNORE INTO products (id,category_id,name,description,price_from,price_to,display_order) VALUES (?,?,?,?,?,?,?)")
       ->execute([$id,$cat,$name,$desc,$pf,$pt,$order]);
}

echo "<h2 style='font-family:sans-serif;color:green'>✅ Base de données créée avec succès!</h2>";
echo "<p style='font-family:sans-serif'><a href='index.php'>→ Aller sur le site</a> &nbsp;|&nbsp; <a href='admin/'>→ Admin</a></p>";
