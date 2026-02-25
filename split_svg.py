import re

def process_svg(filepath, text_fill, icon_fill):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the big path that contains both bird and text
    match = re.search(r'<path fill-rule="evenodd" d="(.*?)"/>', content, re.DOTALL)
    if not match: return
    
    d = match.group(1)
    
    # We need to split d into components.
    # It starts with M or m, so we can split by (?=[Mm])
    parts = re.split(r'(?=[Mm])', d)
    
    icon_parts = []
    text_parts = []
    
    abs_x = 0
    abs_y = 0
    for part in parts:
        if not part.strip(): continue
        
        coords = re.findall(r'[-+]?\d+', part)
        if coords:
            dx, dy = int(coords[0]), int(coords[1])
            if part.strip().startswith('M'):
                abs_x, abs_y = dx, dy
            else:
                abs_x += dx
                abs_y += dy
        
        if abs_x > 4500:
            text_parts.append(part)
        else:
            icon_parts.append(part)
            
    # Now we need to make sure the text_parts start with absolute M if the first one is m.
    # Wait, if we separate them, the relative 'm' at the beginning of text_parts needs to be converted to absolute 'M'.
    # Actually, we know the first text_part is Part 1 "m5176 -665".
    # Since it's relative to the end of Part 0, what is the end of Part 0?
    # It's better to convert all M/m to absolute commands before splitting!
    import svg.path # not available, let's just do it directly.
    pass

# Instead of complex math, we know:
# Part 0 is Bird. It starts with M3025 5260 ... and ends at X0, Y0 (where it closes).
# When z is called, it returns to the start of the subpath. So after z, the pen is at 3025, 5260.
# Then Part 1 is m5176 -665. So it starts at 3025+5176 = 8201, 5260-665 = 4595.
# If we replace "m5176 -665" with "M8201 4595", then it's absolute!
# The rest of text parts are relative to each other, which is fine as long as they stay together in one path.
# So:
# bird_path = Part 0 (M3025...) + all M... that have abs_x < 4500.
# text_path = M8201 4595 + rest of Part 1 + Part 2...11 + all M... that have abs_x > 4500.

def split_and_save(filepath, text_fill, icon_fill):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    match = re.search(r'<path fill-rule="evenodd" d="(.*?)"/>', content, re.DOTALL)
    if not match: return
    d = match.group(1)
    
    parts = re.split(r'(?=[Mm])', d)
    
    bird_d = ""
    text_d = ""
    
    abs_x = 0
    for i, part in enumerate(parts):
        if not part.strip(): continue
        coords = re.findall(r'[-+]?\d+', part)
        dx, dy = int(coords[0]), int(coords[1])
        
        if part.strip().startswith('M'):
            abs_x = dx
            if abs_x < 4500:
                bird_d += part
            else:
                text_d += part
        else: # 'm'
            if i == 1:
                # This is the start of text (m5176 -665)
                # We convert it to M8201 4595
                assert part.startswith('m5176 -665')
                part = part.replace('m5176 -665', 'M8201 4595', 1)
                text_d += part
            else:
                # other text pieces
                text_d += part

    new_paths = f'<path fill-rule="evenodd" fill="{icon_fill}" d="{bird_d}"/>\n<path fill-rule="evenodd" fill="{text_fill}" d="{text_d}"/>'
    
    # replace the old <path> with the two new <paths>
    content = content.replace(match.group(0), new_paths)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("Processing logo.svg: logo text green, icon white")
# In logo.svg: the shield is #1a4d4d, bird was white. Text should be green. So text_fill="#1a4d4d", icon_fill="#ffffff"
split_and_save('assets/images/logo.svg', text_fill="#1a4d4d", icon_fill="#ffffff")

print("Processing logo-inverted.svg: logo text white, icon green")
# In logo-inverted.svg: the shield is #ffffff, bird was green. Text should be white. So text_fill="#ffffff", icon_fill="#1a4d4d"
split_and_save('assets/images/logo-inverted.svg', text_fill="#ffffff", icon_fill="#1a4d4d")

print("Processing favicon.svg")
split_and_save('assets/images/favicon.svg', text_fill="#1a4d4d", icon_fill="#ffffff")
