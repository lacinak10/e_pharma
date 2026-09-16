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
| `logo.jpeg` | Lockup complet (symbole + « E.PHARMA MOBILE » + accroche), fourni par la marque. C'est **le** logo de l'application : en-tête boutique, pied de page, barre latérale, bandeau invité, favicon, icône iOS et carte de partage social. |
| `logo-mark.png` | La pastille seule : le symbole détouré du lockup, recadré sur fond blanc, 256 px. **Actuellement inutilisée** — l'interface affiche le lockup complet. |
| `logo-apple-touch.png` | La même pastille en 180 px. **Inutilisée** elle aussi. |

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

Les deux PNG restent disponibles si l'on veut un jour une marque compacte :
le lockup, chargé de texte, perd sa lisibilité en dessous de ~120 px.
Après remplacement d'un visuel, incrémenter `asset_version` dans `config/app.php`.

## `carte-abidjan.svg` — création originale
Carte stylisée d'Abidjan (lagune Ébrié, quartiers, pins des livreurs),
dessinée pour ce projet. Aucune contrainte de licence.

## Remplacement
Déposer un fichier de même nom dans le même dossier suffit :
aucun chemin n'est codé en dur ailleurs que dans ces deux accesseurs
(`User::avatar_url`, `Medicine::image_src`).
