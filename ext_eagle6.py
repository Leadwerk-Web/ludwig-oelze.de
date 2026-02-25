import re

with open('assets/images/qt_q_95.svg', 'r') as f:
    orig = f.read()

shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
shield_path = shield_match.group(1)

parts = re.split(r'(?=[Mm])', shield_path)
parts = [p.strip() for p in parts if p.strip()]

outer_shield_only = parts[0]
inner_shield_start = f"M1754 4764 " + parts[1][10:]
eagle_start = f"M3081 2774 " + parts[2][11:]

text_match = re.search(r'd="(M3025 5260.*?)"', orig, re.DOTALL)
text_combined = f"{text_match.group(1)} {re.search(r'd=\"(M3430 4374.*?)\"', orig, re.DOTALL).group(1)} {re.search(r'd=\"(M6779 4524.*?)\"', orig, re.DOTALL).group(1)} {re.search(r'd=\"(M5217 3090.*?)\"', orig, re.DOTALL).group(1)}"

# The favicon works. How did favicon work?
# favicon.svg had:
# OUTER_SHIELD_COLOR='#ffffff'
# INNER_SHIELD_COLOR='#1a4d4d'
# OUTER_COMBINED = Outer + Inner -> White frame with Inner hole. (Inner transparent)
# INNER_COMBINED = Inner + Eagle -> Green shield with Eagle hole. (Eagle transparent, showing White background? No, showing nothing, just website background).
# Wait, user said favicon "olmuş" (is done), meaning they LIKE that the eagle is transparent in the favicon!
# Wait! In the audio they said "sadece favicon olmuş, favicon dosyasındaki yapıyı logo-inverted'daki kalkanla değiştir ve beyaz göz ekle" (only favicon worked, replace the shield in logo-inverted with the favicon's structure, and add a white eye).

# The eagle's eye was transparent because it was cut out of the eagle, which was cut out of the inner shield.
# Wait, the eagle is part 2. Is there a part 3 for the eye?
# Looking closely:
# parts[0]: outer shield
# parts[1]: inner shield
# parts[2]: eagle
# Let's count parts in `parts`:
print(len(parts))
# Wait, the eagle has an eye cutout? Let's check parts length.
if len(parts) > 3:
    eye_start = f"M... " + parts[3][...]
else:
    print("NO EYE PART")

template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="1152.000000pt" height="648.000000pt" viewBox="0 0 1152.000000 648.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0,648) scale(0.100000,-0.100000)" stroke="none">

<!-- Outer Frame -->
<path fill-rule="evenodd" fill="{OUTER_SHIELD_COLOR}" d="{OUTER_COMBINED}"/>

<!-- Inner Frame -->
<path fill-rule="evenodd" fill="{INNER_SHIELD_COLOR}" d="{INNER_COMBINED}"/>

<!-- Eye shape -->
{EYE_SECTION}

<!-- Text -->
{TEXT_SECTION}
</g>
</svg>
"""

# In logo-inverted.svg:
# The user wants "favicon's structure".
# Favicon: Outer #ffffff, Inner #1a4d4d.
# Text: White (#ffffff).
# They additionally want a "beyaz göz" (white eye) for the bird.

# The eagle is parts[2]. The eye is a hole IN the eagle?
# No, if there is an eye, it would be another path. Let's see if parts[2] contains an eye (another 'M'/'m' inside?).
# Since I split by 'M'/'m', the eye would be parts[3]. Let's check if there is a parts[3].
