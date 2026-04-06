// La Biscornue — Game Mission UI

var allProducts  = [];
var allCats      = [];
var activeCat    = 0;
var searchQ      = '';
var WA_NUMBER    = window.WA_NUMBER || '';

/* ── Load ─────────────────────────────── */
async function loadMenu() {
  try {
    const r = await fetch('api/menu.php');
    const d = await r.json();
    allProducts = d.products   || [];
    allCats     = d.categories || [];
    renderMenu();
  } catch(e) {
    document.getElementById('menuWrap').innerHTML =
      '<div style="text-align:center;padding:60px;color:rgba(255,255,255,.4)">Erreur de chargement…</div>';
  }
}

/* ── Filter ───────────────────────────── */
function switchCategory(id, btn) {
  activeCat = id;
  document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderMenu();
}
function filterSearch() {
  searchQ = (document.getElementById('searchBox').value || '').toLowerCase().trim();
  renderMenu();
}

/* ── Render menu ──────────────────────── */
function renderMenu() {
  const wrap = document.getElementById('menuWrap');
  const filtered = allProducts.filter(p => {
    if (!p.active) return false;
    const catOk  = activeCat === 0 || p.category_id == activeCat;
    const nameOk = !searchQ  || (p.name+' '+(p.description||'')).toLowerCase().includes(searchQ);
    return catOk && nameOk;
  });

  if (!filtered.length) {
    wrap.innerHTML = '<div style="text-align:center;padding:60px;color:rgba(255,255,255,.4)">Aucun plat trouvé 🔍</div>';
    return;
  }

  let html = '';
  if (activeCat === 0 && !searchQ) {
    const groups = {};
    allCats.forEach(c => { groups[c.id] = []; });
    groups[0] = [];
    filtered.forEach(p => { const k = p.category_id || 0; if (!groups[k]) groups[k]=[]; groups[k].push(p); });
    allCats.forEach(cat => {
      if (!groups[cat.id] || !groups[cat.id].length) return;
      html += renderSection(cat, groups[cat.id]);
    });
    if (groups[0] && groups[0].length) html += renderSection(null, groups[0]);
  } else {
    html = '<div class="prod-grid">' + filtered.map(buildCard).join('') + '</div>';
  }

  wrap.innerHTML = html;

  setTimeout(() => {
    const cards = wrap.querySelectorAll('.menu-card');
    cards.forEach((card, i) => {
      card.style.opacity   = '0';
      card.style.transform = 'translateY(24px)';
      card.style.transition = `opacity .35s ${i*35}ms, transform .35s ${i*35}ms`;
      requestAnimationFrame(() => { card.style.opacity = '1'; card.style.transform = ''; });
      initTilt(card);
    });
  }, 10);
}

function renderSection(cat, products) {
  const header = cat
    ? `<div class="cat-section-header">
        <span class="cat-section-icon">${cat.icon||'🍽️'}</span>
        <span class="cat-section-name">${esc(cat.name)}</span>
        <span class="cat-section-count">${products.length}</span>
       </div>`
    : '';
  return `<div class="cat-section">${header}<div class="prod-grid">${products.map(buildCard).join('')}</div></div>`;
}

function buildCard(p) {
  const img = p.image_url
    ? `<img class="menu-card-img" src="${p.image_url}" alt="${esc(p.name)}" loading="lazy" onerror="this.outerHTML='<div class=menu-card-placeholder>🍽️</div>'">`
    : `<div class="menu-card-placeholder">${p.cat_icon||'🍽️'}</div>`;
  const price = fmtPrice(p);
  const cat   = p.cat_name ? `<div class="menu-card-cat" style="color:${p.cat_color||'#d4a853'}">${esc(p.cat_name)}</div>` : '';
  const desc  = p.description ? `<div class="menu-card-desc">${esc(p.description)}</div>` : '';
  return `<div class="menu-card" onclick="openMission(${p.id})" data-id="${p.id}">
    ${img}
    <div class="menu-card-body">
      ${cat}
      <div class="menu-card-name">${esc(p.name)}</div>
      ${price ? `<div class="menu-card-price">${price}</div>` : ''}
      ${desc}
    </div>
  </div>`;
}

/* ── 3D Tilt ──────────────────────────── */
function initTilt(card) {
  card.addEventListener('mousemove', function(e) {
    const r  = card.getBoundingClientRect();
    const rx = ((e.clientY - r.top  - r.height/2) / (r.height/2)) * -8;
    const ry = ((e.clientX - r.left - r.width/2)  / (r.width/2))  *  8;
    card.style.transform  = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) scale(1.04)`;
    card.style.transition = 'transform .05s, box-shadow .2s';
    card.style.zIndex     = '5';
  });
  card.addEventListener('mouseleave', function() {
    card.style.transform  = '';
    card.style.zIndex     = '';
    card.style.transition = 'transform .5s cubic-bezier(.25,1,.5,1), box-shadow .2s';
  });
}

/* ══════════════════════════════════════════
   🎮 MISSION PANEL
══════════════════════════════════════════ */

// Emoji icons per option keyword
var optIcons = {
  'pita':'🫓','galette':'🫔','pain':'🍞',
  'kebab':'🥩','poulet':'🍗','mixte':'🍖','fallafel':'🥙','végétarien':'🥗','falafel':'🧆',
  'blanche':'🤍','harissa':'🌶️','algérienne':'🧡','bbq':'🫙','ketchup':'🍅','samourai':'⚔️','sauce verte':'🌿',
  'fromage':'🧀','bacon':'🥓','œuf':'🥚','oeuf':'🥚',
  'coca':'🥤','coca-cola':'🥤','eau':'💧','jus':'🍊','jus d\'orange':'🍊',
  'petite':'🔸','grande':'🔶','saignant':'🩸','à point':'👌','bien cuit':'🔥',
  'classique':'⭐','épicée':'🌶️','extra cheese':'🧀',
};
function getOptIcon(label) {
  const key = label.toLowerCase();
  for (const k in optIcons) { if (key.includes(k)) return optIcons[k]; }
  return '●';
}

function openMission(id) {
  const p = allProducts.find(x => x.id == id);
  if (!p) return;

  // Card click bounce
  const card = document.querySelector(`[data-id="${id}"]`);
  if (card) {
    card.style.transition = 'transform .12s';
    card.style.transform  = 'scale(0.93)';
    setTimeout(() => { card.style.transform = ''; card.style.transition = ''; }, 160);
  }

  const price = fmtPrice(p);
  const desc  = p.description || '';
  const ytId  = getYtId(desc);
  const cleanDesc = ytId ? desc.replace(/https?:\/\/[^\s]*(youtube\.com|youtu\.be)[^\s]*/g,'').trim() : desc;

  // Image or placeholder
  const imgContent = p.image_url && !ytId
    ? `<img class="mission-img" src="${p.image_url}" alt="${esc(p.name)}">`
    : `<div class="mission-img-ph">${p.cat_icon||'🍽️'}</div>`;

  // YouTube
  const ytHtml = ytId
    ? `<div class="yt-wrap"><iframe class="yt-frame" src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe></div>`
    : '';

  // Option groups → loadout sections
  let loadoutHtml = '';
  if (p.options && p.options.length) {
    loadoutHtml = '<div class="mission-loadout">';
    p.options.forEach((og, gi) => {
      const sectionIcon = getOptIcon(og.name);
      loadoutHtml += `
        <div class="loadout-section" data-group="${gi}">
          <div class="loadout-label">
            <span class="loadout-icon">${sectionIcon}</span>
            <span class="loadout-title">${esc(og.name)}</span>
            <span class="loadout-required">REQUIS</span>
          </div>
          <div class="loadout-opts">`;
      og.items.forEach((item, ii) => {
        const icon  = getOptIcon(item.name);
        const extra = item.price_extra > 0 ? `<span class="opt-extra">+€${parseFloat(item.price_extra).toFixed(2)}</span>` : '';
        const sel   = ii === 0 ? ' sel' : '';
        loadoutHtml += `<button class="loadout-opt${sel}" data-group="${gi}" data-name="${esc(item.name)}" data-extra="${item.price_extra||0}" onclick="selectOpt(this)">
          <span class="opt-icon">${icon}</span>
          <span class="opt-label">${esc(item.name)}</span>
          ${extra}
        </button>`;
      });
      loadoutHtml += `</div></div>`;
    });
    loadoutHtml += '</div>';
  }

  const catBadge = p.cat_name
    ? `<div class="mission-badge" style="background:${p.cat_color||'#d4a853'}22;border-color:${p.cat_color||'#d4a853'}55;color:${p.cat_color||'#d4a853'}">
        🎯 MISSION — ${esc(p.cat_name).toUpperCase()}
       </div>`
    : `<div class="mission-badge">🎯 MISSION</div>`;

  const el = document.createElement('div');
  el.className = 'mission-overlay';
  el.id = 'missionOverlay';
  el.innerHTML = `
    <div class="mission-backdrop" onclick="closeMission()"></div>
    <div class="mission-panel">

      <div class="mission-img-wrap">
        ${imgContent}
        <div class="mission-img-gradient"></div>
        <button class="mission-close" onclick="closeMission()">✕</button>
      </div>

      <div class="mission-identity">
        ${catBadge}
        <div class="mission-name">${esc(p.name)}</div>
        ${price ? `<div class="mission-price">${price}</div>` : ''}
        ${cleanDesc ? `<div class="mission-desc">${esc(cleanDesc)}</div>` : ''}
      </div>

      <div class="mission-divider"></div>

      ${ytHtml}
      ${loadoutHtml}

      <div class="mission-footer">
        <div class="mission-summary" id="missionSummary">Sélectionne tes options ci-dessus…</div>
        <button class="mission-cta" onclick="launchMission('${esc(p.name)}')">
          <span>🚀</span>
          <span>LANCER LA MISSION</span>
          <span class="cta-arrow">→</span>
        </button>
      </div>
    </div>`;

  document.body.appendChild(el);
  document.body.style.overflow = 'hidden';

  // Auto-build summary from default (first) selections
  setTimeout(updateSummary, 50);
}

function selectOpt(btn) {
  const group = btn.dataset.group;
  document.querySelectorAll(`#missionOverlay .loadout-opt[data-group="${group}"]`)
    .forEach(b => b.classList.remove('sel'));
  btn.classList.add('sel');
  updateSummary();
}

function updateSummary() {
  const sel = document.querySelectorAll('#missionOverlay .loadout-opt.sel');
  if (!sel.length) { document.getElementById('missionSummary').textContent = ''; return; }
  const parts = [];
  sel.forEach(b => parts.push(b.dataset.name));
  document.getElementById('missionSummary').textContent = '📋 ' + parts.join(' · ');
}

function launchMission(productName) {
  const sel = document.querySelectorAll('#missionOverlay .loadout-opt.sel');
  const parts = [`🎯 Commande: *${productName}*`];
  sel.forEach(b => {
    const extra = parseFloat(b.dataset.extra||0);
    const price = extra > 0 ? ` (+€${extra.toFixed(2)})` : '';
    parts.push(`   • ${b.dataset.name}${price}`);
  });
  parts.push('\nBonjour, je souhaite passer cette commande 😊');
  const msg  = encodeURIComponent(parts.join('\n'));
  const num  = WA_NUMBER.replace(/\D/g,'');
  window.open(`https://wa.me/${num}?text=${msg}`, '_blank');
}

function closeMission() {
  const ov = document.getElementById('missionOverlay');
  if (!ov) return;
  ov.style.opacity    = '0';
  ov.style.transition = 'opacity .2s';
  setTimeout(() => { ov.remove(); document.body.style.overflow = ''; }, 220);
}

/* ── Helpers ──────────────────────────── */
function fmtPrice(p) {
  if (!p.price_from) return '';
  return p.price_to && p.price_to != p.price_from
    ? `€${parseFloat(p.price_from).toFixed(2)} – €${parseFloat(p.price_to).toFixed(2)}`
    : `€${parseFloat(p.price_from).toFixed(2)}`;
}
function getYtId(text) {
  if (!text) return null;
  const m = text.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/);
  return m ? m[1] : null;
}
function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Init ─────────────────────────────── */
loadMenu();
