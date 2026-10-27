<style>
.dh-page{--dh-a:#2563eb;--dh-s:#eff6ff;--dh-s2:#dbeafe;width:100%;margin:0;padding:32px 40px 48px;box-sizing:border-box}
.dh-page *{box-sizing:border-box}
.dh-heading{margin:0 0 6px;color:#111827;font-size:30px;font-weight:700;letter-spacing:-.02em;line-height:1.25}
.dh-description{margin:0;color:#6b7280;font-size:15px;line-height:1.6}

/* Hero banner */
.dh-hero{position:relative;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;padding:28px 32px;border-radius:22px;background:linear-gradient(120deg,#1e3a8a 0%,#2563eb 55%,#7c3aed 100%);color:#fff;overflow:hidden;box-shadow:0 12px 30px -12px rgb(37 99 235 / 55%)}
.dh-hero::before,.dh-hero::after{content:"";position:absolute;border-radius:999px;background:rgb(255 255 255 / 9%);pointer-events:none}
.dh-hero::before{width:260px;height:260px;right:-60px;top:-120px}
.dh-hero::after{width:160px;height:160px;right:180px;bottom:-100px}
.dh-hero-main{position:relative;z-index:1;display:flex;align-items:center;gap:18px;min-width:0}
.dh-hero-icon{display:flex;align-items:center;justify-content:center;width:64px;height:64px;flex-shrink:0;border-radius:18px;background:rgb(255 255 255 / 16%);border:1px solid rgb(255 255 255 / 28%);backdrop-filter:blur(6px)}
.dh-hero-icon svg{width:32px;height:32px}
.dh-hero-title{margin:0;color:#fff;font-size:30px;font-weight:700;letter-spacing:-.02em;line-height:1.2}
.dh-hero-sub{margin:6px 0 0;color:rgb(255 255 255 / 80%);font-size:15px;line-height:1.5}
.dh-hero-nav{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:8px}
.dh-hero-nav a{padding:8px 16px;border:1px solid rgb(255 255 255 / 30%);border-radius:999px;background:rgb(255 255 255 / 12%);color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:background .2s,color .2s}
.dh-hero-nav a:hover{background:#fff;color:#1e3a8a}
.dh-hero-nav a:focus-visible{outline:2px solid #fff;outline-offset:2px}
.dark .dh-hero{background:linear-gradient(120deg,#172554 0%,#1d4ed8 55%,#5b21b6 100%);box-shadow:none}
.dh-section-heading{scroll-margin-top:90px}
html{scroll-behavior:smooth}
@media(max-width:640px){.dh-hero{padding:20px;border-radius:18px}.dh-hero-icon{width:52px;height:52px}.dh-hero-title{font-size:24px}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}

/* Sections: flat, a header row with a hairline, then the grid. Each has its own accent. */
.dh-section{margin-top:36px}
.dh-section:nth-of-type(1){--dh-a:#2563eb;--dh-s:#eff6ff;--dh-s2:#dbeafe}
.dh-section:nth-of-type(2){--dh-a:#059669;--dh-s:#ecfdf5;--dh-s2:#d1fae5}
.dh-section:nth-of-type(3){--dh-a:#7c3aed;--dh-s:#f5f3ff;--dh-s2:#ede9fe}
.dh-section:nth-of-type(4){--dh-a:#d97706;--dh-s:#fffbeb;--dh-s2:#fef3c7}
.dh-section-heading{display:flex;align-items:center;gap:12px;margin:0;color:#111827;font-size:13px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;line-height:1.4}
.dh-section-heading::before{content:"";width:4px;height:18px;border-radius:4px;background:var(--dh-a)}
.dh-section-heading::after{content:"";flex:1;height:1px;background:linear-gradient(90deg,#d1d5db,transparent)}
.dh-section .dh-grid{margin-top:16px}

/* Full-width responsive grid: as many columns as fit */
.dh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;margin-top:24px}

.dh-card{position:relative;display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:16px;min-width:0;padding:18px 20px;border:1px solid #e5e7eb;border-radius:18px;background:#fff;text-decoration:none;overflow:hidden;transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}
.dh-card::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:var(--dh-a);opacity:0;transition:opacity .2s}
.dh-card:hover{transform:translateY(-3px);border-color:var(--dh-a);box-shadow:0 14px 28px -10px rgb(16 24 40 / 18%)}
.dh-card:hover::before{opacity:1}
.dh-icon{display:flex;align-items:center;justify-content:center;width:60px;height:60px;flex-shrink:0;border-radius:16px;background:linear-gradient(135deg,var(--dh-s),var(--dh-s2));font-size:30px;line-height:1;transition:transform .2s ease}
.dh-card:hover .dh-icon{transform:scale(1.08) rotate(-3deg)}
.dh-body{min-width:0}
.dh-title-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.dh-card h2{margin:0;color:#111827;font-size:16px;font-weight:650;line-height:1.35;overflow-wrap:break-word}
.dh-card p{margin:4px 0 0;color:#6b7280;font-size:13.5px;line-height:1.5}
.dh-badge{flex-shrink:0;padding:4px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;letter-spacing:.06em;line-height:1}
.dh-go{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:999px;background:var(--dh-s);color:var(--dh-a);transition:background .2s,color .2s,transform .2s}
.dh-go svg{width:18px;height:18px}
.dh-card:hover .dh-go{background:var(--dh-a);color:#fff;transform:translateX(3px)}
.dh-link{display:inline-block;margin-top:10px;padding:4px 10px;border-radius:999px;background:#f3f4f6;color:#9ca3af;font-size:12px;font-weight:600;line-height:1.4}

/* Not configured yet: muted, flat, no hover motion */
.dh-card-disabled{cursor:default;border-style:dashed;background:#fafafa}
.dh-card-disabled:hover{transform:none;border-color:#e5e7eb;box-shadow:none}
.dh-card-disabled:hover::before{opacity:0}
.dh-card-disabled .dh-icon{filter:grayscale(1);opacity:.5;background:#f3f4f6}
.dh-card-disabled:hover .dh-icon{transform:none}
.dh-card-disabled h2{color:#6b7280}

.dh-card:focus-visible,.dh-back:focus-visible{outline:2px solid var(--dh-a,#2563eb);outline-offset:3px}
.dh-back{display:inline-flex;align-items:center;margin-bottom:20px;padding:10px 16px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;color:#2563eb;font-size:14px;font-weight:500;text-decoration:none;transition:border-color .2s}
.dh-back:hover{border-color:#2563eb}
.dh-empty{grid-column:1/-1;padding:32px;border:1px dashed #d1d5db;border-radius:16px;background:#fff;margin-top:24px}
.dh-empty h2{margin:0;color:#111827;font-size:18px;font-weight:600}
.dh-empty p{margin:10px 0 0;color:#6b7280;line-height:1.6}

/* Dark mode */
.dark .dh-section:nth-of-type(1){--dh-a:#60a5fa;--dh-s:#172554;--dh-s2:#1e3a8a}
.dark .dh-section:nth-of-type(2){--dh-a:#34d399;--dh-s:#052e22;--dh-s2:#065f46}
.dark .dh-section:nth-of-type(3){--dh-a:#a78bfa;--dh-s:#2e1065;--dh-s2:#4c1d95}
.dark .dh-section:nth-of-type(4){--dh-a:#fbbf24;--dh-s:#3a2a08;--dh-s2:#78350f}
.dark .dh-section-heading,.dark .dh-heading,.dark .dh-card h2,.dark .dh-empty h2{color:#f3f4f6}
.dark .dh-section-heading::after{background:linear-gradient(90deg,#374151,transparent)}
.dark .dh-card,.dark .dh-back,.dark .dh-empty{background:#1f2937;border-color:#374151}
.dark .dh-card:hover{border-color:var(--dh-a);box-shadow:0 14px 28px -10px rgb(0 0 0 / 55%)}
.dark .dh-description,.dark .dh-card p,.dark .dh-empty p{color:#9ca3af}
.dark .dh-card:hover .dh-go{color:#111827}
.dark .dh-back{color:#93c5fd}
.dark .dh-badge{background:#453a16;color:#fde68a}
.dark .dh-card-disabled{background:#182130}
.dark .dh-card-disabled:hover{border-color:#374151}
.dark .dh-card-disabled h2{color:#9ca3af}
.dark .dh-card-disabled .dh-icon,.dark .dh-link{background:#263041}
.dark .dh-link{color:#6b7280}

@media(max-width:1100px){.dh-page{padding:24px}}
@media(max-width:640px){.dh-page{padding:20px 16px 32px}.dh-grid{grid-template-columns:minmax(0,1fr);gap:12px}.dh-card{padding:14px 16px;gap:12px}.dh-icon{width:50px;height:50px;font-size:24px}.dh-heading{font-size:24px}.dh-back{max-width:100%;line-height:1.5}}
@media(prefers-reduced-motion:reduce){.dh-card,.dh-icon,.dh-go{transition:none}.dh-card:hover,.dh-card:hover .dh-go{transform:none}}
</style>
