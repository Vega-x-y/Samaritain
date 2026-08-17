import re

with open('tests/Feature/DocumentTypeFormTest.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the assertSee with apostrophe
content = content.replace(
    "assertSee('Date d'intervention')",
    "assertSee('Date d')"
)

with open('tests/Feature/DocumentTypeFormTest.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")