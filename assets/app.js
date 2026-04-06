// La Biscornue — Menu JS

var allProducts = [];
var activeCategory = 0;

document.addEventListener('DOMContentLoaded', function() {
  loadMenu();
});

function loadMenu() {
  fetch('api/menu.php')
    .then(function(r){ return r.json(); })
    .then(function(data) {
      allProducts = data;
      renderAll();
    })
    .catch(function() {
      document.getElementById('menuWrap').innerHTML =
        '<p style="text-align:center;padding:60px;color:#9a8572">Impossible de charger le menu.</p>';
    });
}

function renderAll() {
  var q = (document.getElementById('searchBox') ? document.getElementById('searchBox').value : '').toLowerCase().trim();
  var wrap = document.getElementById('menuWrap');
  wrap.innerHTML = '';

  // Group by category
  var catMap = {};
  allProducts.forEach(function(p) {
    var catId = p.category_id || 0;
    if (!catMap[catId]) catMap[catId] = { name: p.category_name, icon: p.category_icon, color: p.category_color, items: [] };
    var nameMatch = !q || (p.name + ' ' + (p.description||'')).toLowerCase().includes(q);
    var catMatch  = activeCategory === 0 || parseInt(p.category_id) === activeCategory;
    if (nameMatch && catMatch) catMap[catId].items.push(p);
  });

  var hasAny = false;
  Object.keys(catMap).forEach(function(catId) {
    var cat = catMap[catId];
    if (!cat.items.length) return;
    hasAny = true;
    var sec = document.createElement('div');
    sec.className = 'cat-section';
    sec.id = 'cat-' + catId;

    var lighten = hexToRgba(cat.color || '#d4a853', 0.15);
    sec.innerHTML =
      '<div class="cat-heading">' +
      '<div class="cat-heading-icon" style="background:' + lighten + '">' + (cat.icon||'🍽️') + '</div>' +
      '<span class="cat-heading-name">' + cat.name + '</span>' +
      '<span class="cat-heading-count">' + cat.items.length + ' article(s)</span>' +
      '</div>' +
      '<div class="menu-grid" id="grid-' + catId + '"></div>';
    wrap.appendChild(sec);

    var grid = document.getElementById('grid-' + catId);
    cat.items.forEach(function(p) {
      grid.appendChild(buildCard(p));
    });
  });

  if (!hasAny) {
    wrap.innerHTML = '<div class="no-results">🔍 Aucun résultat trouvé.</div>';
  }
}

function buildCard(p) {
  var card = document.createElement('div');
  card.className = 'menu-card';
  card.onclick = function() { openDetail(p); };

  var color = p.category_color || '#d4a853';

  // Image or emoji
  var imgHtml;
  if (p.image_url) {
    imgHtml = '<div class="card-img"><img src="' + p.image_url + '" alt="' + p.name + '" loading="lazy"></div>';
  } else {
    imgHtml = '<div class="card-img" style="background:' + hexToRgba(color, .1) + '">' + (p.category_icon||'🍽️') + '</div>';
  }

  var priceHtml;
  if (p.price_from && p.price_to && p.price_from !== p.price_to) {
    priceHtml = '<div class="card-price"><small>à partir de </small>€' + parseFloat(p.price_from).toFixed(2) + '</div>';
  } else if (p.price_from) {
    priceHtml = '<div class="card-price"><small>€</small>' + parseFloat(p.price_from).toFixed(2) + '</div>';
  } else {
    priceHtml = '<div class="card-price" style="color:#bbb">—</div>';
  }

  card.innerHTML =
    imgHtml +
    '<div class="card-body">' +
    '<div class="card-name">' + p.name + '</div>' +
    (p.description ? '<div class="card-desc">' + p.description + '</div>' : '') +
    '<div class="card-footer">' +
    priceHtml +
    '<button class="card-btn" style="background:' + color + '" onclick="event.stopPropagation();openDetail(event._p||(' + JSON.stringify(p).replace(/'/g,"\\'") + '))" title="Voir les détails">ℹ️</button>' +
    '</div>' +
    '</div>';

  return card;
}

// ── Detail overlay ──────────────────────────────────
function openDetail(p) {
  var color = p.category_color || '#d4a853';
  var lighten = hexToRgba(color, 0.15);

  var imgHtml;
  if (p.image_url) {
    imgHtml = '<img class="detail-header-bg" src="' + p.image_url + '" alt="">';
  } else {
    imgHtml = '<div class="detail-header-bg" style="background:' + lighten + '"></div>' +
              '<div class="detail-header-emoji">' + (p.category_icon||'🍽️') + '</div>';
  }

  var priceHtml;
  if (p.price_from && p.price_to && p.price_from !== p.price_to) {
    priceHtml = '<div class="detail-price"><small>€</small>' +
      parseFloat(p.price_from).toFixed(2) +
      ' <span style="font-size:16px;color:#9a8572">— €' + parseFloat(p.price_to).toFixed(2) + '</span></div>';
  } else if (p.price_from) {
    priceHtml = '<div class="detail-price"><small>€</small>' + parseFloat(p.price_from).toFixed(2) + '</div>';
  } else {
    priceHtml = '';
  }

  // Options
  var optHtml = '';
  if (p.options && p.options.length) {
    optHtml = '<div class="detail-options"><h4>Options disponibles</h4>';
    p.options.forEach(function(g) {
      optHtml += '<div class="option-group">';
      optHtml += '<div class="option-group-title">' + g.title + '</div>';
      optHtml += '<div class="option-items">';
      g.items.forEach(function(it) {
        var cls = it.price > 0 ? 'option-chip has-price' : 'option-chip';
        optHtml += '<span class="' + cls + '">' + it.label +
          (it.price > 0 ? ' +€' + parseFloat(it.price).toFixed(2) : '') + '</span>';
      });
      optHtml += '</div></div>';
    });
    optHtml += '</div>';
  }

  var overlay = document.getElementById('detailOverlay');
  overlay.innerHTML =
    '<div class="detail-bg" onclick="closeDetail()"></div>' +
    '<div class="detail-panel" id="dtPanel">' +
    '<div class="detail-header">' +
    imgHtml +
    '<div class="detail-header-overlay"></div>' +
    '<button class="detail-close" onclick="closeDetail()">✕</button>' +
    '<div class="detail-cat-badge" style="background:' + hexToRgba(color,.35) + '">' + (p.category_icon||'') + ' ' + (p.category_name||'') + '</div>' +
    '<div class="detail-hname">' + p.name + '</div>' +
    '</div>' +
    '<div class="detail-body">' +
    (p.description ? '<div class="detail-desc">' + p.description + '</div>' : '') +
    '<div class="detail-price-row">' + priceHtml + '</div>' +
    optHtml +
    '</div>' +
    '</div>';

  overlay.style.display = 'flex';
  setTimeout(function() {
    var panel = document.getElementById('dtPanel');
    if (panel) panel.classList.add('dt-in');
  }, 10);
}

function closeDetail() {
  var panel = document.getElementById('dtPanel');
  if (!panel) return;
  panel.classList.remove('dt-in');
  panel.classList.add('dt-out');
  setTimeout(function() {
    var ov = document.getElementById('detailOverlay');
    ov.style.display = 'none';
    ov.innerHTML = '';
  }, 280);
}

// ── Category filter ─────────────────────────────────
function switchCategory(catId, btn) {
  activeCategory = catId;
  document.querySelectorAll('.cat-tab').forEach(function(b) { b.classList.remove('active'); });
  btn.classList.add('active');
  renderAll();
  document.getElementById('menuWrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Search ──────────────────────────────────────────
function filterSearch() {
  renderAll();
}

// ── Util ────────────────────────────────────────────
function hexToRgba(hex, alpha) {
  hex = hex.replace('#','');
  if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
  var r = parseInt(hex.substring(0,2),16);
  var g = parseInt(hex.substring(2,4),16);
  var b = parseInt(hex.substring(4,6),16);
  return 'rgba('+r+','+g+','+b+','+alpha+')';
}

// Keyboard close
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeDetail();
});
