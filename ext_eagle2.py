import re

# Read qt_q_95 which has the original eagle
with open('assets/images/qt_q_95.svg', 'r') as f:
    text = f.read()

# Original shield+eagle path starts at M2200 5100 -> the whole shield.
# Wait, look at qt_q_95.svg line 72:
# <path fill="#1a4d4d" d="M2200 5100 c-257 -16 -802 -79 ...
# -618 72 -214 19 -948 28 -1165 15z m-446 -336 c282 -189 474 -297 671 -377 ...
# The 'm-446 -336' starts the eagle which is cut out of the shield!
# So the shield and eagle are ONE path.
# Because it uses fill-rule="evenodd" (or default non-zero, but since they are opposite directions, it forms a hole).
# Wait, in the original qt_q_95.svg, that path is:
# <path fill="#1a4d4d" d="M2200...
# Let's extract exactly that entire path! That's the shield WITH the eagle cutout!

shield_eagle_match = re.search(r'<path fill="#1a4d4d" d="(M2200 5100.*?z.*?z.*?z)"/>', text, re.DOTALL)
if not shield_eagle_match:
    # maybe it ends with just one z? Let's match from M2200 to the very end of that path string
    shield_eagle_match = re.search(r'M2200 5100.*?(?=")', text, re.DOTALL)

shield_eagle_path = shield_eagle_match.group(0)

# Now extract the text parts (the rest of the paths)
# In qt_q_95.svg:
# Bird is actually the shield cutout. Wait!
# The user said "the bird in logo.svg is white and shield is white".
# Ah! In my previous build_logos.py, I used `bird_part` which was M3025 5260. That is NOT the bird! That is the TEXT "Ludwig Oelze"!
# And M2200 5100 is the SHIELD + EAGLE!

# Let's verify M3025:
# M3025 5260 ... is the text!
# My previous script mistook the text for the bird, and the shield for the shield.

# Let's rewrite build_logos.py completely.
text_match = re.search(r'M3025 5260.*?(?=")', text, re.DOTALL)
text_path = text_match.group(0)

bird_hole_match = re.search(r'M3430 4374.*?(?=")', text, re.DOTALL)
d_hole_match = re.search(r'M6779 4524.*?(?=")', text, re.DOTALL)
o_hole_match = re.search(r'M5217 3090.*?(?=")', text, re.DOTALL)

# The text and its holes should be combined into one path with evenodd
text_combined = f"{text_path} {bird_hole_match.group(0)} {d_hole_match.group(0)} {o_hole_match.group(0)}"

template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="1152.000000pt" height="648.000000pt" viewBox="0 0 1152.000000 648.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,648.000000) scale(0.100000,-0.100000)" stroke="none">
<path fill="{SHIELD_COLOR}" fill-rule="evenodd" d="{SHIELD_EAGLE_PATH}"/>
<path fill="{TEXT_COLOR}" fill-rule="evenodd" d="{TEXT_COMBINED}"/>
</g>
</svg>
"""

def save_logo(filename, shield_color, text_color):
    res = template.format(
        SHIELD_COLOR=shield_color,
        SHIELD_EAGLE_PATH=shield_eagle_path,
        TEXT_COLOR=text_color,
        TEXT_COMBINED=text_combined
    )
    with open(filename, 'w') as f:
        f.write(res)
    print(f"Generated {filename}")

# Wait, if shield_eagle_path contains both shield and eagle as one path, then the eagle will be a HOLE in the shield.
# So if shield is green, eagle is transparent (background shows through).
# But wait, the user said "Kalkan yeşil, içindeki kartal beyaz" (Shield green, eagle white).
# If the eagle is a hole, then it will be transparent, not white!
# Unless we put a white background behind the shield, or we separate the eagle!

# The user's qt_q_95.svg structure:
# It had a white background rect: <rect width="100%" height="100%" fill="#ffffff" />
# Then a transparent fill for text?
# Actually, qt_q_95.svg original had fill="#000000" (black) before I modified it.
pass
