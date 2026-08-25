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

## `carte-abidjan.svg` — création originale
Carte stylisée d'Abidjan (lagune Ébrié, quartiers, pins des livreurs),
dessinée pour ce projet. Aucune contrainte de licence.

## Remplacement
Déposer un fichier de même nom dans le même dossier suffit :
aucun chemin n'est codé en dur ailleurs que dans ces deux accesseurs
(`User::avatar_url`, `Medicine::image_src`).
