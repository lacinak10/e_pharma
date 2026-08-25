# Comptes de test — E-PHARMA

> Mot de passe commun à tous les comptes : **`password`**
>
> Source de vérité : `database/seeders/UserSeeder.php`.
> Pour (re)créer ces comptes : `php artisan db:seed --class=UserSeeder`
> (le seeder est idempotent — il met à jour les comptes existants au lieu de les dupliquer).

---

## Managers (3)

Accès complet au back-office : vérification de disponibilité, attribution des livreurs,
catalogue, pharmacies partenaires, statistiques, création d'utilisateurs.

| Nom             | Email                   | Mot de passe | Téléphone             | Zone         | Après login        |
|-----------------|-------------------------|--------------|-----------------------|--------------|--------------------|
| Koffi Assamoi   | manager@epharma.test    | password     | +225 27 22 00 00 00   | Plateau      | `/admin/dashboard` |
| Nadège Kouassi  | manager2@epharma.test   | password     | +225 27 22 00 00 21   | Cocody       | `/admin/dashboard` |
| Serge Kouadio   | manager3@epharma.test   | password     | +225 27 22 00 00 33   | Treichville  | `/admin/dashboard` |

---

## Livreurs (4)

Espace livreur uniquement : courses attribuées, acceptation/refus, avancement du statut.
Les coordonnées GPS servent au classement par distance dans l'écran d'attribution du manager.

| Nom           | Email                   | Mot de passe | Téléphone             | Zone      | Après login         |
|---------------|-------------------------|--------------|-----------------------|-----------|---------------------|
| Mamadou Koné  | livreur@epharma.test    | password     | +225 07 00 00 00 00   | Plateau   | `/admin/my-orders`  |
| Fatou Sylla   | livreur2@epharma.test   | password     | +225 07 00 00 00 12   | Adjamé    | `/admin/my-orders`  |
| Yao Brou      | livreur3@epharma.test   | password     | +225 07 00 00 00 31   | Yopougon  | `/admin/my-orders`  |
| Adjoua Tanoh  | livreur4@epharma.test   | password     | +225 07 00 00 00 44   | Marcory   | `/admin/my-orders`  |

---

## Clients (3)

Boutique publique : catalogue, panier, commande avec ou sans ordonnance, suivi et notation.

| Nom           | Email                  | Mot de passe | Téléphone             | Zone              | Après login   |
|---------------|------------------------|--------------|-----------------------|-------------------|---------------|
| Aïcha Diallo  | client@epharma.test    | password     | +225 07 00 00 00 02   | Cocody Angré      | `/` (boutique)|
| Ismaël Touré  | client2@epharma.test   | password     | +225 05 00 00 00 09   | Marcory           | `/` (boutique)|
| Awa Bamba     | client3@epharma.test   | password     | +225 01 00 00 00 17   | Yopougon Niangon  | `/` (boutique)|

---

## Récapitulatif

| Email                   | Mot de passe | Rôle    |
|-------------------------|--------------|---------|
| manager@epharma.test    | password     | manager |
| manager2@epharma.test   | password     | manager |
| manager3@epharma.test   | password     | manager |
| livreur@epharma.test    | password     | courier |
| livreur2@epharma.test   | password     | courier |
| livreur3@epharma.test   | password     | courier |
| livreur4@epharma.test   | password     | courier |
| client@epharma.test     | password     | client  |
| client2@epharma.test    | password     | client  |
| client3@epharma.test    | password     | client  |

---

*Pour créer d'autres comptes depuis l'interface : connectez-vous en manager → `/admin/users/create`.*
