import os
import re
from collections import defaultdict

SKIP_DIRS = {'受信添付', '送信添付', '添付'}
mail_dir = r'C:\e\data\HidemaruMail\MailBox'

def get_message_id(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            for line in f:
                line = line.strip()
                if line.lower().startswith('message-id:'):
                    return line.split(':', 1)[1].strip()
                if line == '':
                    break  # ヘッダー部分の終わり
    except Exception:
        pass
    return None

id_map = defaultdict(list)

print("スキャン中...")
for root, dirs, files in os.walk(mail_dir):
    dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
    for fname in files:
        if fname.endswith('.txt'):
            fpath = os.path.join(root, fname)
            mid = get_message_id(fpath)
            if mid:
                id_map[mid].append(fpath)

duplicates = {mid: paths for mid, paths in id_map.items() if len(paths) > 1}

total = sum(len(v) - 1 for v in duplicates.values())
print(f"重複メール数: {total} 件")

if total == 0:
    print("重複はありません。")
else:
    for mid, paths in list(duplicates.items())[:5]:
        print(f"\n  [例] {mid}")
        for p in paths:
            print(f"    {p}")

    ans = input(f"\n{total} 件の重複ファイルを削除しますか？ (yes/no): ")
    if ans.strip().lower() == 'yes':
        deleted = 0
        for mid, paths in duplicates.items():
            for fpath in paths[1:]:
                try:
                    os.remove(fpath)
                    deleted += 1
                except Exception as e:
                    print(f"エラー: {fpath} - {e}")
        print(f"\n完了: {deleted} 件削除しました。")
    else:
        print("削除をキャンセルしました。")
