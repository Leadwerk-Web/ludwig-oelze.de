import glob
import re

app_link = '                        <a href="https://meine-finanzen.digital/login" target="_blank">Kunden-App Login</a>'

for f in glob.glob('*.html'):
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    if 'meine-finanzen.digital/login' in content:
        continue
        
    # Find the footer-links div under the "Navigation" title and add it at the end of the div
    # Match: <a href="expats.html">Expats</a>\s*</div>
    
    match = re.search(r'(<a href="expats\.html">Expats</a>\s*)(</div>)', content)
    if match:
        new_content = content[:match.start(2)] + app_link + '\n                    ' + content[match.start(2):]
        with open(f, 'w', encoding='utf-8') as out:
            out.write(new_content)
        print(f'Added app link to {f}')
    else:
        print(f'Could not find insertion point in {f}')
