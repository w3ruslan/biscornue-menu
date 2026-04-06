<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }

$db = getDB();
$products   = $db->query('SELECT p.*,c.name AS cat_name, c.color AS cat_color FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY c.display_order, p.display_order, p.name')->fetchAll();
$categories = $db->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();
$total  = count($products);
$active = count(array_filter($products, fn($p) => $p['active']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — La Biscornue</title>
  <link rel="stylesheet" href="assets/admin.css?v=<?= filemtime(__DIR__.'/assets/admin.css') ?>">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <span>🥙</span>
    <div>
      <div class="s-name">La Biscornue</div>
      <div class="s-role">Admin</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active">🍽️ Menu</a>
    <a href="categories.php" class="nav-item">🏷️ Catégories</a>
    <a href="../index.php" class="nav-item" target="_blank">🌐 Voir le site</a>
    <a href="logout.php" class="nav-item logout">🚪 Déconnexion</a>
  </nav>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <h1>🍽️ Gestion du menu</h1>
    <button class="btn-primary" onclick="openAddModal()">+ Ajouter un plat</button>
  </div>

  <div class="stats">
    <div class="stat-card"><div class="stat-num"><?= $total ?></div><div class="stat-lbl">Total plats</div></div>
    <div class="stat-card"><div class="stat-num"><?= $active ?></div><div class="stat-lbl">Visibles</div></div>
    <div class="stat-card"><div class="stat-num"><?= count($categories) ?></div><div class="stat-lbl">Catégories</div></div>
  </div>

  <!-- Filter bar -->
  <div class="filter-bar">
    <input type="text" id="adminSearch" class="search-input" placeholder="🔍 Rechercher..." oninput="filterCards()">
    <div class="cat-tabs" id="catTabs">
      <button class="cat-tab active" onclick="filterCat(0,this)">Tous (<?= $total ?>)</button>
      <?php foreach ($categories as $c):
        $cnt = count(array_filter($products, fn($p) => $p['category_id'] == $c['id']));
      ?>
      <button class="cat-tab" onclick="filterCat(<?= $c['id'] ?>,this)">
        <?= $c['name'] ?> (<?= $cnt ?>)
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Card grid -->
  <div class="prod-grid" id="prodGrid">
    <?php foreach ($products as $p):
      $imgSrc = $p['image_url'] ?? '';
      if ($imgSrc && str_starts_with($imgSrc,'uploads/')) $imgSrc = '../'.$imgSrc;
    ?>
    <div class="admin-card <?= $p['active'] ? '' : 'inactive' ?>"
         id="row-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>"
         data-cat="<?= (int)($p['category_id']??0) ?>"
         data-name="<?= strtolower(htmlspecialchars($p['name'].' '.($p['description']??''))) ?>">
      <div class="ac-top">
        <span class="drag-handle">⠿</span>
        <input type="checkbox" class="row-check" value="<?= $p['id'] ?>" onchange="updateBulk()">
      </div>
      <div class="ac-img">
        <?php if ($imgSrc): ?>
          <img src="<?= htmlspecialchars($imgSrc) ?>" alt="">
        <?php else: ?>
          <span class="ac-emoji"><?= $p['cat_color'] ? '🍽️' : '🍽️' ?></span>
        <?php endif; ?>
      </div>
      <div class="ac-info">
        <div class="ac-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="ac-price">
          <?php if ($p['price_from']): ?>
            <?= $p['price_to'] && $p['price_to'] != $p['price_from']
              ? '€'.number_format($p['price_from'],2).' – €'.number_format($p['price_to'],2)
              : '€'.number_format($p['price_from'],2) ?>
          <?php else: ?>—<?php endif; ?>
        </div>
        <?php if ($p['cat_name']): ?>
          <span class="ac-cat" style="background:<?= htmlspecialchars($p['cat_color']??'#d4a853') ?>20;color:<?= htmlspecialchars($p['cat_color']??'#d4a853') ?>"><?= htmlspecialchars($p['cat_name']) ?></span>
        <?php endif; ?>
      </div>
      <div class="ac-actions">
        <button class="ac-eye <?= $p['active']?'':'eye-off' ?>" id="eye-<?= $p['id'] ?>"
          onclick="toggleActive(<?= $p['id'] ?>,<?= $p['active'] ?>)"><?= $p['active']?'👁️':'🙈' ?></button>
        <button class="ac-edit" onclick='openEditModal(<?= json_encode($p,JSON_HEX_APOS|JSON_HEX_TAG|JSON_HEX_AMP) ?>)'>✏️</button>
        <button class="ac-del" onclick="delProduct(<?= $p['id'] ?>,'<?= addslashes($p['name']) ?>')">🗑️</button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$products): ?>
    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9a8572">Aucun plat. Cliquez sur "+ Ajouter".</div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL -->
<div id="modal" class="modal-overlay" style="display:none" onclick="if(event.target.id==='modal')closeModal()">
  <div class="modal-box">
    <div class="modal-header">
      <h2 id="modalTitle">Ajouter un plat</h2>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div style="padding:24px">
      <!-- Image -->
      <div class="img-row">
        <div class="img-preview" id="imgPreview"><span id="imgPh">📷</span></div>
        <div style="flex:1">
          <label>URL de l'image</label>
          <div style="display:flex;gap:8px;margin-top:4px">
            <input type="text" id="fImgUrl" placeholder="https://..." oninput="previewImg(this.value)" style="flex:1">
          </div>
        </div>
      </div>
      <input type="hidden" id="fId">
      <div class="form-row">
        <div class="form-group">
          <label>Nom du plat *</label>
          <input type="text" id="fName" placeholder="ex: Kebab">
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <select id="fCat">
            <option value="">— Choisir —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea id="fDesc" rows="3" placeholder="Description du plat..."></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Prix de (€)</label>
          <input type="number" id="fPriceFrom" placeholder="10.00" step="0.10" min="0">
        </div>
        <div class="form-group">
          <label>Prix jusqu'à (€)</label>
          <input type="number" id="fPriceTo" placeholder="14.00" step="0.10" min="0">
        </div>
      </div>
      <div class="form-group">
        <label>Visible sur le site</label>
        <select id="fActive">
          <option value="1">✅ Oui</option>
          <option value="0">❌ Non</option>
        </select>
      </div>
      <div class="modal-actions">
        <button onclick="closeModal()" class="btn-cancel">Annuler</button>
        <button onclick="saveProduct()" class="btn-save" id="saveBtn">💾 Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<script>
var activeCat = 0;

// ── Filter ────────────────────────────────────────
function filterCat(id, btn) {
  activeCat = id;
  document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterCards();
}
function filterCards() {
  var q = (document.getElementById('adminSearch').value||'').toLowerCase().trim();
  var shown = 0;
  document.querySelectorAll('#prodGrid .admin-card').forEach(function(card) {
    var catOk  = activeCat === 0 || parseInt(card.dataset.cat) === activeCat;
    var nameOk = !q || (card.dataset.name||'').includes(q);
    var vis = catOk && nameOk;
    card.style.setProperty('display', vis ? 'flex' : 'none', 'important');
    if (vis) shown++;
  });
}

// ── Drag-drop reorder ─────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  Sortable.create(document.getElementById('prodGrid'), {
    handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost',
    onEnd: async function() {
      const ids = [...document.querySelectorAll('#prodGrid [data-id]')].map(el=>el.dataset.id);
      await fetch('../api/admin.php?action=reorder', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ids})
      });
    }
  });
});

// ── Toggle active ─────────────────────────────────
async function toggleActive(id, cur) {
  const nv = cur ? 0 : 1;
  await fetch('../api/admin.php?action=toggle', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id, active: nv})
  });
  const card = document.getElementById('row-'+id);
  const btn  = document.getElementById('eye-'+id);
  if (nv===0){ card.classList.add('inactive'); btn.textContent='🙈'; btn.classList.add('eye-off'); btn.setAttribute('onclick','toggleActive('+id+',0)'); }
  else       { card.classList.remove('inactive'); btn.textContent='👁️'; btn.classList.remove('eye-off'); btn.setAttribute('onclick','toggleActive('+id+',1)'); }
}

// ── Bulk ──────────────────────────────────────────
function updateBulk() {}

// ── Delete ────────────────────────────────────────
async function delProduct(id, name) {
  if (!confirm('Supprimer "'+name+'" ?')) return;
  await fetch('../api/admin.php?action=delete&id='+id, {method:'DELETE'});
  document.getElementById('row-'+id)?.remove();
}

// ── Modal ─────────────────────────────────────────
var selImgUrl = '';

function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Ajouter un plat';
  clearForm(); document.getElementById('modal').style.display = 'flex';
}
function openEditModal(p) {
  document.getElementById('modalTitle').textContent = 'Modifier le plat';
  document.getElementById('fId').value       = p.id;
  document.getElementById('fName').value     = p.name || '';
  document.getElementById('fDesc').value     = p.description || '';
  document.getElementById('fPriceFrom').value= p.price_from || '';
  document.getElementById('fPriceTo').value  = p.price_to || '';
  document.getElementById('fCat').value      = p.category_id || '';
  document.getElementById('fActive').value   = p.active;
  selImgUrl = p.image_url || '';
  previewImg(selImgUrl);
  document.getElementById('fImgUrl').value   = selImgUrl;
  document.getElementById('modal').style.display = 'flex';
}
function closeModal() { document.getElementById('modal').style.display = 'none'; }
function clearForm() {
  ['fId','fName','fDesc','fPriceFrom','fPriceTo','fImgUrl'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('fCat').value=''; document.getElementById('fActive').value='1';
  selImgUrl=''; previewImg('');
}
function previewImg(url) {
  const box = document.getElementById('imgPreview');
  const ph  = document.getElementById('imgPh');
  if (url && url.startsWith('http')) {
    box.innerHTML = '<img src="'+url+'" style="width:100%;height:100%;object-fit:cover;border-radius:8px">';
    selImgUrl = url;
  } else {
    box.innerHTML = '<span id="imgPh">📷</span>';
  }
}
async function saveProduct() {
  const name = document.getElementById('fName').value.trim();
  if (!name) { alert('Le nom est obligatoire.'); return; }
  const btn = document.getElementById('saveBtn');
  btn.disabled = true; btn.textContent = '⏳...';
  const id = document.getElementById('fId').value;
  const payload = {
    id: id||undefined, name,
    description: document.getElementById('fDesc').value.trim(),
    price_from: document.getElementById('fPriceFrom').value||null,
    price_to:   document.getElementById('fPriceTo').value||null,
    category_id:document.getElementById('fCat').value||null,
    image_url:  selImgUrl || document.getElementById('fImgUrl').value.trim(),
    active:     parseInt(document.getElementById('fActive').value),
  };
  const res  = await fetch('../api/admin.php?action='+(id?'edit':'add'), {
    method: id?'PUT':'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const json = await res.json();
  if (json.ok) { closeModal(); location.reload(); }
  else { alert('Erreur: '+(json.error||'?')); btn.disabled=false; btn.textContent='💾 Enregistrer'; }
}
</script>
</body>
</html>
