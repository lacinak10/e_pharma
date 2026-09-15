# Images du projet

Toutes les images embarquées sont **libres de droit**.

## `photos/` — Unsplash License
Usage commercial autorisé, sans attribution obligatoire.
<https://unsplash.com/license>

| Fichier | Sujet |
|---|---|
| `pharmacie.jpg` | Intérieur de pharmacie |
| `ordonnance.jpg` | Ordonnance / documents |
| `livraison.jpg` | Livreur à vélo |
| `medicaments.jpg`, `blister.jpg`, `comprimes.jpg`, `gelules.jpg` | Packshots médicaments (repli catalogue) |

## `avatars/` — Unsplash License
`av-1.jpg` … `av-8.jpg` : portraits utilisés comme photo de profil par défaut.
Attribués de façon déterministe via `User::getAvatarUrlAttribute()` (`id % 8`).

## Logo — propriété de la marque

| Fichier | Rôle |
|---|---|
| `logo.jpeg` | Lockup complet (symbole + « E.PHARMA MOBILE » + accroche), fourni par la marque. Sert à la carte de partage social (`og:image`, `twitter:image`). |
| `logo-mark.png` | La pastille seule : le symbole détouré du lockup, recadré sur fond blanc, 256 px. C'est la marque affichée dans l'interface (en-tête boutique, pied de page, barre latérale, bandeau invité) et la favicon. |
| `logo-apple-touch.png` | La même pastille en 180 px, pour l'icône d'écran d'accueil iOS. |

Les deux PNG sont **dérivés** de `logo.jpeg` — ils ne sont pas des originaux.
Pour les régénérer après un changement de logo :

```bash
# Le symbole occupe 84x95 px à partir de (208, 126) dans le lockup 500x500.
convert logo.jpeg -crop 84x95+208+126 +repage \
        -fuzz 15% -fill white -opaque "rgb(235,244,250)" \
        -background white -gravity center -extent 116x116 \
        -resize 256x256 -strip logo-mark.png
convert logo-mark.png -resize 180x180 -strip logo-apple-touch.png
```

Le lockup complet reste illisible en dessous de ~120 px : c'est pourquoi
l'interface et la favicon montrent la pastille, jamais `logo.jpeg`.
Après remplacement, incrémenter `asset_version` dans `config/app.php`.

## `carte-abidjan.svg` — création originale
Carte stylisée d'Abidjan (lagune Ébrié, quartiers, pins des livreurs),
dessinée pour ce projet. Aucune contrainte de licence.

## Remplacement
Déposer un fichier de même nom dans le même dossier suffit :
aucun chemin n'est codé en dur ailleurs que dans ces deux accesseurs
(`User::avatar_url`, `Medicine::image_src`).
