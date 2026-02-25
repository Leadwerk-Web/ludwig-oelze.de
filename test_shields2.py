import re

with open('assets/images/qt_q_95.svg', 'r') as f:
    orig = f.read()

shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
shield_path = shield_match.group(1)

parts = re.split(r'(?=[Mm])', shield_path)
parts = [p.strip() for p in parts if p.strip()]

# Convert relative parts to absolute
part0 = parts[0] # shield (M2200 5100...) => Ends at M2200 5100 because of 'z'

# Part 1: m-446 -336
# 2200-446 = 1754, 5100-336 = 4764
part1 = f"M1754 4764 " + parts[1][10:] # Inner shield or Eagle?

# Part 2: m1327 -1990
# 1754+1327 = 3081, 4764-1990 = 2774
part2 = f"M3081 2774 " + parts[2][11:] # Eagle or Eye?

template = """<?xml version="1.0" standalone="no"?>
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="1152.000000pt" height="648.000000pt" viewBox="0 0 1152.000000 648.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,648.000000) scale(0.100000,-0.100000)" stroke="none">
<path fill="red" d="{P0}"/>
<path fill="blue" d="{P1}"/>
<path fill="green" d="{P2}"/>
</g>
</svg>
"""

res = template.format(P0=part0, P1=part1, P2=part2)
with open('assets/images/test_shield.svg', 'w') as f:
    f.write(res)
print("Generated test_shield.svg")
