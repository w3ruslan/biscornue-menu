<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SHOP_NAME ?> — Menu</title>
  <meta name="description" content="La Biscornue — Restaurant grec à Oudon. Sandwichs, tacos, burgers faits maison.">
  <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__.'/assets/style.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>

<!-- 18+ Gate -->
<div id="ageGate" class="age-gate-overlay">
  <div class="age-gate-box">
    <div class="age-gate-logo">🥙</div>
    <div class="age-gate-brand"><?= SHOP_NAME ?></div>
    <div class="age-gate-sub">Restaurant grec · Oudon</div>
    <p class="age-gate-text">Ce site peut contenir des produits avec de l'alcool.<br>Veuillez confirmer votre âge.</p>
    <p class="age-gate-q">Avez-vous <strong>18 ans ou plus</strong> ?</p>
    <div class="age-gate-btns">
      <button class="age-gate-yes" onclick="ageAccept()">✅ Oui, j'ai 18 ans ou plus</button>
      <button class="age-gate-no"  onclick="ageDecline()">❌ Non, j'ai moins de 18 ans</button>
    </div>
    <p class="age-gate-legal">En entrant, vous confirmez avoir l'âge légal requis.</p>
  </div>
</div>
<script>
(function(){
  if(!sessionStorage.getItem('age_ok')){
    document.getElementById('ageGate').style.display='flex';
    document.body.style.overflow='hidden';
  }
})();
function ageAccept(){
  sessionStorage.setItem('age_ok','1');
  var g=document.getElementById('ageGate');
  g.style.opacity='0'; g.style.transition='opacity .4s';
  setTimeout(function(){g.style.display='none';document.body.style.overflow='';},400);
}
function ageDecline(){
  document.getElementById('ageGate').innerHTML=
    '<div class="age-gate-box" style="text-align:center">'+
    '<div style="font-size:56px">🚫</div>'+
    '<h2 style="margin:16px 0 10px;color:#c0392b">Accès refusé</h2>'+
    '<p style="color:#888">Ce site est réservé aux personnes majeures.</p>'+
    '</div>';
  setTimeout(function(){window.location.href='https://www.google.fr';},2500);
}
</script>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-badge">⭐ 5.0 · 41 avis Google</div>
      <h1 class="hero-title">La<br><span>Biscornue</span></h1>
      <p class="hero-sub">Restaurant grec fait maison à Oudon.<br>Sandwichs, tacos, burgers & plus.</p>
      <div class="hero-info">
        <span class="hero-chip">📍 Oudon, France</span>
        <span class="hero-chip">💶 10 – 20 €</span>
        <span class="hero-chip">🕐 Ouvert aujourd'hui</span>
      </div>
    </div>
    <div class="hero-right">
      <div class="stars">⭐⭐⭐⭐⭐ <span class="star-count">41 avis</span></div>
      <div style="font-size:56px">🥙</div>
    </div>
  </div>
</section>

<!-- CATEGORY TABS -->
<div class="cat-strip">
  <div class="cat-strip-inner" id="catTabs">
    <button class="cat-tab active" onclick="switchCategory(0,this)">🍽️ Tout</button>
    <?php
    try {
      $db = getDB();
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
    placeholder="Rechercher un plat..."
    oninput="filterSearch()">
</div>

<!-- MENU -->
<div class="menu-wrap">
  <div id="menuWrap">
    <div style="text-align:center;padding:60px;color:#9a8572">Chargement du menu...</div>
  </div>
</div>

<!-- DETAIL OVERLAY -->
<div id="detailOverlay" style="display:none"></div>

<!-- FOOTER -->
<footer class="site-footer">
  <p><strong><?= SHOP_NAME ?></strong> · Restaurant grec · Oudon, France</p>
  <p style="margin-top:6px">Les prix peuvent varier selon les options choisies.</p>
</footer>

<script src="assets/app.js?v=<?= filemtime(__DIR__.'/assets/app.js') ?>"></script>
</body>
</html>
