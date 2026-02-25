import re

with open('assets/images/favicon.svg', 'r') as f:
    text = f.read()

paths = re.findall(r'd="([^"]+)"', text)
min_x, max_x = float('inf'), float('-inf')
min_y, max_y = float('inf'), float('-inf')

for p in paths:
    # Just grab all numbers and assume they are mostly coords (there may be bezier commands but let's just get the min/max)
    # The commands mix lengths, but the absolute M commands give a good hint.
    # Actually, svgpaths are complex. 
    pass

# We roughly know the shield X ranges from 1200 to 3500 according to analysis.
# Wait, X=2200, Y=5100.
# Let's adjust viewBox by hand and visually see. 
# viewBox="min-x min-y width height"
# Let's try viewBox="130 130 320 400" (scaled coords)
text = text.replace('viewBox="0 0 450.000 648.000000"', 'viewBox="135 120 310 400"')
with open('assets/images/favicon.svg', 'w') as f:
    f.write(text)
# Actually, the SVG was originally width="1152" and height="648", viewBox="0 0 1152 648".
# So the shield is at X ~ 135 to 445. (width 310)
# Y is ~ 120 to 520 (height 400).
