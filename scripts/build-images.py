#!/usr/bin/env python3
"""Génère les images optimisées (WebP) du site à partir des originaux.
Usage : python3 scripts/build-images.py <dossier AEROBOAT contenant les originaux> [dossier uploads WordPress]
Sortie : public/img/<slug>-<largeur>.webp  +  public/img/manifest.json
"""
import json, os, sys
from PIL import Image, ImageOps

SRC = sys.argv[1] if len(sys.argv) > 1 else '.'
UP = sys.argv[2] if len(sys.argv) > 2 else os.path.join(SRC, 'SiTE/_/www/wp-content/uploads')
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'public', 'img')
os.makedirs(OUT, exist_ok=True)
W = lambda n: 'WhatsApp Image 2025-10-26 at 12.55.%s.jpeg' % n

# slug -> (chemin source, largeurs, recadrage ratio l/h ou None)
PHOTOS = {
  'lake-alpine':   (os.path.join(SRC, W('52 (1)')), [800, 1400], None),
  'water-run':     (os.path.join(SRC, W('51')), [800, 1600], None),
  'water-skim':    (os.path.join(SRC, W('52 (2)')), [800, 1400], None),
  'hold-red':      (os.path.join(SRC, W('51 (2)')), [800, 1400], None),
  'lakeside-duo':  (os.path.join(SRC, W('52')), [800, 1400], None),
  'ribs-ground':   (os.path.join(SRC, W('53 (3)')), [800, 1400], None),
  'dusk-ground':   (os.path.join(SRC, W('55 (2)')), [800, 1400], None),
  'dusk-lake':     (os.path.join(SRC, W('55')), [800, 1400], None),
  'bench-build':   (os.path.join(SRC, W('56 (3)')), [800, 1400], None),
  'wing-closeup':  (os.path.join(SRC, W('56')), [800, 1400], None),
  'wing-pavement': (os.path.join(SRC, W('59')), [800, 1400], None),
  'cfd-streams':   (os.path.join(SRC, W('53 (2)')), [800, 1400], None),
  'cfd-color':     (os.path.join(SRC, W('57 (3)')), [800, 1400], None),
  'render-dark':   (os.path.join(SRC, W('54')), [800, 1400], None),
  'render-white':  (os.path.join(SRC, W('57 (1)')), [800, 1400], None),
  'render-blue':   (os.path.join(SRC, W('57 (2)')), [800, 1400], None),
  'team-build':    (os.path.join(SRC, W('49')), [800, 1400], None),
  'cgi-v-red':     (os.path.join(UP, '2025/09/imageprojet2a-2.png'), [800, 1400], None),
  'model-water':   (os.path.join(UP, '2025/09/imageprojet-1.jpg'), [800, 1400], None),
  'ribs-lawn':     (os.path.join(UP, '2025/11/imageprojet4-edited.jpg'), [800, 1400], None),
  'event-stand':   (os.path.join(UP, '2025/09/photo-equipe.jpg'), [800, 1080], None),
}
# portraits : recadrés 4:5, largeur 640
TEAM = {
  'team-clement':  os.path.join(UP, '2025/09/photoclement-scaled.jpg'),
  'team-joan':     os.path.join(UP, '2025/09/photojoan.jpeg'),
  'team-pierre':   os.path.join(UP, '2025/09/photo4-edited.jpg'),
  'team-kerian':   os.path.join(UP, '2025/09/photokerian.jpeg'),
  'team-mathis':   os.path.join(UP, '2025/10/photomathis.jpeg'),
  'team-benjamin': os.path.join(UP, '2025/10/img_5246.jpg'),
  'team-chislain': os.path.join(UP, '2025/10/image.png'),
  'team-robin':    os.path.join(UP, '2025/09/img_52251.jpg'),
}
LOGOS = {
  'logo':        os.path.join(UP, '2025/09/cropped-logoabf-without-bg-1.png'),
  'logo-white':  os.path.join(UP, '2025/09/chatgpt_image_16_sept._2025__09_43_42-removebg-preview.png'),
  'partner-3dx':   os.path.join(UP, '2025/10/3dexperience-platform-information.jpg'),
  'partner-ionis': os.path.join(UP, '2025/10/logo_ionis-education-group_quadri_2022.png'),
  'partner-uncb':  os.path.join(UP, '2025/10/images.jpg'),
}
manifest = {}
def save(im, slug, w, q=78):
    if im.width > w:
        im = im.resize((w, round(im.height * w / im.width)), Image.LANCZOS)
    p = os.path.join(OUT, f'{slug}-{w}.webp')
    im.save(p, 'WEBP', quality=q, method=6)
    manifest.setdefault(slug, {})[str(w)] = {'w': im.width, 'h': im.height, 'kb': os.path.getsize(p) // 1024}

for slug, (path, widths, _) in PHOTOS.items():
    im = ImageOps.exif_transpose(Image.open(path)).convert('RGB')
    for w in widths: save(im, slug, w)
for slug, path in TEAM.items():
    im = ImageOps.exif_transpose(Image.open(path)).convert('RGB')
    im = ImageOps.fit(im, (640, 800), Image.LANCZOS, centering=(0.5, 0.35))
    save(im, slug, 640, 82)
for slug, path in LOGOS.items():
    im = Image.open(path).convert('RGBA')
    save(im, slug, 512, 90)
# favicons / icônes
logo = Image.open(LOGOS['logo']).convert('RGBA')
for s in (32, 180, 512):
    logo.resize((s, s), Image.LANCZOS).save(os.path.join(OUT, f'icon-{s}.png'))
json.dump(manifest, open(os.path.join(OUT, 'manifest.json'), 'w'), indent=1)
print(len(manifest), 'images ->', OUT)
