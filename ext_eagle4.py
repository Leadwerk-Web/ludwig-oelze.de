import re

def process_paths():
    with open('assets/images/qt_q_95.svg', 'r') as f:
        orig = f.read()

    # Get the shield+eagle path
    shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
    shield_path = shield_match.group(1)
    
    # We must properly split it into Shield, Eagle, and Eagle Eye.
    # The path starts with M2200 5100... then 'z'. This is the shield.
    # Then 'm-446 -336' starts the eagle.
    # Then another 'z'.
    # Then 'm1327 -1990' starts the eye.
    # Then 'z'.

    parts = re.split(r'(?=[Mm])', shield_path)
    parts = [p.strip() for p in parts if p.strip()]
    
    # parts[0]: shield (M)
    # parts[1]: eagle (m)
    # parts[2]: eye (m)
    
    shield_only = parts[0]
    
    # Convert eagle's relative M to absolute.
    # Shield ends at M2200 5100 (because it closes).
    # m-446 -336 -> M (2200-446) (5100-336) = M 1754 4764
    eagle_start = f"M1754 4764 " + parts[1][10:] # skip 'm-446 -336'
    
    # Eye is relative to eagle start? No, when z is called, it returns to the start of the current subpath!
    # Eagle subpath started at 1754, 4764.
    # Eye: 'm1327 -1990' -> 1754+1327=3081, 4764-1990=2774
    # Let's verify: M3081 2774
    eye_start = f"M3081 2774 " + parts[2][11:]
    
    eagle_combined = f"{eagle_start} {eye_start}"
    
    # Extract text parts
    text_match = re.search(r'd="(M3025 5260.*?)"', orig, re.DOTALL)
    text_path = text_match.group(1)
    bird_hole_match = re.search(r'd="(M3430 4374.*?)"', orig, re.DOTALL)
    d_hole_match = re.search(r'd="(M6779 4524.*?)"', orig, re.DOTALL)
    o_hole_match = re.search(r'd="(M5217 3090.*?)"', orig, re.DOTALL)
    text_combined = f"{text_path} {bird_hole_match.group(1)} {d_hole_match.group(1)} {o_hole_match.group(1)}"

    template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="1152.000000pt" height="648.000000pt" viewBox="0 0 1152.000000 648.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,648.000000) scale(0.100000,-0.100000)" stroke="none">
<path fill-rule="evenodd" fill="{SHIELD_COLOR}" d="{SHIELD_ONLY}"/>
<path fill-rule="evenodd" fill="{EAGLE_COLOR}" d="{EAGLE_COMBINED}"/>
<path fill-rule="evenodd" fill="{TEXT_COLOR}" d="{TEXT_COMBINED}"/>
</g>
</svg>
"""

    def save_logo(filename, shield_color, eagle_color, text_color):
        res = template.format(
            SHIELD_COLOR=shield_color,
            SHIELD_ONLY=shield_only,
            EAGLE_COLOR=eagle_color,
            EAGLE_COMBINED=eagle_combined,
            TEXT_COLOR=text_color,
            TEXT_COMBINED=text_combined
        )
        with open(filename, 'w') as f:
            f.write(res)
        print(f"Generated {filename}")

    # For logo.svg (Light mode):  
    # Shield Green, Eagle White, Text Green
    save_logo('assets/images/logo.svg', '#1a4d4d', '#ffffff', '#1a4d4d')

    # For logo-inverted.svg (Dark mode): 
    # Shield White (with transparency showing through), Eagle Green, Text White.
    # Wait, if Eagle is explicitly drawn with Green, the eye will be... Green too if we just combine it!
    # The eagle and eye are combined with evenodd. 
    # So the Eye is a Hole in the Eagle path! This perfectly makes the eye transparent and shows the background!
    save_logo('assets/images/logo-inverted.svg', '#ffffff', '#1a4d4d', '#ffffff')

process_paths()
