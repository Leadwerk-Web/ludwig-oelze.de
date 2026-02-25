import re

with open('assets/images/qt_q_95.svg', 'r', encoding='utf-8') as f:
    text = f.read()

# Original bird path starts at M3025
bird_outer_match = re.search(r'M3025 5260.*?l434 0 7 -141z', text, re.DOTALL)
bird_outer = bird_outer_match.group(0) if bird_outer_match else ""

parts = [p for p in re.split(r'(?=[Mm])', bird_outer) if p.strip()]

if len(parts) >= 2:
    bird_part = parts[0] # M3025 ... z
    text_parts = parts[1:] # m5176 ... z etc
else:
    bird_part = bird_outer
    text_parts = []

if text_parts and text_parts[0].strip().startswith('m5176 -665'):
    text_parts[0] = text_parts[0].replace('m5176 -665', 'M8201 4595', 1)

text_outer = "".join(text_parts)

shield_match = re.search(r'M2200 5100.*?z', text, re.DOTALL)
shield_path = shield_match.group(0) if shield_match else ""

bird_hole_match = re.search(r'M3430 4374.*?z', text, re.DOTALL)
bird_hole_path = bird_hole_match.group(0) if bird_hole_match else ""

d_hole_match = re.search(r'M6779 4524.*?z', text, re.DOTALL)
d_hole_path = d_hole_match.group(0) if d_hole_match else ""

o_hole_match = re.search(r'M5217 3090.*?z', text, re.DOTALL)
o_hole_path = o_hole_match.group(0) if o_hole_match else ""

template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="{WIDTH}pt" height="{HEIGHT}pt" viewBox="{VIEWBOX}"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate({TRANSX},{TRANSY}) scale(0.100000,-0.100000)" stroke="none">
<path fill="{SHIELD_COLOR}" d="{SHIELD_PATH}"/>
<path fill-rule="evenodd" fill="{BIRD_COLOR}" d="{BIRD_OUTER} {BIRD_HOLE}"/>
{TEXT_SECTION}
</g>
</svg>
"""

def save_logo(filename, shield_color, bird_color, text_color=None, width=1152, height=648, viewbox="0 0 1152 648", transx=0, transy=648):
    if text_color is None:
        text_section = ""
    else:
        text_section = f'<path fill-rule="evenodd" fill="{text_color}" d="{text_outer} {d_hole_path} {o_hole_path}"/>'
        
    res = template.format(
        SHIELD_COLOR=shield_color,
        SHIELD_PATH=shield_path,
        BIRD_COLOR=bird_color,
        BIRD_OUTER=bird_part,
        BIRD_HOLE=bird_hole_path,
        TEXT_SECTION=text_section,
        WIDTH=width,
        HEIGHT=height,
        VIEWBOX=viewbox,
        TRANSX=transx,
        TRANSY=transy
    )
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(res)
    print(f"Generated {filename}")

save_logo('assets/images/logo.svg', '#1a4d4d', '#ffffff', '#1a4d4d')
save_logo('assets/images/logo-inverted.svg', '#ffffff', '#1a4d4d', '#ffffff')

# favicon: Just shield and bird. But centered nicely maybe?
# The shield bounding box in original logo is approx:
# M2200 5100 ... lower point Y goes around 1000 or so. 
# Shield X from 2200 to 2200+1300 = 3500? Actually, we don't necessarily have to crop viewBox to shape unless needed. 
# Browsers resize SVGs ok as long as preserveAspectRatio="xMidYMid meet" is there, but a huge transparent right side is bad for favicon.
# Wait, let's look at analyze_svg_coords.py
# Part 0: X=3025, Y=5260 (Bird)
# Part 12: X=3430, Y=4374 (Bird hole)
# Shield: M2200 5100.
# The shield is essentially the leftmost part. Let's make the viewbox for favicon around:
# Left margin: approx 1000
# Width: approx 3500-1000 = 2500 in those path units (which scale by 0.1).
# 1152 width * 10 = 11520. 
# Actually, viewBox="0 0 450 648"
save_logo('assets/images/favicon.svg', '#1a4d4d', '#ffffff', width=450, height=648, viewbox="0 0 450.000 648.000000")
