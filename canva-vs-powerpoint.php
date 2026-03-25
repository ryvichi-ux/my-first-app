<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PowerPoint vs Canva 使用率の変化</title>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: linear-gradient(135deg, #f0f4ff 0%, #fff8f0 100%);
    font-family: 'Hiragino Sans', 'Meiryo', 'Yu Gothic', sans-serif;
    min-height: 100vh;
    padding: 24px 20px;
  }
  .page-title {
    text-align: center;
    margin-bottom: 28px;
  }
  .page-title h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #1a1a2e;
    line-height: 1.3;
  }
  .page-title h1 .ppt   { color: #d63031; }
  .page-title h1 .canva { color: #6c5ce7; }
  .page-title .subtitle {
    margin-top: 8px;
    font-size: 0.95rem;
    color: #636e72;
  }
  .badge-reverse {
    display: inline-block;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: #fff;
    font-weight: 900;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-left: 10px;
    vertical-align: middle;
    box-shadow: 0 2px 10px rgba(0,184,148,0.45);
  }
  /* サマリーカード */
  .summary-cards {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }
  .card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 22px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    text-align: center;
    flex: 1;
    min-width: 180px;
    max-width: 250px;
  }
  .card .label { font-size: 0.8rem; color: #636e72; margin-bottom: 6px; }
  .card .value { font-size: 1.9rem; font-weight: 900; line-height: 1; }
  .card .note  { font-size: 0.72rem; color: #b2bec3; margin-top: 5px; }
  .card.ppt   .value { color: #d63031; }
  .card.cross .value { color: #00b894; }
  .card.cnva  .value { color: #6c5ce7; }
  /* グラフラッパー */
  .chart-wrapper {
    background: #fff;
    border-radius: 20px;
    padding: 24px 20px 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.09);
    margin-bottom: 20px;
  }
  .chart-wrapper .chart-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 3px;
  }
  .chart-wrapper .chart-sub {
    font-size: 0.8rem;
    color: #b2bec3;
    margin-bottom: 14px;
  }
  /* 凡例 */
  .legend-note {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: #636e72;
    margin-top: 10px;
  }
  .ln-item { display: flex; align-items: center; gap: 7px; }
  .ln-solid  { width: 26px; height: 3px; border-radius: 2px; }
  .ln-dashed {
    width: 26px; height: 3px;
    background: repeating-linear-gradient(90deg, currentColor 0, currentColor 5px, transparent 5px, transparent 9px);
  }
  #main-chart { width: 100%; height: 460px; }
  /* 解説カード */
  .explain-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
  }
  @media (max-width: 680px) {
    .explain-grid { grid-template-columns: 1fr; }
    .page-title h1 { font-size: 1.4rem; }
  }
  .explain-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
  }
  .explain-card .icon { font-size: 1.8rem; margin-bottom: 8px; }
  .explain-card h3 { font-size: 0.95rem; font-weight: 700; color: #1a1a2e; margin-bottom: 7px; }
  .explain-card p  { font-size: 0.83rem; color: #636e72; line-height: 1.7; }
  /* 出典 */
  .source-box {
    background: #f8f9ff;
    border: 1px solid #dde4ff;
    border-left: 4px solid #6c5ce7;
    border-radius: 8px;
    padding: 14px 18px;
    font-size: 0.79rem;
    color: #636e72;
    line-height: 2;
  }
  .source-box strong { color: #1a1a2e; }
  .source-box a { color: #6c5ce7; text-decoration: none; }
  .source-box a:hover { text-decoration: underline; }
  .source-box .est-note {
    display: inline-block;
    background: #fff8e1;
    border: 1px solid #f0c040;
    color: #856404;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 0.72rem;
    margin-top: 6px;
  }
</style>
</head>
<body>

<div class="page-title">
  <h1>
    <span class="ppt">PowerPoint</span> vs <span class="canva">Canva</span> 使用率の変化
    <span class="badge-reverse">2022年 逆転！</span>
  </h1>
  <p class="subtitle">プレゼンテーションソフト 市場シェアの推移（2018〜2025年）</p>
</div>

<div class="summary-cards">
  <div class="card ppt">
    <div class="label">PowerPoint（2025年実測）</div>
    <div class="value">20%</div>
    <div class="note">6sense調査 プレゼンソフト市場</div>
  </div>
  <div class="card cross">
    <div class="label">逆転した年</div>
    <div class="value">2022年</div>
    <div class="note">CanvaがPowerPointを初めて追い越した</div>
  </div>
  <div class="card cnva">
    <div class="label">Canva（2025年実測）</div>
    <div class="value">56%</div>
    <div class="note">月間アクティブユーザー 2.6億人</div>
  </div>
</div>

<div class="chart-wrapper">
  <div class="chart-title">プレゼンテーションソフト 市場シェアの推移</div>
  <div class="chart-sub">縦軸：市場シェア（%）｜横軸：年｜点線は6sense 2025年実測値</div>
  <div id="main-chart"></div>
  <div class="legend-note">
    <div class="ln-item"><span class="ln-solid" style="background:#d63031"></span>PowerPoint（推計）</div>
    <div class="ln-item"><span class="ln-solid" style="background:#6c5ce7"></span>Canva（推計）</div>
    <div class="ln-item"><span class="ln-dashed" style="color:#d63031"></span>PowerPoint（2025実測）</div>
    <div class="ln-item"><span class="ln-dashed" style="color:#6c5ce7"></span>Canva（2025実測）</div>
  </div>
</div>

<div class="explain-grid">
  <div class="explain-card">
    <div class="icon">📊</div>
    <h3>PowerPointとは？</h3>
    <p>マイクロソフト社が1987年から提供するスライド作成ソフト。長年「プレゼンといえばPowerPoint」と言われてきた定番ツール。企業・学校で広く使われてきた。</p>
  </div>
  <div class="explain-card">
    <div class="icon">🎨</div>
    <h3>Canvaとは？</h3>
    <p>2013年オーストラリア生まれのデザインツール。ブラウザ・スマホで使えて、おしゃれなテンプレートが豊富。プログラミング不要でかんたんにデザインできる。</p>
  </div>
  <div class="explain-card">
    <div class="icon">🔀</div>
    <h3>なぜ逆転したの？</h3>
    <p>Canvaは2022年に月間1億ユーザー突破、2025年には2.6億人へ急成長。使いやすさ・スマホ対応・無料プランが人気の理由。Fortune500企業の95%も利用中。</p>
  </div>
</div>

<div class="source-box">
  <strong>データ出典・参考資料</strong><br>
  ・ <a href="https://6sense.com/tech/presentation/canva-market-share" target="_blank">6sense「Canva Presentation Market Share」</a>（2025年実測）：Canva 56.49% ／ PowerPoint 20.39%<br>
  ・ <a href="https://www.demandsage.com/canva-statistics/" target="_blank">DemandSage「Canva Statistics 2026」</a>：月間アクティブユーザー数の推移データ<br>
  ・ <a href="https://backlinko.com/canva-users" target="_blank">Backlinko「Canva User and Revenue Statistics」</a>：2024年12月 2.2億人・2025年 2.6億人<br>
  ・ <a href="https://www.canva.com/newsroom/news/canva-2025-wrap/" target="_blank">Canva公式「2025 in review」</a>：月間2.6億ユーザー・年間収益$35億（2025年実績）<br>
  ・ <a href="https://6sense.com/tech/presentation/microsoft-powerpoint-market-share" target="_blank">6sense「Microsoft PowerPoint Market Share」</a>（2025年実測）<br>
  <span class="est-note">⚠️ 2018〜2021年は報告トレンドをもとにした推計値。2022年以降は6senseほかの報告データを使用。</span>
</div>

<script>
// ========================================================
// データ定義
// 2018-2024 : 各種調査報告をもとにした推計（実線）
// 2025      : 6sense実測（Canva 56.49% / PowerPoint 20.39%）点線
// ========================================================
const years = ['2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025'];

// 実績ライン（2025はnull→点線系列で別表示）
const pptData   = [68, 62, 53, 42, 33, 25, 18, null];
const canvaData = [ 5, 10, 20, 33, 45, 52, 58, null];

// 2025実測（2024から接続するため2024値も含む）
const ppt2025   = [null, null, null, null, null, null, 18, 20];
const canva2025 = [null, null, null, null, null, null, 58, 56];

const chart = echarts.init(document.getElementById('main-chart'));

const option = {
  backgroundColor: '#ffffff',
  animation: true,
  animationDuration: 1300,
  animationEasing: 'cubicOut',

  tooltip: {
    trigger: 'axis',
    backgroundColor: '#1a1a2e',
    borderColor: '#6c5ce7',
    borderWidth: 2,
    textStyle: { color: '#fff', fontSize: 13 },
    formatter: function(params) {
      const year = params[0].axisValue;
      const map  = {};
      params.forEach(p => { if (p.value != null) map[p.seriesName] = p.value; });
      const ppt  = map['PowerPoint'] ?? map['PowerPoint（2025）'];
      const cnv  = map['Canva']      ?? map['Canva（2025）'];

      let html = `<div style="font-weight:900;font-size:15px;margin-bottom:8px;color:#fdcb6e">📅 ${year}年</div>`;
      if (ppt != null) html += `<div style="margin:4px 0"><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#d63031;margin-right:6px"></span>📋 PowerPoint：<strong style="color:#ff7675;font-size:17px">${ppt}%</strong></div>`;
      if (cnv != null) html += `<div style="margin:4px 0"><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#6c5ce7;margin-right:6px"></span>🎨 Canva：<strong style="color:#a29bfe;font-size:17px">${cnv}%</strong></div>`;
      if (year === '2022') html += `<div style="margin-top:8px;padding:5px 10px;background:#00b894;border-radius:8px;font-weight:700;font-size:12px">🔀 この年に逆転！</div>`;
      if (year === '2025') html += `<div style="margin-top:6px;font-size:11px;color:#aaa">6sense実測値</div>`;
      return html;
    }
  },

  legend: {
    show: false
  },

  grid: { left: 58, right: 36, top: 60, bottom: 50 },

  xAxis: {
    type: 'category',
    data: years,
    axisLabel: { fontSize: 15, fontWeight: 700, color: '#1a1a2e', formatter: '{value}年' },
    axisLine: { lineStyle: { color: '#dfe6e9', width: 2 } },
    axisTick: { show: false },
    splitLine: { show: true, lineStyle: { color: '#f4f6f8', type: 'dashed' } }
  },

  yAxis: {
    type: 'value', min: 0, max: 80, interval: 10,
    axisLabel: { fontSize: 12, color: '#b2bec3', formatter: '{value}%' },
    axisLine: { show: false },
    axisTick: { show: false },
    splitLine: { lineStyle: { color: '#f4f6f8', type: 'dashed' } },
    name: '市場シェア（%）',
    nameTextStyle: { fontSize: 11, color: '#b2bec3', padding: [0, 0, 0, 8] }
  },

  series: [
    // ── PowerPoint 実績（実線） ──
    {
      name: 'PowerPoint',
      type: 'line',
      data: pptData,
      smooth: 0.35,
      connectNulls: false,
      symbol: 'circle', symbolSize: 11,
      lineStyle: { color: '#d63031', width: 4, shadowColor: 'rgba(214,48,49,0.25)', shadowBlur: 10 },
      itemStyle: { color: '#d63031', borderColor: '#fff', borderWidth: 3 },
      label: { show: true, position: 'top', fontSize: 12, fontWeight: 700, color: '#d63031', formatter: p => p.value != null ? p.value + '%' : '' },
      areaStyle: {
        color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [{ offset: 0, color: 'rgba(214,48,49,0.13)' }, { offset: 1, color: 'rgba(214,48,49,0)' }] }
      },
      markLine: {
        silent: true,
        symbol: ['none', 'none'],
        lineStyle: { color: '#00b894', width: 2.5, type: 'dashed' },
        label: {
          show: true, position: 'insideEndTop',
          formatter: '← 2022年：逆転！',
          fontSize: 13, fontWeight: 900, color: '#00b894',
          backgroundColor: 'rgba(0,184,148,0.1)', padding: [4, 10], borderRadius: 6
        },
        data: [{ xAxis: '2022' }]
      }
    },
    // ── Canva 実績（実線） ──
    {
      name: 'Canva',
      type: 'line',
      data: canvaData,
      smooth: 0.35,
      connectNulls: false,
      symbol: 'circle', symbolSize: 11,
      lineStyle: { color: '#6c5ce7', width: 4, shadowColor: 'rgba(108,92,231,0.25)', shadowBlur: 10 },
      itemStyle: { color: '#6c5ce7', borderColor: '#fff', borderWidth: 3 },
      label: { show: true, position: 'bottom', fontSize: 12, fontWeight: 700, color: '#6c5ce7', formatter: p => p.value != null ? p.value + '%' : '' },
      areaStyle: {
        color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [{ offset: 0, color: 'rgba(108,92,231,0.13)' }, { offset: 1, color: 'rgba(108,92,231,0)' }] }
      },
      markArea: {
        silent: true,
        data: [[
          { xAxis: '2021', itemStyle: { color: 'rgba(0,184,148,0.05)' } },
          { xAxis: '2023' }
        ]]
      }
    },
    // ── PowerPoint 2025実測（点線） ──
    {
      name: 'PowerPoint（2025）',
      type: 'line',
      data: ppt2025,
      smooth: 0.35,
      connectNulls: false,
      symbol: 'circle', symbolSize: 11,
      lineStyle: { color: '#d63031', width: 3, type: 'dashed' },
      itemStyle: { color: '#d63031', borderColor: '#fff', borderWidth: 3 },
      label: { show: true, position: 'top', fontSize: 12, fontWeight: 700, color: '#d63031',
        formatter: p => p.dataIndex === 7 && p.value != null ? p.value + '%' : '' }
    },
    // ── Canva 2025実測（点線） ──
    {
      name: 'Canva（2025）',
      type: 'line',
      data: canva2025,
      smooth: 0.35,
      connectNulls: false,
      symbol: 'circle', symbolSize: 11,
      lineStyle: { color: '#6c5ce7', width: 3, type: 'dashed' },
      itemStyle: { color: '#6c5ce7', borderColor: '#fff', borderWidth: 3 },
      label: { show: true, position: 'bottom', fontSize: 12, fontWeight: 700, color: '#6c5ce7',
        formatter: p => p.dataIndex === 7 && p.value != null ? p.value + '%' : '' }
    }
  ]
};

chart.setOption(option);
window.addEventListener('resize', () => chart.resize());
</script>

</body>
</html>
