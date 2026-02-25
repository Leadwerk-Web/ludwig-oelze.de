import re

with open('assets/images/logo.svg', 'r', encoding='utf-8') as f:
    content = f.read()

match = re.search(r'<path fill-rule="evenodd" d="(.*?)"/>', content, re.DOTALL)
if match:
    d = match.group(1)
    parts = re.findall(r'[Mm][^Mm]*', d)
    
    abs_x, abs_y = 0, 0
    for i, part in enumerate(parts):
        # find the first coordinate
        coords = re.findall(r'[-+]?\d+', part)
        if not coords: continue
        dx, dy = int(coords[0]), int(coords[1])
        if part.strip().startswith('M'):
            abs_x, abs_y = dx, dy
        else:
            abs_x += dx
            abs_y += dy
        
        print(f"Part {i}: X={abs_x}, Y={abs_y}, length {len(part)}, cmd {part[:20].strip()}")
