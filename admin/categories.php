<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
$db = getDB();
$categories = $db->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catégories — La Biscornue</title>
  <link rel="stylesheet" href="assets/admin.css?v=<?= filemtime(__DIR__.'/assets/admin.css') ?>">
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><span>🥙</span><div><div class="s-name">La Biscornue</div><div class="s-role">Admin</div></div></div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item">🍽️ Menu</a>
    <a href="categories.php" class="nav-item active">🏷️ Catégories</a>
    <a href="../index.php" class="nav-item" target="_blank">🌐 Voir le site</a>
    <a href="logout.php" class="nav-item logout">🚪 Déconnexion</a>
  </nav>
</aside>
<div class="main">
  <div class="topbar"><h1>🏷️ Catégories</h1><button class="btn-primary" onclick="openAdd()">+ Ajouter</button></div>
  <div style="max-width:600px">
    <?php foreach ($categories as $c): ?>
    <div class="cat-row" id="crow-<?= $c['id'] ?>">
      <div style="display:flex;align-items:center;gap:12px;flex:1">
        <span style="font-size:24px"><?= $c['icon'] ?></span>
        <div>
          <div style="font-weight:700"><?= htmlspecialchars($c['name']) ?></div>
          <div style="font-size:11px;color:#9a8572">Ordre: <?= $c['display_order'] ?></div>
        </div>
        <div style="width:20px;height:20px;border-radius:50%;background:<?= htmlspecialchars($c['color']) ?>;margin-left:8px"></div>
      </div>
      <button class="btn-edit" onclick='openEdit(<?= json_encode($c,JSON_HEX_APOS|JSON_HEX_TAG) ?>)'>✏️</button>
      <button class="btn-del" onclick="delCat(<?= $c['id'] ?>,'<?= addslashes($c['name']) ?>')">🗑️</button>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div id="modal" class="modal-overlay" style="display:none" onclick="if(event.target.id==='modal')closeModal()">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header"><h2 id="modalTitle">Catégorie</h2><button class="modal-close" onclick="closeModal()">✕</button></div>
    <div style="padding:24px">
      <input type="hidden" id="fId">
      <div class="form-group"><label>Nom *</label><input type="text" id="fName" placeholder="Sandwichs"></div>
      <div class="form-row">
        <div class="form-group"><label>Icône</label><input type="text" id="fIcon" placeholder="🥙" maxlength="4"></div>
        <div class="form-group"><label>Couleur</label><input type="color" id="fColor" value="#d4a853" style="height:44px;width:100%;border-radius:9px;border:2px solid #e8ddd0;cursor:pointer"></div>
      </div>
      <div class="form-group"><label>Ordre d'affichage</label><input type="number" id="fOrder" value="0" min="0"></div>
      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModal()">Annuler</button>
        <button class="btn-save" onclick="saveCat()">💾 Enregistrer</button>
      </div>
    </div>
  </div>
</div>
<script>
function openAdd(){ document.getElementById('modalTitle').textContent='Nouvelle catégorie'; clearForm(); document.getElementById('modal').style.display='flex'; }
function openEdit(c){ document.getElementById('modalTitle').textContent='Modifier'; document.getElementById('fId').value=c.id; document.getElementById('fName').value=c.name; document.getElementById('fIcon').value=c.icon; document.getElementById('fColor').value=c.color; document.getElementById('fOrder').value=c.display_order; document.getElementById('modal').style.display='flex'; }
function closeModal(){ document.getElementById('modal').style.display='none'; }
function clearForm(){ ['fId','fName','fIcon'].forEach(i=>document.getElementById(i).value=''); document.getElementById('fColor').value='#d4a853'; document.getElementById('fOrder').value='0'; }
async function saveCat(){
  const name=document.getElementById('fName').value.trim();
  if(!name){alert('Nom requis');return;}
  const id=document.getElementById('fId').value;
  const payload={id:id||undefined,name,icon:document.getElementById('fIcon').value||'🍽️',color:document.getElementById('fColor').value,display_order:parseInt(document.getElementById('fOrder').value)||0};
  const res=await fetch('../api/admin.php?action='+(id?'edit_cat':'add_cat'),{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const json=await res.json();
  if(json.ok){closeModal();location.reload();}else{alert('Erreur: '+(json.error||'?'));}
}
async function delCat(id,name){
  if(!confirm('Supprimer "'+name+'" ?'))return;
  await fetch('../api/admin.php?action=delete_cat&id='+id,{method:'DELETE'});
  document.getElementById('crow-'+id)?.remove();
}
</script>
</body>
</html>
