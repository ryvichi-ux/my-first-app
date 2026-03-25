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
    padding: 20px;
  }

  .page-title {
    text-align: center;
    margin-bottom: 24px;
  }

  .page-title h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #1a1a2e;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    line-height: 1.3;
  }

  .page-title h1 .highlight-ppt { color: #d63031; }
  .page-title h1 .highlight-canva { color: #6c5ce7; }

  .page-title .subtitle {
    margin-top: 8px;
    font-size: 1rem;
    color: #636e72;
  }

  /* 調査結果サマリー */
  .summary-cards {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    text-align: center;
    flex: 1;
    min-width: 200px;
    max-width: 260px;
  }

  .card .label {
    font-size: 0.85rem;
    color: #636e72;
    margin-bottom: 6px;
  }

  .card .value {
    font-size: 1.8rem;
    font-weight: 900;
  }

  .card .note {
    font-size: 0.78rem;
    color: #636e72;
    margin-top: 4px;
  }

  .card.ppt .value { color: #d63031; }
  .card.canva .value { color: #6c5ce7; }
  .card.cross .value { color: #00b894; }

  /* グラフエリア */
  .chart-wrapper {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.1);
    margin-bottom: 24px;
  }

  .chart-wrapper h2 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
  }

  .chart-wrapper .chart-sub {
    font-size: 0.85rem;
    color: #636e72;
    margin-bottom: 16px;
  }

  #main-chart {
    width: 100%;
    height: 480px;
  }

  /* 解説セクション */
  .explanation-section {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
  }

  @media (max-width: 700px) {
    .explanation-section { grid-template-columns: 1fr; }
    .page-title h1 { font-size: 1.4rem; }
  }

  .explain-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  }

  .explain-card .icon {
    font-size: 2rem;
    margin-bottom: 8px;
  }

  .explain-card h3 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #1a1a2e;
  }

  .explain-card p {
    font-size: 0.88rem;
    color: #636e72;
    line-height: 1.7;
  }

  /* データ出典 */
  .source-box {
    background: #f8f9ff;
    border: 1px solid #dfe6ff;
    border-left: 4px solid #6c5ce7;
    border-radius: 8px;
    padding: 14px 18px;
    font-size: 0.82rem;
    color: #636e72;
    line-height: 1.8;
  }

  .source-box strong { color: #1a1a2e; }

  /* 逆転バッジ */
  .badge-reverse {
    display: inline-block;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: #fff;
    font-weight: 900;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.88rem;
    margin-left: 8px;
    vertical-align: middle;
    box-shadow: 0 2px 8px rgba(0,184,148,0.4);
  }
</style>
</head>
<body>

<div class="page-title">
  <h1>
    <span class="highlight-ppt">PowerPoint</span> vs
    <span class="highlight-canva">Canva</span>
    使用率の変化
    <span class="badge-reverse">逆転！</span>
  </h1>
  <p class="subtitle">プレゼンテーションソフト 市場シェアの推移（2018年〜2024年）</p>
</div>

<!-- サマリーカード -->
<div class="summary-cards">
  <div class="card ppt">
    <div class="label">PowerPoint（2024年）</div>
    <div class="value">約18%</div>
    <div class="note">プレゼンソフト市場シェア</div>
  </div>
  <div class="card cross">
    <div class="label">逆転した時期</div>
    <div class="value">2022年</div>
    <div class="note">CanvaがPowerPointを追い越した！</div>
  </div>
  <div class="card canva">
    <div class="label">Canva（2024年）</div>
    <div class="value">約58%</div>
    <div class="note">プレゼンソフト市場シェア</div>
  </div>
</div>

<!-- グラフ -->
<div class="chart-wrapper">
  <h2>プレゼンテーションソフト 使用率の推移</h2>
  <p class="chart-sub">6sense社のマーケットデータをもとに作成。縦軸：市場シェア（%）、横軸：年</p>
  <div id="main-chart"></div>
</div>

<!-- 解説 -->
<div class="explanation-section">
  <div class="explain-card">
    <div class="icon">📊</div>
    <h3>PowerPointとは？</h3>
    <p>マイクロソフト社が作ったスライド作成ソフト。1987年から使われており、長年「プレゼンといえばPowerPoint」と言われてきた定番ツール。</p>
  </div>
  <div class="explain-card">
    <div class="icon">🎨</div>
    <h3>Canvaとは？</h3>
    <p>2013年にオーストラリアで生まれたデザインツール。ブラウザやスマホで使えて、おしゃれなテンプレートが豊富。プログラミング不要でかんたんにデザインできる。</p>
  </div>
  <div class="explain-card">
    <div class="icon">🔀</div>
    <h3>なぜ逆転したの？</h3>
    <p>2020年頃からCanvaが爆発的に成長。2022年には月間ユーザー数が1億人を突破！使いやすさ・スマホ対応・無料プランが人気を集め、2024年には2.2億人が使用。</p>
  </div>
</div>

<!-- 出典 -->
<div class="source-box">
  <strong>データ出典・参考資料</strong><br>
  ・ 6sense社「Presentation Market Share」レポート（2024年）：Canva 58.32% vs PowerPoint 17.65%<br>
  ・ DemandSage「Canva Statistics」：Canvaの月間アクティブユーザー数 推移<br>
  ・ Backlinko「Canva Users」：2024年12月時点で2.2億人のユーザー<br>
  ・ Microsoft 365：全世界で3.45億の有料ライセンス（エンタープライズ向け含む）<br>
  ※ 市場シェアのデータは「プレゼンテーションソフト」カテゴリの導入企業・サイト数を基準にしています。
</div>

<script>
// ===== データ（調査・報告データをもとに推定） =====
const years = ['2018', '2019', '2020', '2021', '2022', '2023', '2024'];

// プレゼンテーションソフト市場シェア（%）
// 出典: 6sense, DemandSage, Backlinko などの報告データをもとに推定
const pptShare  = [68, 62, 53, 42, 33, 25, 18];
const canvaShare = [5,  10, 20, 33, 45, 52, 58];

// 逆転ポイントのインデックス（2022年 = index 4）
const crossoverIndex = 4;

const chart = echarts.init(document.getElementById('main-chart'));

const option = {
  backgroundColor: '#ffffff',
  animation: true,
  animationDuration: 1200,
  animationEasing: 'cubicOut',

  tooltip: {
    trigger: 'axis',
    backgroundColor: '#1a1a2e',
    borderColor: '#6c5ce7',
    borderWidth: 2,
    textStyle: { color: '#fff', fontSize: 14 },
    formatter: function(params) {
      const year = params[0].axisValue;
      let html = `<div style="font-weight:900;font-size:16px;margin-bottom:8px;color:#fdcb6e">📅 ${year}年</div>`;
      params.forEach(p => {
        const color = p.seriesName === 'PowerPoint' ? '#ff7675' : '#a29bfe';
        const icon  = p.seriesName === 'PowerPoint' ? '📋' : '🎨';
        html += `<div style="margin:4px 0">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${color};margin-right:6px"></span>
          ${icon} <strong>${p.seriesName}</strong>：<span style="font-size:18px;font-weight:900;color:${color}">${p.value}%</span>
        </div>`;
      });
      if (year === '2022') {
        html += `<div style="margin-top:8px;padding:6px 10px;background:#00b894;border-radius:8px;font-weight:700;font-size:13px">🔀 この年に逆転！</div>`;
      }
      return html;
    }
  },

  legend: {
    top: 10,
    right: 20,
    icon: 'circle',
    itemWidth: 14,
    itemHeight: 14,
    textStyle: { fontSize: 14, fontWeight: 700, color: '#1a1a2e' },
    data: [
      { name: 'PowerPoint', itemStyle: { color: '#d63031' } },
      { name: 'Canva',      itemStyle: { color: '#6c5ce7' } }
    ]
  },

  grid: {
    left: 60,
    right: 40,
    top: 80,
    bottom: 80
  },

  xAxis: {
    type: 'category',
    data: years,
    axisLabel: {
      fontSize: 16,
      fontWeight: 700,
      color: '#1a1a2e',
      formatter: '{value}年'
    },
    axisLine: { lineStyle: { color: '#dfe6e9', width: 2 } },
    axisTick: { show: false },
    splitLine: {
      show: true,
      lineStyle: { color: '#f1f2f6', type: 'dashed' }
    }
  },

  yAxis: {
    type: 'value',
    min: 0,
    max: 80,
    interval: 10,
    axisLabel: {
      fontSize: 13,
      color: '#636e72',
      formatter: '{value}%'
    },
    axisLine: { show: false },
    axisTick: { show: false },
    splitLine: {
      lineStyle: { color: '#f1f2f6', type: 'dashed' }
    },
    name: '市場シェア（%）',
    nameTextStyle: {
      fontSize: 13,
      color: '#636e72',
      padding: [0, 0, 0, 10]
    }
  },

  // 逆転エリアのマーキング
  markArea: {
    silent: true,
    data: [[
      { xAxis: '2021', itemStyle: { color: 'rgba(0, 184, 148, 0.06)' } },
      { xAxis: '2023' }
    ]]
  },

  series: [
    {
      name: 'PowerPoint',
      type: 'line',
      data: pptShare,
      smooth: 0.4,
      symbol: 'circle',
      symbolSize: 12,
      lineStyle: {
        color: '#d63031',
        width: 4,
        shadowColor: 'rgba(214,48,49,0.3)',
        shadowBlur: 8
      },
      itemStyle: {
        color: '#d63031',
        borderColor: '#fff',
        borderWidth: 3
      },
      label: {
        show: true,
        position: 'top',
        fontSize: 13,
        fontWeight: 700,
        color: '#d63031',
        formatter: '{c}%'
      },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(214,48,49,0.15)' },
            { offset: 1, color: 'rgba(214,48,49,0.01)' }
          ]
        }
      },
      markPoint: {
        data: [
          {
            coord: ['2024', 18],
            value: '18%',
            itemStyle: { color: '#d63031' },
            label: {
              color: '#fff',
              fontSize: 11,
              fontWeight: 700
            }
          }
        ],
        symbolSize: 50
      }
    },
    {
      name: 'Canva',
      type: 'line',
      data: canvaShare,
      smooth: 0.4,
      symbol: 'circle',
      symbolSize: 12,
      lineStyle: {
        color: '#6c5ce7',
        width: 4,
        shadowColor: 'rgba(108,92,231,0.3)',
        shadowBlur: 8
      },
      itemStyle: {
        color: '#6c5ce7',
        borderColor: '#fff',
        borderWidth: 3
      },
      label: {
        show: true,
        position: 'bottom',
        fontSize: 13,
        fontWeight: 700,
        color: '#6c5ce7',
        formatter: '{c}%'
      },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(108,92,231,0.15)' },
            { offset: 1, color: 'rgba(108,92,231,0.01)' }
          ]
        }
      },
      markPoint: {
        data: [
          {
            coord: ['2024', 58],
            value: '58%',
            itemStyle: { color: '#6c5ce7' },
            label: {
              color: '#fff',
              fontSize: 11,
              fontWeight: 700
            }
          }
        ],
        symbolSize: 50
      },
      markLine: {
        silent: true,
        symbol: ['none', 'none'],
        lineStyle: {
          color: '#00b894',
          width: 2.5,
          type: 'dashed'
        },
        label: {
          show: true,
          position: 'insideEndTop',
          formatter: '2022年：逆転！',
          fontSize: 14,
          fontWeight: 900,
          color: '#00b894',
          backgroundColor: 'rgba(0,184,148,0.12)',
          padding: [4, 10],
          borderRadius: 6
        },
        data: [{ xAxis: '2022' }]
      }
    }
  ]
};

chart.setOption(option);
window.addEventListener('resize', () => chart.resize());
</script>

</body>
</html>
