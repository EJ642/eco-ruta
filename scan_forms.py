import pathlib, re
root = pathlib.Path('forms')
rows = []
for p in sorted(root.glob('*.php')):
    txt = p.read_text(encoding='utf-8', errors='ignore')
    view = bool(re.search(r'<html|<!DOCTYPE html|include __DIR__ .*includes/header.php|include __DIR__ .*includes/footer.php|include "\.\./includes/header.php"|include "\.\./includes/footer.php"|include \'\.\./includes/header.php\'|include \'\.\./includes/footer.php\'', txt))
    api = bool(re.search(r'\* API:|echo json_encode|header\(|session_start\(|session_name\(|json_encode\(', txt))
    rows.append((p.name, view, api))
print('name,view,api')
for name, view, api in rows:
    print(f'{name},{int(view)},{int(api)}')
