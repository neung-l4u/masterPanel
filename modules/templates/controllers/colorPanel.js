class ColorControlPanel extends HTMLElement {
  static get observedAttributes() { return ['storage-key','scope','controls','position']; }

  constructor(){
    super();
    this.attachShadow({ mode: 'open' });

    this._storageKey = this.getAttribute('storage-key') || 'stickyTheme';
    this._scopeSel   = this.getAttribute('scope') || ':root';
    this._controls   = (this.getAttribute('controls') || 'heading,text,bg,cardBg,footerBg,button,link,linkHover,linkVisited')
                        .split(',').map(s=>s.trim()).filter(Boolean);
    this._position   = this.getAttribute('position') || 'bottom';

    // ตัวแปรธีมที่รองรับ
    this._vars = {
      heading:     ['--heading-color', '#111111'],
      text:        ['--text-color',    '#222222'],
      bg:          ['--bg-color',      '#ffffff'],
      cardBg:      ['--cardBg',       '#84398b'],
      footerBg:    ['--footer-bg',     '#140a29'],
      btn:         ['--btn-bg',        '#0d6efd'],
      btnText:     ['--btn-text',      '#ffffff'],
      link:        ['--link',          '#0a58ca'],
      linkHover:   ['--link-hover',    '#084298'],
      linkVisited: ['--link-visited',  '#6f42c1'],
    };

    this._els = {};          // เก็บรีเฟอเรนซ์ input color
    this._hidden = false;    // จะโหลดจาก localStorage ตอน connect
    this._baseline = null;   // baseline จาก CSS author styles
  }

  // ---------- Attribute lifecycle ----------
  attributeChangedCallback(name){
    if(name === 'storage-key') this._storageKey = this.getAttribute('storage-key') || 'stickyTheme';
    if(name === 'scope')       this._scopeSel   = this.getAttribute('scope') || ':root';
    if(name === 'controls')    this._controls   = (this.getAttribute('controls')||'').split(',').map(s=>s.trim()).filter(Boolean);
    if(name === 'position')    this._position   = this.getAttribute('position') || 'bottom';
    if(this.isConnected) this._render();
  }

  connectedCallback(){
    // โหลดสถานะซ่อน/แสดง
    const saved = this._loadSaved();
    if (typeof saved.hidden === 'boolean') this._hidden = saved.hidden;

    // ถ่ายรูป baseline จาก CSS ของเทมเพลต (ต้องทำก่อน render/apply saved)
    this._captureBaseline();

    // วาด UI แล้ว bind keys
    this._render();
    this._bindEscToggle();
  }

  disconnectedCallback(){
    document.removeEventListener('keydown', this._escHandler);
  }

  // ---------- Utilities ----------
  _target(){
    if(this._scopeSel === ':root') return document.documentElement;
    const el = document.querySelector(this._scopeSel);
    return el || document.documentElement;
  }
  _getVar(name){
    const t = this._target();
    return getComputedStyle(t).getPropertyValue(name).trim();
  }
  _loadSaved(){
    try { return JSON.parse(localStorage.getItem(this._storageKey) || '{}'); }
    catch { return {}; }
  }
  _savePatch(patch){
    const saved = this._loadSaved();
    const next = {
      // รวมค่าสีทั้งหมด + สถานะซ่อน
      heading:     patch.heading     ?? saved.heading     ?? this._getVar(this._vars.heading[0]),
      text:        patch.text        ?? saved.text        ?? this._getVar(this._vars.text[0]),
      bg:          patch.bg          ?? saved.bg          ?? this._getVar(this._vars.bg[0]),
      cardBg:      patch.cardBg      ?? saved.cardBg      ?? this._getVar(this._vars.cardBg[0]),
      footerBg:    patch.footerBg    ?? saved.footerBg    ?? this._getVar(this._vars.footerBg[0]),
      btn:         patch.btn         ?? saved.btn         ?? this._getVar(this._vars.btn[0]),
      btnText:     patch.btnText     ?? saved.btnText     ?? this._getVar(this._vars.btnText[0]),
      link:        patch.link        ?? saved.link        ?? this._getVar(this._vars.link[0]),
      linkHover:   patch.linkHover   ?? saved.linkHover   ?? this._getVar(this._vars.linkHover[0]),
      linkVisited: patch.linkVisited ?? saved.linkVisited ?? this._getVar(this._vars.linkVisited[0]),
      hidden:      patch.hidden      ?? saved.hidden      ?? this._hidden,
    };
    localStorage.setItem(this._storageKey, JSON.stringify(next));
  }
  _apply(cssVar, value){
    this._target().style.setProperty(cssVar, value);
  }
  _applyAndSave(key, value){
    const [cssVar] = this._vars[key];
    this._apply(cssVar, value);
    this._savePatch({ [key]: value });
  }

  // แปลงค่าสีให้เป็น HEX #RRGGBB (ถ้าเป็น rgb()/rgba() จะพยายามแปลง)
  _toHex(v, fallback='#000000'){
    if(!v) return fallback;
    v = v.trim();
    // ถ้าเป็น hex แล้ว
    if(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)){
      if(v.length === 4){ // #RGB -> #RRGGBB
        return '#'+v[1]+v[1]+v[2]+v[2]+v[3]+v[3];
      }
      return v.toUpperCase();
    }
    // rgb/rgba
    const m = v.match(/^rgba?\((\d+)[,\s]+(\d+)[,\s]+(\d+)/i);
    if(m){
      const r = Math.max(0, Math.min(255, parseInt(m[1],10)));
      const g = Math.max(0, Math.min(255, parseInt(m[2],10)));
      const b = Math.max(0, Math.min(255, parseInt(m[3],10)));
      const to2 = (n)=>n.toString(16).padStart(2,'0').toUpperCase();
      return `#${to2(r)}${to2(g)}${to2(b)}`;
    }
    // ชนิดอื่น ๆ (color name, hsl ฯลฯ) — พยายามโยนเข้า canvas เพื่อ normalize
    try{
      const c = document.createElement('canvas');
      const ctx = c.getContext('2d');
      ctx.fillStyle = '#000';
      ctx.fillStyle = v; // ถ้ารับได้จะกลายเป็น rgb(...)
      const rgb = ctx.fillStyle; // อาจกลับมาเป็น #hex หรือ rgb(...)
      if(rgb) return this._toHex(rgb, fallback);
    }catch{}
    return fallback;
  }

  _syncInput(key){
    const [cssVar, def] = this._vars[key];
    const colorEl  = this._els[key];
    const hexInput = this.shadowRoot.getElementById(`${key}-hex`);
    const saved   = this._loadSaved();
    const current = saved[key] || this._getVar(cssVar) || def;
    const valHex  = this._toHex(current, def);

    if(colorEl)  colorEl.value = valHex;
    if(hexInput) hexInput.value = valHex;
  }

  // ---------- Baseline & Reset ----------
  _captureBaseline(){
    this._baseline = {};
    const target = this._target();

    const readAuthorValue = (el, cssVar) => {
      const inline = el.style.getPropertyValue(cssVar);
      if (inline) el.style.removeProperty(cssVar); // ถอด inline ชั่วคราว
      const val = getComputedStyle(el).getPropertyValue(cssVar).trim();
      if (inline) el.style.setProperty(cssVar, inline); // ใส่กลับ
      return val;
    };

    const readFromScopeOrRoot = (cssVar) => {
      // scope ก่อน
      let v = readAuthorValue(target, cssVar);
      if (!v) {
        const root = document.documentElement;
        const inlineRoot = root.style.getPropertyValue(cssVar);
        if (inlineRoot) root.style.removeProperty(cssVar);
        v = getComputedStyle(root).getPropertyValue(cssVar).trim();
        if (inlineRoot) root.style.setProperty(cssVar, inlineRoot);
      }
      return v;
    };

    Object.values(this._vars).forEach(([cssVar, def])=>{
      const author = readFromScopeOrRoot(cssVar);
      // เก็บ baseline เป็น hex ถ้าเป็นสี (เพื่อ sync กับ input color)
      const base = author ? this._toHex(author, def) : def;
      this._baseline[cssVar] = base;
    });
  }

  _reset(){
    if (!this._baseline) this._captureBaseline();

    // เซ็ตกลับตาม baseline ทั้งหมด
    Object.entries(this._vars).forEach(([key, [cssVar]])=>{
      const v = this._baseline[cssVar];
      if (this._els[key]) this._els[key].value = v;
      const hexEl = this.shadowRoot.getElementById(`${key}-hex`);
      if (hexEl) hexEl.value = v;
      this._apply(cssVar, v);
    });

    // เคลียร์สถานะซ่อน
    this._hidden = false;
    this._reflectHidden();

    // เขียน baseline ลง storage
    const baselineForSave = {};
    Object.entries(this._vars).forEach(([key, [cssVar]])=>{
      baselineForSave[key] = this._baseline[cssVar];
    });
    baselineForSave.hidden = false;
    this._savePatch(baselineForSave);
  }

  // ---------- Toggle ----------
  _bindEscToggle(){
    this._escHandler = (e)=>{
      if(e.key === 'Escape'){
        this._hidden = !this._hidden;
        this._savePatch({ hidden: this._hidden });
        this._reflectHidden();
      }
    };
    document.addEventListener('keydown', this._escHandler);
  }

  _reflectHidden(){
    const panel = this.shadowRoot.getElementById('panel');
    const toggle = this.shadowRoot.getElementById('toggle');

    if(panel) panel.style.display = this._hidden ? 'none' : 'flex';

    if(toggle){
      toggle.setAttribute('aria-expanded', String(!this._hidden));
      toggle.textContent = this._hidden ? 'Show Panel' : 'Hide Panel';
      toggle.title = this._hidden ? 'Show Panel' : 'Hide Panel';
    }
  }

  // ---------- Render ----------
  _render(){
    const posStyle = this._position === 'top'
      ? 'top:0; bottom:auto;'
      : 'bottom:0; top:auto;';

    const togglePos = this._position === 'top'
      ? 'top:8px; bottom:auto;'
      : 'bottom:8px; top:auto;';

    const controlHtml = (key, label, def) => `
      <label class="lbl">
        <span>${label}</span>
        <input type="color" id="${key}" value="${def}" />
        <input type="text" class="hex" id="${key}-hex" value="${def}"
               inputmode="text" autocomplete="off" spellcheck="false"
               maxlength="7" placeholder="#000000" />
      </label>
    `;

    this.shadowRoot.innerHTML = `
      <style>
        :host{
          position:fixed; left:0; right:0; ${posStyle}
          z-index:9999; display:block;
          font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          pointer-events: none; /* ให้ปุ่มกับพาเนลรับ event เอง */
        }
        .wrapper, .cp, .toggle { pointer-events: auto; }

        /* Panel */
        .cp{
          display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:.65rem .75rem;
          margin:0; padding:.9rem 1.1rem; color:#fff;
          width: min(780px, 94vw);
          background: rgba(0,0,0,.42);
          backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
          font-size:14px;
          box-shadow: 0 6px 24px rgba(0,0,0,.25);
        }
        .lbl{
          display:flex; align-items:center; gap:.45rem;
          background: rgba(255,255,255,.12);
          padding:.35rem .55rem; border-radius:.6rem;
        }
        .lbl span{ font-size:13px; opacity:.95; color:#fff; letter-spacing:.2px; }
        input[type="color"]{
          appearance:none; width:36px; height:24px; padding:0; border:0; background:transparent; cursor:pointer;
        }
        .hex{
          width: 86px;
          padding:.25rem .4rem;
          border:1px solid rgba(255,255,255,.28);
          border-radius:.35rem;
          background: rgba(255,255,255,.85);
          font: 600 12.5px ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
          text-transform: uppercase; color:#000;
        }
        .hex:focus{ outline: none; border-color: rgba(0,0,0,.35); background:#fff; }

        .btn{
          border:1px solid rgba(255,255,255,.35);
          background: rgba(255,255,255,.10);
          color:#fff; padding:.4rem .7rem; border-radius:.6rem;
          font-size:13px; cursor:pointer;
        }
        .btn:hover{ background: rgba(255,255,255,.18); }

        /* Liquid glass shell (ใส่เอฟเฟกต์เดิมของพี่ได้) */
        .wrapper{
          display:flex; align-items:center; justify-content:center;
          margin:.5rem auto;
        }
        .liquidGlass-wrapper { position: relative; display:flex; font-weight:600; overflow:hidden;
          color:black; cursor:default; box-shadow:0 6px 6px rgba(0,0,0,.2), 0 0 20px rgba(0,0,0,.1);
          transition: all .4s cubic-bezier(.175,.885,.32,2.2);
          border-radius: 1rem;
        }
        .liquidGlass-effect { position:absolute; z-index:0; inset:0; backdrop-filter: blur(3px); overflow:hidden; isolation:isolate; }
        .liquidGlass-tint { z-index:1; position:absolute; inset:0; background: rgba(255,255,255,.18); }
        .liquidGlass-shine { position:absolute; inset:0; z-index:2; overflow:hidden;
          box-shadow: inset 2px 2px 1px 0 rgba(255,255,255,.4), inset -1px -1px 1px 1px rgba(255,255,255,.35);
        }
        .liquidGlass-text { z-index:3; font-size:2rem; color:black; }
        .dock{ display:flex; align-items:center; justify-content:center; gap:8px; border-radius:1rem; padding:.4rem; }

        /* Toggle button */
        .toggle{
          position: absolute; right: 8px; ${togglePos}
          display:inline-flex; align-items:center; justify-content:center;
          gap:.4rem; padding:.45rem .8rem;
          border-radius: 999px; border:1px solid rgba(255,255,255,.35);
          background: rgba(0,0,0,.5); color:#fff; font-size:12.5px;
          cursor:pointer; user-select:none;
          box-shadow: 0 6px 24px rgba(0,0,0,.25);
          transition: bottom .28s ease, top .28s ease, background .2s ease, transform .2s ease;
        }
        .toggle:hover{ background: rgba(0,0,0,.65); }
      </style>

      <!-- Toggle -->
      <button id="toggle" class="toggle" aria-expanded="true" title="Hide Panel">Hide Panel</button>

      <!-- Panel (พับ/กาง) -->
      <div id="panel" class="wrapper">
        <div class="liquidGlass-wrapper">
          <div class="liquidGlass-effect"></div>
          <div class="liquidGlass-tint"></div>
          <div class="liquidGlass-shine"></div>
          <div class="liquidGlass-text">
            <div class="dock cp" role="region" aria-label="Color control panel">
              ${this._controls.includes('heading')   ? controlHtml('heading','Heading', this._vars.heading[1]) : ''}
              ${this._controls.includes('text')      ? controlHtml('text','Text', this._vars.text[1]) : ''}
              ${this._controls.includes('bg')        ? controlHtml('bg','Background', this._vars.bg[1]) : ''}
              ${(this._controls.includes('card') || this._controls.includes('cardBg')) ? controlHtml('cardBg','Card', this._vars.cardBg[1]) : ''}
              ${this._controls.includes('footerBg')  ? controlHtml('footerBg','Footer', this._vars.footerBg[1]) : ''}
              ${this._controls.includes('button')    ? controlHtml('btn','Button', this._vars.btn[1]) : ''}
              <button class="btn" id="reset">Reset</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // cache inputs
    Object.keys(this._vars).forEach(k=>{
      this._els[k] = this.shadowRoot.getElementById(k) || null;
    });

    // apply saved/defaults (apply ยังไม่ sync input)
    const saved = this._loadSaved();
    Object.entries(this._vars).forEach(([key, [cssVar, def]])=>{
      const val = saved[key] || this._getVar(cssVar) || def;
      this._apply(cssVar, val);
    });

    // sync inputs to current values
    Object.keys(this._vars).forEach(k => this._syncInput(k));

    // bind inputs (color + hex)
    const normHex = (v) => {
      if(!v) return null;
      v = v.trim().toUpperCase();
      if(v[0] !== '#') v = '#'+v;
      const ok = /^#([0-9A-F]{3}|[0-9A-F]{6})$/.test(v);
      if(!ok) return null;
      if(v.length === 4){ v = '#'+v[1]+v[1]+v[2]+v[2]+v[3]+v[3]; }
      return v;
    };

    const bind = key => {
      const colorEl = this._els[key];
      const hexEl   = this.shadowRoot.getElementById(`${key}-hex`);

      if(colorEl){
        colorEl.oninput = e => {
          const v = e.target.value;
          this._applyAndSave(key, v);
          if(hexEl) hexEl.value = v.toUpperCase();
        };
      }
      if(hexEl){
        const applyIfValid = () => {
          const v = normHex(hexEl.value);
          if(v){
            this._applyAndSave(key, v);
            if(colorEl) colorEl.value = v;
            hexEl.value = v;
          }
        };
        hexEl.oninput   = applyIfValid;
        hexEl.onblur    = applyIfValid;
        hexEl.onkeydown = (e)=>{ if(e.key==='Enter'){ e.preventDefault(); applyIfValid(); hexEl.blur(); } };
      }
    };
    Object.keys(this._vars).forEach(bind);

    // reset
    const resetBtn = this.shadowRoot.getElementById('reset');
    if(resetBtn) resetBtn.onclick = () => this._reset();

    // toggle
    const toggleBtn = this.shadowRoot.getElementById('toggle');
    if(toggleBtn){
      toggleBtn.onclick = () => {
        // micro interaction
        toggleBtn.style.transform = 'translateY(-4px)';
        setTimeout(()=> toggleBtn.style.transform = '', 180);

        this._hidden = !this._hidden;
        this._savePatch({ hidden: this._hidden });
        this._reflectHidden();
      };
    }

    // สะท้อนสถานะเริ่มต้น (ตำแหน่งปุ่ม + ซ่อน/โชว์)
    this._reflectHidden();
  }
}

customElements.define('color-control-panel', ColorControlPanel);
