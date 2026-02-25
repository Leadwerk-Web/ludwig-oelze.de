import re

def get_bounding_box(path_str):
    # Extract all numbers
    coords = re.findall(r'[-+]?\d*\.\d+|[-+]?\d+', path_str)
    # The first number after M is X, then Y
    # It's a bit complex because it has relative and absolute commands
    # But for a rough check, let's just find the max/min of absolute X (usually after M)
    m_match = re.search(r'M\s*([-+]?\d+)', path_str)
    if m_match:
        return int(m_match.group(1))
    return 0

with open('assets/images/logo.svg', 'r', encoding='utf-8') as f:
    content = f.read()

# find the big path
match = re.search(r'<path fill-rule="evenodd" d="(.*?)"/>', content, re.DOTALL)
if match:
    d = match.group(1)
    # Split by 'm' or 'M'
    # Actually, svg paths can have 'm' relative. 
    # It's safer to just split by 'M' and 'm', but 'm' is relative.
    # The first text says: M3025 5260... z m5176 -665... z m1857 66... z m-4988 -13... z m740 -2... z m1330 -38... z m2100 43... z m-3833 -1421... z m1571 -172... z m697 142... z m1535 4... z m1024 -135... z M3430 4374 M6779 4524 M5217 3090
    
    parts = re.split(r'(?=[Mm])', d)
    for i, part in enumerate(parts):
        if not part.strip(): continue
        print(f"Part {i}: length {len(part)}, starts with {part[:20]}")
