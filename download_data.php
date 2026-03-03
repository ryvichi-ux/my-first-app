<?php
/**
 * 日経225 データダウンローダー
 * Yahoo Finance から ^N225 の月足全期間データを取得して CSV に保存します。
 *
 * 使用方法:
 *   ブラウザで http://your-server/download_data.php にアクセス、または
 *   コマンドライン: php download_data.php
 */

// ============================================================
// 設定
// ============================================================
define('DATA_DIR',   __DIR__ . '/data');
define('CSV_FILE',   DATA_DIR . '/nikkei225.csv');
define('TICKER',     '%5EN225');           // ^N225 をURLエンコード
define('INTERVAL',   '1mo');              // 月足
define('TIMEOUT',    30);                 // cURLタイムアウト（秒）

// ============================================================
// データ取得関数
// ============================================================

/**
 * Yahoo Finance から株価データをダウンロードして配列で返す
 */
function fetchYahooFinance(string $ticker, string $interval = '1mo'): ?string
{
    // period1=0（Unix epoch開始）〜 現在
    $period1 = 0;
    $period2 = time();

    // まずクッキー取得用リクエストでcrumbを入手
    $cookieJar = tempnam(sys_get_temp_dir(), 'yf_cookie_');

    $baseUrl = "https://finance.yahoo.com/quote/{$ticker}/history/";
    $ch = curl_init($baseUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_COOKIEJAR       => $cookieJar,
        CURLOPT_COOKIEFILE      => $cookieJar,
        CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
        CURLOPT_TIMEOUT         => TIMEOUT,
        CURLOPT_SSL_VERIFYPEER  => true,
    ]);
    $html  = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno || !$html) {
        return null;
    }

    // crumb を HTML から抽出
    $crumb = '';
    if (preg_match('/"CrumbStore":\{"crumb":"([^"]+)"\}/', $html, $m)) {
        $crumb = $m[1];
    }
    // 新形式
    if (!$crumb && preg_match('/crumb=([A-Za-z0-9.\/]+)/', $html, $m)) {
        $crumb = $m[1];
    }

    // V7 API でダウンロード（crumb なしでも動く場合あり）
    $downloadUrl = sprintf(
        'https://query1.finance.yahoo.com/v7/finance/download/%s?period1=%d&period2=%d&interval=%s&events=history&includeAdjustedClose=true%s',
        $ticker,
        $period1,
        $period2,
        $interval,
        $crumb ? '&crumb=' . urlencode($crumb) : ''
    );

    $ch2 = curl_init($downloadUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_COOKIEFILE      => $cookieJar,
        CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
        CURLOPT_TIMEOUT         => TIMEOUT,
        CURLOPT_SSL_VERIFYPEER  => true,
        CURLOPT_HTTPHEADER      => [
            'Accept: text/csv,*/*',
            'Referer: https://finance.yahoo.com/',
        ],
    ]);
    $csv   = curl_exec($ch2);
    $code  = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch2);
    curl_close($ch2);

    @unlink($cookieJar);

    if ($errno || $code !== 200) {
        return null;
    }

    return $csv;
}

/**
 * CSV 文字列を検証してパースする
 */
function parseCsv(string $csv): array
{
    $rows = [];
    $lines = explode("\n", trim($csv));

    if (empty($lines)) return [];

    $header = str_getcsv(array_shift($lines));
    // 必須カラムの確認
    $required = ['Date', 'Open', 'High', 'Low', 'Close'];
    foreach ($required as $col) {
        if (!in_array($col, $header, true)) {
            return [];
        }
    }

    $idx = array_flip($header);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $cols = str_getcsv($line);
        if (count($cols) < 5) continue;

        $close = floatval($cols[$idx['Close']]);
        if ($close <= 0 || $cols[$idx['Close']] === 'null') continue;

        $rows[] = [
            $cols[$idx['Date']],
            floatval($cols[$idx['Open']]),
            floatval($cols[$idx['High']]),
            floatval($cols[$idx['Low']]),
            $close,
            $close,
            isset($idx['Volume']) ? intval($cols[$idx['Volume']]) : 0,
        ];
    }

    return $rows;
}

// ============================================================
// メイン処理
// ============================================================

$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
}

// データディレクトリを作成
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

$log = [];

function log_msg(string $msg): void {
    global $log, $isCli;
    $log[] = $msg;
    if ($isCli) {
        echo $msg . "\n";
    }
}

log_msg("=== 日経225データダウンローダー ===");
log_msg("取得中: ^N225 月足 全期間...");

$csvContent = fetchYahooFinance(TICKER, INTERVAL);

if ($csvContent === null) {
    log_msg("エラー: Yahoo Finance からのデータ取得に失敗しました。");
    log_msg("ネットワーク接続を確認し、再試行してください。");
    $success = false;
} else {
    $rows = parseCsv($csvContent);

    if (empty($rows)) {
        log_msg("エラー: CSVデータのパースに失敗しました。");
        log_msg("取得したデータの最初の200文字: " . substr($csvContent, 0, 200));
        $success = false;
    } else {
        // CSVに書き出し
        $fp = fopen(CSV_FILE, 'w');
        fputcsv($fp, ['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume']);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        log_msg("✓ 成功: " . count($rows) . " 件のデータを取得しました。");
        log_msg("✓ 保存先: " . CSV_FILE);
        log_msg("✓ 期間: " . $rows[0][0] . " ～ " . $rows[count($rows) - 1][0]);
        $success = true;
    }
}

if (!$isCli):
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>日経225 データダウンロード</title>
<style>
  body { background:#0d1117; color:#e6edf3; font-family:monospace; padding:24px; }
  h1 { color:#58a6ff; margin-bottom:16px; }
  pre { background:#161b22; border:1px solid #30363d; padding:16px; border-radius:8px; line-height:1.6; }
  .ok  { color:#3fb950; }
  .err { color:#f85149; }
  a { color:#58a6ff; }
</style>
</head>
<body>
<h1>日経225 データダウンローダー</h1>
<pre><?php
foreach ($log as $line) {
    $line = htmlspecialchars($line);
    if (str_starts_with(trim($line), '✓')) {
        echo "<span class='ok'>$line</span>\n";
    } elseif (str_starts_with(trim($line), 'エラー')) {
        echo "<span class='err'>$line</span>\n";
    } else {
        echo "$line\n";
    }
}
?></pre>
<?php if ($success): ?>
<p class="ok">✓ <a href="index.php">チャートページ（index.php）へ戻る</a></p>
<?php else: ?>
<p class="err">データ取得に失敗しました。手動でCSVをダウンロードして <code>data/nikkei225.csv</code> に配置してください。</p>
<h2 style="color:#ffd700;margin-top:16px">手動ダウンロード方法</h2>
<pre>
1. https://finance.yahoo.com/quote/%5EN225/history/ を開く
2. 「Max」を選択して全期間のデータを表示
3. 「Download」ボタンでCSVをダウンロード
4. ダウンロードしたファイルを data/nikkei225.csv として保存
</pre>
<?php endif; ?>
</body>
</html>
<?php
endif;
