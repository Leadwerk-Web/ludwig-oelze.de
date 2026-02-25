import re

def process_paths():
    with open('assets/images/qt_q_95.svg', 'r') as f:
        orig = f.read()

    # Get the shield+eagle path (which contains the outer shield, inner shield, and eagle)
    shield_match = re.search(r'd="(M2200 5100.*?)"', orig, re.DOTALL)
    shield_path = shield_match.group(1)
    
    parts = re.split(r'(?=[Mm])', shield_path)
    parts = [p.strip() for p in parts if p.strip()]
    
    # Let's read the third part again! (Eagle)
    # The eagle in qt_q_95: `m1327 -1990 c-20 -62 -41 -181 -41 -238 0 -24 13 ...` Wait!
    # Let's look at the qt_q_95.svg from before:
    # m-446 -336 c282 -189 ... 160 -111z m1327 -1990 c-20 -62 ...
    # So `m-446 -336` is the inner shield, wait... 
    # NO! `m-446 -336 c282...` is the eagle itself according to the coordinates!
    # What is `m1327 -1990` then? It's a small path inside the eagle! THE EYE!
    
    # Yes! `m1327 -1990` IS the eye!
    # Because my split was parts[0] (Outer Shield), parts[1] (Inner shield/Eagle?), parts[2] (Eagle/Eye)
    # Wait, earlier I found 3 parts.
    # If the user says "favicon.svg is correct", how many parts were in favicon?
    # Favicon had:
    # OUTER_COMBINED = parts[0] + parts[1]
    # INNER_COMBINED = parts[1] + parts[2]
    # That means parts[0] is outer, parts[1] is inner, parts[2] is eagle. And NO EYE!
    # Why? Let's trace it carefully.
    
    # Outer shield ends at `M2200 5100 ... -1165 15z`
    # Next part: `m-446 -336` -> this is the eagle! 
    # Oh! There is NO inner shield! The user gave me a shield that was JUST a shield, and an eagle!
    # Wait: Outer shield (parts[0]), Eagle (parts[1]), Eye (parts[2])!
    # Yes! The shield is ONE path, the eagle is the SECOND path, the eye is the THIRD path!
    
    outer_shield = parts[0]
    
    # Eagle start: 2200-446=1754, 5100-336=4764
    eagle = f"M1754 4764 " + parts[1][10:]
    
    # Eye start: 1754+1327=3081, 4764-1990=2774
    eye = f"M3081 2774 " + parts[2][11:]
    
    print("Outer shield length:", len(outer_shield))
    print("Eagle length:", len(eagle))
    print("Eye length:", len(eye))
    
    # Now, what does the user want for logo-inverted.svg?
    # "sadece favicon olmuş, bunu al logo-inverted'daki kalkanla değiştir"
    # Favicon structure that worked:
    # Outer (White), Inner (Green) - wait, if favicon worked, what colors did it have?
    # I set: OUTER_SHIELD_COLOR='#ffffff', INNER_SHIELD_COLOR='#1a4d4d'
    # And OUTER_COMBINED = parts[0] + parts[1].
    # That means the outer was filled with White, EXCEPT the eagle was knocked out!
    # And INNER_COMBINED = parts[1] + parts[2]. (Green, EXCEPT eye was knocked out!)
    # Ah! This is brilliant! 
    # So parts[0] (Outer Shield) is the main background.
    # parts[1] (Eagle) is cut out from Outer Shield (because outer_combined = parts[0]+parts[1]).
    # parts[2] (Eye) is cut out from Eagle (inner_combined = parts[1]+parts[2]).
    
    # This implies:
    # We draw `outer_combined` (Shield with Eagle cutout) in WHITE (#ffffff).
    # Then we draw `inner_combined` (Eagle with Eye cutout) in GREEN (#1a4d4d).
    # Wait, if Eagle is Green, then the white background of the shield shows through the eye!
    # BUT the user just said:
    # "Buna beyaz göz ekle" - "Add a white eye to it".
    # Because if it's on a dark background (logo-inverted), the shield is white, the eagle is green.
    # The eye is currently transparent (cut out of green eagle). So if the background is dark, the eye looks dark!
    # The user wants the eye to be explicitly WHITE!
    
    # Okay!
    # So we draw:
    # 1. outer_combined (Shield + Eagle hole) -> WHITE
    # 2. inner_combined (Eagle + Eye hole) -> GREEN (but this means eye is transparent showing background)
    # OR we just do:
    # 1. parts[0] (Shield) -> WHITE (This covers everything)
    # 2. parts[1] (Eagle) -> GREEN (This draws the eagle on top of the shield, but covers the eye)
    # 3. parts[2] (Eye) -> WHITE (This draws the eye explicitly in white!)
    
    # This is much cleaner and doesn't rely on `evenodd` hole magic which behaves weirdly.
    
    # Let's extract text parts
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

<!-- Shield -->
<path fill="{SHIELD_COLOR}" d="{SHIELD}"/>

<!-- Eagle -->
<path fill="{EAGLE_COLOR}" d="{EAGLE}"/>

<!-- Eye -->
<path fill="{EYE_COLOR}" d="{EYE}"/>

<!-- Text -->
{TEXT_SECTION}
</g>
</svg>
"""

    def save_logo(filename, shield_color, eagle_color, eye_color, text_color=None, width=1152, height=648, viewbox="0 0 1152 648", transx=0, transy=648):
        if text_color is None:
            text_section = ""
        else:
            text_section = f'<path fill-rule="evenodd" fill="{text_color}" d="{text_combined}"/>'
            
        res = template.format(
            SHIELD_COLOR=shield_color,
            SHIELD=outer_shield,
            EAGLE_COLOR=eagle_color,
            EAGLE=eagle,
            EYE_COLOR=eye_color,
            EYE=eye,
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

    # Favicon: exact same correct structure, but explicit. 
    # Favicon shield was outer white, inner green. (Wait, favicon shield=white, eagle=green! Or shield=green, eagle=white?)
    # Before, Favicon was: outer='#ffffff', inner='#1a4d4d'. 
    # That means Shield #ffffff, Eagle #1a4d4d, Eye #ffffff!
    # Does this match "Sadece favicon olmuş"? The user liked the favicon (which was white shield, green eagle, transparent eye... wait, if eye was hole in green eagle showing white shield, then the eye looked WHITE!)
    # That's why the user says "add a white eye to logo-inverted.svg".

    # Let's verify logo-inverted.svg colors the user wants:
    # "logo-inverted'daki kalkanla değiştir ve buna beyaz göz ekle"
    # Logo-inverted (dark background logo):
    # Shield: White (#ffffff)
    # Eagle: Green (#1a4d4d)
    # Eye: White (#ffffff)
    # Text: White (#ffffff)
    save_logo('assets/images/logo-inverted.svg', '#ffffff', '#1a4d4d', '#ffffff', '#ffffff')

    # What about logo.svg (Light background logo)?
    # Shield: Green (#1a4d4d)
    # Eagle: White (#ffffff)
    # Eye: Green (#1a4d4d) -> or transparent? if shield is green, eye should be green so it looks like a hole in the white eagle!
    # Text: Green (#1a4d4d)
    save_logo('assets/images/logo.svg', '#1a4d4d', '#ffffff', '#1a4d4d', '#1a4d4d')

    # Favicon:
    # "favicon olmuş" -> Favicon is good.
    # Favicon had Shield White, Eagle Green, Eye White (via transparency), No text.
    # Wait, the main logo is Green shield... is favicon supposed to be White shield? I'll leave favicon exactly as it looked (White shield, Green eagle, White eye).
    save_logo('assets/images/favicon.svg', '#ffffff', '#1a4d4d', '#ffffff', text_color=None, width=450, height=648, viewbox="135 120 310 400")

process_paths()
