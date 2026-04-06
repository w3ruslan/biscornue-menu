// La Biscornue — Game UI JS

var allProducts = [];
var allCats     = [];
var activeCat   = 0;
var searchQ     = '';

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

/* ── Render ───────────────────────────── */
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
    // Grouped by category
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

  // Stagger entrance + tilt
  setTimeout(() => {
    const cards = wrap.querySelectorAll('.menu-card');
    cards.forEach((card, i) => {
      card.style.opacity   = '0';
      card.style.transform = 'translateY(24px)';
      card.style.transition = `opacity .35s ${i*35}ms, transform .35s ${i*35}ms`;
      requestAnimationFrame(() => {
        card.style.opacity   = '1';
        card.style.transform = '';
      });
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
  return `<div class="menu-card" onclick="openDetail(${p.id})" data-id="${p.id}">
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
    const r   = card.getBoundingClientRect();
    const x   = e.clientX - r.left;
    const y   = e.clientY - r.top;
    const rx  = ((y - r.height/2) / (r.height/2)) * -8;
    const ry  = ((x - r.width/2)  / (r.width/2))  *  8;
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

/* ── Detail Panel ─────────────────────── */
function openDetail(id) {
  const p = allProducts.find(x => x.id == id);
  if (!p) return;

  // Click bounce
  const card = document.querySelector(`[data-id="${id}"]`);
  if (card) {
    card.style.transition = 'transform .1s';
    card.style.transform  = 'scale(0.94)';
    setTimeout(() => { card.style.transform = ''; card.style.transition = ''; }, 180);
  }

  const price = fmtPrice(p);
  const desc  = p.description || '';
  const ytId  = getYtId(desc);
  const cleanDesc = ytId ? desc.replace(/https?:\/\/[^\s]*(youtube\.com|youtu\.be)[^\s]*/g,'').trim() : desc;

  const imgHtml = ytId
    ? ''
    : p.image_url
      ? `<img class="detail-img" src="${p.image_url}" alt="${esc(p.name)}">`
      : `<div class="detail-img-ph">🍽️</div>`;

  const ytHtml = ytId
    ? `<div class="yt-wrap"><iframe class="yt-frame" src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe></div>`
    : '';

  let optsHtml = '';
  if (p.options && p.options.length) {
    p.options.forEach(og => {
      optsHtml += `<div class="detail-opt-label">${esc(og.name)}</div><div class="opts-grid">`;
      og.items.forEach(item => {
        const extra = item.price_extra > 0 ? ` +€${parseFloat(item.price_extra).toFixed(2)}` : '';
        optsHtml += `<button class="opt-btn" onclick="selOpt(this)">${esc(item.name)}${extra}</button>`;
      });
      optsHtml += '</div>';
    });
  }

  const catBadge = p.cat_name
    ? `<div class="detail-cat" style="color:${p.cat_color||'#d4a853'}">${p.cat_icon||''} ${esc(p.cat_name)}</div>`
    : '';

  const overlay = document.createElement('div');
  overlay.className = 'detail-overlay';
  overlay.id = 'detailOverlay';
  overlay.innerHTML = `
    <div class="detail-backdrop" onclick="closeDetail()"></div>
    <div class="detail-panel">
      <button class="detail-close" onclick="closeDetail()">✕</button>
      ${imgHtml}
      <div class="detail-body">
        ${catBadge}
        <div class="detail-name">${esc(p.name)}</div>
        ${price ? `<div class="detail-price">${price}</div>` : ''}
        ${ytHtml}
        ${cleanDesc ? `<div class="detail-desc">${esc(cleanDesc)}</div>` : ''}
        ${optsHtml}
        <button class="detail-cta" onclick="closeDetail()">
          🛒 &nbsp;Commander
        </button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';
}

function closeDetail() {
  const ov = document.getElementById('detailOverlay');
  if (!ov) return;
  ov.style.opacity    = '0';
  ov.style.transition = 'opacity .2s';
  setTimeout(() => { ov.remove(); document.body.style.overflow = ''; }, 220);
}

function selOpt(btn) {
  btn.closest('.opts-grid').querySelectorAll('.opt-btn').forEach(b => b.classList.remove('sel'));
  btn.classList.add('sel');
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
