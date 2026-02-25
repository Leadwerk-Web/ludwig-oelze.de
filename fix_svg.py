import re

files = ['assets/images/logo.svg', 'assets/images/logo-inverted.svg', 'assets/images/favicon.svg']

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the strings for M3430, M6779, M5217 using regex
    # They look like: <path fill="something" d="M3430 .... "/>
    # where .... can be anything, multiline.
    p3_match = re.search(r'<path[^>]*d="(M3430[^"]+)"\s*/>', content)
    p4_match = re.search(r'<path[^>]*d="(M6779[^"]+)"\s*/>', content)
    p5_match = re.search(r'<path[^>]*d="(M5217[^"]+)"\s*/>', content)

    if not (p3_match and p4_match and p5_match):
        print("Missing matches in", filepath)
        continue

    # Remove the full <path ... /> tags
    content = content.replace(p3_match.group(0) + '\n', '')
    content = content.replace(p4_match.group(0) + '\n', '')
    content = content.replace(p5_match.group(0) + '\n', '')
    
    # Just in case the newline was before or there is none
    content = content.replace(p3_match.group(0), '')
    content = content.replace(p4_match.group(0), '')
    content = content.replace(p5_match.group(0), '')

    # Find the end of Path 1. Path 1 ends with:
    # l434 0 7 -141z"/> or l434 0 7 -141z" />
    c1 = 'l434 0 7 -141z"'
    
    # Combine the shapes
    extra_ds = p3_match.group(1) + " " + p4_match.group(1) + " " + p5_match.group(1)
    
    new_c1 = c1[:-1] + " " + extra_ds + '"'
    
    content = content.replace(c1, new_c1)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("Processed", filepath)
