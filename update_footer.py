import glob
import re

instagram_html = '''                        <a href="https://www.instagram.com/ludwig_finanzmakler" aria-label="Instagram" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>'''

facebook_html = '''                        <a href="https://www.facebook.com/107601304793660" aria-label="Facebook" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>'''

new_address_html = '''                        <div class="footer-contact-item" style="align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" style="margin-top: 4px; flex-shrink: 0;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span><strong style="font-weight: 600; font-size: 0.9em;">Post- & Geschäftsanschrift:</strong><br>Bismarckstr. 26, 76530 Baden-Baden</span>
                        </div>
                        <div class="footer-contact-item" style="align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" style="margin-top: 4px; flex-shrink: 0;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span><strong style="font-weight: 600; font-size: 0.9em;">Büroanschrift für Termine:</strong><br>Lange Str. 75, 76530 Baden-Baden</span>
                        </div>'''

for f in glob.glob('*.html'):
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    dirty = False
    
    # Check social links
    if 'instagram.com/ludwig_finanzmakler' not in content:
        match = re.search(r'(<a href="https://linkedin\.com"[^>]*>.*?</a>)', content, re.DOTALL)
        if match:
            content = content.replace(match.group(1), match.group(1) + '\n' + instagram_html + '\n' + facebook_html)
            dirty = True
            
    # Check address in footer
    match_address = re.search(r'(<div class="footer-contact-item">\s*<svg[^>]*>.*?<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />.*?<circle cx="12" cy="10" r="3" />.*?</svg>\s*<span> Rheinstraße 80<br>76532 Baden-Baden </span>\s*</div>)', content, re.DOTALL | re.IGNORECASE)
    
    # More robust match for the old address
    match_addr2 = re.search(r'(<div class="footer-contact-item">\s*<svg[^>]*>.*?<circle cx="12" cy="10" r="3" />\s*</svg>\s*<span>Rheinstraße 80<br>76532 Baden-Baden</span>\s*</div>)', content, re.DOTALL)
    
    if match_addr2:
        content = content.replace(match_addr2.group(1), new_address_html)
        dirty = True
        
    if dirty:
        with open(f, 'w', encoding='utf-8') as out:
            out.write(content)
        print(f'Updated footer in {f}')
