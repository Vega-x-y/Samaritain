from pathlib import Path
import urllib.request

base = Path('public/images/parcelles')
images = {
    'mfilou-12a.png': 'https://placehold.co/600x320?text=Mfilou+12A',
    'mfilou-05b.png': 'https://placehold.co/600x320?text=Mfilou+05B',
    'bacongo-21c.png': 'https://placehold.co/600x320?text=Bacongo+21C',
    'bacongo-14d.png': 'https://placehold.co/600x320?text=Bacongo+14D',
    'poto-poto-33e.png': 'https://placehold.co/600x320?text=Poto-Poto+33E',
    'poto-poto-47f.png': 'https://placehold.co/600x320?text=Poto-Poto+47F',
    'mfilou-23j.png': 'https://placehold.co/600x320?text=Mfilou+23J',
    'bacongo-27k.png': 'https://placehold.co/600x320?text=Bacongo+27K',
}

base.mkdir(parents=True, exist_ok=True)

for name, url in images.items():
    path = base / name
    print(f'Downloading {name}...')
    urllib.request.urlretrieve(url, path)
print('done')
