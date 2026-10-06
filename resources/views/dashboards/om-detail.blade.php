<!DOCTYPE html>
@php
  if (!function_exists('omFormatCompact')) {
      function omFormatCompact($value) {
          $value = $value ?? 0;

          if ($value >= 1000000) {
              return number_format($value / 1000000, 2) . '<small>M</small>';
          }

          if ($value >= 1000) {
              return number_format($value / 1000, 0) . '<small>K</small>';
          }

          return number_format($value, 0);
      }
  }

  if (!function_exists('omPercentLabel')) {
      function omPercentLabel($value) {
          $value = $value ?? 0;

          if ($value > 0) {
              return '<b class="up">▲' . number_format($value, 1) . '%</b>';
          }

          if ($value < 0) {
              return '<b class="dn">▼' . number_format(abs($value), 1) . '%</b>';
          }

          return '<b class="fl">0.0%</b>';
      }
  }
@endphp
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $mallName }} — OM Dashboard</title>
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<style>
  :root{
    --pkw-blue:#332E83; --pkw-blue-mid:#6A66B0; --pkw-blue-l:#9C99C9; --pkw-blue-soft:#C9C7E4;
    --pkw-blue-tint:#EAEEF6; --pkw-red:#E3161B; --pkw-red-soft:#FCEBEB; --amber:#C77A16;
    --bg:#f4f7fb; --card:#FFFFFF; --ink:#26252E; --ink-2:#6B7280; --ink-3:#9CA3AF; --line:#E2E8F0;
    --pos:#332E83; --neg:#E3161B; --radius:10px;
    --shadow:0 1px 2px rgba(30,41,59,.05), 0 3px 12px rgba(30,41,59,.05);
    --num:"Segoe UI","Segoe UI Variable Display",Inter,system-ui,sans-serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{background:var(--bg);color:var(--ink);
    font-family:"Segoe UI","Segoe UI Variable Text",Inter,system-ui,-apple-system,sans-serif;
    font-size:12.5px;line-height:1.38;-webkit-font-smoothing:antialiased}
  .wrap{
  width:100%;
  max-width:none;
  margin:0;
  padding:12px 16px 24px;
}

  header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:11px}
  .brandmark{width:6px;height:30px;background:var(--pkw-blue);border-radius:2px}
  header h1{font-size:17px;font-weight:600;letter-spacing:-.01em}
  header .sub{font-size:11px;color:var(--ink-2)}
  .daterange{margin-left:auto;display:flex;align-items:center;gap:7px;flex-wrap:wrap}
  .daterange .lbl{font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--ink-3)}
  .daterange .field{display:flex;align-items:center;gap:5px;background:var(--card);border:1px solid var(--line);border-radius:8px;padding:6px 11px;box-shadow:var(--shadow)}
  .daterange input[type=date]{border:none;background:none;font:inherit;font-size:12px;font-weight:600;color:var(--pkw-blue);font-variant-numeric:tabular-nums;cursor:pointer;color-scheme:light}
  .daterange input[type=date]:focus{outline:none}
  .daterange .arrow{color:var(--ink-3);font-size:11px}

  .tabs{display:inline-flex;background:var(--pkw-blue-tint);border:1px solid var(--line);border-radius:10px;padding:3px;gap:3px;margin-bottom:13px}
  .tabs button{appearance:none;border:none;background:none;font:inherit;font-size:12.5px;font-weight:600;color:var(--ink-2);cursor:pointer;padding:7px 18px;border-radius:8px}
  .tabs a{
  border:0;
  background:transparent;
  color:#536072;
  padding:10px 24px;
  border-radius:9px;
  font-size:13px;
  font-weight:700;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}

.tabs a.active{
  background:#332e83;
  color:#fff;
}
  .tabs button.active{background:var(--pkw-blue);color:#fff;box-shadow:0 1px 3px rgba(51,46,131,.28)}

  .tab-page{display:none}
  .tab-page.active{display:block;animation:fade .2s ease}
  @keyframes fade{from{opacity:0;transform:translateY(3px)}to{opacity:1;transform:none}}
  @media (prefers-reduced-motion:reduce){.tab-page.active{animation:none}}

  .board{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:start}
  .board.two{grid-template-columns:repeat(2,minmax(0,1fr))}
  .col{display:flex;flex-direction:column;gap:12px;min-width:0}
  canvas{max-width:100%}

  .sec{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:13px 14px}
  .sec.full{margin-bottom:12px}
  .sec-title{display:flex;align-items:center;gap:8px;margin-bottom:10px}
  .sec-title h2{font-size:12.5px;font-weight:700;letter-spacing:-.01em;color:var(--ink)}
  .sec-title h2::before{content:"";display:inline-block;width:3.5px;height:13px;background:var(--pkw-blue);border-radius:2px;margin-right:7px;vertical-align:-2px}
  .sec-title .hint{margin-left:auto;font-size:9.5px;font-weight:600;color:var(--ink-3);letter-spacing:.03em}
  .seg{margin-left:auto;display:flex;border:1px solid var(--line);border-radius:6px;overflow:hidden}
  .seg button{border:none;background:var(--card);padding:3px 9px;font:inherit;font-size:10px;font-weight:600;color:var(--ink-2);cursor:pointer}
  .seg button.on{background:var(--pkw-blue);color:#fff}

  .up{color:var(--pos);font-weight:600} .dn{color:var(--neg);font-weight:600} .fl{color:var(--ink-2);font-weight:600}
  .delta-row{display:flex;gap:12px;font-size:10.5px;font-variant-numeric:tabular-nums;color:var(--ink-3);margin-top:5px}
  .delta-row b{font-weight:600}

  .kcards{display:grid;gap:8px}
  .kcards.two{grid-template-columns:1fr 1fr}
  .kcards.three{grid-template-columns:repeat(3,1fr)}
  .kcards.five{grid-template-columns:repeat(5,minmax(0,1fr))}
  .kcard{background:var(--pkw-blue);color:#fff;border-radius:8px;padding:10px 11px}
  .kcard .k{font-size:9px;font-weight:600;letter-spacing:.03em;opacity:.82;text-transform:uppercase}
  .kcard .v{font-family:var(--num);font-size:19px;font-weight:600;letter-spacing:-.02em;margin-top:2px;font-variant-numeric:tabular-nums}
  .kcard .v small{font-size:10px;font-weight:600;opacity:.85}
  .kcard .vs{display:flex;flex-direction:column;gap:1px;margin-top:5px;font-size:9.5px;font-variant-numeric:tabular-nums;opacity:.94}
  .kcard .vs span{display:flex;justify-content:space-between}
  .kcard .vs .up{color:#B9E6C9} .kcard .vs .dn{color:#F6B9BB} .kcard .vs .fl{color:#D9D7EC}
  .kcard.plain{background:var(--pkw-blue-tint);color:var(--ink)}
  .kcard.plain .k{opacity:.7;color:var(--ink-2)}
  .kcard.plain .v{color:var(--pkw-blue)}

  .chart-xs{height:100px} .chart-sm{height:130px} .chart-md{height:160px}
  .muted{font-size:10.5px;color:var(--ink-2)}
  .avg-line{display:flex;gap:14px;font-size:10.5px;color:var(--ink-2);margin-top:6px;font-variant-numeric:tabular-nums}
  .avg-line b{color:var(--ink);font-weight:600}
  .pk-meta{display:grid;grid-template-columns:1fr 1fr;gap:4px 14px;font-size:11px;color:var(--ink-2);margin-bottom:8px;font-variant-numeric:tabular-nums}
  .pk-meta b{color:var(--ink);font-weight:600}

  table{width:100%;border-collapse:collapse;font-size:11px}
  thead th{background:var(--pkw-blue-tint);color:var(--pkw-blue);font-weight:700;text-align:left;padding:5px 7px;font-size:9px;letter-spacing:.03em;text-transform:uppercase;white-space:nowrap}
  thead th:first-child{border-radius:5px 0 0 5px} thead th:last-child{border-radius:0 5px 5px 0}
  tbody td{padding:5px 7px;border-bottom:1px solid var(--line);font-variant-numeric:tabular-nums;vertical-align:top}
  tbody tr:last-child td{border-bottom:none}
  td.num{text-align:right}
  .tag{display:inline-block;font-size:8.5px;font-weight:700;padding:1px 6px;border-radius:20px;letter-spacing:.02em}
  .tag.new{background:var(--pkw-blue-tint);color:var(--pkw-blue)}
  .tag.out{background:var(--pkw-red-soft);color:var(--pkw-red)}
  .tag.reno{background:#FDF3E4;color:var(--amber)}
  .tag.ok{background:#E7F4EC;color:#1F7A45}
  .days-pill{font-weight:700} .days-pill.hot{color:var(--pkw-red)} .days-pill.warm{color:var(--amber)}

  .issue-top{display:flex;align-items:flex-end;gap:14px}
  .issue-top .big{font-family:var(--num);font-size:34px;font-weight:600;line-height:1;color:var(--pkw-blue);font-variant-numeric:tabular-nums}
  .issue-brk{display:flex;flex-direction:column;gap:2px;font-size:11px;color:var(--ink-2);font-variant-numeric:tabular-nums}
  .issue-brk span b{color:var(--ink);font-weight:600}
  .dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:6px;vertical-align:1px}
  .dot.o{background:var(--pkw-red)} .dot.p{background:var(--amber)} .dot.c{background:var(--pkw-blue)}
  .topissue{margin-top:10px;border-top:1px solid var(--line);padding-top:9px;display:grid;gap:3px;font-size:11px}
  .topissue .lab{color:var(--ink-3);width:60px;display:inline-block}
  .topissue b{font-weight:600}

  .pbar{display:grid;grid-template-columns:96px 1fr 42px;align-items:center;gap:9px;margin:7px 0}
  .pbar .pl{font-size:10.5px;color:var(--ink-2)}
  .pbar .track{position:relative;height:13px;background:var(--pkw-blue-tint);border-radius:7px}
  .pbar .fill{position:absolute;left:0;top:0;bottom:0;background:var(--pkw-blue);border-radius:7px}
  .pbar .fill.over{background:var(--pkw-red)}
  .pbar .pace{position:absolute;top:-3px;bottom:-3px;width:2px;background:var(--ink);opacity:.5}
  .pbar .pv{font-size:10.5px;font-weight:600;text-align:right;font-variant-numeric:tabular-nums}
  .pbar .pv.over{color:var(--pkw-red)}
  .note{font-size:10px;color:var(--ink-3);margin-top:7px;display:flex;align-items:center;gap:5px}
  .note i{display:inline-block;width:2px;height:11px;background:var(--ink);opacity:.5;vertical-align:-1px}

  /* gauge */
  .gauge-wrap{display:flex;gap:16px;align-items:center}
  .gauge{width:132px;height:132px;position:relative;flex:none}
  .gauge .ctr{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
  .gauge .ctr .n{font-family:var(--num);font-size:26px;font-weight:600;color:var(--pkw-blue);font-variant-numeric:tabular-nums}
  .gauge .ctr .t{font-size:9px;color:var(--ink-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em}
  .gauge-side{flex:1;font-size:11.5px}
  .gauge-side .lo{background:var(--pkw-red-soft);border:1px solid #F3C9CA;border-radius:8px;padding:9px 11px}
  .gauge-side .lo .lb{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--pkw-red)}
  .gauge-side .lo .val{font-weight:600;margin-top:2px}

  /* big stat */
  .bigstat{display:flex;flex-direction:column;gap:2px}
  .bigstat .n{font-family:var(--num);font-size:32px;font-weight:600;color:var(--pkw-blue);line-height:1;font-variant-numeric:tabular-nums}
  .bigstat .n.red{color:var(--pkw-red)}
  .bigstat .lb{font-size:10px;color:var(--ink-2);font-weight:600}
  .twostat{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .rate{font-size:10.5px;color:var(--ink-2);margin-top:2px}
  .rate b{color:var(--ink);font-weight:700}

  /* kpi rate cards (kaizen) */
  .kzcards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
  .kz{background:var(--card);border:1px solid var(--line);border-radius:9px;box-shadow:var(--shadow);padding:11px 12px}
  .kz .k{font-size:9px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--ink-3)}
  .kz .v{font-family:var(--num);font-size:26px;font-weight:600;color:var(--pkw-blue);margin:3px 0 2px;font-variant-numeric:tabular-nums}
  .kz .v.red{color:var(--pkw-red)}
  .kz .r{font-size:10.5px;color:var(--ink-2);font-variant-numeric:tabular-nums}
  .kz .r b{color:var(--ink);font-weight:700}

  /* narrative band labels + compact (all tabs) */
  .band{display:flex;align-items:center;gap:9px;margin:15px 2px 9px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)}
  .band .no{width:17px;height:17px;border-radius:5px;background:var(--pkw-blue);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;flex:none}
  .band .ln{flex:1;height:1px;background:var(--line)}
  .kzcards{margin-bottom:2px}
  .tab-page .sec{padding:11px 12px}
  .tab-page .board{gap:10px}
  .tab-page .col{gap:10px}
  .tab-page .sec-title{margin-bottom:8px}
  .tab-page > .band:first-child{margin-top:3px}
  .chart-k{height:116px}
  .pbar-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px 30px}

  .problem-board{
  align-items:stretch;
}

.problem-board .col{
  height:100%;
}

.problem-board .sec{
  height:100%;
}

.item-duration-sec{
  min-height:100%;
}

.item-duration-chart{
  height:300px;
}

  /* heatmap */
  .hm{display:grid;gap:3px;margin-top:4px;font-variant-numeric:tabular-nums}
  .hm .lab{font-size:9px;color:var(--ink-3);display:flex;align-items:center}
  .hm .col{font-size:8.5px;color:var(--ink-3);text-align:center}
  .hm .cell{aspect-ratio:1.4/1;border-radius:3px;min-height:15px}
  .legend{display:flex;align-items:center;gap:5px;font-size:9.5px;color:var(--ink-3);margin-top:9px;justify-content:flex-end}
  .legend i{width:13px;height:9px;border-radius:2px;display:inline-block}

  .back-btn{
  display:inline-flex;
  align-items:center;
  gap:6px;
  width:max-content;
  text-decoration:none;
  background:#ffffff;
  color:#2563eb;
  border:1px solid var(--line);
  border-radius:10px;
  padding:9px 14px;
  font-size:13px;
  font-weight:700;
  box-shadow:var(--shadow);
  margin-bottom:14px;
}

.back-btn:hover{
  background:#eff6ff;
  border-color:#bfdbfe;
}

.mp-top{
  display:grid;
  grid-template-columns:145px 1fr;
  gap:22px;
  align-items:center;
  margin-bottom:16px;
}

.mp-alert{
  background:#fff0f0;
  border:1px solid #f4b8b8;
  border-radius:10px;
  padding:14px 16px;
}

.mp-alert-title{
  font-size:10px;
  font-weight:800;
  color:var(--pkw-red);
  text-transform:uppercase;
  letter-spacing:.04em;
  margin-bottom:6px;
}

.mp-alert-value{
  font-size:14px;
  font-weight:800;
  color:var(--pkw-red);
}

.mp-chart{
  height:185px;
}

.donut-static{
  width:132px;
  height:132px;
  border-radius:50%;
  position:relative;
  flex:0 0 132px;
  display:flex;
  align-items:center;
  justify-content:center;
}

.donut-inner{
  width:86px;
  height:86px;
  border-radius:50%;
  background:#fff;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
}

.donut-value{
  font-size:28px;
  font-weight:800;
  color:var(--pkw-blue);
  line-height:1;
}

.donut-label{
  font-size:10px;
  font-weight:800;
  color:#9aa3b4;
  margin-top:7px;
}

.mp-chart{
  height:170px;
}

#incDept{
  max-height:170px;
}

.kcard .peak{
  margin-top:7px;
  padding-top:6px;
  border-top:1px solid rgba(255,255,255,.2);
  font-size:9.5px;
  display:flex;
  justify-content:space-between;
  opacity:.92;
}

.kcard .peak b{
  font-weight:700;
}

.kcard.plain .peak{
  border-top-color:var(--line);
  opacity:1;
  color:var(--ink-2);
}

.kcard.plain .peak b{
  color:var(--ink);
}

.eq-badge{
  display:inline-block;
  font-size:11px;
  font-weight:700;
  padding:4px 10px;
  border-radius:999px;
  background:var(--pkw-red-soft);
  color:var(--pkw-red);
}

.eq-meta{
  font-size:11px;
  color:var(--ink-2);
  margin-left:8px;
}

.oc-stats{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
  margin-bottom:10px;
}

.oc-stat{
  text-align:center;
  border:1px solid var(--line);
  border-radius:9px;
  padding:11px 8px;
  background:var(--pkw-blue-tint);
}

.oc-stat .n{
  font-family:var(--num);
  font-size:26px;
  font-weight:600;
  line-height:1;
}

.oc-stat.open .n{
  color:#1F7A45;
}

.oc-stat.close .n{
  color:var(--pkw-red);
}

.oc-stat .lb{
  font-size:9.5px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.03em;
  color:var(--ink-2);
  margin-top:4px;
}

.cons-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
}

.cons-item{
  border:1px solid var(--line);
  border-radius:8px;
  padding:9px 10px;
  background:var(--pkw-blue-tint);
}

.cons-item .ci-lbl{
  font-size:10px;
  color:var(--ink-2);
  font-weight:600;
}

.cons-item .ci-val{
  font-family:var(--num);
  font-size:18px;
  font-weight:600;
  color:var(--pkw-blue);
  margin-top:3px;
}

.cons-item .ci-val small{
  font-size:10px;
  font-weight:600;
  color:var(--ink-2);
}

.mp-groups{
  display:flex;
  flex-direction:column;
  gap:3px;
}

.mp-grp-head{
  display:flex;
  align-items:center;
  gap:7px;
  font-size:10px;
  font-weight:700;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:var(--ink-2);
  margin:10px 0 5px;
}

.mp-grp-head:first-child{
  margin-top:2px;
}

.mp-grp-head .dot{
  width:7px;
  height:7px;
  border-radius:50%;
  background:var(--pkw-blue);
  flex:none;
}

.mp-row{
  display:grid;
  grid-template-columns:108px 1fr 32px;
  align-items:center;
  gap:8px;
  font-size:11px;
  padding:3px 0;
  color:var(--ink-2);
}

.mp-row .mp-track{
  height:8px;
  background:var(--pkw-blue-tint);
  border-radius:5px;
  overflow:hidden;
}

.mp-row .mp-fill{
  height:100%;
  border-radius:5px;
}

.mp-row .mp-pct{
  font-weight:600;
  text-align:right;
  font-variant-numeric:tabular-nums;
  color:var(--ink);
}

.wd-filter{
  display:flex;
  align-items:center;
  gap:9px;
  margin-bottom:10px;
}

.wd-filter .wd-lbl{
  font-size:10px;
  font-weight:700;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:var(--ink-3);
}

.wd-filter select{
  font:inherit;
  font-size:11.5px;
  font-weight:600;
  color:var(--pkw-blue);
  border:1px solid var(--line);
  border-radius:7px;
  padding:5px 10px;
  background:var(--card);
  cursor:pointer;
}

.wd-filter select:focus{
  outline:none;
  border-color:var(--pkw-blue-mid);
}

.chart-tall{
  height:214px;
}

.budget-legend{
  display:flex;
  gap:16px;
  font-size:10px;
  color:var(--ink-2);
  margin-bottom:9px;
}

.budget-legend i{
  width:10px;
  height:10px;
  border-radius:3px;
  display:inline-block;
  margin-right:5px;
  vertical-align:-1px;
}

.budget-dept details{
  border-bottom:1px solid var(--line);
}

.budget-dept details:last-child{
  border-bottom:none;
}

.budget-dept summary{
  cursor:pointer;
  list-style:none;
  padding:9px 0;
}

.budget-dept summary::-webkit-details-marker{
  display:none;
}

.budget-dept summary::marker{
  content:"";
}

.budget-dept summary:hover .pl{
  color:var(--pkw-blue);
}

.pbar-stack{
  display:flex;
  flex-direction:column;
  gap:6px;
}

.pbar-stack .bsum-head{
  display:grid;
  grid-template-columns:120px 1fr 54px 14px;
  align-items:baseline;
  gap:9px;
}

.pbar-stack .pl{
  font-size:11px;
  color:var(--ink);
  font-weight:700;
}

.pbar-stack .bsum-nums{
  display:flex;
  gap:14px;
  font-size:10px;
  color:var(--ink-2);
  font-variant-numeric:tabular-nums;
  flex-wrap:wrap;
}

.pbar-stack .bsum-nums b{
  color:var(--ink);
  font-weight:600;
}

.pbar-stack .bsum-nums .u b{
  color:var(--amber);
}

.pbar-stack .track{
  position:relative;
  height:13px;
  background:var(--pkw-blue-tint);
  border-radius:7px;
  overflow:hidden;
  display:flex;
}

.pbar-stack .seg-b{
  height:100%;
  background:var(--pkw-blue);
}

.pbar-stack .seg-u{
  height:100%;
  background:var(--amber);
}

.pbar-stack .pv{
  font-size:10.5px;
  font-weight:600;
  text-align:right;
  font-variant-numeric:tabular-nums;
}

.pbar-stack .chev{
  font-size:10px;
  color:var(--ink-3);
  transition:transform .15s;
  text-align:center;
}

.budget-dept details[open] .chev{
  transform:rotate(90deg);
}

.budget-detail{
  padding:1px 0 11px 4px;
}

.budget-detail table{
  font-size:10.5px;
}

.budget-detail .tag.unb{
  background:#FDF3E4;
  color:var(--amber);
  font-size:8px;
}

.consumable-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.cons-card{
  background:var(--pkw-blue-tint);
  border:1px solid var(--line);
  border-radius:9px;
  padding:14px 16px;
  min-height:70px;
}

.cons-name{
  font-size:12px;
  color:var(--ink-2);
  font-weight:600;
  margin-bottom:10px;
}

.cons-val{
  font-size:22px;
  color:var(--pkw-blue);
  font-weight:600;
  line-height:1;
}

.cons-val small{
  font-size:12px;
  color:var(--ink-2);
  font-weight:500;
  margin-left:3px;
}

.asset-filter{
  display:flex;
  align-items:center;
  gap:9px;
  margin:10px 0 8px;
}

.asset-filter .asset-lbl{
  font-size:10px;
  font-weight:700;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:var(--ink-3);
}

.asset-filter select{
  font:inherit;
  font-size:11.5px;
  font-weight:600;
  color:var(--pkw-blue);
  border:1px solid var(--line);
  border-radius:7px;
  padding:5px 10px;
  background:var(--card);
  cursor:pointer;
}

.asset-filter select:focus{
  outline:none;
  border-color:var(--pkw-blue-mid);
}

.equip-status-kpi{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
  margin-bottom:10px;
}

.equip-status-box{
  background:var(--pkw-blue-tint);
  border:1px solid var(--line);
  border-radius:10px;
  padding:16px 14px;
  text-align:center;
  min-height:78px;
}

.equip-status-box .equip-status-num{
  font-size:32px;
  line-height:1;
  font-weight:600;
  margin-bottom:8px;
}

.equip-status-box.on .equip-status-num{
  color:#1F7A45;
}

.equip-status-box.off .equip-status-num{
  color:var(--pkw-red);
}

.equip-status-label{
  font-size:11px;
  color:var(--ink-2);
  font-weight:700;
  letter-spacing:.04em;
}

  footer{margin-top:14px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:10px;color:var(--ink-3);border-top:1px solid var(--line);padding-top:9px}

  @media (max-width:980px){.board{grid-template-columns:repeat(2,minmax(0,1fr))}.kzcards{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media (max-width:640px){.board,.board.two{grid-template-columns:1fr}.kcards.three,.kcards.five{grid-template-columns:1fr 1fr}.daterange{width:100%}.tabs{width:100%}.tabs button{flex:1;text-align:center}}
</style>
</head>
<body>
<div class="wrap">

  <a href="{{ route('datahub.index') }}" class="back-btn">
  ← Back to OM Dashboard
</a>

  <header>
    <div class="brandmark"></div>
    <div>
      <h1>{{ $mallName }} — OM Dashboard</h1>
      <div class="sub">Operations Manager View · Pakuwon Group Jakarta</div>
    </div>
    
     <form method="GET" action="{{ route('datahub.om.detail', $mall) }}" class="daterange">
  <span class="lbl">Date Range</span>
  <div class="field">
    <input type="date" id="dStart" name="start_date" value="{{ $startDate }}" aria-label="Start date">
    <span class="arrow">→</span>
    <input type="date" id="dEnd" name="end_date" value="{{ $endDate }}" aria-label="End date">
    <input type="hidden" name="traffic_granularity" value="{{ $trafficGranularity ?? 'monthly' }}">
    <input type="hidden" name="tenant_movement_type" value="{{ $tenantMovementType ?? 'all' }}">
    <input type="hidden" id="activeTabInput" name="tab" value="{{ $activeTab ?? 'overview' }}">
    <button type="submit" style="border:none;background:#332E83;color:white;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:700;cursor:pointer;">
      Apply
    </button>
  </div>
</form>

  </header>

  <div class="tabs" id="omTabs">
    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'overview' ? 'active' : '' }}"
       data-tab="overview"
       href="{{ route('datahub.om.detail', $mall) }}?start_date={{ $startDate }}&end_date={{ $endDate }}&traffic_granularity={{ $trafficGranularity }}&tenant_movement_type={{ $tenantMovementType }}&tab=overview">
        Operational Overview
    </a>

    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'department' ? 'active' : '' }}"
       data-tab="department"
       href="{{ route('datahub.om.detail', $mall) }}?start_date={{ $startDate }}&end_date={{ $endDate }}&traffic_granularity={{ $trafficGranularity }}&tenant_movement_type={{ $tenantMovementType }}&tab=department">
        Department Report
    </a>

    <a class="om-tab-link {{ ($activeTab ?? 'overview') === 'kaizen' ? 'active' : '' }}"
       data-tab="kaizen"
       href="{{ route('datahub.om.detail', $mall) }}?start_date={{ $startDate }}&end_date={{ $endDate }}&traffic_granularity={{ $trafficGranularity }}&tenant_movement_type={{ $tenantMovementType }}&tab=kaizen">
        Kaizen Report
    </a>
</div>

  <!-- ═════════ TAB 1 · OPERATIONAL OVERVIEW ═════════ -->
  <section class="tab-page {{ ($activeTab ?? 'overview') === 'overview' ? 'active' : '' }}" id="tab-ovw">

    <!-- Band 1 · Traffic & Visitors -->
    <div class="band"><span class="no">1</span>Traffic &amp; Visitors<span class="ln"></span></div>
    <div class="sec">
      <div class="sec-title"><h2>Traffic</h2><span class="hint">vs LW · LM · LY</span></div>
      <div class="kcards five">
        <div class="kcard">
          <div class="k">Head Count</div>
            <div class="v" id="headCountVal">{!! omFormatCompact($trafficSummary['head_count'] ?? 0) !!}</div>
              <div class="vs">
              <span>LW <span id="headLwVal">{!! omPercentLabel($trafficSummary['head_lw_percent'] ?? 0) !!}</span></span>
              <span>LM <span id="headLmVal">{!! omPercentLabel($trafficSummary['head_lm_percent'] ?? 0) !!}</span></span>
              <span>LY <span id="headLyVal">{!! omPercentLabel($trafficSummary['head_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak">
            <span>Peak</span>
              <b id="headPeakVal">{{ $trafficSummary['head_peak_hour'] ?? '-' }}</b>
                </div>
        </div>

        <div class="kcard">
          <div class="k">Car Count</div>
          <div class="v" id="carCountVal">{!! omFormatCompact($trafficSummary['car_count'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="carLwVal">{!! omPercentLabel($trafficSummary['car_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="carLmVal">{!! omPercentLabel($trafficSummary['car_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="carLyVal">{!! omPercentLabel($trafficSummary['car_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak"><span>Peak</span><b id="carPeakVal">{{ $trafficSummary['parking_peak_hour'] ?? '-' }}</b></div>
        </div>

        <div class="kcard">
          <div class="k">Motor Count</div>
          <div class="v" id="motorCountVal">{!! omFormatCompact($trafficSummary['motor_count'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="motorLwVal">{!! omPercentLabel($trafficSummary['motor_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="motorLmVal">{!! omPercentLabel($trafficSummary['motor_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="motorLyVal">{!! omPercentLabel($trafficSummary['motor_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak"><span>Peak</span><b id="motorPeakVal">{{ $trafficSummary['parking_peak_hour'] ?? '-' }}</b></div>
        </div>

        <div class="kcard">
          <div class="k">Blue Bird</div>
          <div class="v" id="blueBirdVal">{!! omFormatCompact($trafficSummary['blue_bird'] ?? 0) !!}</div>
          <div class="vs">
            <span>LW <span id="blueBirdLwVal">{!! omPercentLabel($trafficSummary['blue_bird_lw_percent'] ?? 0) !!}</span></span>
            <span>LM <span id="blueBirdLmVal">{!! omPercentLabel($trafficSummary['blue_bird_lm_percent'] ?? 0) !!}</span></span>
            <span>LY <span id="blueBirdLyVal">{!! omPercentLabel($trafficSummary['blue_bird_ly_percent'] ?? 0) !!}</span></span>
          </div>
          <div class="peak">
              <span>Peak</span>
                <b id="blueBirdPeakVal">{{ $trafficSummary['blue_bird_peak_hour'] ?? '-' }} WIB</b>
                  </div>
        </div>

        <div class="kcard">
  <div class="k">Drop Off</div>
  <div class="v" id="dropOffVal">{!! omFormatCompact($trafficSummary['drop_off_count'] ?? 0) !!}</div>

  <div class="vs">
    <span>LW <span id="dropOffLwVal">{!! omPercentLabel($trafficSummary['drop_off_lw_percent'] ?? 0) !!}</span></span>
    <span>LM <span id="dropOffLmVal">{!! omPercentLabel($trafficSummary['drop_off_lm_percent'] ?? 0) !!}</span></span>
    <span>LY <span id="dropOffLyVal">{!! omPercentLabel($trafficSummary['drop_off_ly_percent'] ?? 0) !!}</span></span>
  </div>

  <div class="peak">
    <span>Peak</span>
    <b id="dropOffPeakVal">{{ $trafficSummary['drop_off_peak_hour'] ?? '-' }} WIB</b>
  </div>
</div>
    </div>

    <!-- Band 2 · Movement Patterns -->
    <div class="band"><span class="no">2</span>Movement Patterns<span class="ln"></span></div>
    <div class="board">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Traffic Pattern</h2>
          <form method="GET" action="{{ route('datahub.om.detail', $mall) }}" class="seg">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="tenant_movement_type" value="{{ $tenantMovementType ?? 'all' }}">
            <input type="hidden" name="tab" value="overview">

  <button type="submit"
          name="traffic_granularity"
          value="monthly"
          class="{{ ($trafficGranularity ?? 'monthly') === 'monthly' ? 'on' : '' }}">
    Monthly
  </button>

  <button type="submit"
          name="traffic_granularity"
          value="weekly"
          class="{{ ($trafficGranularity ?? 'monthly') === 'weekly' ? 'on' : '' }}">
    Weekly
  </button>

  <button type="submit"
          name="traffic_granularity"
          value="daily"
          class="{{ ($trafficGranularity ?? 'monthly') === 'daily' ? 'on' : '' }}">
    Daily
  </button>
</form>
        </div>
          <!-- <pre style="font-size:11px;background:#f8fafc;border:1px solid #ddd;padding:8px;max-height:180px;overflow:auto;">
            {{ json_encode($trafficPattern ?? [], JSON_PRETTY_PRINT) }}
              </pre> -->
          <div class="muted" style="font-weight:600;margin-bottom:3px">Traffic Count</div>
          <div class="chart-xs"><canvas id="ovCount"></canvas></div>
          <div class="avg-line">
            <span>Avg <b id="ovAvgVehicle">—</b></span>
              <span>WD <b>—</b></span>
                <span>WE <b>—</b></span>
                      </div>
          <div class="muted" style="font-weight:600;margin:9px 0 3px">Traffic Income</div>
          <div class="chart-xs"><canvas id="ovIncome"></canvas></div>
          <div class="avg-line">
            <span>Avg <b id="ovAvgIncome">—</b></span>
              <span>Peak <b>—</b></span>
                </div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Parking Count</h2></div>
          
         <div class="pk-meta">
  <span>
    Avg Dur. Car
    <b id="parkingAvgCarVal">{{ number_format(($parkingSummary['avg_parking_duration_minutes'] ?? 0) / 60, 1) }} hr</b>
  </span>

  <span>
    Avg Dur. Motor
  <b id="parkingAvgMotorVal">{{ number_format(($parkingSummary['avg_parking_duration_minutes'] ?? 0) / 60, 1) }} hr</b> 
  </span>

  <span>
    Peak Car
  <b id="parkingPeakCarVal">{{ $parkingSummary['peak_parking_hour'] ?? '-' }}</b>
  </span>

  <span>
    Peak Motor
  <b id="parkingPeakMotorVal">{{ $parkingSummary['peak_parking_hour'] ?? '-' }}</b>
  </span>

  <span>
    Off-Peak Car
    <b>{{ $parkingSummary['off_peak_car_hour'] ?? '11:00' }}</b>
  </span>

  <span>
    Off-Peak Motor
    <b>{{ $parkingSummary['off_peak_motor_hour'] ?? '10:00' }}</b>
  </span>
</div>

          <div class="chart-sm"><canvas id="ovPark"></canvas></div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Valet</h2></div>

          <div class="kcards three">
            <div class="kcard plain">
              <div class="k">Served</div>
              <div class="v">
                <span id="valetServedVal">{{ number_format($valetSummary['valet_served'] ?? 0, 0) }}</span><small> cars</small>
              </div>
            </div>

            <div class="kcard plain">
              <div class="k">Avg Wait</div>
              <div class="v">
                <span id="valetAvgWaitVal">{{ number_format($valetSummary['valet_avg_wait_minutes'] ?? 0, 1) }}</span><small> min</small>
              </div>
            </div>

            <div class="kcard plain">
              <div class="k">Income</div>
              <div class="v">
                <span id="valetIncomeVal">{{ number_format(($valetSummary['valet_income'] ?? 0) / 1000000, 0) }}</span><small>M</small>
              </div>
            </div>
          </div>

          <div class="muted" style="font-weight:600;margin:10px 0 3px">Valet Income Trend</div>
          <div class="chart-xs"><canvas id="ovValet"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Band 3 · Issues & Risk -->
<div class="band"><span class="no">3</span>Issues &amp; Risk<span class="ln"></span></div>

<div class="board two">
  <div class="col">
    <div class="sec">
      <div class="sec-title"><h2>Total Issue</h2></div>

      <div class="issue-top">
        <div class="big" id="issueTotalVal">
           {{ number_format($issueSummary['total_issue'] ?? 0, 0) }}
        </div>

        <div class="issue-brk">
          <span>
            <span class="dot o"></span>Open
            <b id="issueOpenVal">{{ number_format($issueSummary['open_issue'] ?? 0, 0) }}</b>
          </span>

          <span>
            <span class="dot p"></span>In Progress
            <b id="issueProgressVal">{{ number_format($issueSummary['in_progress_issue'] ?? 0, 0) }}</b>
          </span>

          <span>
            <span class="dot c"></span>Closed
            <b id="issueClosedVal">{{ number_format($issueSummary['closed_issue'] ?? 0, 0) }}</b>
          </span>
        </div>
      </div>

      <div class="delta-row">
        <span>
          Oldest Issue
          <b class="dn" id="issueOldestVal">{{ number_format($issueSummary['oldest_issue_days'] ?? 0, 0) }} days</b>
        </span>
      </div>

      <div class="topissue">
        <div class="muted" style="font-weight:700;color:var(--pkw-blue);margin-bottom:2px">
          Top Issue
        </div>

        <div>
          <span class="lab">Dept</span>
          <b id="issueTopDeptVal">{{ $issueSummary['top_issue_department'] ?? '-' }}</b>
        </div>

        <div>
          <span class="lab">Area</span>
          <b id="issueTopAreaVal">{{ $issueSummary['top_issue_area'] ?? '-' }}</b>
        </div>

        <div>
          <span class="lab">Issue</span>
          <b id="issueTopItemVal">{{ $issueSummary['top_issue_item'] ?? '-' }}</b>
        </div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="sec">
      <div class="sec-title"><h2>Issue Risk Monitor</h2></div>

      <table>
        <thead>
          <tr>
              <th>Area</th>
              <th>Loc.</th>
              <th>Issue</th>
              <th>Dept</th>
              <th>Status</th>
              <th class="num">Days</th>
          </tr>
        </thead>

       <tbody id="issueRiskBody">
  @forelse($issueRiskMonitor as $issue)
    @php
      $days = $issue['days'] ?? 0;

      $dayClass = match (true) {
          $days >= 14 => 'hot',
          $days >= 7 => 'warm',
          default => '',
      };
    @endphp

    <tr>
      <td>{{ $issue['area'] ?? '-' }}</td>
      <td>{{ $issue['location'] ?? '-' }}</td>
      <td>{{ $issue['issue'] ?? '-' }}</td>
      <td>{{ $issue['department'] ?? '-' }}</td>
      <td>{{ $issue['status'] ?? '-' }}</td>
      <td class="num">
        <span class="days-pill {{ $dayClass }}">
          {{ number_format($days, 0) }}
        </span>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" style="text-align:center;color:var(--ink-3);padding:12px;">
        No issue data
      </td>
    </tr>
  @endforelse
</tbody>
      </table>
    </div>
  </div>
</div>

    <!-- Band 4 · Tenancy & Events -->
    <div class="band" id="overviewDetailTrigger"><span class="no">4</span>Tenancy &amp; Events<span class="ln"></span></div>
    <div class="board">
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>Fit Out List</h2></div>

  <table>
    <thead>
  <tr>
    <th>Tenant</th>
    <th>Location</th>
  </tr>
</thead>

    <tbody id="fitOutBody">
      @forelse($fitOutList as $fitout)
        <tr>
  <td>{{ $fitout['tenant'] ?? '-' }}</td>
  <td>{{ $fitout['location'] ?? '-' }}</td>
</tr>
      @empty
        <tr>
          <td colspan="2" style="text-align:center;color:var(--ink-3);padding:12px;">
            No fit out data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title">
    <h2>Tenant Movement</h2>

    <form method="GET" action="{{ route('datahub.om.detail', $mall) }}" class="seg" id="tenantMovementSeg">
      <input type="hidden" name="start_date" value="{{ $startDate }}">
      <input type="hidden" name="end_date" value="{{ $endDate }}">
      <input type="hidden" name="traffic_granularity" value="{{ $trafficGranularity ?? 'monthly' }}">
      <input type="hidden" name="tab" value="overview">
  <button type="submit"
          name="tenant_movement_type"
          value="all"
          class="{{ ($tenantMovementType ?? 'all') === 'all' ? 'on' : '' }}">
    All
  </button>

  <button type="submit"
          name="tenant_movement_type"
          value="new"
          class="{{ ($tenantMovementType ?? 'all') === 'new' ? 'on' : '' }}">
    New
  </button>

  <button type="submit"
          name="tenant_movement_type"
          value="pullout"
          class="{{ ($tenantMovementType ?? 'all') === 'pullout' ? 'on' : '' }}">
    Pull-out
  </button>
</form>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tenant</th>
        <th>Cat.</th>
        <th>Type</th>
      </tr>
    </thead>

    <tbody id="tenantMovementBody">
      @forelse($tenantMovement as $tenant)
        @php
          $type = $tenant['type'] ?? '-';

          $typeClass = match (strtolower($type)) {
              'new' => 'new',
              'out' => 'out',
              default => 'reno',
          };
        @endphp

        <tr>
          <td>{{ $tenant['tenant'] ?? '-' }}</td>
          <td>{{ $tenant['category'] ?? '-' }}</td>
          <td>
            <span class="tag {{ $typeClass }}">
              {{ $type }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant movement data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title">
    <h2>Casual Leasing / Promo · Event Pipeline</h2>
  </div>

  <table>
    <thead>
      <tr>
        <th>Location</th>
        <th>Event</th>
        <th>Period</th>
      </tr>
    </thead>

    <tbody id="eventPipelineBody">
      @forelse($eventPipeline as $event)
        <tr>
          <td>{{ $event['location'] ?? '-' }}</td>
          <td>{{ $event['event'] ?? '-' }}</td>
          <td>{{ $event['period'] ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No event pipeline data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
    </div>

   <!-- Band 5 · Budget Control -->
<div class="band"><span class="no">5</span>Budget Control<span class="ln"></span></div>

<div class="sec">
  <div class="sec-title">
    <h2>Budget vs Spending · by Department</h2>
    <span class="hint">spending as % of budget · click a department for item detail</span>
  </div>

  <div class="budget-legend">
    <span><i style="background:var(--pkw-blue)"></i>Budgeted</span>
    <span><i style="background:var(--amber)"></i>Unbudgeted</span>
  </div>

  <div class="budget-dept" id="budgetDeptBody">
    @forelse($budgetSpending as $deptIndex => $dept)
      @php
        $budget = (float) ($dept['total_budget'] ?? 0);
        $budgetedSpending = (float) ($dept['budgeted_spending'] ?? 0);
        $unbudgetedSpending = (float) ($dept['unbudgeted_spending'] ?? 0);
        $usagePercent = (float) ($dept['usage_percent'] ?? 0);

        $budgetedWidth = $budget > 0
            ? min(100, max(0, ($budgetedSpending / $budget) * 100))
            : 0;

        $unbudgetedWidth = $budget > 0
            ? min(100 - $budgetedWidth, max(0, ($unbudgetedSpending / $budget) * 100))
            : 0;

        $isOpen = $deptIndex === 0 ? 'open' : '';
      @endphp

      <details {{ $isOpen }}>
        <summary>
          <div class="pbar-stack">
            <div class="bsum-head">
              <span class="pl">{{ $dept['department'] ?? '-' }}</span>

              <span class="bsum-nums">
                <span>Budget <b>{{ number_format($budget / 1000000, 0) }}M</b></span>
                <span>Spending <b>{{ number_format($budgetedSpending / 1000000, 0) }}M</b></span>
                <span class="u">Unbudgeted <b>{{ number_format($unbudgetedSpending / 1000000, 0) }}M</b></span>
              </span>

              <span class="pv">
                {{ number_format($usagePercent, 0) }}%
              </span>

              <span class="chev">›</span>
            </div>

            <div class="track">
              <div class="seg-b" style="width:{{ $budgetedWidth }}%"></div>
              <div class="seg-u" style="width:{{ $unbudgetedWidth }}%"></div>
            </div>
          </div>
        </summary>

        <div class="budget-detail">
          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th class="num">Budget</th>
                <th class="num">Spending</th>
                <th class="num">Unbudgeted</th>
                <th class="num">Usage %</th>
              </tr>
            </thead>

            <tbody>
              @forelse(($dept['items'] ?? []) as $item)
                <tr>
                  <td>
                    {{ $item['item'] ?? '-' }}

                    @if($item['is_unbudgeted'] ?? false)
                      <span class="tag unb">Unbudgeted</span>
                    @endif
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? '—'
                        : number_format(($item['budget'] ?? 0) / 1000000, 0) . 'M' }}
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? '—'
                        : number_format(($item['spending'] ?? 0) / 1000000, 0) . 'M' }}
                  </td>

                  <td class="num">
                    {{ ($item['is_unbudgeted'] ?? false)
                        ? number_format(($item['unbudgeted'] ?? 0) / 1000000, 0) . 'M'
                        : '—' }}
                  </td>

                  <td class="num">
                    {{ ($item['usage_percent'] ?? null) === null
                        ? '—'
                        : number_format($item['usage_percent'], 0) . '%' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" style="text-align:center;color:var(--ink-3);padding:12px;">
                    No budget item data
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>
    @empty
      <div style="font-size:12px;color:var(--ink-3);padding:12px 0;">
        No budget spending data
      </div>
    @endforelse
  </div>
</div>

<!-- Band 6 · Work Detail per Dept -->
<div class="band"><span class="no">6</span>Work Detail per Dept<span class="ln"></span></div>

<div class="sec">
  <div class="sec-title">
    <h2>Work Detail per Dept</h2>
    <span class="hint">Daily Activity</span>
  </div>

  <div class="wd-filter">
    <span class="wd-lbl">Department</span>

    <select id="wdDeptSel">
      <option value="all">All Departments</option>
      @foreach(($workDetailDaily['departments'] ?? []) as $dept)
        @php
          $deptValue = strtolower(str_replace(' ', '', $dept['department'] ?? ''));
        @endphp
        <option value="{{ $deptValue }}">{{ $dept['department'] ?? '-' }}</option>
      @endforeach
    </select>
  </div>

  <div class="chart-tall">
    <canvas id="wdChart"></canvas>
  </div>

  <div class="muted" id="wdFoot" style="margin-top:10px">
    Loading work detail data...
  </div>
</div>

</section>

<!-- ═════════ TAB 2 · ISORT REPORT ═════════ -->
<section class="tab-page {{ ($activeTab ?? 'overview') === 'department' ? 'active' : '' }}" id="tab-isort">

  <!-- Band 1 · Manpower & Incidents -->
  <div class="band"><span class="no">1</span>Manpower &amp; Incidents<span class="ln"></span></div>

  <div class="board two">
    <div class="col">
      <div class="sec">
        <div class="sec-title">
          <h2>Manpower Fulfillment</h2>
          <div class="seg" id="mpTabSeg">
            <button type="button" class="on" data-mp="inhouse">Inhouse</button>
            <button type="button" data-mp="outsource">Outsource</button>
          </div>
        </div>

        @php
          $manpowerInhouse = $manpowerFulfillment['inhouse'] ?? [];
          $manpowerOverall = $manpowerInhouse['overall'] ?? 0;
          $lowestDept = '-';
          $lowestPct = null;
        @endphp

        <div class="gauge-wrap">
          <div class="gauge">
            <canvas id="mpGauge"></canvas>
            <div class="ctr">
              <div class="n" id="mpOverall">{{ number_format($manpowerOverall, 0) }}</div>
                <div class="t" id="mpOverallLabel">Inhouse</div>
            </div>
          </div>

          <div class="gauge-side">
            <div class="lo" id="mpLowestBox">
              <div class="lb">Lowest Fulfillment</div>
              <div class="val" id="mpLowest">
                  Inhouse tidak memakai target fulfillment
              </div>
            </div>
          </div>
        </div>

        <div class="muted" style="font-weight:600;margin:11px 0 3px" id="mpByLabel">
          By Department
        </div>

        <div class="chart-md" id="mpChartWrap">
          <canvas id="mpDept"></canvas>
        </div>

        <div class="mp-groups" id="mpGroupsWrap" style="display:none"></div>
      </div>
    </div>

    <div class="col">
      <div class="sec">
        <div class="sec-title">
          <h2>Incident by Dept</h2>
          <span class="hint">Daily Department Report</span>
        </div>

        <div class="chart-sm">
          <canvas id="incDept"></canvas>
        </div>

        <div class="muted" id="incDeptText" style="margin-top:9px">
          Total
            <b style="color:var(--ink)">
              {{ number_format($incidentByDepartment['total_incident'] ?? 0, 0) }} incidents
            </b>
              this period · {{ $incidentByDepartment['highest_department'] ?? '-' }} highest
              ({{ number_format($incidentByDepartment['highest_case'] ?? 0, 0) }}).
          </div>
      </div>
    </div>
  </div>

  <!-- Band 2 · Equipment Health -->
  <div class="band" id="departmentEquipmentTrigger"><span class="no">2</span>Equipment Health<span class="ln"></span></div>

  <div class="board two">
    <div class="col">
     <div class="sec">
  <div class="sec-title">
    <h2>Equipment Status</h2>
    <span class="hint">Below normal saja</span>
  </div>

  <div style="margin-bottom:10px">
    <span class="eq-badge" id="equipmentBelowVal">
      {{ number_format($equipmentStatus['below_normal'] ?? 0, 0) }} below normal
    </span>

    <span class="eq-meta" id="equipmentMetaVal">
       ditampilkan · {{ number_format($equipmentStatus['total_monitored'] ?? 0, 0) }} unit &amp; parameter dipantau
    </span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item / Parameter</th>
        <th>Status</th>
      </tr>
    </thead>

    <tbody id="equipmentStatusBody">
      @forelse(($equipmentStatus['items'] ?? []) as $item)
        <tr>
          <td>{{ $item['department'] ?? '-' }}</td>
          <td>{{ $item['item_parameter'] ?? '-' }}</td>
          <td>
            <span class="tag out">
              {{ $item['status'] ?? 'Below Normal' }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No below normal equipment data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
    </div>

    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Equipment Close / Open</h2>
  </div>

  <div class="equip-status-kpi">
    <div class="equip-status-box on">
      <div class="equip-status-num" id="equipmentOnVal">
        {{ number_format($equipmentOpenClose['total_on'] ?? 0, 0) }}
      </div>
      <div class="equip-status-label">OPEN / ON</div>
    </div>

    <div class="equip-status-box off">
      <div class="equip-status-num" id="equipmentOffVal">
       {{ number_format($equipmentOpenClose['total_off'] ?? 0, 0) }}
      </div>
      <div class="equip-status-label">CLOSE / OFF</div>
    </div>
  </div>

  <div class="muted" style="margin:12px 0 8px;">
    Daftar di bawah hanya menampilkan equipment dengan status
    <b style="color:var(--pkw-red)">Close / Off</b>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item</th>
        <th class="num">Qty</th>
      </tr>
    </thead>

    <tbody id="equipmentOpenCloseBody">
      @forelse(($equipmentOpenClose['items'] ?? []) as $item)
        <tr>
          <td>{{ $item['department'] ?? '-' }}</td>
          <td>{{ $item['item'] ?? '-' }}</td>
          <td class="num">{{ number_format($item['qty'] ?? 0, 0) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No close / off equipment data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
    </div>
  </div>

  <!-- Band 3 · Inventory & Tenant -->
  <div class="band"><span class="no">3</span>Inventory &amp; Tenant Update<span class="ln"></span></div>

  <div class="board">
    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Consumable</h2>
    <span class="hint">Item daily usage</span>
  </div>

  <div class="consumable-grid" id="consumableBody">
    @forelse($consumableUsage as $item)
      <div class="cons-card">
        <div class="cons-name">{{ $item['name'] ?? '-' }}</div>
        <div class="cons-val">
          {{ number_format($item['total'] ?? 0, 1) }}
          <small>{{ $item['uom'] ?? '' }}</small>
        </div>
      </div>
    @empty
      <div style="font-size:12px;color:var(--ink-3);padding:12px;">
        No consumable usage data
      </div>
    @endforelse
  </div>
</div>
    </div>

    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Asset Inventory</h2>
  </div>

  <div class="equip-status-kpi">
  <div class="equip-status-box on">
    <div class="equip-status-num" id="assetGoodNum">
      {{ number_format($assetInventory['total_good'] ?? 0, 0) }}
    </div>
    <div class="equip-status-label">GOOD CONDITION</div>
  </div>

  <div class="equip-status-box off">
    <div class="equip-status-num" id="assetBadNum">
      {{ number_format($assetInventory['total_bad'] ?? 0, 0) }}
    </div>
    <div class="equip-status-label">BAD CONDITION</div>
  </div>
</div>

  <div class="asset-filter">
    <span class="asset-lbl">Department</span>

    <select id="assetDeptSel">
      <option value="all">All Departments</option>
      @foreach(($assetInventory['departments'] ?? []) as $dept)
        @php
          $deptValue = strtolower(str_replace(' ', '', $dept['department_raw'] ?? ''));
        @endphp
        <option value="{{ $deptValue }}">{{ $dept['department'] ?? '-' }}</option>
      @endforeach
    </select>
  </div>

  <div class="muted" style="font-weight:700;margin:12px 0 7px;">
    Bad Condition Items
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th>Item</th>
        <th class="num">Qty</th>
      </tr>
    </thead>

    <tbody id="assetBadBody">
      <tr>
        <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
          Loading asset inventory data...
        </td>
      </tr>
    </tbody>
  </table>
</div>
    </div>

    <div class="col">
      <div class="sec">
  <div class="sec-title">
    <h2>Tenant Update</h2>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tenant</th>
        <th>Unit</th>
        <th>Type</th>
        <th>Status</th>
      </tr>
    </thead>

    <tbody id="tenantUpdateBody">
      @forelse($tenantUpdate as $tenant)
        @php
          $status = strtoupper(trim($tenant['status'] ?? '-'));

          $statusClass = match (true) {
              str_contains($status, 'OPEN') => 'new',
              str_contains($status, 'OPERATING') => 'ok',
              str_contains($status, 'CLOSED') => 'out',
              str_contains($status, 'WAITING') => 'reno',
              str_contains($status, 'FIT OUT') => 'reno',
              default => 'new',
          };
        @endphp

        <tr>
          <td>{{ $tenant['tenant'] ?? '-' }}</td>
          <td>{{ $tenant['unit'] ?? '-' }}</td>
          <td>{{ $tenant['type'] ?? '-' }}</td>
          <td>
            <span class="tag {{ $statusClass }}">
              {{ $tenant['status'] ?? '-' }}
            </span>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant update data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
    </div>
  </div>

</section>


  <!-- ═════════ TAB 3 · KAIZEN REPORT ═════════ -->
  <section class="tab-page {{ ($activeTab ?? 'overview') === 'kaizen' ? 'active' : '' }}" id="tab-kaizen">

    <!-- Band 1 · Case Status -->
    <div class="band"><span class="no">1</span>Monitoring · Case Status<span class="ln"></span></div>
    <div class="kzcards">
  <div class="kz">
    <div class="k">Open Case</div>
      <div class="v" id="kzOpenCaseVal">
        {{ number_format($kaizenSummary['total_open'] ?? 0, 0) }}
      </div>
      <div class="r">
        Open rate <b id="kzOpenRateVal">{{ number_format($kaizenSummary['open_rate'] ?? 0, 1) }}%</b>
      </div>
  </div>

  <div class="kz">
    <div class="k">Close Case</div>
   <div class="v" id="kzCloseCaseVal">
     {{ number_format($kaizenSummary['total_closed'] ?? 0, 0) }}
    </div>
    <div class="r">
       Close rate <b id="kzCloseRateVal">{{ number_format($kaizenSummary['closed_rate'] ?? 0, 1) }}%</b>
     </div>
  </div>

  <div class="kz">
    <div class="k">Overdue Case</div>
     <div class="v red" id="kzOverdueCaseVal">
       {{ number_format($kaizenSummary['total_overdue'] ?? 0, 0) }}
     </div>
     <div class="r">
      Overdue rate <b id="kzOverdueRateVal">{{ number_format($kaizenSummary['overdue_rate'] ?? 0, 1) }}%</b>
    </div>
  </div>

  <div class="kz">
    <div class="k">Total Avg Duration</div>
     <div class="v">
  <span id="kzAvgDurationVal">{{ number_format($kaizenSummary['avg_duration_day'] ?? 0, 1) }}</span><span style="font-size:14px"> d</span>
  </div>
    <div class="r">across solved cases</div>
  </div>
</div>

    <!-- Band 2 · Where & When -->
    <div class="band"><span class="no">2</span>Where &amp; When<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Hotspot Alert · Cases by Area</h2></div>
          <div class="chart-k"><canvas id="kzHotspot"></canvas></div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Peak Day / Hour Pattern</h2></div>
          <div class="hm" id="kzHeat" role="img" aria-label="Kaizen case density by day and hour"></div>
          <div class="legend">Low <i style="background:#EAEEF6"></i><i style="background:#BFBCE0"></i><i style="background:#6A66B0"></i><i style="background:#332E83"></i> High</div>
        </div>
      </div>
    </div>

    <!-- Band 3 · Department Performance -->
    <div class="band"><span class="no">3</span>Department Performance<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Duration by Department</h2><span class="hint">days</span></div>
          <div class="chart-k"><canvas id="kzDuration"></canvas></div>
          <div class="twostat" style="margin-top:9px">
            <div class="bigstat"><div class="lb">Fastest</div>
            <div style="font-weight:600;color:#1F7A45"><span id="kzFastestDuration">—</span></div></div>
            <div class="bigstat"><div class="lb">Slowest</div>
            <div style="font-weight:600;color:var(--pkw-red)"><span id="kzSlowestDuration">—</span></div></div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>Overdue Rate by Department</h2><span class="hint">%</span></div>
  <div class="chart-k chart-k-overdue"><canvas id="kzOverdue"></canvas></div>

  <div class="twostat" style="margin-top:9px;visibility:hidden;">
    <div class="bigstat">
      <div class="lb">Spacer</div>
      <div style="font-weight:600;">—</div>
    </div>
    <div class="bigstat">
      <div class="lb">Spacer</div>
      <div style="font-weight:600;">—</div>
    </div>
  </div>
</div>
      </div>
    </div>

    <!-- Band 4 · Staff Productivity -->
    <div class="band"><span class="no">4</span>Staff Productivity<span class="ln"></span></div>
    <div class="board two">
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Case per Staff</h2><span class="hint">by department</span></div>
          <div class="chart-k"><canvas id="kzCasePerStaff"></canvas></div>
          <table style="margin-top:9px">
            <thead><tr><th>Dept</th><th class="num">Staff</th><th class="num">Cases</th><th class="num">Case/Staff</th></tr></thead>
            <tbody id="kzCasePerStaffBody">
              <tr>
                <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
              Loading...
            </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col">
        <div class="sec">
          <div class="sec-title"><h2>Avg Duration per Staff</h2><span class="hint">days · by department</span></div>
          <div class="chart-k"><canvas id="kzDurPerStaff"></canvas></div>
          <table style="margin-top:9px">
            <thead><tr><th>Dept</th><th class="num">Staff</th><th class="num">Cases</th><th class="num">Avg Dur/Staff</th></tr></thead>
            <tbody id="kzDurationPerStaffBody">
              <tr>
                  <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
                  Loading...
              </td>
              </tr>
          </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Band 5 · Problem Deep-Dive -->
<div class="band"><span class="no">5</span>Problem Deep-Dive<span class="ln"></span></div>
<div class="board problem-board">
      <div class="col">
        <div class="sec">
  <div class="sec-title"><h2>List of Overdue Case</h2></div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th class="num">Cases</th>
        <th>Status</th>
        <th class="num">Duration</th>
      </tr>
    </thead>

    <tbody id="kzOverdueCasesBody">
      @forelse($kaizenOverdueCases as $case)
        @php
          $status = $case['status'] ?? '-';

          $tagClass = strtolower($status) === 'overdue'
              ? 'out'
              : 'reno';
        @endphp

        <tr>
          <td>{{ $case['department'] ?? '-' }}</td>
          <td class="num">{{ number_format($case['total_case'] ?? 0, 0) }}</td>
          <td>
            <span class="tag {{ $tagClass }}">
              {{ $status }}
            </span>
          </td>
          <td class="num">
            {{ number_format($case['duration_day'] ?? 0, 0) }}d
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No overdue case data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec">
  <div class="sec-title">

  <!-- <pre style="font-size:11px;background:#f8fafc;border:1px solid #ddd;padding:8px;max-height:160px;overflow:auto;">
{{ json_encode($kaizenRepeatIncident ?? [], JSON_PRETTY_PRINT) }}
</pre> -->

    <h2>Repeat Incident Rate</h2>
    <span class="hint">same item · ~30 days</span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Dept</th>
        <th class="num">Cases</th>
        <th>Item</th>
        <th>Area</th>
      </tr>
    </thead>

    <tbody id="kzRepeatIncidentBody">
      @forelse($kaizenRepeatIncident as $repeat)
        <tr>
          <td>{{ $repeat['department'] ?? '-' }}</td>
          <td class="num">{{ number_format($repeat['total_case'] ?? 0, 0) }}</td>
          <td>{{ $repeat['item'] ?? '-' }}</td>
          <td>{{ $repeat['area'] ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No repeat incident data
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
      </div>
      <div class="col">
        <div class="sec item-duration-sec">
  <div class="sec-title">
    <h2>Duration Solved by Item / Subitem</h2>
    <span class="hint">avg days</span>
  </div>

  <div class="chart-k item-duration-chart">
    <canvas id="kzItemDur"></canvas>
  </div>
</div>
      </div>
    </div>
  </section>


  <footer>
    <span>Data source: IFCA, PG Card, APP.Pakuwon, Isort, Parking Vendor, Valet System, Fit Out ME</span>
    <span id="lastUpdated"></span>
  </footer>
</div>

@php
  $assetInventoryJs = $assetInventory ?? [
      'total_good' => 0,
      'total_bad' => 0,
      'total_qty' => 0,
      'departments' => [],
      'items' => [],
  ];
@endphp

<script>
const BLUE='#332E83',BLUE_MID='#6A66B0',BLUE_L='#9C99C9',BLUE_XL='#C9C7E4',RED='#E3161B',AMBER='#C77A16',GREEN='#1F7A45',INK2='#6B7280',LINE='#E2E8F0';
function fmtCompact(value){
  value = Number(value || 0);

  if(value >= 1000000){
    return (value / 1000000).toFixed(2) + '<small>M</small>';
  }

  if(value >= 1000){
    return Math.round(value / 1000).toLocaleString('en-US') + '<small>K</small>';
  }

  return value.toLocaleString('en-US');
}

function fmtPercent(value){
  value = Number(value || 0);

  if(value > 0){
    return '<b class="up">▲' + value.toFixed(1) + '%</b>';
  }

  if(value < 0){
    return '<b class="dn">▼' + Math.abs(value).toFixed(1) + '%</b>';
  }

  return '<b class="fl">0.0%</b>';
}

function setHtml(id, value){
  const el = document.getElementById(id);
  if(el){
    el.innerHTML = value;
  }
}
let trafficPatternBQ = @json($trafficPattern ?? []);
let parkingSummaryBQ = @json($parkingSummary ?? []);
let valetTrendBQ = @json($valetTrend ?? []);
let kaizenSummaryBQ = @json($kaizenSummary ?? []);
let kaizenHotspotBQ = @json($kaizenHotspot ?? []);
let kaizenPeakHourBQ = @json($kaizenPeakHour ?? []);
let kaizenDurationDeptBQ = @json($kaizenDurationDept ?? []);
let kaizenOverdueDeptBQ = @json($kaizenOverdueDept ?? []);
let kaizenCasePerStaffBQ = @json($kaizenCasePerStaff ?? []);
let kaizenDurationPerStaffBQ = @json($kaizenDurationPerStaff ?? []);
let kaizenRepeatIncidentBQ = @json($kaizenRepeatIncident ?? []);
let kaizenDurationItemBQ = @json($kaizenDurationItem ?? []);
let kaizenOverdueCasesBQ = @json($kaizenOverdueCases ?? []);
let manpowerFulfillmentBQ = @json($manpowerFulfillment ?? []);
let incidentByDepartmentBQ = @json($incidentByDepartment ?? []);
let workDetailDailyBQ = @json($workDetailDaily ?? ['all' => [], 'departments' => []]);
let assetInventoryBQ = @json($assetInventoryJs);
Chart.defaults.font.family='"Segoe UI",Inter,system-ui,sans-serif';
Chart.defaults.font.size=10; Chart.defaults.color=INK2;
const M=['Jan','Feb','Mar','Apr','May','Jun','Jul'];
const charts={}; function mk(k,ctx,cfg){ if(charts[k])charts[k].destroy(); charts[k]=new Chart(ctx,cfg); }
const built={ovw:false,isort:false,kaizen:false};

function gauge(id, val, color){
  const safeVal = Math.max(0, Math.min(100, Number(val || 0)));

  mk(id, document.getElementById(id), {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [safeVal, 100 - safeVal],
        backgroundColor: [color, '#EAEEF6'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '74%',
      rotation: 0,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false }
      }
    }
  });
}

function hbar(id,labels,data,colors){ mk(id,document.getElementById(id),{type:'bar',
  data:{labels,datasets:[{data,backgroundColor:colors||BLUE,borderRadius:3,barPercentage:.7}]},
  options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
    scales:{x:{grid:{color:LINE},ticks:{font:{size:8.5}}},y:{grid:{display:false},ticks:{font:{size:9.5}}}}}}); }

function populateWdDepartmentOptions(){
  const wdSelect = document.getElementById('wdDeptSel');

  if(!wdSelect){
    return;
  }

  const currentValue = wdSelect.value || 'all';
  const departments = workDetailDailyBQ.departments || [];

  wdSelect.innerHTML = `
    <option value="all">All Departments</option>
  `;

  departments.forEach(dept => {
    const deptName = dept.department || '-';
    const deptValue = String(deptName).toLowerCase().replace(/\s+/g, '');

    const option = document.createElement('option');
    option.value = deptValue;
    option.textContent = deptName;

    wdSelect.appendChild(option);
  });

  if([...wdSelect.options].some(option => option.value === currentValue)){
    wdSelect.value = currentValue;
  } else {
    wdSelect.value = 'all';
  }
}

function wdRender(){
  const wdSelect = document.getElementById('wdDeptSel');
  const footEl = document.getElementById('wdFoot');
  const chartEl = document.getElementById('wdChart');

  if(!wdSelect || !chartEl){
    return;
  }

  const sel = wdSelect.value;

  let rows = [];

  if(sel === 'all'){
    rows = workDetailDailyBQ.all || [];
  } else {
    const dept = (workDetailDailyBQ.departments || []).find(d => {
      return String(d.department || '').toLowerCase().replace(/\s+/g,'') === sel;
    });

    rows = dept ? (dept.categories || []) : [];
  }

  rows = rows
    .map(r => ({
      category: r.category || '-',
      total: Number(r.total_activity || 0)
    }))
    .sort((a,b) => b.total - a.total);

  if(rows.length === 0){
    if(charts.wdChart){
      charts.wdChart.destroy();
      delete charts.wdChart;
    }

    if(footEl){
      footEl.innerHTML = 'No work detail data for selected period.';
    }

    return;
  }

  hbar(
    'wdChart',
    rows.map(r => r.category),
    rows.map(r => r.total)
  );

  const total = rows.reduce((s,r) => s + r.total, 0);
  const top = rows[0]?.category || '-';
  const deptLabel = wdSelect.selectedOptions[0]?.text || '-';

  if(footEl){
    footEl.innerHTML =
      'Total <b>' + total.toLocaleString('en-US') + ' entries</b> this period' +
      (sel === 'all'
        ? ' across all departments · '
        : ' · ' + deptLabel + ' · '
      ) +
      '<b>' + top + '</b> highest.';
  }
}

function renderAssetInventory(){
  const body = document.getElementById('assetBadBody');
  const sel = document.getElementById('assetDeptSel');
  const goodEl = document.getElementById('assetGoodNum');
  const badEl = document.getElementById('assetBadNum');

  if(!body || !sel){
    return;
  }

  const selected = sel.value;

  let rows = assetInventoryBQ.items || [];

  let totalGood = Number(assetInventoryBQ.total_good || 0);
  let totalBad = Number(assetInventoryBQ.total_bad || 0);

  if(selected !== 'all'){
    const dept = (assetInventoryBQ.departments || []).find(d => {
      return String(d.department_raw || '').toLowerCase().replace(/\s+/g,'') === selected;
    });

    totalGood = Number(dept?.total_good || 0);
    totalBad = Number(dept?.total_bad || 0);

    rows = rows.filter(r => {
      return String(r.department_raw || '').toLowerCase().replace(/\s+/g,'') === selected;
    });
  }

  if(goodEl){
    goodEl.textContent = totalGood.toLocaleString('en-US');
  }

  if(badEl){
    badEl.textContent = totalBad.toLocaleString('en-US');
  }

  if(rows.length === 0){
    body.innerHTML = `
      <tr>
        <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
          No bad condition asset data
        </td>
      </tr>
    `;
    return;
  }

  body.innerHTML = rows.slice(0, 10).map(row => `
    <tr>
      <td>${row.department || '-'}</td>
      <td>${row.item || '-'}</td>
      <td class="num">${Number(row.qty || 0).toLocaleString('en-US')}</td>
    </tr>
  `).join('');
}

function buildOvw(){

  if(built.ovw)return;

  built.ovw = true;

  const labels = trafficPatternBQ.map(r => r.period_label || '-');

  const carCounts = trafficPatternBQ.map(r => Number(r.car_count || 0) / 1000000);

  const motorCounts = trafficPatternBQ.map(r => Number(r.motorcycle_count || 0) / 1000000);

  const carIncome = trafficPatternBQ.map(r => Number(r.car_income || 0) / 1000000000);

  const motorIncome = trafficPatternBQ.map(r => Number(r.motorcycle_income || 0) / 1000000000);

  const totalVehicle = trafficPatternBQ.reduce((sum, r) => {
    return sum + Number(r.total_vehicle_count || 0);
  }, 0);

  const totalIncome = trafficPatternBQ.reduce((sum, r) => {
    return sum + Number(r.total_income || 0);
  }, 0);

  const avgVehicle = trafficPatternBQ.length ? totalVehicle / trafficPatternBQ.length : 0;

  const avgIncome = trafficPatternBQ.length ? totalIncome / trafficPatternBQ.length : 0;

  const avgVehicleEl = document.getElementById('ovAvgVehicle');

  const avgIncomeEl = document.getElementById('ovAvgIncome');

  if(avgVehicleEl){

    avgVehicleEl.textContent = avgVehicle >= 1000000
      ? (avgVehicle / 1000000).toFixed(2) + 'M'
      : Math.round(avgVehicle / 1000).toLocaleString('en-US') + 'K';

  }

  if(avgIncomeEl){

    avgIncomeEl.textContent = avgIncome >= 1000000000
      ? (avgIncome / 1000000000).toFixed(2) + 'B'
      : Math.round(avgIncome / 1000000).toLocaleString('en-US') + 'M';

  }

  const legOpt = {

    display:true,

    position:'bottom',

    labels:{

      boxWidth:9,

      boxHeight:9,

      font:{size:9.5},

      padding:6

    }

  };

  mk('ovCount', document.getElementById('ovCount'), {

    type:'bar',

    data:{

      labels:labels,

      datasets:[

        {

          label:'Motorcycle',

          data:motorCounts,

          backgroundColor:BLUE_XL,

          stack:'t',

          borderRadius:2

        },

        {

          label:'Car',

          data:carCounts,

          backgroundColor:BLUE,

          stack:'t',

          borderRadius:2

        }

      ]

    },

    options:{

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:legOpt,

        tooltip:{

          callbacks:{

            label:c => c.dataset.label + ': ' + c.parsed.y.toFixed(2) + 'M'

          }

        }

      },

      scales:{

        x:{

          stacked:true,

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          stacked:true,

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'M',

            font:{size:8.5}

          }

        }

      }

    }

  });

  mk('ovIncome', document.getElementById('ovIncome'), {

    type:'bar',

    data:{

      labels:labels,

      datasets:[

        {

          label:'Motorcycle',

          data:motorIncome,

          backgroundColor:BLUE_XL,

          stack:'i',

          borderRadius:2

        },

        {

          label:'Car',

          data:carIncome,

          backgroundColor:BLUE,

          stack:'i',

          borderRadius:2

        }

      ]

    },

    options:{

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:legOpt,

        tooltip:{

          callbacks:{

            label:c => c.dataset.label + ': ' + c.parsed.y.toFixed(2) + 'B'

          }

        }

      },

      scales:{

        x:{

          stacked:true,

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          stacked:true,

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'B',

            font:{size:8.5}

          }

        }

      }

    }

  });

  mk('ovPark', document.getElementById('ovPark'), {

    type:'bar',

    data:{

      labels:['Car','Motorcycle'],

      datasets:[

        {

          data:[

            Number(parkingSummaryBQ.car_count || 0),

            Number(parkingSummaryBQ.motorcycle_count || 0)

          ],

          backgroundColor:[BLUE, BLUE_MID],

          borderRadius:3,

          barPercentage:.6

        }

      ]

    },

    options:{

      indexAxis:'y',

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:{display:false},

        tooltip:{

          callbacks:{

            label:c => c.parsed.x.toLocaleString('en-US')

          }

        }

      },

      scales:{

        x:{

          grid:{color:LINE},

          ticks:{

            font:{size:8.5},

            callback:v => Number(v).toLocaleString('en-US')

          }

        },

        y:{

          grid:{display:false}

        }

      }

    }

  });

  const valetLabels = valetTrendBQ.map(r => r.period_label || '-');

  const valetIncome = valetTrendBQ.map(r => Number(r.valet_income || 0) / 1000000);

  mk('ovValet', document.getElementById('ovValet'), {

    type:'line',

    data:{

      labels:valetLabels,

      datasets:[

        {

          data:valetIncome,

          borderColor:BLUE,

          borderWidth:2,

          pointRadius:2,

          tension:.3,

          fill:true,

          backgroundColor:'rgba(51,46,131,.06)'

        }

      ]

    },

    options:{

      responsive:true,

      maintainAspectRatio:false,

      plugins:{

        legend:{display:false},

        tooltip:{

          callbacks:{

            label:c => Number(c.parsed.y || 0).toLocaleString('en-US') + 'M IDR'

          }

        }

      },

      scales:{

        x:{

          grid:{display:false},

          ticks:{font:{size:8.5}}

        },

        y:{

          grid:{color:LINE},

          ticks:{

            callback:v => v + 'M',

            font:{size:8.5}

          }

        }

      }

    }

  });

  wdRender();
  renderAssetInventory();

}

const assetDeptSelect = document.getElementById('assetDeptSel');

if(assetDeptSelect){
  assetDeptSelect.addEventListener('change', renderAssetInventory);
}


function buildIsort(){
  if(built.isort)return;
  built.isort = true;

  renderMp('inhouse');

  const incCanvas = document.getElementById('incDept');

  if(incCanvas){
    const incRows = incidentByDepartmentBQ.departments || [];
    const incLabels = incRows.map(r => r.department || '-');
    const incValues = incRows.map(r => Number(r.total_case || 0));

    mk('incDept', incCanvas, {
      type:'bar',
      data:{
        labels:incLabels,
        datasets:[
          {
            data:incValues,
            backgroundColor:BLUE,
            borderRadius:3,
            barPercentage:.65
          }
        ]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
          legend:{display:false},
          tooltip:{
            callbacks:{
              label:c => Number(c.parsed.y || 0).toLocaleString('en-US') + ' incidents'
            }
          }
        },
        scales:{
          x:{
            grid:{display:false},
            ticks:{font:{size:9}}
          },
          y:{
            grid:{color:LINE},
            ticks:{font:{size:8.5}}
          }
        }
      }
    });
  }
}

function setMpLowestBox(label, val, mode){
  const el = document.getElementById('mpLowest');

  if(!el){
    return;
  }

  if(mode === 'inhouse'){
    el.innerHTML = 'Inhouse tidak memakai target fulfillment';
    return;
  }

  el.innerHTML =
    label + ' · <b style="color:var(--pkw-red)">' +
    Number(val || 0).toFixed(0) +
    '%</b>';
}

function renderMp(mode){
  const chartWrap = document.getElementById('mpChartWrap');
  const groupsWrap = document.getElementById('mpGroupsWrap');
  const overallEl = document.getElementById('mpOverall');
  const overallLabelEl = document.getElementById('mpOverallLabel');
  const labelEl = document.getElementById('mpByLabel');

  const data = manpowerFulfillmentBQ[mode] || {};
  const rows = data.departments || [];

  if(chartWrap){
    chartWrap.style.display = '';
  }

  if(groupsWrap){
    groupsWrap.style.display = 'none';
  }

  if(mode === 'inhouse'){
    const total = Number(data.overall || data.total_actual || 0);

    gauge('mpGauge', 100, BLUE);

    if(overallEl){
      overallEl.textContent = total.toLocaleString('en-US');
    }

    if(overallLabelEl){
      overallLabelEl.textContent = 'Inhouse';
    }

    setMpLowestBox('-', null, 'inhouse');

    if(labelEl){
      labelEl.textContent = 'By Department';
    }

    hbar(
      'mpDept',
      rows.map(r => r.department_name || r.department || '-'),
      rows.map(r => Number(r.actual || r.total_actual || 0)),
      BLUE
    );

    return;
  }

  const overall = Number(data.overall || 0);
  const lowestLabel = data.lowest_department || '-';
  const lowestVal = Number(data.lowest_percentage || 0);

  gauge('mpGauge', overall, overall >= 100 ? BLUE : RED);

  if(overallEl){
    overallEl.textContent = overall.toFixed(0) + '%';
  }

  if(overallLabelEl){
    overallLabelEl.textContent = 'Overall';
  }

  setMpLowestBox(lowestLabel, lowestVal, 'outsource');

  if(labelEl){
    labelEl.textContent = 'By Department';
  }

  hbar(
    'mpDept',
    rows.map(r => r.department_name || r.department || '-'),
    rows.map(r => Number(r.percentage || 0)),
    rows.map(r => Number(r.percentage || 0) < 100 ? RED : BLUE)
  );
}

function loadKaizenMain(){
  console.log('Loading kaizen main AJAX:', kaizenMainUrl);

  fetchOmData(kaizenMainUrl)
    .then(data => {
      console.log('Kaizen Main AJAX result:', data);

      kaizenSummaryBQ = data.kaizenSummary || {};
      kaizenHotspotBQ = data.kaizenHotspot || [];
      kaizenPeakHourBQ = data.kaizenPeakHour || [];
      kaizenDurationDeptBQ = data.kaizenDurationDept || [];
      kaizenOverdueDeptBQ = data.kaizenOverdueDept || [];
      kaizenCasePerStaffBQ = data.kaizenCasePerStaff || [];
      kaizenDurationPerStaffBQ = data.kaizenDurationPerStaff || [];
      kaizenRepeatIncidentBQ = data.kaizenRepeatIncident || [];
      kaizenDurationItemBQ = data.kaizenDurationItem || [];
      kaizenOverdueCasesBQ = data.kaizenOverdueCases || [];

      built.kaizen = false;
      buildKaizen();
    })
    .catch(error => {
      console.error('Kaizen Main AJAX error:', error);
    });
}

function buildKaizen(){
  if(built.kaizen)return;
  built.kaizen = true;

  const kzSummary = kaizenSummaryBQ || {};

setHtml('kzOpenCaseVal', Number(kzSummary.total_open || 0).toLocaleString('en-US'));
setHtml('kzOpenRateVal', Number(kzSummary.open_rate || 0).toFixed(1) + '%');

setHtml('kzCloseCaseVal', Number(kzSummary.total_closed || 0).toLocaleString('en-US'));
setHtml('kzCloseRateVal', Number(kzSummary.closed_rate || 0).toFixed(1) + '%');

setHtml('kzOverdueCaseVal', Number(kzSummary.total_overdue || 0).toLocaleString('en-US'));
setHtml('kzOverdueRateVal', Number(kzSummary.overdue_rate || 0).toFixed(1) + '%');

setHtml('kzAvgDurationVal', Number(kzSummary.avg_duration_day || 0).toFixed(1));

const overdueBody = document.getElementById('kzOverdueCasesBody');

if(overdueBody){
  if((kaizenOverdueCasesBQ || []).length === 0){
    overdueBody.innerHTML = `
      <tr>
        <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
          No overdue case data
        </td>
      </tr>
    `;
  } else {
    overdueBody.innerHTML = kaizenOverdueCasesBQ.map(row => {
      const status = row.status || '-';
      const tagClass = String(status).toLowerCase() === 'overdue' ? 'out' : 'reno';

      return `
        <tr>
          <td>${row.department || '-'}</td>
          <td class="num">${Number(row.total_case || 0).toLocaleString('en-US')}</td>
          <td>
            <span class="tag ${tagClass}">
              ${status}
            </span>
          </td>
          <td class="num">${Number(row.duration_day || 0).toLocaleString('en-US')}d</td>
        </tr>
      `;
    }).join('');
  }
}

const repeatBody = document.getElementById('kzRepeatIncidentBody');

if(repeatBody){
  if((kaizenRepeatIncidentBQ || []).length === 0){
    repeatBody.innerHTML = `
      <tr>
        <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
          No repeat incident data
        </td>
      </tr>
    `;
  } else {
    repeatBody.innerHTML = kaizenRepeatIncidentBQ.map(row => `
      <tr>
        <td>${row.department || '-'}</td>
        <td class="num">${Number(row.total_case || 0).toLocaleString('en-US')}</td>
        <td>${row.item || '-'}</td>
        <td>${row.area || '-'}</td>
      </tr>
    `).join('');
  }
}

  const hotspotLabels = kaizenHotspotBQ.map(r => r.location_name || '-');
  const hotspotValues = kaizenHotspotBQ.map(r => Number(r.total || 0));

  hbar('kzHotspot', hotspotLabels, hotspotValues);

  // duration by dept
  const durationLabels = kaizenDurationDeptBQ.map(r => r.department || '-');
  const durationValues = kaizenDurationDeptBQ.map(r => Number(r.avg_duration_day || 0));

  const fastestDuration = kaizenDurationDeptBQ.length ? kaizenDurationDeptBQ[0] : null;
  const slowestDuration = kaizenDurationDeptBQ.length ? kaizenDurationDeptBQ[kaizenDurationDeptBQ.length - 1] : null;

  const durationColors = durationValues.map((v, i) => {
    if(i === 0) return '#1F7A45';
    if(i === durationValues.length - 1) return RED;
    return BLUE;
  });

  mk('kzDuration', document.getElementById('kzDuration'), {
    type:'bar',
    data:{
      labels:durationLabels,
      datasets:[
        {
          data:durationValues,
          backgroundColor:durationColors,
          borderRadius:3,
          barPercentage:.65
        }
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{
          callbacks:{
            label:c => Number(c.parsed.y || 0).toFixed(1) + ' days'
          }
        }
      },
      scales:{
        x:{
          grid:{display:false},
          ticks:{font:{size:9}}
        },
        y:{
          grid:{color:LINE},
          ticks:{
            callback:v => v + 'd',
            font:{size:8.5}
          }
        }
      }
    }
  });

  const fastestEl = document.getElementById('kzFastestDuration');
  const slowestEl = document.getElementById('kzSlowestDuration');

  if(fastestEl){
    fastestEl.textContent = fastestDuration
      ? `${fastestDuration.department || '-'} · ${Number(fastestDuration.avg_duration_day || 0).toFixed(1)}d`
      : '—';
  }

  if(slowestEl){
    slowestEl.textContent = slowestDuration
      ? `${slowestDuration.department || '-'} · ${Number(slowestDuration.avg_duration_day || 0).toFixed(1)}d`
      : '—';
  }

  // overdue rate by dept
  const overdueLabels = kaizenOverdueDeptBQ.map(r => r.department || '-');
  const overdueValues = kaizenOverdueDeptBQ.map(r => Number(r.overdue_rate || 0));

  mk('kzOverdue', document.getElementById('kzOverdue'), {
    type:'bar',
    data:{
      labels:overdueLabels,
      datasets:[
        {
          data:overdueValues,
          backgroundColor:overdueValues.map(v => v >= 5 ? RED : BLUE),
          borderRadius:3,
          barPercentage:.65
        }
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{
          callbacks:{
            label:c => Number(c.parsed.y || 0).toFixed(1) + '%'
          }
        }
      },
      scales:{
        x:{
          grid:{display:false},
          ticks:{font:{size:9}}
        },
        y:{
          grid:{color:LINE},
          ticks:{
            callback:v => v + '%',
            font:{size:8.5}
          }
        }
      }
    }
  });

  // case per staff
const caseStaffLabels = kaizenCasePerStaffBQ.map(r => r.department || '-');
const caseStaffValues = kaizenCasePerStaffBQ.map(r => Number(r.case_per_staff || 0));

mk('kzCasePerStaff', document.getElementById('kzCasePerStaff'), {
  type:'bar',
  data:{
    labels:caseStaffLabels,
    datasets:[
      {
        data:caseStaffValues,
        backgroundColor:BLUE,
        borderRadius:3,
        barPercentage:.62
      }
    ]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
      legend:{display:false},
      tooltip:{
        callbacks:{
          label:c => Number(c.parsed.y || 0).toFixed(1) + ' cases / staff'
        }
      }
    },
    scales:{
      x:{
        grid:{display:false},
        ticks:{font:{size:9}}
      },
      y:{
        grid:{color:LINE},
        ticks:{font:{size:8.5}}
      }
    }
  }
});

const caseStaffBody = document.getElementById('kzCasePerStaffBody');

if(caseStaffBody){
  caseStaffBody.innerHTML = kaizenCasePerStaffBQ.length
    ? kaizenCasePerStaffBQ.map(r => `
        <tr>
          <td>${r.department || '-'}</td>
          <td class="num">${Number(r.total_staff || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.total_case || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.case_per_staff || 0).toFixed(1)}</td>
        </tr>
      `).join('')
    : `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No case per staff data
          </td>
        </tr>
      `;
}

  // duration per staff
const durStaffLabels = kaizenDurationPerStaffBQ.map(r => r.department || '-');
const durStaffValues = kaizenDurationPerStaffBQ.map(r => Number(r.duration_per_staff_day || 0));

mk('kzDurPerStaff', document.getElementById('kzDurPerStaff'), {
  type:'bar',
  data:{
    labels:durStaffLabels,
    datasets:[
      {
        data:durStaffValues,
        backgroundColor:BLUE_MID,
        borderRadius:3,
        barPercentage:.62
      }
    ]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
      legend:{display:false},
      tooltip:{
        callbacks:{
          label:c => Number(c.parsed.y || 0).toFixed(1) + ' days / staff'
        }
      }
    },
    scales:{
      x:{
        grid:{display:false},
        ticks:{font:{size:9}}
      },
      y:{
        grid:{color:LINE},
        ticks:{
          callback:v => v + 'd',
          font:{size:8.5}
        }
      }
    }
  }
});

const durStaffBody = document.getElementById('kzDurationPerStaffBody');

if(durStaffBody){
  durStaffBody.innerHTML = kaizenDurationPerStaffBQ.length
    ? kaizenDurationPerStaffBQ.map(r => `
        <tr>
          <td>${r.department || '-'}</td>
          <td class="num">${Number(r.total_staff || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.total_case || 0).toLocaleString('en-US')}</td>
          <td class="num">${Number(r.duration_per_staff_day || 0).toFixed(1)}d</td>
        </tr>
      `).join('')
    : `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No duration per staff data
          </td>
        </tr>
      `;
}

  // duration solved by item / subitem
const itemDurationLabels = kaizenDurationItemBQ.map(r => r.item || '-');
const itemDurationValues = kaizenDurationItemBQ.map(r => Number(r.avg_duration_day || 0));

hbar('kzItemDur', itemDurationLabels, itemDurationValues);

  buildHeat();
}

function buildHeat(){
  const hm = document.getElementById('kzHeat');
  if(!hm || hm.dataset.done) return;

  hm.dataset.done = '1';

  const hours = [8,10,12,14,16,18,20,22];

  const days = [
    { no: 1, label: 'Mon' },
    { no: 2, label: 'Tue' },
    { no: 3, label: 'Wed' },
    { no: 4, label: 'Thu' },
    { no: 5, label: 'Fri' },
    { no: 6, label: 'Sat' },
    { no: 7, label: 'Sun' },
  ];

  const maxValue = Math.max(
    1,
    ...kaizenPeakHourBQ.map(r => Number(r.total_case || 0))
  );

  const caseMap = {};

  kaizenPeakHourBQ.forEach(r => {
    const key = Number(r.day_no || 0) + '-' + Number(r.hour_of_day || 0);
    caseMap[key] = Number(r.total_case || 0);
  });

  const ramp = ['#EAEEF6','#D2CFE8','#BFBCE0','#8F8BC4','#6A66B0','#332E83'];

  hm.style.gridTemplateColumns = '30px repeat(' + hours.length + ', 1fr)';

  hm.appendChild(document.createElement('div'));

  hours.forEach(h => {
    const c = document.createElement('div');
    c.className = 'col';
    c.textContent = h;
    hm.appendChild(c);
  });

  days.forEach(day => {
    const l = document.createElement('div');
    l.className = 'lab';
    l.textContent = day.label;
    hm.appendChild(l);

    hours.forEach(h => {
      const value = caseMap[day.no + '-' + h] || 0;

      const level = value <= 0
        ? 0
        : Math.min(5, Math.ceil((value / maxValue) * 5));

      const cell = document.createElement('div');
      cell.className = 'cell';
      cell.style.background = ramp[level];
      cell.title = day.label + ' ' + h + ':00 · ' + value.toLocaleString('en-US') + ' cases';

      hm.appendChild(cell);
    });
  });
}

// Dates change without reloading the page; obsolete responses cannot update it.
const omResponses = new Map();
let omDates = { start: @json($startDate), end: @json($endDate) };
let omGeneration = 0;
let omPending = 0;
let omFailed = false;
function updateOmLoading(){
}
function fetchOmData(url){
  const target = new URL(url, window.location.href);
  target.searchParams.set('start_date', omDates.start);
  target.searchParams.set('end_date', omDates.end);
  url = target.href;
  const generation = omGeneration;
  let entry = omResponses.get(url);
  if(!entry || (!entry.pending && entry.expiresAt <= Date.now())){
    entry = { pending: true, expiresAt: 0, controller: new AbortController() };
    omPending++;
    updateOmLoading();
    entry.promise = fetch(url, { signal: entry.controller.signal })
      .then(response => {
        if(!response.ok) throw new Error('OM request failed: ' + response.status);
        return response.json();
      })
      .then(data => {
        entry.expiresAt = Date.now() + 60000;
        return data;
      })
      .catch(error => {
        if(omResponses.get(url) === entry) omResponses.delete(url);
        if(generation === omGeneration && error.name !== 'AbortError') omFailed = true;
        throw error;
      })
      .finally(() => {
        entry.pending = false;
        if(generation === omGeneration){
          omPending--;
          updateOmLoading();
        }
      });
    omResponses.set(url, entry);
  }
  return entry.promise.then(data => {
    if(generation !== omGeneration) throw new DOMException('Date range changed', 'AbortError');
    return data;
  });
}
const activeTabFromLaravel = @json($activeTab ?? 'overview');

const overviewTrafficUrl =
  "{{ route('datahub.om.overview-traffic', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const overviewChartUrl =
  "{{ route('datahub.om.overview-chart', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}"
  + "&traffic_granularity={{ $trafficGranularity ?? 'monthly' }}";

const overviewIssueUrl =
  "{{ route('datahub.om.overview-issue', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

let tenantMovementType = "{{ $tenantMovementType ?? 'all' }}";

function getOverviewDetailUrl(){
  return "{{ route('datahub.om.overview-detail', $mall) }}"
    + "?start_date={{ $startDate }}"
    + "&end_date={{ $endDate }}"
    + "&tenant_movement_type=" + encodeURIComponent(tenantMovementType);
}

const overviewBudgetUrl =
  "{{ route('datahub.om.overview-budget', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const overviewWorkDetailUrl =
  "{{ route('datahub.om.overview-work-detail', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentAllUrl =
  "{{ route('datahub.om.department-all', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentEquipmentUrl =
  "{{ route('datahub.om.department-equipment', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const departmentInventoryUrl =
  "{{ route('datahub.om.department-inventory', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";

const kaizenMainUrl =
  "{{ route('datahub.om.kaizen-main', $mall) }}"
  + "?start_date={{ $startDate }}"
  + "&end_date={{ $endDate }}";  
  
let overviewDetailLoaded = false;  

function loadOverviewMain(){
  console.log('Loading overview traffic AJAX:', overviewTrafficUrl);
  console.log('Loading overview chart AJAX:', overviewChartUrl);
  console.log('Loading overview detail AJAX:', getOverviewDetailUrl());

  function renderOverviewTraffic(data){
  const s = data.trafficSummary || {};

  setHtml('headCountVal', fmtCompact(s.head_count));
  setHtml('headLwVal', fmtPercent(s.head_lw_percent));
  setHtml('headLmVal', fmtPercent(s.head_lm_percent));
  setHtml('headLyVal', fmtPercent(s.head_ly_percent));
  setHtml('headPeakVal', s.head_peak_hour || '-');

  setHtml('carCountVal', fmtCompact(s.car_count));
  setHtml('carLwVal', fmtPercent(s.car_lw_percent));
  setHtml('carLmVal', fmtPercent(s.car_lm_percent));
  setHtml('carLyVal', fmtPercent(s.car_ly_percent));
  setHtml('carPeakVal', s.parking_peak_hour || '-');

  setHtml('motorCountVal', fmtCompact(s.motor_count));
  setHtml('motorLwVal', fmtPercent(s.motor_lw_percent));
  setHtml('motorLmVal', fmtPercent(s.motor_lm_percent));
  setHtml('motorLyVal', fmtPercent(s.motor_ly_percent));
  setHtml('motorPeakVal', s.parking_peak_hour || '-');

  setHtml('blueBirdVal', fmtCompact(s.blue_bird));
  setHtml('blueBirdLwVal', fmtPercent(s.blue_bird_lw_percent));
  setHtml('blueBirdLmVal', fmtPercent(s.blue_bird_lm_percent));
  setHtml('blueBirdLyVal', fmtPercent(s.blue_bird_ly_percent));
  setHtml('blueBirdPeakVal', (s.blue_bird_peak_hour || '-') + ' WIB');

  setHtml('dropOffVal', fmtCompact(s.drop_off_count));
  setHtml('dropOffLwVal', fmtPercent(s.drop_off_lw_percent));
  setHtml('dropOffLmVal', fmtPercent(s.drop_off_lm_percent));
  setHtml('dropOffLyVal', fmtPercent(s.drop_off_ly_percent));
  setHtml('dropOffPeakVal', (s.drop_off_peak_hour || '-') + ' WIB');
}

  function renderOverviewChartInfo(data){
  const p = data.parkingSummary || {};
  const v = data.valetSummary || {};

  const avgParkingHour = Number(p.avg_parking_duration_minutes || 0) / 60;

  setHtml('parkingAvgCarVal', avgParkingHour.toFixed(1) + ' hr');
  setHtml('parkingAvgMotorVal', avgParkingHour.toFixed(1) + ' hr');
  setHtml('parkingPeakCarVal', p.peak_parking_hour || '-');
  setHtml('parkingPeakMotorVal', p.peak_parking_hour || '-');

  setHtml('valetServedVal', Number(v.valet_served || 0).toLocaleString('en-US'));
  setHtml('valetAvgWaitVal', Number(v.valet_avg_wait_minutes || 0).toFixed(1));
  setHtml('valetIncomeVal', Math.round(Number(v.valet_income || 0) / 1000000).toLocaleString('en-US'));
}

  fetchOmData(overviewTrafficUrl)
  .then(data => {
    console.log('Overview Traffic AJAX result:', data);

    renderOverviewTraffic(data);
  })
  .catch(error => {
    console.error('Overview Traffic AJAX error:', error);
  });

  fetchOmData(overviewChartUrl)
  .then(data => {
    console.log('Overview Chart AJAX result:', data);

    renderOverviewChartInfo(data);

    trafficPatternBQ = data.trafficPattern || [];
    parkingSummaryBQ = data.parkingSummary || [];
    valetTrendBQ = data.valetTrend || [];

    built.ovw = false;
    buildOvw();

    // setTimeout(prefetchDepartmentMain, 500);
  })
  .catch(error => {
    console.error('Overview Chart AJAX error:', error);
  });


}

if(activeTabFromLaravel === 'overview'){
  loadOverviewMain();
}

function renderOverviewIssue(data){
  const s = data.issueSummary || {};
  const risks = data.issueRiskMonitor || [];

  setHtml('issueTotalVal', Number(s.total_issue || 0).toLocaleString('en-US'));
  setHtml('issueOpenVal', Number(s.open_issue || 0).toLocaleString('en-US'));
  setHtml('issueProgressVal', Number(s.in_progress_issue || 0).toLocaleString('en-US'));
  setHtml('issueClosedVal', Number(s.closed_issue || 0).toLocaleString('en-US'));
  setHtml('issueOldestVal', Number(s.oldest_issue_days || 0).toLocaleString('en-US') + ' days');

  setHtml('issueTopDeptVal', s.top_issue_department || '-');
  setHtml('issueTopAreaVal', s.top_issue_area || '-');
  setHtml('issueTopItemVal', s.top_issue_item || '-');

  const body = document.getElementById('issueRiskBody');

  if(!body){
    return;
  }

  if(risks.length === 0){
    body.innerHTML = `
      <tr>
        <td colspan="6" style="text-align:center;color:var(--ink-3);padding:12px;">
          No issue data
        </td>
      </tr>
    `;
    return;
  }

  body.innerHTML = risks.map(row => {
    const days = Number(row.days || 0);
    let dayClass = '';

    if(days >= 14){
      dayClass = 'hot';
    } else if(days >= 7){
      dayClass = 'warm';
    }

    return `
      <tr>
        <td>${row.area || '-'}</td>
        <td>${row.location || '-'}</td>
        <td>${row.issue || '-'}</td>
        <td>${row.department || '-'}</td>
        <td>${row.status || '-'}</td>
        <td class="num">
          <span class="days-pill ${dayClass}">
            ${days.toLocaleString('en-US')}
          </span>
        </td>
      </tr>
    `;
  }).join('');
}

function renderOverviewTenancy(data){
  const fitOutList = data.fitOutList || [];
  const tenantMovement = data.tenantMovement || [];
  const eventPipeline = data.eventPipeline || [];

  const fitOutBody = document.getElementById('fitOutBody');
  const tenantMovementBody = document.getElementById('tenantMovementBody');
  const eventPipelineBody = document.getElementById('eventPipelineBody');

  if(fitOutBody){
    if(fitOutList.length === 0){
      fitOutBody.innerHTML = `
        <tr>
          <td colspan="2" style="text-align:center;color:var(--ink-3);padding:12px;">
            No fit out data
          </td>
        </tr>
      `;
    } else {
      fitOutBody.innerHTML = fitOutList.map(row => `
        <tr>
          <td>${row.tenant || '-'}</td>
          <td>${row.location || '-'}</td>
        </tr>
      `).join('');
    }
  }

  if(tenantMovementBody){
    if(tenantMovement.length === 0){
      tenantMovementBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant movement data
          </td>
        </tr>
      `;
    } else {
      tenantMovementBody.innerHTML = tenantMovement.map(row => {
        const type = row.type || '-';
        const lowerType = String(type).toLowerCase();

        let typeClass = 'reno';

        if(lowerType === 'new'){
          typeClass = 'new';
        } else if(lowerType === 'out'){
          typeClass = 'out';
        }

        return `
          <tr>
            <td>${row.tenant || '-'}</td>
            <td>${row.category || '-'}</td>
            <td>
              <span class="tag ${typeClass}">
                ${type}
              </span>
            </td>
          </tr>
        `;
      }).join('');
    }
  }

  if(eventPipelineBody){
    if(eventPipeline.length === 0){
      eventPipelineBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No event pipeline data
          </td>
        </tr>
      `;
    } else {
      eventPipelineBody.innerHTML = eventPipeline.map(row => `
        <tr>
          <td>${row.location || '-'}</td>
          <td>${row.event || '-'}</td>
          <td>${row.period || '-'}</td>
        </tr>
      `).join('');
    }
  }
}

function renderOverviewBudget(data){
  const rows = data.budgetSpending || [];
  const body = document.getElementById('budgetDeptBody');

  if(!body){
    return;
  }

  if(rows.length === 0){
    body.innerHTML = `
      <div style="font-size:12px;color:var(--ink-3);padding:12px 0;">
        No budget spending data
      </div>
    `;
    return;
  }

  body.innerHTML = rows.map((dept, index) => {
    const budget = Number(dept.total_budget || 0);
    const budgetedSpending = Number(dept.budgeted_spending || 0);
    const unbudgetedSpending = Number(dept.unbudgeted_spending || 0);
    const usagePercent = Number(dept.usage_percent || 0);

    const budgetedWidth = budget > 0
      ? Math.min(100, Math.max(0, (budgetedSpending / budget) * 100))
      : 0;

    const unbudgetedWidth = budget > 0
      ? Math.min(100 - budgetedWidth, Math.max(0, (unbudgetedSpending / budget) * 100))
      : 0;

    const items = dept.items || [];

    const itemRows = items.length
      ? items.map(item => {
          const isUnbudgeted = !!item.is_unbudgeted;
          const itemBudget = Number(item.budget || 0);
          const itemSpending = Number(item.spending || 0);
          const itemUnbudgeted = Number(item.unbudgeted || 0);

          return `
            <tr>
              <td>
                ${item.item || '-'}
                ${isUnbudgeted ? '<span class="tag unb">Unbudgeted</span>' : ''}
              </td>

              <td class="num">
                ${isUnbudgeted ? '—' : Math.round(itemBudget / 1000000).toLocaleString('en-US') + 'M'}
              </td>

              <td class="num">
                ${isUnbudgeted ? '—' : Math.round(itemSpending / 1000000).toLocaleString('en-US') + 'M'}
              </td>

              <td class="num">
                ${isUnbudgeted ? Math.round(itemUnbudgeted / 1000000).toLocaleString('en-US') + 'M' : '—'}
              </td>

              <td class="num">
                ${item.usage_percent === null || item.usage_percent === undefined
                  ? '—'
                  : Number(item.usage_percent || 0).toFixed(0) + '%'}
              </td>
            </tr>
          `;
        }).join('')
      : `
          <tr>
            <td colspan="5" style="text-align:center;color:var(--ink-3);padding:12px;">
              No budget item data
            </td>
          </tr>
        `;

    return `
      <details ${index === 0 ? 'open' : ''}>
        <summary>
          <div class="pbar-stack">
            <div class="bsum-head">
              <span class="pl">${dept.department || '-'}</span>

              <span class="bsum-nums">
                <span>Budget <b>${Math.round(budget / 1000000).toLocaleString('en-US')}M</b></span>
                <span>Spending <b>${Math.round(budgetedSpending / 1000000).toLocaleString('en-US')}M</b></span>
                <span class="u">Unbudgeted <b>${Math.round(unbudgetedSpending / 1000000).toLocaleString('en-US')}M</b></span>
              </span>

              <span class="pv">
                ${usagePercent.toFixed(0)}%
              </span>

              <span class="chev">›</span>
            </div>

            <div class="track">
              <div class="seg-b" style="width:${budgetedWidth}%"></div>
              <div class="seg-u" style="width:${unbudgetedWidth}%"></div>
            </div>
          </div>
        </summary>

        <div class="budget-detail">
          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th class="num">Budget</th>
                <th class="num">Spending</th>
                <th class="num">Unbudgeted</th>
                <th class="num">Usage %</th>
              </tr>
            </thead>

            <tbody>
              ${itemRows}
            </tbody>
          </table>
        </div>
      </details>
    `;
  }).join('');
}

function loadOverviewDetail(){
  if(overviewDetailLoaded){
    return;
  }

  overviewDetailLoaded = true;

  const detailUrl = getOverviewDetailUrl();

  // The PHP development server handles requests in queue order. Show the
  // visible Issues & Risk and Tenancy sections before the lower widgets.
  fetchOmData(overviewIssueUrl)
    .then(data => {
      console.log('Overview Issue AJAX result:', data);
      renderOverviewIssue(data);
    })
    .catch(error => {
      console.error('Overview Issue AJAX error:', error);
    });

  fetchOmData(detailUrl)
    .then(data => {
      console.log('Overview Detail AJAX result:', data);
      renderOverviewTenancy(data);
    })
    .catch(error => {
      console.error('Overview Detail AJAX error:', error);
    });

  fetchOmData(overviewBudgetUrl)
    .then(data => {
      console.log('Overview Budget AJAX result:', data);
      renderOverviewBudget(data);
    })
    .catch(error => {
      console.error('Overview Budget AJAX error:', error);
    });

  fetchOmData(overviewWorkDetailUrl)
    .then(data => {
      console.log('Overview Work Detail AJAX result:', data);
      workDetailDailyBQ = data.workDetailDaily || { all: [], departments: [] };
      populateWdDepartmentOptions();
      wdRender();
    })
    .catch(error => {
      console.error('Overview Work Detail AJAX error:', error);
    });
}

const tenantMovementSeg = document.getElementById('tenantMovementSeg');

if(tenantMovementSeg){
  tenantMovementSeg.addEventListener('click', function(e){
    const btn = e.target.closest('button[name="tenant_movement_type"]');

    if(!btn){
      return;
    }

    e.preventDefault();

    tenantMovementSeg.querySelectorAll('button').forEach(button => {
      button.classList.toggle('on', button === btn);
    });

    tenantMovementType = btn.value || 'all';

    const detailUrl = getOverviewDetailUrl();

    console.log('Reload tenant movement AJAX:', detailUrl);

    fetchOmData(detailUrl)
      .then(data => {
        console.log('Tenant Movement AJAX result:', data);

        renderOverviewTenancy(data);
      })
      .catch(error => {
        console.error('Tenant Movement AJAX error:', error);
      });
  });
}

if(activeTabFromLaravel === 'overview'){
  // Start the lower Overview requests immediately instead of waiting until the
  // user scrolls to the trigger. They are independent from the traffic charts.
  loadOverviewDetail();
}

function populateAssetDepartmentOptions(){
  const sel = document.getElementById('assetDeptSel');

  if(!sel){
    return;
  }

  const current = sel.value || 'all';
  const departments = assetInventoryBQ.departments || [];

  sel.innerHTML = '<option value="all">All Departments</option>';

  departments.forEach(dept => {
    const label = dept.department || '-';
    const raw = dept.department_raw || label;
    const value = String(raw).toLowerCase().replace(/\s+/g, '');

    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;

    sel.appendChild(option);
  });

  if([...sel.options].some(option => option.value === current)){
    sel.value = current;
  } else {
    sel.value = 'all';
  }
}

function renderDepartmentInventory(data){
  const consumables = data.consumableUsage || [];
  const tenants = data.tenantUpdate || [];

  const consumableBody = document.getElementById('consumableBody');

  if(consumableBody){
    if(consumables.length === 0){
      consumableBody.innerHTML = `
        <div style="font-size:12px;color:var(--ink-3);padding:12px;">
          No consumable usage data
        </div>
      `;
    } else {
      consumableBody.innerHTML = consumables.map(item => `
        <div class="cons-card">
          <div class="cons-name">${item.name || '-'}</div>
          <div class="cons-val">
            ${Number(item.total || 0).toLocaleString('en-US', {maximumFractionDigits:1})}
            <small>${item.uom || ''}</small>
          </div>
        </div>
      `).join('');
    }
  }

  assetInventoryBQ = data.assetInventory || {
    total_good: 0,
    total_bad: 0,
    total_qty: 0,
    departments: [],
    items: []
  };

  populateAssetDepartmentOptions();
  renderAssetInventory();

  const tenantBody = document.getElementById('tenantUpdateBody');

  if(tenantBody){
    if(tenants.length === 0){
      tenantBody.innerHTML = `
        <tr>
          <td colspan="4" style="text-align:center;color:var(--ink-3);padding:12px;">
            No tenant update data
          </td>
        </tr>
      `;
    } else {
      tenantBody.innerHTML = tenants.map(row => {
        const status = String(row.status || '-').toUpperCase().trim();

        let statusClass = 'new';

        if(status.includes('OPEN')){
          statusClass = 'new';
        } else if(status.includes('OPERATING')){
          statusClass = 'ok';
        } else if(status.includes('CLOSED')){
          statusClass = 'out';
        } else if(status.includes('WAITING') || status.includes('FIT OUT')){
          statusClass = 'reno';
        }

        return `
          <tr>
            <td>${row.tenant || '-'}</td>
            <td>${row.unit || '-'}</td>
            <td>${row.type || '-'}</td>
            <td>
              <span class="tag ${statusClass}">
                ${row.status || '-'}
              </span>
            </td>
          </tr>
        `;
      }).join('');
    }
  }
}

function loadDepartmentInventory(){
  console.log('Loading department inventory AJAX:', departmentInventoryUrl);

  fetchOmData(departmentInventoryUrl)
    .then(data => {
      console.log('Department Inventory AJAX result:', data);

      renderDepartmentInventory(data);
    })
    .catch(error => {
      console.error('Department Inventory AJAX error:', error);
    });
}

function renderDepartmentEquipment(data){
  const equipmentStatus = data.equipmentStatus || {};
  const equipmentOpenClose = data.equipmentOpenClose || {};

  const statusItems = equipmentStatus.items || [];
  const openCloseItems = equipmentOpenClose.items || [];

  setHtml(
    'equipmentBelowVal',
    Number(equipmentStatus.below_normal || 0).toLocaleString('en-US') + ' below normal'
  );

  setHtml(
    'equipmentMetaVal',
    'ditampilkan · ' +
    Number(equipmentStatus.total_monitored || 0).toLocaleString('en-US') +
    ' unit & parameter dipantau'
  );

  setHtml('equipmentOnVal', Number(equipmentOpenClose.total_on || 0).toLocaleString('en-US'));
  setHtml('equipmentOffVal', Number(equipmentOpenClose.total_off || 0).toLocaleString('en-US'));

  const statusBody = document.getElementById('equipmentStatusBody');

  if(statusBody){
    if(statusItems.length === 0){
      statusBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No below normal equipment data
          </td>
        </tr>
      `;
    } else {
      statusBody.innerHTML = statusItems.map(item => `
        <tr>
          <td>${item.department || '-'}</td>
          <td>${item.item_parameter || '-'}</td>
          <td>
            <span class="tag out">
              ${item.status || 'Below Normal'}
            </span>
          </td>
        </tr>
      `).join('');
    }
  }

  const openCloseBody = document.getElementById('equipmentOpenCloseBody');

  if(openCloseBody){
    if(openCloseItems.length === 0){
      openCloseBody.innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center;color:var(--ink-3);padding:12px;">
            No close / off equipment data
          </td>
        </tr>
      `;
    } else {
      openCloseBody.innerHTML = openCloseItems.map(item => `
        <tr>
          <td>${item.department || '-'}</td>
          <td>${item.item || '-'}</td>
          <td class="num">${Number(item.qty || 0).toLocaleString('en-US')}</td>
        </tr>
      `).join('');
    }
  }
}

function loadDepartmentEquipment(){
  console.log('Loading department equipment AJAX:', departmentEquipmentUrl);

  fetchOmData(departmentEquipmentUrl)
    .then(data => {
      console.log('Department Equipment AJAX result:', data);

      renderDepartmentEquipment(data);
    })
    .catch(error => {
      console.error('Department Equipment AJAX error:', error);
    });
}

function applyDepartmentMain(data){
  manpowerFulfillmentBQ = data.manpowerFulfillment || {};
  incidentByDepartmentBQ = data.incidentByDepartment || {};

  built.isort = false;
  buildIsort();

  const totalInc = Number(incidentByDepartmentBQ.total_incident || 0);
  const highestDept = incidentByDepartmentBQ.highest_department || '-';
  const highestCase = Number(incidentByDepartmentBQ.highest_case || 0);

  const incText = document.getElementById('incDeptText');
  if(incText){
    incText.innerHTML =
      'Total <b style="color:var(--ink)">' +
      totalInc.toLocaleString('en-US') +
      ' incidents</b> this period · ' +
      highestDept +
      ' highest (' +
      highestCase.toLocaleString('en-US') +
      ').';
  }
}

function loadDepartmentMain(){
  console.log('Loading department main AJAX:', departmentMainUrl);

  fetchOmData(departmentMainUrl)
    .then(data => {
      console.log('Department Main AJAX result:', data);

      applyDepartmentMain(data);
    })
    .catch(error => {
      console.error('Department Main AJAX error:', error);
    });
}

let departmentEquipmentLoaded = false;

let departmentInventoryLoaded = false;

function loadDepartmentPage(){
  console.log('Loading complete department report AJAX:', departmentAllUrl);

  fetchOmData(departmentAllUrl)
    .then(data => {
      applyDepartmentMain(data);
      renderDepartmentEquipment(data);
      renderDepartmentInventory(data);
    })
    .catch(error => console.error('Department report AJAX error:', error));
}
if(activeTabFromLaravel === 'department'){
  loadDepartmentPage();
}
if(activeTabFromLaravel === 'kaizen'){
  loadKaizenMain();
}

let departmentTabLoaded = activeTabFromLaravel === 'department';

function activateOmTab(tabName){
    const activeTabInput = document.getElementById('activeTabInput');

  if(activeTabInput){
    activeTabInput.value = tabName;
  }
  document.querySelectorAll('.om-tab-link').forEach(link => {
    link.classList.toggle('active', link.dataset.tab === tabName);
  });

  document.querySelectorAll('.tab-page').forEach(page => {
    page.classList.remove('active');
  });

  if(tabName === 'overview'){
  document.getElementById('tab-ovw')?.classList.add('active');

  built.ovw = false;
  loadOverviewMain();

  loadOverviewDetail();
}

  if(tabName === 'department'){
  document.getElementById('tab-isort')?.classList.add('active');

  if(!departmentTabLoaded){
    departmentTabLoaded = true;
  }

  loadDepartmentPage();
}

  if(tabName === 'kaizen'){
  document.getElementById('tab-kaizen')?.classList.add('active');

  built.kaizen = false;
  loadKaizenMain();
}

  const url = new URL(window.location.href);
  url.searchParams.set('tab', tabName);
  window.history.pushState({}, '', url);
}

const omDateForm = document.querySelector('form.daterange');
if(omDateForm){
  omDateForm.addEventListener('submit', event => {
    event.preventDefault();
    const start = document.getElementById('dStart');
    const end = document.getElementById('dEnd');
    end.setCustomValidity('');
    if(!start.value || !end.value || start.value > end.value){
      end.setCustomValidity('Pilih tanggal awal dan akhir yang valid.');
      end.reportValidity();
      return;
    }
    omGeneration++;
    for(const [key, entry] of omResponses){
      if(entry.pending){
        entry.controller.abort();
        omResponses.delete(key);
      } else if(entry.expiresAt <= Date.now()){
        omResponses.delete(key);
      }
    }
    omPending = 0;
    omFailed = false;
    omDates = { start: start.value, end: end.value };
    document.querySelectorAll('input[name="start_date"]').forEach(input => input.value = omDates.start);
    document.querySelectorAll('input[name="end_date"]').forEach(input => input.value = omDates.end);
    document.querySelectorAll('.om-tab-link').forEach(link => {
      const url = new URL(link.href);
      url.searchParams.set('start_date', omDates.start);
      url.searchParams.set('end_date', omDates.end);
      link.href = url.href;
    });
    overviewDetailLoaded = false;
    departmentEquipmentLoaded = false;
    departmentInventoryLoaded = false;
    const url = new URL(window.location.href);
    url.searchParams.set('start_date', omDates.start);
    url.searchParams.set('end_date', omDates.end);
    window.history.replaceState({}, '', url);
    activateOmTab(document.getElementById('activeTabInput').value || 'overview');
    updateOmLoading();
  });
  omDateForm.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('input', () => document.getElementById('dEnd').setCustomValidity(''));
  });
}
document.querySelectorAll('.om-tab-link').forEach(link => {
  link.addEventListener('click', function(e){
    e.preventDefault();

    const tabName = this.dataset.tab || 'overview';
    activateOmTab(tabName);
  });
});

if(activeTabFromLaravel === 'kaizen'){
  buildKaizen();
}

document.querySelectorAll('.seg:not(form)').forEach(seg=>{
  seg.addEventListener('click', e=>{
    if(e.target.tagName !== 'BUTTON') return;

    seg.querySelectorAll('button').forEach(b=>{
      b.classList.toggle('on', b === e.target);
    });
  });
});

const mpTabSeg = document.getElementById('mpTabSeg');
if(mpTabSeg){
  mpTabSeg.addEventListener('click', e=>{
    if(e.target.tagName !== 'BUTTON') return;
    renderMp(e.target.dataset.mp);
  });
}



(function(){const now=new Date();const d=now.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});const t=now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});document.getElementById('lastUpdated').textContent='Last updated: '+d+', '+t+' WIB';})();



const wdDeptSelect = document.getElementById('wdDeptSel');

if(wdDeptSelect){
  wdDeptSelect.addEventListener('change', wdRender);
}

</script>
</body>
</html>
