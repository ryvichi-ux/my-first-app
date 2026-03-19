import os
import hashlib
from collections import defaultdict

def get_file_hash(filepath):
    h = hashlib.md5()
    try:
        with open(filepath, 'rb') as f:
            h.update(f.read())
        return h.hexdigest()
    except Exception:
        return None

mail_dir = r'C:\e\data\HidemaruMail\MailBox'

# 添付ファイルフォルダ名（スキャン対象外）
SKIP_DIRS = {'受信添付', '送信添付', '添付'}

hash_map = defaultdict(list)

print("スキャン中...")
for root, dirs, files in os.walk(mail_dir):
    # 添付フォルダはスキャンしない
    dirs[:] = [d for d in dirs if d not in SKIP_DIRS]

    for fname in files:
        if fname.endswith('.txt'):
            fpath = os.path.join(root, fname)
            h = get_file_hash(fpath)
            if h:
                hash_map[h].append(fpath)

duplicates = {h: paths for h, paths in hash_map.items() if len(paths) > 1}

total = sum(len(v) - 1 for v in duplicates.values())
print(f"重複メール数: {total} 件")

if total == 0:
    print("重複はありません。")
else:
    ans = input(f"\n{total} 件の重複ファイルを削除しますか？ (yes/no): ")
    if ans.strip().lower() == 'yes':
        deleted = 0
        for h, paths in duplicates.items():
            for fpath in paths[1:]:  # 最初の1件を残して削除
                try:
                    os.remove(fpath)
                    print(f"削除: {fpath}")
                    deleted += 1
                except Exception as e:
                    print(f"エラー: {fpath} - {e}")
        print(f"\n完了: {deleted} 件削除しました。")
    else:
        print("削除をキャンセルしました。")
