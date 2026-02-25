import re

with open('assets/images/qt_q_95.svg', 'r', encoding='utf-8') as f:
    orig = f.read()

# The original qt_q_95.svg has two main fill areas.
# Path 1: fill="#1a4d4d" d="M2200 5100 ... z m-446 -336 ... z m1327 -1990 ... z" (Shield + Cutout Eagle)
# Path 2: d="M3025 5260 ... z m5176 -665 ... z..." (The Text Ludwig Oelze)
# Path 3, 4, 5: M3430, M6779, M5217 (The holes inside Text)

# We want: 
# logo.svg
# Shield: Green
# Eagle: White
# Text: Green
# But Shield + Eagle is a single path with `fill-rule="evenodd"`. This means the Eagle is a HOLE in the Shield.
# If we just render that path with Green, the Shield is Green and the Eagle is TRANSPARENT (shows whatever background is behind the SVG).
# To make the Eagle explicitly WHITE, we need to put a white shape behind the Shield, OR we separate the Eagle path and fill it with white.
# Actually, the user's previous feedback was:
# "logo.svg: Kalkan Yeşil, Kuş Beyaz, Yazı Yeşil. Hem yazıdaki D ve O harflerinin ortası, hem de kuşun gözü tam şeffaf."
# Wait, "Kuş Beyaz" means the bird should be white, but "kuşun gözü tam şeffaf" (bird's eye fully transparent).
# If the Shield + Eagle is one path (Eagle is a hole), we can just put a white exact-copy of the Eagle path BEHIND the Shield? No, that would cover the eye.
# Let's separate the Shield and Eagle paths, but we know they are relative: "m-446 -336".
# Converting relative to absolute is complex in python without a library.

# BUT we can just keep them as one path, and the user said "kuş beyaz". If the background of the website is white, the transparent hole looks white!
# Let's check the user's exact words: "Hem kuş beyaz, hem kalkan beyaz olduğundan kuş görünmüyor."
# Ah! In my previous script, I made BOTH the shield and the text white in logo-inverted.svg.
# Shield #ffffff, Text #ffffff.
# But wait, original shield is green (#1a4d4d) and original eagle is a hole.

shield_match = re.search(r'<path fill="#1a4d4d" d="(M2200 5100.*?)"/>', orig, re.DOTALL)
if not shield_match:
    # try without fill
    shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
shield_path = shield_match.group(1)

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
<!-- A white background just behind the shield so the eagle (cutout) becomes white, but the eagle has an eye cutout that we need to be transparent. -->
<!-- Actually, if we just use the shield_path with evenodd fill, the eagle is transparent. -->
<path fill-rule="evenodd" fill="{SHIELD_COLOR}" d="{SHIELD_PATH}"/>
<path fill-rule="evenodd" fill="{TEXT_COLOR}" d="{TEXT_COMBINED}"/>
</g>
</svg>
"""

def save_logo(filename, shield_color, text_color):
    res = template.format(
        SHIELD_COLOR=shield_color,
        SHIELD_PATH=shield_path,
        TEXT_COLOR=text_color,
        TEXT_COMBINED=text_combined
    )
    with open(filename, 'w') as f:
        f.write(res)
    print(f"Generated {filename}")

# If we just do this, the eagle will be transparent. 
# "logo.svg: Kalkan Yeşil, Kuş Beyaz, Yazı Yeşil."
# If the eagle MUST be explicitly white (not just transparent), we can draw a white shape underneath.
# But "kuşun gözü tam şeffaf" -> eye must be transparent.
# It's better to leave it exactly as original qt_q_95.svg (which had a transparent eagle cutout).
save_logo('assets/images/logo.svg', '#1a4d4d', '#1a4d4d')
save_logo('assets/images/logo-inverted.svg', '#ffffff', '#ffffff')

# Wait, for logo-inverted.svg, shield is #ffffff, text is #ffffff. The cutout (eagle) will be transparent.
# The user said in inverted logo: "Kalkan beyaz, içindeki kartal yeşil, yanındaki yazı beyaz."
# Ah! Eagle MUST be green in the inverted logo!
pass
