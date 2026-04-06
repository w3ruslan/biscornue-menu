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

// ── Categories ─────────────────────────
$cats = [
    [1, 'Sandwichs',    '🥙', '#c0392b', 1],
    [2, 'Tacos',        '🌯', '#e67e22', 2],
    [3, 'Burgers',      '🍔', '#27ae60', 3],
    [4, 'Menu Enfant',  '👶', '#2980b9', 4],
    [5, 'Petit Faim',   '🍟', '#8e44ad', 5],
];
foreach ($cats as [$id,$name,$icon,$color,$order]) {
    $db->prepare("INSERT IGNORE INTO categories (id,name,icon,color,display_order) VALUES (?,?,?,?,?)")
       ->execute([$id,$name,$icon,$color,$order]);
}

// ── Products ───────────────────────────
$products = [
    [1, 1,'Kebab',          'Viande kebab marinée, crudités, sauce au choix dans pain pita ou galette.',           10.00,12.00,1],
    [2, 1,'La Curryosite',  'Poulet curry maison, légumes frais, sauce au choix dans pain pita ou galette.',       10.00,12.00,2],
    [3, 1,'Végétarien',     'Légumes grillés, feta, sauce au choix dans pain pita ou galette.',                    10.00,12.00,3],
    [4, 1,'Berlineur',      'Notre sandwich signature avec viande et garnitures maison.',                          12.00,14.00,4],
    [5, 2,'Tacos 1 Viande', 'Galette grillée, viande au choix, frites, fromage fondu, sauce fromagère.',          10.00,12.00,1],
    [6, 2,'Tacos 2 Viandes','Galette grillée, 2 viandes au choix, frites, fromage fondu, sauce fromagère.',       12.00,14.00,2],
    [7, 2,'Tacos 3 Viandes','Galette grillée, 3 viandes au choix, frites, fromage fondu, sauce fromagère.',       14.00,16.00,3],
    [8, 3,'La Biquette',    'Steak haché maison, cheddar, salade, tomate, oignons, sauce au choix.',              12.00,14.00,1],
    [9, 3,'Le Majestueux',  'Double steak haché maison, double cheddar, bacon, sauce spéciale maison.',           12.00,14.00,2],
    [10,3,'TOTORO',         'Notre burger signature, recette secrète. Sauce spéciale non modifiable.',             13.00,15.00,3],
    [11,4,'Cheeseburger Menu','Cheeseburger + frites. Avec ou sans boisson.',                                     7.90, 8.90, 1],
    [12,4,'Nuggets Menu',   '5 nuggets + frites. Avec ou sans boisson.',                                          7.90, 8.90, 2],
    [13,5,'Frites',         'Croustillantes, dorées à souhait.',                                                   3.00, 6.00, 1],
    [14,5,'Tenders',        'Filets de poulet croustillants, sauce au choix.',                                     5.00,10.00,2],
    [15,5,'Nuggets',        'Moelleux à l\'intérieur, croustillants dehors, sauce au choix.',                     4.00, 8.00, 3],
];
foreach ($products as [$id,$cat,$name,$desc,$pf,$pt,$order]) {
    $db->prepare("INSERT IGNORE INTO products (id,category_id,name,description,price_from,price_to,display_order) VALUES (?,?,?,?,?,?,?)")
       ->execute([$id,$cat,$name,$desc,$pf,$pt,$order]);
}

// ── Options: clear & re-seed ───────────
$db->exec('DELETE FROM options_items');
$db->exec('DELETE FROM options_groups');

// Helper
function addGroup($db, $product_id, $title, $order, $items) {
    $db->prepare("INSERT INTO options_groups (product_id,title,display_order) VALUES (?,?,?)")
       ->execute([$product_id, $title, $order]);
    $gid = $db->lastInsertId();
    foreach ($items as $i => [$label, $price]) {
        $db->prepare("INSERT INTO options_items (group_id,label,price,display_order) VALUES (?,?,?,?)")
           ->execute([$gid, $label, $price, $i]);
    }
}

// ── Sandwichs (1-4): Pain + Sauce ──────
$pain  = [['Pita',0],['Galette',0]];
$sauces = [['Blanche',0],['Harissa',0],['Algérienne',0],['BBQ',0],['Ketchup',0],['Samourai',0],['Sauce Verte',0]];
foreach ([1,2,3,4] as $pid) {
    addGroup($db, $pid, 'Ton Pain',   0, $pain);
    addGroup($db, $pid, 'Ta Sauce',   1, $sauces);
}
// Viande pour Kebab et Berlineur
addGroup($db, 1, 'Ta Viande', 2, [['Kebab',0],['Poulet',0],['Mixte',0]]);
addGroup($db, 4, 'Ta Viande', 2, [['Kebab',0],['Poulet',0],['Mixte',0]]);

// ── Tacos (5-7): Viande(s) + Sauce fromagère ──
$viandes1 = [['Kebab',0],['Poulet',0],['Mixte',0]];
$fromagere = [['Classique',0],['Épicée',0],['Extra Cheese',2.00]];
addGroup($db, 5, 'Ta Viande',          0, $viandes1);
addGroup($db, 5, 'Sauce Fromagère',    1, $fromagere);
addGroup($db, 6, 'Viande 1',           0, $viandes1);
addGroup($db, 6, 'Viande 2',           1, $viandes1);
addGroup($db, 6, 'Sauce Fromagère',    2, $fromagere);
addGroup($db, 7, 'Viande 1',           0, $viandes1);
addGroup($db, 7, 'Viande 2',           1, $viandes1);
addGroup($db, 7, 'Viande 3',           2, $viandes1);
addGroup($db, 7, 'Sauce Fromagère',    3, $fromagere);

// ── Burgers (8-10): Cuisson + Extras ──
$cuisson = [['À point',0],['Saignant',0],['Bien cuit',0]];
$extras  = [['+Fromage supp.',1.00],['+Bacon',2.00],['+Œuf',1.50],['+Avocat',1.50]];
addGroup($db, 8, 'Cuisson',  0, $cuisson);
addGroup($db, 8, 'Extras',   1, $extras);
addGroup($db, 9, 'Cuisson',  0, $cuisson);
addGroup($db, 9, 'Extras',   1, $extras);
addGroup($db,10, 'Cuisson',  0, $cuisson);

// ── Menus Enfants (11-12): Boisson ─────
$boissons = [['Eau',0],['Jus d\'Orange',0],['Coca-Cola',0],['Sans Boisson',-1.00]];
addGroup($db, 11, 'Ta Boisson', 0, $boissons);
addGroup($db, 12, 'Ta Boisson', 0, $boissons);

// ── Petit Faim (13-15): Taille + Sauce ─
addGroup($db, 13, 'Ta Taille', 0, [['Petite — €3',0],['Grande — €6',3.00]]);
addGroup($db, 14, 'Quantité',  0, [['3 Tenders — €5',0],['6 Tenders — €10',5.00]]);
addGroup($db, 14, 'Ta Sauce',  1, $sauces);
addGroup($db, 15, 'Quantité',  0, [['6 Nuggets — €4',0],['12 Nuggets — €8',4.00]]);
addGroup($db, 15, 'Ta Sauce',  1, $sauces);

echo "<h2 style='font-family:sans-serif;color:green;padding:20px'>✅ Base de données créée avec succès!</h2>";
echo "<p style='font-family:sans-serif;padding:0 20px'><a href='index.php'>→ Aller sur le site</a> &nbsp;|&nbsp; <a href='admin/'>→ Admin</a></p>";
