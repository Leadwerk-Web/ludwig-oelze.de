import re

def process_paths():
    with open('assets/images/qt_q_95.svg', 'r') as f:
        orig = f.read()

    # Get the shield+eagle path (which contains the outer shield, inner shield, and eagle)
    shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
    shield_path = shield_match.group(1)
    
    parts = re.split(r'(?=[Mm])', shield_path)
    parts = [p.strip() for p in parts if p.strip()]
    
    # parts[0]: Outer shield (M2200)
    # parts[1]: Inner shield (m-446 -336)
    # parts[2]: Eagle (m1327 -1990)
    
    outer_shield_only = parts[0]
    
    # Inner shield start 2200-446=1754, 5100-336=4764
    inner_shield_start = f"M1754 4764 " + parts[1][10:]
    
    # Eagle start 1754+1327=3081, 4764-1990=2774
    eagle_start = f"M3081 2774 " + parts[2][11:]
    
    # Extract text parts
    text_match = re.search(r'd="(M3025 5260.*?)"', orig, re.DOTALL)
    text_path = text_match.group(1)
    bird_hole_match = re.search(r'd="(M3430 4374.*?)"', orig, re.DOTALL)
    d_hole_match = re.search(r'd="(M6779 4524.*?)"', orig, re.DOTALL)
    o_hole_match = re.search(r'd="(M5217 3090.*?)"', orig, re.DOTALL)
    text_combined = f"{text_path} {bird_hole_match.group(1)} {d_hole_match.group(1)} {o_hole_match.group(1)}"

    template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="{WIDTH}pt" height="{HEIGHT}pt" viewBox="{VIEWBOX}"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate({TRANSX},{TRANSY}) scale(0.100000,-0.100000)" stroke="none">

<!-- Outer Frame -->
<path fill-rule="evenodd" fill="{OUTER_SHIELD_COLOR}" d="{OUTER_COMBINED}"/>

<!-- Inner Frame -->
<path fill-rule="evenodd" fill="{INNER_SHIELD_COLOR}" d="{INNER_COMBINED}"/>

<!-- Text -->
{TEXT_SECTION}
</g>
</svg>
"""

    def save_logo(filename, outer_color, inner_color, eagle_color, text_color=None, width=1152, height=648, viewbox="0 0 1152 648", transx=0, transy=648):
        # We want the Outer Shield with an Inner Shield cutout.
        # So OUTER_COMBINED = Outer + Inner
        # Then we paint the Inner Shield with Eagle cutout.
        # So INNER_COMBINED = Inner + Eagle
        
        outer_combined = f"{outer_shield_only} {inner_shield_start}"
        inner_combined = f"{inner_shield_start} {eagle_start}"
        
        if text_color is None:
            text_section = ""
        else:
            text_section = f'<path fill-rule="evenodd" fill="{text_color}" d="{text_combined}"/>'
            
        res = template.format(
            OUTER_SHIELD_COLOR=outer_color,
            OUTER_COMBINED=outer_combined,
            INNER_SHIELD_COLOR=inner_color,
            INNER_COMBINED=inner_combined,
            TEXT_COLOR=text_color,
            TEXT_SECTION=text_section,
            WIDTH=width,
            HEIGHT=height,
            VIEWBOX=viewbox,
            TRANSX=transx,
            TRANSY=transy
        )
        with open(filename, 'w') as f:
            f.write(res)
        print(f"Generated {filename}")

    # For logo.svg (Light mode):  
    # Outer shield: White
    # Inner shield: Green (#1a4d4d)
    # Eagle: White -> Since eagle is cutout from inner shield, it will be transparent and show through. But is outer shield white behind the eagle? YES, because the eagle cutout is inside the inner shield, which is layered ON TOP of the outer shield (which does NOT have an eagle cutout, only an inner shield cutout).
    save_logo('assets/images/logo.svg', '#ffffff', '#1a4d4d', '#ffffff', '#1a4d4d')

    # For logo-inverted.svg (Dark mode): 
    # Outer shield: Transparent? Or White? Or Green? The user didn't specify dark mode's outer shield but usually it's White or Transparent.
    # Actually if dark mode: Inner shield is White, Eagle is Green.
    save_logo('assets/images/logo-inverted.svg', '#1a4d4d', '#ffffff', '#1a4d4d', '#ffffff')

    # Favicon
    save_logo('assets/images/favicon.svg', '#ffffff', '#1a4d4d', '#ffffff', width=450, height=648, viewbox="135 120 310 400")

process_paths()
