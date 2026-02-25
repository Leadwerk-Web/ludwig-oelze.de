import re

with open('assets/images/qt_q_95.svg', 'r') as f:
    text = f.read()

# Extract shield from qt_q_95
shield_match = re.search(r'M2200 5100.*?z', text, re.DOTALL)
shield_path = shield_match.group(0) if shield_match else ""

# Wait, there's another path inside the shield in qt_q_95:
# looking at qt_q_95.svg from line 76:
# m-446 -336 c282 -189 474 -297 671 -377 ...
# This IS the eagle!
# The shield is the first part M2200 5100 ... -1165 15z
# And then right after it is m-446 -336 ...
# Let's extract them correctly.

paths = re.split(r'(?=[Mm])', shield_path)
real_shield = paths[0]
real_eagle = "".join(paths[1:])

# But the eagle is currently relative to the end of the shield, but part of the same path with fill="#1a4d4d"!
# If we separate it, we must change m-446 -336 to absolute coordinate to make it a standalone path.
pass
