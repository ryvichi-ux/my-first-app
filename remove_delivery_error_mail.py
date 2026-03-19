import os

SKIP_DIRS = {'受信添付', '送信添付', '添付'}
mail_dir = r'C:\e\data\HidemaruMail\MailBox'

# 配信エラーメールと判定するキーワード（From/Subject/ヘッダー）
FROM_KEYWORDS = [
    'mailer-daemon',
    'mail delivery',
    'mail delivery system',
    'mail administrator',
    'postmaster',
    'delivery status notification',
    'delivery failure',
    'undeliverable',
    'auto-submitted',
]

SUBJECT_KEYWORDS = [
    'undeliverable',
    'undelivered mail',
    'delivery failure',
    'delivery status notification',
    'mail delivery failed',
    'mail delivery failure',
    'returned mail',
    'returned to sender',
    'failure notice',
    'delivery report',
    'mail system error',
    'non-delivery',
    'non delivery',
    '配信不能',
    '配信エラー',
    '送信失敗',
    'メール配信エラー',
    '配信不達',
]

def is_delivery_error(filepath):
    """ヘッダーを解析して配信エラーメールか判定する"""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            headers = {}
            last_key = None
            for line in f:
                if line == '\n' or line == '\r\n':
                    break  # ヘッダー終わり
                # 折り返し行（継続行）: 先頭が空白
                if line[0:1] in (' ', '\t') and last_key:
                    headers[last_key] += ' ' + line.strip().lower()
                    continue
                stripped = line.strip()
                if ':' in stripped:
                    key, _, val = stripped.partition(':')
                    last_key = key.strip().lower()
                    headers[last_key] = val.strip().lower()

            # From ヘッダーチェック
            from_val = headers.get('from', '')
            for kw in FROM_KEYWORDS:
                if kw in from_val:
                    return True

            # Subject ヘッダーチェック
            subject_val = headers.get('subject', '')
            for kw in SUBJECT_KEYWORDS:
                if kw in subject_val:
                    return True

            # Content-Type が delivery-status の場合
            ct = headers.get('content-type', '')
            if 'delivery-status' in ct or 'report-type=delivery-status' in ct:
                return True

            # Auto-Submitted ヘッダー（自動返信）
            auto = headers.get('auto-submitted', '')
            if auto and auto != 'no':
                # Fromも確認して誤検知を防ぐ
                for kw in FROM_KEYWORDS:
                    if kw in from_val:
                        return True

    except Exception:
        pass
    return False

print("配信エラーメールをスキャン中...")

targets = []
for root, dirs, files in os.walk(mail_dir):
    dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
    for fname in files:
        if fname.endswith('.txt'):
            fpath = os.path.join(root, fname)
            if is_delivery_error(fpath):
                targets.append(fpath)

total = len(targets)
print(f"配信エラーメール数: {total} 件")

if total == 0:
    print("対象メールは見つかりませんでした。")
else:
    print("\n--- 削除対象（最大20件表示） ---")
    for fpath in targets[:20]:
        print(f"  {fpath}")
    if total > 20:
        print(f"  ... 他 {total - 20} 件")

    print()
    ans = input(f"{total} 件の配信エラーメールを削除しますか？ (yes/no): ")
    if ans.strip().lower() == 'yes':
        deleted = 0
        errors = 0
        for fpath in targets:
            try:
                os.remove(fpath)
                deleted += 1
            except Exception as e:
                print(f"エラー: {fpath} - {e}")
                errors += 1
        print(f"\n完了: {deleted} 件削除しました。", end='')
        if errors:
            print(f"（{errors} 件はアクセス拒否）")
        else:
            print()
    else:
        print("削除をキャンセルしました。")
