# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Le projet

**ePharma** — plateforme de commande de médicaments à Abidjan (Laravel 12, PHP 8.4, Blade).
Promesse produit : **vérifier la disponibilité auprès de pharmacies partenaires avant que le
client ou un livreur ne se déplace**, avec un verdict annoncé en moins de 5 minutes.

Trois interfaces, une seule application :
- **Boutique client** — routes à la racine, URL en français (`/medicaments`, `/panier`, `/mes-commandes`), noms `store.*`
- **Back-office manager** — `/admin`, noms `manager.*` (sauf `admin.dashboard`, `admin.settings.*`, `admin.profile.*`, `admin.notifications.*` partagés avec le livreur)
- **Espace livreur** — `/admin/my-orders`, noms `courier.*`

Langue : tout est en français — interface, commentaires, messages de validation, messages de commit.

## Commandes

```bash
php artisan serve                      # serveur de dev (http://localhost:8000)
composer dev                           # serve + queue:listen + pail + vite en parallèle

composer test                          # config:clear puis artisan test
php artisan test --filter=nom_du_test  # un seul test
php artisan test tests/Feature/ProfileTest.php

php artisan migrate:fresh --seed       # base complète + jeu de démo
php artisan db:seed --class=UserSeeder # recréer les comptes de test (idempotent)
```

Base de dev : **MariaDB `e_pharma`**. Les tests tournent sur SQLite en mémoire (`phpunit.xml`),
donc une migration doit rester compatible SQLite.

**Ne pas lancer `npm run build` / `vite`.** Aucune vue n'utilise `@vite` : le CSS et le JS sont
des fichiers statiques servis depuis `public/assets/`, inclus par `layouts/partials/head.blade.php`
avec un cache-buster `?v={{ config('app.asset_version') }}`. Après avoir modifié
`public/assets/css/epharma-ds.css` ou `public/assets/js/epharma.js`, incrémenter
`asset_version` dans `config/app.php` (ou `ASSET_VERSION` en env). Vite, Tailwind et Alpine
restent dans `package.json` sans être utilisés — **ne pas les réintroduire dans les vues.**

**Ne pas lancer `./vendor/bin/pint` sur les fichiers existants.** 57 des 132 fichiers dévient
volontairement du préréglage Laravel : le code aligne les `=>` et les `=` en colonnes, ce que
Pint réécrirait en masse. Suivre le style du fichier voisin plutôt que l'outil.

## Architecture

### `OrderStatus` est la source de vérité

`app/Enums/OrderStatus.php` porte les **14 statuts** du cycle de vie et tout ce qui en découle :
libellé client (`label()`), texte du badge (`badge()`), couleur et teinte (`color()`, `tint()`,
`tone()`), acteur responsable (`actor()`), étape de livraison (`deliveryStep()`), statut suivant
côté livreur (`nextCourierStep()`), libellé du bouton livreur (`courierActionLabel()`), et les
prédicats (`needsManager()`, `isCourierPhase()`, `isFinal()`, `isCancelableByClient()`).

**Aucune vue ne doit redéclarer sa propre table de correspondance statut → libellé/couleur.**
Si une information de présentation manque, l'ajouter à l'enum.

Le parcours : `PENDING_VALIDATION` → `CHECKING` → `AVAILABLE` / `PARTIALLY_AVAILABLE` /
`UNAVAILABLE` → `COURIER_ASSIGNED` → `TO_PHARMACY` → `AT_PHARMACY` → `PICKED_UP` →
`TO_CLIENT` → `DELIVERED` → `RATED`, plus `REFUSED` et `CANCELED`.

### Toute transition passe par `OrderWorkflow`

`app/Services/OrderWorkflow.php` est le seul endroit qui écrit `$order->status`. Chaque méthode
vérifie le statut de départ (`assertStatus`), applique le changement dans une transaction,
journalise un `OrderEvent` et notifie le client (`record()`). **Les contrôleurs n'écrivent jamais
`$order->status` directement** — ils injectent le service dans leur constructeur et appellent
`startChecking`, `refuse`, `addItem`, `settleVerdict`, `assignCourier`, `advanceDelivery`,
`refuseAssignment`, `rate` ou `cancel`.

Le journal `OrderEvent` alimente la timeline client **et** le flux « Activité en direct » du
back-office : un statut changé sans événement laisse les deux écrans muets.

`app/Services/OrderService.php` couvre la création : `createFromCart()` (panier session) et
`createFromPrescription()` (ordonnance téléversée, commande **sans aucune ligne** — c'est le
manager qui composera le panier avec `OrderWorkflow::addItem`). Frais de livraison :
constante `DELIVERY_FEE = 1500` dans ce service. Les montants sont des **entiers** (FCFA).

### ePharma ne détient aucun stock

Le catalogue est un **référentiel** de ce qu'on sait commander, pas un inventaire. Il n'existe ni
colonne `stock` ni `StockService` : commander n'est jamais bloqué, et `CartController` ne fait
aucun contrôle de quantité disponible.

La disponibilité affichée est un **constat**, jamais une promesse : `Medicine::scopeWithAvailabilitySignal()`
agrège `order_items.availability` sur 7 jours glissants, et `availabilitySignal()` en tire
« Confirmé chez N partenaires » / « Rarement trouvé ces derniers jours » / « Disponibilité à
vérifier ». Les prix sont indicatifs jusqu'au verdict.

### Le chrono de 5 minutes

`Order::CHECK_DURATION_SECONDS = 300`. `startChecking()` pose `check_deadline_at`, et le modèle
expose `secondsLeftForCheck()`, `check_clock` (« 4:12 »), `check_progress` et `isCheckOverdue()`.
Le décompte visuel est piloté côté client par les attributs `data-ep-chrono*` lus par
`public/assets/js/epharma.js` (aucune dépendance JS).

### L'encaissement en ligne arrive après le verdict, jamais au checkout

`payment_method` vaut `cash`, `momo` ou `card`. Les deux derniers sont **prépayés en
ligne** via GeniusPay (`Order::requiresPrepayment()`), `cash` reste encaissé par le livreur.

Le lien de paiement naît dans `OrderWorkflow::settleVerdict()`, **pas** au checkout : avant
le verdict, `total_amount` est indicatif, et une ligne finalement introuvable obligerait à
rembourser — geste que l'API Marchand GeniusPay n'expose pas. Après le verdict, le panier
n'est plus modifiable (`assertComposable`), donc le montant ne peut plus bouger sous le
paiement. L'appel réseau est fait **hors transaction** : il ne doit pas tenir de verrou.

`OrderWorkflow::assignCourier()` refuse tant que `Order::awaitsPayment()` est vrai — le
livreur avance l'argent en pharmacie, il ne part pas sans contrepartie.

Le paiement est un **axe orthogonal** aux 14 statuts : ne rien ajouter à `OrderStatus`
(`number()`, `deliveryStep()` et les `match` exhaustifs casseraient). Table `payments` +
`app/Enums/PaymentStatus.php`, qui porte sa propre présentation selon les mêmes règles que
`OrderStatus` — aucune vue ne redéclare la correspondance statut → libellé/couleur.

Toute issue de paiement passe par `OrderWorkflow::applyPaymentStatus()`, point d'entrée
**unique et idempotent** du webhook, de la page de retour client et de la réconciliation.
Il journalise un `OrderEvent` et notifie via `PaymentStatusNotification` (et non
`OrderProgressNotification`, qui tire son texte du statut de la commande).

**Le webhook `POST /api/webhooks/geniuspay`** est la seule route publique non authentifiée.
Signature HMAC sur le **corps brut** (`$request->getContent()`), vérifié en sandbox le
13 septembre 2026. Attention : l'exemple de `geniuspay.ci/docs/api` signe
`json_encode($request->all())` — **il est faux**, un ré-encodage PHP réécrit `10000.00` en
`10000.0`. Le guide du tableau de bord marchand, lui, signe bien
`file_get_contents('php://input')`. Ne pas « corriger » `WebhookSignature` d'après la page
publique. Le webhook répond **toujours 2xx** dès que le message est compris : un 4xx/5xx
déclenche des redélivraisons en boucle.

**Ne jamais valider le format de `payments.reference`.** La documentation annonce
« MTX-XXXXXXXXXX » ; le sandbox émet en réalité `SANDBOX_W2BK4VYVZPTA9FRU`. Le format est la
propriété de GeniusPay et changera encore en production. On stocke ce qu'ils renvoient et on
rapproche dessus, sans rien présumer.

Chaque webhook créé chez GeniusPay porte **son propre** `whsec_`, affiché une seule fois à
la création. Éditer l'URL d'un webhook ne réémet pas de secret ; en supprimer un et en créer
un autre, si. Un 401 persistant vient presque toujours de là.

`php artisan epharma:reconcile-payments` (planifié toutes les 5 min) rattrape les webhooks
perdus. C'est aussi le seul moyen de voir aboutir un paiement en local, où aucun webhook
n'atteint la machine.

### Le numéro de commande est une colonne, pas un accesseur

`orders.reference` porte « CMD-20260904-0001 » : date de création puis séquence remise à zéro
chaque jour. Attribuée par un hook `creating` sur le modèle, donc **quel que soit le chemin de
création** (service, factory, seeder). `Order::nextReference()` prend le `max()` des références
du jour sous `lockForUpdate()` ; l'index unique est le garde-fou hors transaction.

Ne pas retransformer ça en accesseur : une séquence quotidienne demanderait de recompter les
commandes du jour à chaque affichage. L'ancien format `CMD-` + `id` exposait au passage le
volume total de la plateforme.

### Pièges des requêtes

- `Order::scopeAwaitingCourier()` exclut les commandes ayant une attribution **non refusée** :
  une course refusée par son livreur doit revenir dans la file d'attribution.
- `OrderWorkflow::refuseAssignment()` restitue le verdict de disponibilité réel
  (`availabilityVerdict()`), pas un `AVAILABLE` de façade.
- `OrderWorkflow::syncTotals()` facture toutes les lignes **avant** le verdict, puis seulement
  les lignes obtenables après.
- Une commande sur ordonnance naît **sans aucune ligne** : `subtotal` vaut 0 jusqu'à ce que le
  manager compose le panier. Les vues passent par `Order::awaitsComposition()` pour afficher
  « à établir » — un montant inconnu ne doit jamais s'afficher comme un montant nul.
- Les relations vers `User` utilisent `withTrashed()` (comptes en suppression douce) : une
  commande archivée doit continuer d'afficher qui l'a passée.

### Les coordonnées de l'entreprise sont en base, pas en dur

`App\Support\CompanyProfile` porte le nom commercial, le téléphone, l'e-mail, l'adresse et la
ville affichés par la boutique (barre utilitaire, pied de page, fiche médicament). La table
`settings` stocke des couples clé/valeur préfixés `company.` ; `App\Models\Setting` est
volontairement anémique — **rien d'autre ne lit cette table directement**.

`CompanyProfile::DEFAULTS` fournit le repli : un champ jamais saisi, ou vidé, réaffiche sa valeur
par défaut. La boutique n'affiche donc jamais un contact vide. La lecture est mise en cache une
heure (`company.profile`), vidée par `save()`.

Les vues boutique reçoivent `$company` via `StoreComposer` — mêmes limites que les autres
variables partagées : une vue hors de la liste explicite ne l'aura pas. `$company['phone_href']`
est le numéro prêt pour un lien `tel:`, ne pas refaire le `preg_replace` dans les vues.

`/admin/settings` est partagé avec le livreur (préférences de session), mais **seul le manager
rédige les coordonnées** : `SettingsController` ne valide et n'enregistre le bloc entreprise que
si `isManager()`, et la vue ne le rend que dans ce cas. Ne pas déplacer la route vers le groupe
manager — le livreur y garde ses préférences.

### Autorisation

- `App\Http\Middleware\EnsureRole` s'applique en **class-string** :
  `->middleware(EnsureRole::class . ':manager,courier')`, pas un alias.
- `EnsureAccountIsActive` (groupe `web`, après la session) ferme la session d'un compte désactivé.
- `SecurityHeaders` est appendé globalement dans `bootstrap/app.php`.
- Les policies sont déclarées **explicitement** dans `AuthServiceProvider::$policies`
  (`Order`, `Medicine`, `DeliveryAssignment`) — pas d'auto-découverte. `OrderPolicy` encode qui
  peut voir, annuler, traiter, attribuer, avancer et noter.

### Système de design

`public/assets/css/epharma-ds.css` (~930 lignes, classes `ep-*`) + composants Blade
`resources/views/components/ep/*` (badge, card, chrono, timeline, product-card, rating-form…).
Les layouts `store`, `admin` et `guest` posent `class="ep-root"` sur `<html>` et `<body>`.

Une couleur ne fait jamais deux métiers : vert `#0E5C43` = action/confirmé, ambre `#B87514` =
attente, rouge `#A6382F` = indisponible, bleu `#33557F` = livreur en mouvement, neutre = clos.

**Piège de spécificité :** `.ep-root a` colore tous les liens en vert. Les variantes de bouton
sont donc doublement qualifiées `.ep-root .ep-btn--primary` — sinon un `<a>` stylé en bouton
affiche du texte vert sur fond vert. Garder ce doublement pour toute nouvelle variante.

### Contexte partagé des vues

`AppServiceProvider::boot()` :
- remplace les vues de pagination de Laravel (écrites pour Tailwind) par `vendor.pagination.epharma` ;
- attache `StoreComposer` à une **liste explicite** de vues (`layouts.store`, `store.*`,
  `components.store.*`, `components.ep.*`) qui fournit `$cartCount`, `$currentOrder`,
  `$storeRating`, `$partnerCount`. Les vues enfants étant rendues avant le layout, une nouvelle
  vue boutique hors de ces motifs n'aura pas ces variables — étendre la liste.

`App\Support\BackOfficeNavigation` construit le menu latéral et ses compteurs (cache 15 s).

## Tests

Couverte : l'échafaudage Breeze (auth, profil) **et l'encaissement en ligne**
(`tests/Feature/Payment/`, `tests/Unit/GeniusPay/` — 25 cas). **Toujours non couverts : le
cycle de vie logistique des commandes, les étapes livreur et les policies.**

Factories : `User`, `Medicine`, `Order`, `Payment`. `Category` n'a pas `HasFactory` — la
créer avec `Category::firstOrCreate()` dans un test qui a besoin d'un médicament.

## Documentation du dépôt

- `COMPTES.md` — les 10 comptes de test (mot de passe `password` : `manager@epharma.test`,
  `livreur@epharma.test`, `client@epharma.test`…), source de vérité `database/seeders/UserSeeder.php`
- `public/assets/images/IMAGES.md` — provenance des visuels libres de droit
- `security-audit-OWASP-2026-03-22.md` — audit OWASP corrigé le 22 mars 2026, addendum GeniusPay du 13 septembre 2026
- `README.md` — **boilerplate Laravel non modifié**, aucune information sur le projet
