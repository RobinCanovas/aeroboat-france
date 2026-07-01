# Clés de langue attendues par les vues publiques

`contenu-i18n` crée ces clés (fr + en) ; `front-design` les utilise telles quelles. Un besoin de clé supplémentaire se discute avec le mainteneur. `contenu-i18n` peut ajouter librement des sous-clés. `php tests/run.php` détecte toute clé utilisée mais absente.

Convention : liste = tableau indexé ; `{…}` = objet d'une liste. Les statuts sont `done | current | planned`.

- `meta.site_name`, `meta.tagline`, `meta.default_description`, `meta.test_banner`, `meta.title_suffix`
- `nav.home|project|team|partners|news|join|contact`, `nav.skip_to_content`, `nav.menu_open`, `nav.menu_close`, `nav.language_label`, `nav.main_label`, `nav.lang_fr`, `nav.lang_en`
- `common.read_more|back|send|loading|all|published_on|status.done|status.current|status.planned|close|prev|next|zoom`
- `home.hero.{badge,title,lead,cta_primary,cta_secondary}` ; `home.stats[{value,label}]` (4) ; `home.about.{kicker,title,text}` ; `home.cards[{title,text}]` (cartes numérotées, 3 à 4) ; `home.timeline.{title,lead,items[{date,title,text,status}]}` ; `home.partners.{title,lead,cta}` ; `home.news.{title,empty,cta}` ; `home.cta.{title,text,button}`
- `project.hero.{kicker,title,lead}` ; `project.why.{title,text,v3,v4,v6}` ; `project.objectives.{title,items[{title,text}]}` ; `project.v6.{title,lead,items[{title,text}],note_html}` (nouveautés V6 : empennage en T carbone, mousses étanches, étanchéité d'inspiration nautique, flotteurs à cavités… rien d'autre que la source) ; `project.electronics.{title,text}` (architecture à double calculateur) ; `project.timeline.{title,items[{date,title,text,status}]}` ; `project.gallery.{title,lead}`
- `team.hero.{title,lead}` ; `team.who.{title,text}` ; `team.role_default` (« Membre ») ; `team.photo_note`
- `partners.hero.{title,lead}` ; `partners.categories.{school,technology,support}` ; `partners.ionis.{title,text,badge}` (bloc Bourse IONIS) ; `partners.dx.{title,text,licenses_label,years_label,others_label}` ; `partners.become.{title,text,button}` ; `partners.visit`
- `news.hero.{title,lead}` ; `news.empty` ; `news.by` ; `news.back` ; `news.page_of`
- `join.hero.{title,lead}` ; `join.fields.{first_name,last_name,email,school,level,skills,motivation,availability,cv}` (chaque clé = libellé) ; `join.hints.{cv,motivation}` ; `join.consent_html` ; `join.submit` ; `join.sent.{title,text}` ; `join.steps[{title,text}]`
- `contact.hero.{title,lead}` ; `contact.fields.{name,email,subject,body}` ; `contact.consent_html` ; `contact.submit` ; `contact.sent.{title,text}` ; `contact.info.{title,email_label,phone_label,city_label}`
- `rights.hero.{title,lead}` ; `rights.fields.{name,email,request_type,body}` ; `rights.types.{access,rectify,erase,object,portability}` ; `rights.consent_html` ; `rights.submit` ; `rights.sent.{title,text}` ; `rights.delay_note`
- `forms.errors.{required,email,too_long,consent,too_fast,rate_limit,csrf,pdf_invalid,pdf_too_large,generic}` ; `forms.honeypot_label`
- `footer.about.{title,text}` ; `footer.nav.title` ; `footer.legal.{title,legal,privacy,cookies,rights}` ; `footer.contact.{title,text}` ; `footer.social.{title,instagram,linkedin}` ; `footer.copyright` ; `footer.no_tracking`
- `legal.*` (mentions : éditeur, directeur de publication, hébergeur, propriété intellectuelle, responsabilité ; lignes dépendantes de `ORG_*` portent des libellés, les valeurs viennent de `$org`) ; `privacy.*` ; `cookies.*`
- `errors.404.{title,text,cta}` ; `errors.500.{title,text}` ; `errors.403.{title,text}`
- `img.<slug>` : texte alternatif de chaque image de `public/img/manifest.json` (slug exact, ex. `img.lake-alpine`)
