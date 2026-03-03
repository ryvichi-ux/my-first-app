<?php
/**
 * 日経225 月足チャート - 割合（％）表示版
 * Y軸を対数スケールにすることで、同じ視覚的距離 = 同じ変化率（%）を実現
 */

// CSVファイルの読み込み
$csvFile = __DIR__ . '/data/nikkei225.csv';
$data = [];

if (!file_exists($csvFile)) {
    $error = 'データファイルが見つかりません。download_data.php を実行してデータを取得してください。';
} else {
    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle); // ヘッダー行をスキップ

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 5) continue;

        $date  = $row[0]; // YYYY-MM-DD
        $open  = floatval($row[1]);
        $high  = floatval($row[2]);
        $low   = floatval($row[3]);
        $close = floatval($row[4]);
        $vol   = isset($row[6]) ? intval($row[6]) : 0;

        if ($open <= 0 || $high <= 0 || $low <= 0 || $close <= 0) continue;

        // 日付をYYYY-MM形式に
        $dateLabel = substr($date, 0, 7);

        $data[] = [
            'date'   => $dateLabel,
            'open'   => round($open, 2),
            'high'   => round($high, 2),
            'low'    => round($low, 2),
            'close'  => round($close, 2),
            'volume' => $vol,
        ];
    }
    fclose($handle);
}

// 移動平均を計算
function calcMA(array $data, int $period): array {
    $ma = [];
    $n  = count($data);
    for ($i = 0; $i < $n; $i++) {
        if ($i < $period - 1) {
            $ma[] = null;
        } else {
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += $data[$j]['close'];
            }
            $ma[] = round($sum / $period, 2);
        }
    }
    return $ma;
}

$ma25  = calcMA($data, 25);
$ma75  = calcMA($data, 75);
$ma200 = calcMA($data, 200);

// JavaScript用JSONデータ作成
$dates   = array_column($data, 'date');
$ohlc    = array_map(fn($d) => [$d['open'], $d['close'], $d['low'], $d['high']], $data);
$volumes = array_column($data, 'volume');

$jsonDates   = json_encode($dates,   JSON_UNESCAPED_UNICODE);
$jsonOhlc    = json_encode($ohlc,    JSON_UNESCAPED_UNICODE);
$jsonVolumes = json_encode($volumes, JSON_UNESCAPED_UNICODE);
$jsonMa25    = json_encode($ma25,    JSON_UNESCAPED_UNICODE);
$jsonMa75    = json_encode($ma75,    JSON_UNESCAPED_UNICODE);
$jsonMa200   = json_encode($ma200,   JSON_UNESCAPED_UNICODE);

$totalRows    = count($data);
$latestDate   = $data[$totalRows - 1]['date'] ?? '';
$latestClose  = $data[$totalRows - 1]['close'] ?? 0;
$firstClose   = $data[0]['close'] ?? 1;
$totalReturn  = $firstClose > 0 ? round(($latestClose / $firstClose - 1) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>日経225 月足チャート（割合・対数スケール）</title>
<!-- Apache ECharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #0d1117;
    color: #e6edf3;
    font-family: 'Hiragino Sans', 'Meiryo', 'Yu Gothic', Arial, sans-serif;
    min-height: 100vh;
  }
  header {
    background: #161b22;
    border-bottom: 1px solid #30363d;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }
  header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #f0f6fc;
  }
  header h1 span { color: #58a6ff; }
  .badge {
    background: #1f6feb;
    color: #fff;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.03em;
  }
  .info-bar {
    background: #161b22;
    border-bottom: 1px solid #30363d;
    padding: 10px 24px;
    display: flex;
    gap: 32px;
    flex-wrap: wrap;
    font-size: 0.84rem;
    color: #8b949e;
  }
  .info-bar strong { color: #e6edf3; }
  .info-bar .up   { color: #3fb950; }
  .explanation {
    background: #1c2128;
    border: 1px solid #30363d;
    border-radius: 8px;
    margin: 16px 24px;
    padding: 14px 18px;
    font-size: 0.85rem;
    line-height: 1.7;
    color: #adbac7;
  }
  .explanation strong { color: #58a6ff; }
  .explanation .example {
    margin-top: 8px;
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    font-size: 0.83rem;
  }
  .example-item {
    background: #0d1117;
    border: 1px solid #30363d;
    border-radius: 6px;
    padding: 8px 14px;
  }
  .example-item .label { color: #8b949e; font-size: 0.78rem; margin-bottom: 2px; }
  .example-item .value { font-size: 1rem; font-weight: 700; }
  .example-item .pct   { color: #3fb950; font-size: 0.82rem; }
  #chart-container {
    padding: 0 24px 24px;
  }
  #main-chart {
    width: 100%;
    height: 580px;
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 8px;
  }
  .controls {
    display: flex;
    gap: 8px;
    padding: 8px 24px 0;
    flex-wrap: wrap;
  }
  .btn {
    padding: 5px 14px;
    border-radius: 6px;
    border: 1px solid #30363d;
    background: #21262d;
    color: #e6edf3;
    cursor: pointer;
    font-size: 0.82rem;
    transition: background 0.15s;
  }
  .btn:hover, .btn.active { background: #1f6feb; border-color: #1f6feb; }
  .footer {
    text-align: center;
    padding: 16px;
    color: #484f58;
    font-size: 0.78rem;
    border-top: 1px solid #21262d;
    margin-top: 8px;
  }
  .data-source {
    background: #1c2128;
    border: 1px solid #30363d;
    border-left: 3px solid #58a6ff;
    border-radius: 4px;
    margin: 0 24px 12px;
    padding: 10px 14px;
    font-size: 0.82rem;
    color: #8b949e;
  }
  .data-source a { color: #58a6ff; text-decoration: none; }
  .data-source a:hover { text-decoration: underline; }
  .error-box {
    background: #2d1b1b;
    border: 1px solid #f85149;
    border-radius: 8px;
    margin: 16px 24px;
    padding: 16px;
    color: #f85149;
  }
</style>
</head>
<body>

<header>
  <h1>日経<span>225</span> 月足チャート <small style="font-size:0.75rem;color:#8b949e;margin-left:8px;">割合（%）表示・対数スケール</small></h1>
  <span class="badge">LOG SCALE</span>
</header>

<div class="info-bar">
  <div>データ件数: <strong><?= number_format($totalRows) ?> ヶ月</strong></div>
  <div>最新: <strong><?= htmlspecialchars($latestDate) ?></strong></div>
  <div>最新終値: <strong><?= number_format($latestClose) ?> 円</strong></div>
  <div>累積リターン: <strong class="up">+<?= number_format($totalReturn) ?>%</strong>（全期間）</div>
</div>

<?php if (!empty($error)): ?>
<div class="error-box"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="explanation">
  <strong>📊 対数スケール（割合表示）とは？</strong><br>
  通常の線形スケールでは「絶対値の変化」が同じ大きさに表示されます。<br>
  対数スケールでは <strong>同じ視覚的距離 ＝ 同じ変化率（%）</strong> になります。
  <div class="example">
    <div class="example-item">
      <div class="label">例）7,120円 → 7,832円</div>
      <div class="value">+712円</div>
      <div class="pct">= <strong>+10%上昇</strong>（グラフ上の高さは同じ）</div>
    </div>
    <div class="example-item">
      <div class="label">例）61,670円 → 67,837円</div>
      <div class="value">+6,167円</div>
      <div class="pct">= <strong>+10%上昇</strong>（グラフ上の高さは同じ）</div>
    </div>
  </div>
</div>

<div class="data-source">
  📁 データソース: Yahoo Finance 日経225（^N225）月足・全期間 ／
  実データへの更新は <a href="download_data.php">download_data.php</a> を実行してください。
</div>

<div class="controls">
  <button class="btn" onclick="setZoom(0, 100)">全期間</button>
  <button class="btn" onclick="setZoom(85, 100)">直近10年</button>
  <button class="btn" onclick="setZoom(92, 100)">直近5年</button>
  <button class="btn" onclick="setZoom(96, 100)">直近3年</button>
  <button class="btn" onclick="setZoom(98.5, 100)">直近1年</button>
  <button class="btn active" id="logBtn" onclick="toggleScale()">📏 対数スケール（現在）</button>
</div>

<div id="chart-container">
  <div id="main-chart"></div>
</div>

<div class="footer">
  日経225 月足チャート（割合表示版） ／ データは参考用近似値です。投資判断は各自の責任で行ってください。
</div>

<script>
const dates   = <?= $jsonDates ?>;
const ohlcData = <?= $jsonOhlc ?>;
const volumes  = <?= $jsonVolumes ?>;
const ma25     = <?= $jsonMa25 ?>;
const ma75     = <?= $jsonMa75 ?>;
const ma200    = <?= $jsonMa200 ?>;

let isLogScale = true;

// 色の定義
const UP_COLOR   = '#26a69a';  // 陽線（緑）
const DOWN_COLOR = '#ef5350';  // 陰線（赤）

const chart = echarts.init(document.getElementById('main-chart'), 'dark');

function buildOption(logScale) {
  return {
    backgroundColor: '#161b22',
    animation: false,
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      backgroundColor: '#1c2128',
      borderColor: '#30363d',
      textStyle: { color: '#e6edf3', fontSize: 12 },
      formatter: function(params) {
        const d = params[0];
        if (!d) return '';
        const idx = d.dataIndex;
        const o = ohlcData[idx];
        if (!o) return '';
        const [op, cl, lo, hi] = o;
        const isUp = cl >= op;
        const color = isUp ? '#26a69a' : '#ef5350';
        const arrow = isUp ? '▲' : '▼';
        const change = ((cl - op) / op * 100).toFixed(2);

        let html = `<div style="font-weight:700;margin-bottom:6px;color:#58a6ff">${dates[idx]}</div>`;
        html += `<div>始値: <span style="font-weight:600">${op.toLocaleString()}</span></div>`;
        html += `<div>高値: <span style="color:#3fb950;font-weight:600">${hi.toLocaleString()}</span></div>`;
        html += `<div>安値: <span style="color:#f85149;font-weight:600">${lo.toLocaleString()}</span></div>`;
        html += `<div>終値: <span style="color:${color};font-weight:700">${cl.toLocaleString()}</span> <span style="color:${color}">${arrow}${Math.abs(change)}%</span></div>`;

        if (ma25[idx] != null) html += `<div style="color:#ffd700;margin-top:4px">MA25: ${ma25[idx].toLocaleString()}</div>`;
        if (ma75[idx] != null) html += `<div style="color:#ff9800">MA75: ${ma75[idx].toLocaleString()}</div>`;
        if (ma200[idx] != null) html += `<div style="color:#ff6ec7">MA200: ${ma200[idx].toLocaleString()}</div>`;

        return html;
      }
    },
    legend: {
      data: ['日経225', 'MA25', 'MA75', 'MA200'],
      top: 8,
      right: 120,
      textStyle: { color: '#8b949e', fontSize: 11 },
    },
    grid: [
      { left: 80, right: 20, top: 60, bottom: 220 },    // ローソク足
      { left: 80, right: 20, bottom: 130, height: 60 }  // 出来高
    ],
    xAxis: [
      {
        type: 'category',
        data: dates,
        axisLine: { lineStyle: { color: '#30363d' } },
        axisLabel: {
          color: '#8b949e',
          fontSize: 11,
          rotate: 0,
          formatter: function(val) {
            // 年のみ表示（1月の時）
            if (val.endsWith('-01')) return val.substring(0, 4);
            return '';
          }
        },
        splitLine: { show: false },
        gridIndex: 0
      },
      {
        type: 'category',
        data: dates,
        axisLabel: { show: false },
        axisLine: { show: false },
        splitLine: { show: false },
        gridIndex: 1
      }
    ],
    yAxis: [
      {
        scale: true,
        type: logScale ? 'log' : 'value',
        logBase: 10,
        axisLine: { lineStyle: { color: '#30363d' } },
        splitLine: { lineStyle: { color: '#21262d' } },
        axisLabel: {
          color: '#8b949e',
          fontSize: 11,
          formatter: function(val) {
            if (val >= 10000) return (val / 10000).toFixed(0) + '万';
            if (val >= 1000) return val.toLocaleString();
            return val;
          }
        },
        gridIndex: 0
      },
      {
        scale: true,
        type: 'value',
        axisLine: { show: false },
        splitLine: { show: false },
        axisLabel: { show: false },
        gridIndex: 1
      }
    ],
    dataZoom: [
      {
        type: 'inside',
        xAxisIndex: [0, 1],
        start: 0,
        end: 100,
        minValueSpan: 6
      },
      {
        type: 'slider',
        xAxisIndex: [0, 1],
        start: 0,
        end: 100,
        bottom: 10,
        height: 40,
        borderColor: '#30363d',
        backgroundColor: '#0d1117',
        fillerColor: 'rgba(31, 111, 235, 0.15)',
        handleStyle: { color: '#58a6ff' },
        textStyle: { color: '#8b949e', fontSize: 11 },
        dataBackground: {
          areaStyle: { color: '#21262d', opacity: 0.8 },
          lineStyle: { color: '#30363d' }
        },
        selectedDataBackground: {
          areaStyle: { color: '#1f6feb', opacity: 0.3 },
          lineStyle: { color: '#58a6ff' }
        },
        labelFormatter: function(val) {
          return dates[Math.round(val)] || '';
        }
      }
    ],
    series: [
      {
        name: '日経225',
        type: 'candlestick',
        xAxisIndex: 0,
        yAxisIndex: 0,
        data: ohlcData,
        itemStyle: {
          color: UP_COLOR,
          color0: DOWN_COLOR,
          borderColor: UP_COLOR,
          borderColor0: DOWN_COLOR,
          borderWidth: 1
        },
        emphasis: {
          itemStyle: {
            shadowBlur: 8,
            shadowColor: 'rgba(0,0,0,0.5)'
          }
        }
      },
      {
        name: 'MA25',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 0,
        data: ma25,
        smooth: true,
        symbol: 'none',
        lineStyle: { color: '#ffd700', width: 1.5, opacity: 0.85 },
        connectNulls: false,
        tooltip: { show: false }
      },
      {
        name: 'MA75',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 0,
        data: ma75,
        smooth: true,
        symbol: 'none',
        lineStyle: { color: '#ff9800', width: 1.5, opacity: 0.85 },
        connectNulls: false,
        tooltip: { show: false }
      },
      {
        name: 'MA200',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 0,
        data: ma200,
        smooth: true,
        symbol: 'none',
        lineStyle: { color: '#ff6ec7', width: 1.5, opacity: 0.75 },
        connectNulls: false,
        tooltip: { show: false }
      },
      {
        name: '出来高',
        type: 'bar',
        xAxisIndex: 1,
        yAxisIndex: 1,
        data: volumes.map((v, i) => ({
          value: v,
          itemStyle: {
            color: (ohlcData[i] && ohlcData[i][1] >= ohlcData[i][0])
              ? 'rgba(38, 166, 154, 0.5)'
              : 'rgba(239, 83, 80, 0.5)'
          }
        })),
        barMaxWidth: 6
      }
    ]
  };
}

chart.setOption(buildOption(true));

// スケール切替
function toggleScale() {
  isLogScale = !isLogScale;
  chart.setOption(buildOption(isLogScale), { replaceMerge: ['yAxis', 'series'] });
  const btn = document.getElementById('logBtn');
  if (isLogScale) {
    btn.textContent = '📏 対数スケール（現在）';
    btn.classList.add('active');
  } else {
    btn.textContent = '📐 線形スケール（現在）';
    btn.classList.remove('active');
  }
}

// ズーム設定
function setZoom(start, end) {
  chart.dispatchAction({ type: 'dataZoom', start, end });
}

// レスポンシブ対応
window.addEventListener('resize', () => chart.resize());
</script>
</body>
</html>
