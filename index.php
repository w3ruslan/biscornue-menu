<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SHOP_NAME ?> — Menu</title>
  <meta name="description" content="La Biscornue — Restaurant grec à Oudon. Sandwichs, tacos, burgers faits maison.">
  <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__.'/assets/style.css') ?>">
</head>
<body>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-badge">⭐ 5.0 · 41 avis Google</div>
      <h1 class="hero-title">La<br><span>Biscornue</span></h1>
      <p class="hero-sub">Restaurant grec fait maison à Oudon.<br>Sandwichs, tacos, burgers &amp; plus.</p>
      <div class="hero-chips">
        <span class="hero-chip">📍 Oudon, France</span>
        <span class="hero-chip">💶 10 – 20 €</span>
        <span class="hero-chip">🕐 Ouvert aujourd'hui</span>
      </div>
    </div>
    <div class="hero-right">
      <span class="hero-emoji">🥙</span>
      <div class="hero-stars">⭐⭐⭐⭐⭐ <span>41 avis</span></div>
    </div>
  </div>
</section>

<!-- CATEGORY TABS -->
<div class="cat-strip">
  <div class="cat-strip-inner" id="catTabs">
    <button class="cat-tab active" onclick="switchCategory(0,this)">🍽️ Tout</button>
    <?php
    try {
      $db   = getDB();
      $cats = $db->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();
      foreach ($cats as $c):
    ?>
    <button class="cat-tab" onclick="switchCategory(<?= $c['id'] ?>,this)">
      <?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?>
    </button>
    <?php endforeach; } catch(Exception $e) {} ?>
  </div>
</div>

<!-- SEARCH -->
<div class="search-wrap" style="margin-top:28px">
  <input type="text" id="searchBox" class="search-box"
    placeholder="🔍 Rechercher un plat..."
    oninput="filterSearch()">
</div>

<!-- MENU -->
<div class="menu-wrap">
  <div id="menuWrap">
    <div style="text-align:center;padding:60px;color:rgba(255,255,255,.4)">Chargement…</div>
  </div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
  <p><strong><?= SHOP_NAME ?></strong> · Restaurant grec · Oudon, France</p>
  <p style="margin-top:6px;opacity:.6">Les prix peuvent varier selon les options choisies.</p>
</footer>

<script src="assets/app.js?v=<?= filemtime(__DIR__.'/assets/app.js') ?>"></script>
</body>
</html>
