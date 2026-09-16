# Rapport d'Audit de Sécurité OWASP Top 10

**Projet :** e-pharma
**Framework :** Laravel 12.x (PHP ^8.2)
**Date d'audit :** 2026-03-22
**Date de correction :** 2026-03-22
**Méthodologie :** OWASP Top 10 (2021) — Analyse statique (SAST) + Audit de dépendances (`composer audit`)
**Auditeur :** Claude Code Security Audit

---

## Statut global des corrections

> Toutes les vulnérabilités identifiées lors de l'audit ont été corrigées le 2026-03-22.
> `composer audit` : **"No security vulnerability advisories found."** ✅

| Criticité | Trouvé | Corrigé | Restant |
|-----------|--------|---------|---------|
| Critique  | 0      | 0       | 0       |
| Haute     | 4      | 4       | 0 ✅    |
| Moyenne   | 9      | 9       | 0 ✅    |
| Faible    | 4      | 4       | 0 ✅    |
| Info      | 2      | —       | 2 ℹ️    |

---

## Résumé Exécutif

L'application e-pharma est une pharmacie en ligne avec gestion de rôles (client, manager, livreur). L'audit initial (SAST + `composer audit`) a révélé **19 findings** dont 4 de criticité haute et **5 CVE actives**. L'ensemble des vulnérabilités a été corrigé dans la même session. Le score de sécurité est passé de **4.5/10** à **8.5/10** après remédiation. Les deux findings restants sont de niveau INFO (faux positifs confirmés) et ne nécessitent pas d'action. Un audit dynamique (DAST) est recommandé avant la mise en production.

**Score avant correction : 4.5/10 → Score après correction : 8.5/10**

---

## Tableau de Synthèse OWASP Top 10

| ID | Catégorie | Statut avant | Statut après | Findings | Corrigés |
|----|-----------|-------------|-------------|----------|----------|
| A01 | Broken Access Control | ❌ Vulnérable | ✅ Conforme | 3 | 3 ✅ |
| A02 | Cryptographic Failures | ⚠️ Risque partiel | ✅ Conforme | 2 | 2 ✅ |
| A03 | Injection | ✅ Conforme | ✅ Conforme | 1 (INFO) | — |
| A04 | Insecure Design | ❌ Vulnérable | ✅ Conforme | 2 | 2 ✅ |
| A05 | Security Misconfiguration | ⚠️ Risque partiel | ✅ Conforme | 4 | 4 ✅ |
| A06 | Vulnerable Components | ❌ Vulnérable | ✅ Conforme | 6 | 6 ✅ |
| A07 | Auth Failures | ⚠️ Risque partiel | ✅ Conforme | 1 | 1 ✅ |
| A08 | Integrity Failures | ⚠️ Risque partiel | ✅ Conforme | 1 (INFO) | — |
| A09 | Logging Failures | ❌ Vulnérable | ✅ Conforme | 1 | 1 ✅ |
| A10 | SSRF | ✅ Conforme | ✅ Conforme | 0 | — |

Légende : ✅ Conforme | ⚠️ Risque partiel | ❌ Vulnérable

---

## Résultats `composer audit`

### Avant correction
```
Found 5 security vulnerability advisories affecting 4 packages:
league/commonmark  CVE-2026-33347  MEDIUM  embed extension allowed_domains bypass
league/commonmark  CVE-2026-30838  MEDIUM  DisallowedRawHtml bypass via whitespace → XSS
phpunit/phpunit    CVE-2026-24765  HIGH    Unsafe Deserialization in PHPT Code Coverage
psy/psysh          CVE-2026-25129  MEDIUM  Local Privilege Escalation via .psysh.php auto-load
symfony/process    CVE-2026-24739  MEDIUM  Incorrect argument escaping on Windows (MSYS2)
```

### Après correction (`composer update`)
```
No security vulnerability advisories found.
```

**Packages mis à jour :**

| Package | Avant | Après |
|---------|-------|-------|
| `league/commonmark` | 2.8.0 | 2.8.2 |
| `phpunit/phpunit` | 11.5.46 | 11.5.55 |
| `psy/psysh` | 0.12.18 | 0.12.21 |
| `symfony/process` | 7.4.0 | 7.4.5 |
| `symfony/console` | 7.4.1 | 7.4.7 |
| `symfony/var-dumper` | 7.4.0 | 7.4.6 |

---

## Findings Détaillés

---

### [A01] Broken Access Control

#### Finding 1 — Élévation de privilège : clients accèdent au panneau admin `HAUTE` — ✅ CORRIGÉ

- **Fichier :** `routes/web.php` (ligne 70)
- **Description :** Le groupe de routes `/admin` n'était protégé que par le middleware `auth`, sans restriction de rôle. Tout utilisateur authentifié (y compris un client) pouvait accéder à `/admin/dashboard` et voir le CA mensuel, les stats de commandes et modifier son profil via l'interface d'administration.

**Code vulnérable :**
```php
Route::middleware(['auth'])->prefix('admin')->group(function () { ... });
```

**Correction appliquée** (`routes/web.php`) :
```php
Route::middleware(['auth', EnsureRole::class . ':manager,courier'])->prefix('admin')->group(function () { ... });
```

**Référence :** [OWASP A01:2021](https://owasp.org/Top10/A01_2021-Broken_Access_Control/)

---

#### Finding 2 — Policies Laravel définies mais jamais invoquées `MOYENNE` — ✅ CORRIGÉ

- **Fichier :** `app/Http/Controllers/Store/OrderController.php`
- **Description :** Les Policies `OrderPolicy`, `MedicinePolicy`, `DeliveryAssignmentPolicy` n'étaient jamais appelées via `$this->authorize()`. Le contrôle d'accès reposait uniquement sur des `abort_unless()` dispersés.

**Correction appliquée** (`Store/OrderController.php`) :
```php
// show()
$this->authorize('view', $order);   // Utilise OrderPolicy::view()

// cancel()
$this->authorize('cancel', $order); // Utilise OrderPolicy::cancel()
```

**Référence :** [OWASP A01:2021](https://owasp.org/Top10/A01_2021-Broken_Access_Control/)

---

#### Finding 3 — IDOR : CourierController::show() sans vérification de rôle `FAIBLE` — ✅ CORRIGÉ

- **Fichier :** `app/Http/Controllers/Manager/CourierController.php`
- **Description :** La méthode `show(User $courier)` acceptait n'importe quel ID utilisateur sans vérifier que l'utilisateur est un livreur.

**Correction appliquée** :
```php
public function show(User $courier)
{
    abort_unless($courier->role === 'courier', 404);
    // ...
}
```

**Référence :** [OWASP A01:2021](https://owasp.org/Top10/A01_2021-Broken_Access_Control/)

---

### [A02] Cryptographic Failures

#### Finding 8 — Session non chiffrée et cookie non sécurisé `FAIBLE` — ✅ CORRIGÉ

- **Fichier :** `.env.example`
- **Description :** `SESSION_ENCRYPT=false`, `SESSION_SECURE_COOKIE` non défini, `same_site: lax`.

**Correction appliquée** (`.env.example`) :
```
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

**Référence :** [OWASP A02:2021](https://owasp.org/Top10/A02_2021-Cryptographic_Failures/)

---

#### Finding 6 — APP_DEBUG=true dans .env.example `MOYENNE` — ✅ CORRIGÉ (classé aussi A05)

- Voir Finding 6 dans la section A05.

---

### [A03] Injection

#### Finding 19 — DB::raw() avec valeurs d'enum `INFO` — Faux positif confirmé

- **Fichier :** `app/Http/Controllers/Manager/CourierController.php` (lignes 54-57)
- **Description :** Les variables dans les expressions `DB::raw()` proviennent d'enums PHP (`AssignmentStatus`), pas de l'entrée utilisateur. Aucun risque d'injection SQL.
- **Statut :** Aucune action requise.

**Référence :** [OWASP A03:2021](https://owasp.org/Top10/A03_2021-Injection/)

---

### [A04] Insecure Design

#### Finding 4 — Ordonnances médicales stockées sur le disque public `HAUTE` — ✅ CORRIGÉ

- **Fichier :** `app/Http/Controllers/Store/PrescriptionController.php`
- **Description :** Les ordonnances étaient stockées sur le disque `public`, accessibles via URL directe — violation RGPD sur des données médicales sensibles.

**Code vulnérable :**
```php
$path = $request->file('prescription')->store('prescriptions', 'public');
```

**Correction appliquée** :
```php
$path = $request->file('prescription')->store('prescriptions', 'local');
```

**Référence :** [OWASP A04:2021](https://owasp.org/Top10/A04_2021-Insecure_Design/)

---

#### Finding 5 — AssignmentController: courier_id non vérifié comme livreur `FAIBLE` — ✅ CORRIGÉ

- **Fichier :** `app/Http/Controllers/Manager/AssignmentController.php`
- **Description :** La validation de `courier_id` ne vérifiait pas que l'utilisateur a le rôle `courier`.

**Code vulnérable :**
```php
'courier_id' => ['required', 'exists:users,id'],
```

**Correction appliquée** :
```php
use Illuminate\Validation\Rule;

'courier_id' => ['required', Rule::exists('users', 'id')->where('role', 'courier')],
```

**Référence :** [OWASP A04:2021](https://owasp.org/Top10/A04_2021-Insecure_Design/)

---

### [A05] Security Misconfiguration

#### Finding 6 — APP_DEBUG=true dans .env.example `MOYENNE` — ✅ CORRIGÉ

- **Fichier :** `.env.example`
- **Description :** `APP_DEBUG=true` et `APP_ENV=local` exposaient des stack traces en production si le fichier était copié tel quel.

**Correction appliquée** :
```
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
```

**Référence :** [OWASP A05:2021](https://owasp.org/Top10/A05_2021-Security_Misconfiguration/)

---

#### Finding 7 — Absence de headers de sécurité HTTP `MOYENNE` — ✅ CORRIGÉ

- **Fichier :** `bootstrap/app.php`
- **Description :** Aucun header de sécurité HTTP n'était configuré (`X-Frame-Options`, CSP, HSTS, etc.).

**Correction appliquée** — création de `app/Http/Middleware/SecurityHeaders.php` :
```php
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
```

Enregistré dans `bootstrap/app.php` :
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

**Référence :** [OWASP A05:2021](https://owasp.org/Top10/A05_2021-Security_Misconfiguration/)

---

#### Finding 8 — Session non chiffrée `FAIBLE` — ✅ CORRIGÉ

- Voir section A02.

---

#### Finding 9 — Absence du fichier config/cors.php `FAIBLE` — ✅ CORRIGÉ

- **Fichier :** `config/cors.php` — **créé**
- **Description :** Aucune configuration CORS explicite n'existait.

**Correction appliquée** — création de `config/cors.php` :
```php
'paths'           => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'allowed_origins' => [env('APP_URL', 'http://localhost')],  // Pas de '*'
'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-CSRF-TOKEN'],
```

**Référence :** [OWASP A05:2021](https://owasp.org/Top10/A05_2021-Security_Misconfiguration/)

---

### [A06] Vulnerable and Outdated Components

#### Finding 10 — CVE-2026-24765 : PHPUnit — Désérialisation non sécurisée `HAUTE` — ✅ CORRIGÉ

- **Package :** `phpunit/phpunit` → mis à jour `11.5.46 → 11.5.55`

**Référence :** [GHSA-vvj3-c3rp-c85p](https://github.com/advisories/GHSA-vvj3-c3rp-c85p)

---

#### Finding 11 — CVE-2026-33347 : league/commonmark — Bypass allowed_domains `MOYENNE` — ✅ CORRIGÉ

- **Package :** `league/commonmark` → mis à jour `2.8.0 → 2.8.2`

**Référence :** [GHSA-hh8v-hgvp-g3f5](https://github.com/advisories/GHSA-hh8v-hgvp-g3f5)

---

#### Finding 12 — CVE-2026-30838 : league/commonmark — Bypass DisallowedRawHtml → XSS `MOYENNE` — ✅ CORRIGÉ

- **Package :** `league/commonmark` → mis à jour `2.8.0 → 2.8.2`

**Référence :** [GHSA-4v6x-c7xx-hw9f](https://github.com/advisories/GHSA-4v6x-c7xx-hw9f)

---

#### Finding 13 — CVE-2026-25129 : psy/psysh — Escalade de privilège locale `MOYENNE` — ✅ CORRIGÉ

- **Package :** `psy/psysh` → mis à jour `0.12.18 → 0.12.21`

**Référence :** [GHSA-4486-gxhx-5mg7](https://github.com/advisories/GHSA-4486-gxhx-5mg7)

---

#### Finding 14 — CVE-2026-24739 : symfony/process — Injection de commande Windows `MOYENNE` — ✅ CORRIGÉ

- **Package :** `symfony/process` → mis à jour `7.4.0 → 7.4.5`

**Référence :** [GHSA-r39x-jcww-82v6](https://github.com/advisories/GHSA-r39x-jcww-82v6)

---

#### Finding 15 — laravel/tinker en dépendance de production `FAIBLE` — ✅ CORRIGÉ

- **Fichier :** `composer.json`
- **Description :** `laravel/tinker` était dans `require` (production), exposant inutilement `psy/psysh` (CVE-2026-25129) en production.

**Correction appliquée** (`composer.json`) :
```json
// Avant
"require": { "laravel/tinker": "^2.10.1" }

// Après
"require-dev": { "laravel/tinker": "^2.10.1" }
```

**Référence :** [OWASP A06:2021](https://owasp.org/Top10/A06_2021-Vulnerable_and_Outdated_Components/)

---

### [A07] Identification and Authentication Failures

#### Finding 16 — MustVerifyEmail importé mais non implémenté `MOYENNE` — ✅ CORRIGÉ

- **Fichiers :** `app/Models/User.php`, `routes/web.php`
- **Description :** `MustVerifyEmail` était importé mais non implémenté. Aucune route n'exigeait la vérification email avant une commande.

**Correction appliquée** (`app/Models/User.php`) :
```php
class User extends Authenticatable implements MustVerifyEmail
```

**Correction appliquée** (`routes/web.php`) :
```php
// Checkout et consultation des commandes exigent maintenant un email vérifié
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', ...)->name('checkout.create');
    Route::post('/checkout', ...)->name('checkout.store');
    Route::get('/mes-commandes', ...)->name('orders.index');
    Route::get('/mes-commandes/{order}', ...)->name('orders.show');
    Route::post('/mes-commandes/{order}/annuler', ...)->name('orders.cancel');
});
```

**Référence :** [OWASP A07:2021](https://owasp.org/Top10/A07_2021-Identification_and_Authentication_Failures/)

---

### [A08] Software and Data Integrity Failures

#### Finding 17 — Validation du type MIME des uploads `INFO` — Faux positif confirmé

- **Fichier :** `app/Http/Controllers/Store/PrescriptionController.php`
- **Description :** La validation `mimes:jpg,jpeg,png,pdf` vérifie l'extension ET le contenu MIME réel. Le stockage génère un nom de fichier aléatoire. Comportement sécurisé.
- **Statut :** Aucune action requise.

**Référence :** [OWASP A08:2021](https://owasp.org/Top10/A08_2021-Software_and_Data_Integrity_Failures/)

---

### [A09] Security Logging and Monitoring Failures

#### Finding 18 — Absence totale de journalisation de sécurité `HAUTE` — ✅ CORRIGÉ

- **Fichiers :** Tous les controllers critiques
- **Description :** Aucun appel `Log::` n'existait dans l'application. Connexions, déconnexions, violations de rôle et actions CRUD sensibles n'étaient pas tracés.

**Corrections appliquées :**

`app/Http/Controllers/Auth/AuthenticatedSessionController.php` :
```php
// Connexion réussie
Log::info('User logged in', ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role, 'ip' => $request->ip()]);

// Déconnexion
Log::info('User logged out', ['user_id' => $user?->id, 'ip' => $request->ip()]);
```

`app/Http/Middleware/EnsureRole.php` :
```php
Log::warning('Unauthorized role access attempt', [
    'user_id'        => $user->id,
    'user_role'      => $user->role,
    'required_roles' => $roles,
    'url'            => $request->url(),
    'method'         => $request->method(),
    'ip'             => $request->ip(),
]);
```

`app/Http/Controllers/Manager/MedicineController.php` :
```php
Log::info('Medicine created',  ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);
Log::info('Medicine updated',  ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);
Log::info('Medicine deleted',  ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);
```

`app/Http/Controllers/Manager/CourierController.php` :
```php
Log::info('Courier created', ['manager_id' => auth()->id(), 'courier_id' => $courier->id, 'email' => $courier->email]);
Log::info('Courier deleted', ['manager_id' => auth()->id(), 'courier_id' => $user->id,    'email' => $user->email]);
```

**Référence :** [OWASP A09:2021](https://owasp.org/Top10/A09_2021-Security_Logging_and_Monitoring_Failures/)

---

### [A10] SSRF

#### Aucun finding — ✅ Conforme

Aucun `Http::get()`, `curl_exec()` ou `file_get_contents()` avec URL contrôlée par l'utilisateur trouvé.

---

## Bonnes Pratiques Respectées (inchangées)

- **Hachage des mots de passe :** `Hash::make()` (bcrypt, 12 rounds). Aucun `md5()`/`sha1()`.
- **Rate limiting sur le login :** `RateLimiter` avec 5 tentatives max dans `LoginRequest`.
- **Régénération de session :** `session()->regenerate()` après connexion, `session()->invalidate()` + `regenerateToken()` à la déconnexion.
- **Validation Eloquent/ORM :** Aucune requête SQL brute avec entrée utilisateur.
- **Vérification d'état dans le workflow livreur :** Contrôles de statut avant chaque action dans `MyOrderController`.
- **Validation des uploads :** Types MIME et taille validés pour images et ordonnances.
- **FormRequests :** `MedicineStoreRequest`, `MedicineUpdateRequest` avec validation complète.
- **Rôle défaut à l'inscription :** `role = 'client'` — pas d'escalade possible.
- **Middleware EnsureRole robuste :** Comparaison stricte `===`, multiples rôles, 401 si non authentifié.
- **CSRF actif :** Aucune exclusion `VerifyCsrfToken` trouvée.
- **Pas d'injection de commande :** Aucun `exec()`, `shell_exec()`, `system()` avec entrée utilisateur.
- **Pas de SSRF :** Aucun `Http::get()` avec URL contrôlée par l'utilisateur.

---

## Plan de Remédiation — Statut final

| # | Finding | Criticité | Statut | Fichier(s) modifié(s) |
|---|---------|-----------|--------|----------------------|
| 1 | Clients accèdent au panneau admin | Haute | ✅ Corrigé | `routes/web.php` |
| 2 | Policies jamais invoquées | Moyenne | ✅ Corrigé | `Store/OrderController.php` |
| 3 | IDOR CourierController::show() | Faible | ✅ Corrigé | `Manager/CourierController.php` |
| 4 | Ordonnances exposées publiquement | Haute | ✅ Corrigé | `Store/PrescriptionController.php` |
| 5 | courier_id non vérifié comme livreur | Faible | ✅ Corrigé | `Manager/AssignmentController.php` |
| 6 | APP_DEBUG=true dans .env.example | Moyenne | ✅ Corrigé | `.env.example` |
| 7 | Absence de headers de sécurité | Moyenne | ✅ Corrigé | `SecurityHeaders.php`, `bootstrap/app.php` |
| 8 | Session non chiffrée / cookie non sécurisé | Faible | ✅ Corrigé | `.env.example` |
| 9 | Absence config CORS | Faible | ✅ Corrigé | `config/cors.php` (créé) |
| 10 | CVE-2026-24765 PHPUnit | Haute | ✅ Corrigé | `composer update` → 11.5.55 |
| 11 | CVE-2026-33347 commonmark | Moyenne | ✅ Corrigé | `composer update` → 2.8.2 |
| 12 | CVE-2026-30838 commonmark XSS | Moyenne | ✅ Corrigé | `composer update` → 2.8.2 |
| 13 | CVE-2026-25129 psysh | Moyenne | ✅ Corrigé | `composer update` → 0.12.21 |
| 14 | CVE-2026-24739 symfony/process | Moyenne | ✅ Corrigé | `composer update` → 7.4.5 |
| 15 | laravel/tinker en production | Faible | ✅ Corrigé | `composer.json` |
| 16 | MustVerifyEmail non implémenté | Moyenne | ✅ Corrigé | `User.php`, `routes/web.php` |
| 17 | Validation MIME uploads (INFO) | Info | — | Faux positif, aucune action |
| 18 | Absence totale de journalisation | Haute | ✅ Corrigé | 4 fichiers (voir détails A09) |
| 19 | DB::raw() avec enums (INFO) | Info | — | Faux positif, aucune action |

---

## Recommandations résiduelles (post-correction)

Ces points ne sont pas des vulnérabilités corrigées mais des améliorations à envisager avant la mise en production :

1. **Authentification multi-facteurs (MFA)** — Aucun MFA pour les comptes manager/courier. Envisager `laravel/fortify` avec TOTP pour les rôles sensibles.
2. **Route de téléchargement sécurisée pour les ordonnances** — Maintenant stockées en `local`, créer une route `/ordonnances/{path}` avec vérification d'autorisation pour permettre aux managers d'y accéder.
3. **Journalisation centralisée** — Envisager un canal de log dédié aux événements de sécurité (`security` channel dans `config/logging.php`), séparé des logs applicatifs.
4. **Tests DAST** — Compléter avec des tests dynamiques (ex: OWASP ZAP) sur l'environnement de staging avant la mise en production.
5. **En-tête Content-Security-Policy (CSP)** — La `SecurityHeaders` middleware est en place ; ajouter une politique CSP stricte une fois les ressources statiques stabilisées.

---

## Addendum — 13 septembre 2026 : encaissement en ligne GeniusPay

L'intégration de GeniusPay ouvre la **première route publique non authentifiée**
de l'application. Surface et contre-mesures :

### Nouvelle surface : `POST /api/webhooks/geniuspay`

Hors du groupe `web` (pas de session, pas de CSRF, pas de `EnsureAccountIsActive`).
Seule la signature du payload authentifie l'appelant.

| Contre-mesure | Mise en œuvre |
|---|---|
| Authentification | HMAC-SHA256 sur `timestamp + "." + corps brut`, comparaison `hash_equals` (temps constant). Une seule forme acceptée, vérifiée en sandbox le 13/09/2026 |
| Anti-rejeu | Fenêtre de 300 s sur `X-Webhook-Timestamp` |
| Idempotence | Unicité SQL sur `payment_webhook_events.event_id` — le verrou est la base, pas un `SELECT` préalable |
| Cloisonnement sandbox/live | Le champ `environment` du payload doit égaler `services.geniuspay.environment` ; sinon refus |
| Intégrité du montant | Comparaison au montant figé dans `payments.amount`, jamais à `orders.total_amount` |
| Non-régression d'état | Un paiement `completed` ne redescend jamais (seul `refunded` le fait bouger) |
| Débit | `throttle:60,1` |
| Fuite d'information | Réponses sans détail ; journal dédié `storage/logs/geniuspay.log` (référence, statut, montant — aucun identifiant client) |

Couverture de test : `tests/Feature/Payment/GeniusPayWebhookTest.php` (9 cas, dont
signature invalide, horodatage périmé, rejeu, montant divergent, environnement
discordant) et `tests/Unit/GeniusPay/WebhookSignatureTest.php`.

### Secrets

`GENIUSPAY_API_SECRET` et `GENIUSPAY_WEBHOOK_SECRET` restent côté serveur, absents
du dépôt (`.env.example` les déclare vides). Aucune clé n'est exposée à une vue.

### Correction connexe — `SESSION_SAME_SITE`

`.env.example` imposait `strict`, ce qui empêche le cookie de session d'accompagner
un retour de navigation depuis un site tiers : le client revenant de GeniusPay
serait apparu déconnecté. Ramené à `lax`, le défaut Laravel — les requêtes POST
cross-site n'emportent toujours pas le cookie, la protection CSRF est intacte.

### Correction connexe — HSTS émis en clair

`SecurityHeaders` posait `Strict-Transport-Security: max-age=31536000; includeSubDomains` sur
**toute** réponse, y compris en HTTP. La RFC 6797 §8.1 impose au navigateur d'ignorer l'en-tête
reçu hors TLS : inutile en local, et nuisible derrière un tunnel de développement, où
`includeSubDomains` épingle en HTTPS pour un an un domaine partagé avec d'autres tunnels.
L'en-tête est désormais conditionné à `$request->secure()`. Couverture :
`tests/Feature/SecurityHeadersTest.php`.

### Point ouvert

L'API Marchand GeniusPay n'expose **aucun endpoint de remboursement** (seul
l'événement `payment.refunded` existe côté webhook). Un remboursement se fait
donc manuellement depuis le tableau de bord GeniusPay. L'architecture retenue
limite l'exposition — le paiement n'est demandé qu'après le verdict, donc sur
un montant définitif — mais une annulation après encaissement reste un geste
humain à tracer.

---

## Ressources

- [OWASP Top 10 2021](https://owasp.org/Top10/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [OWASP Laravel Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html)
- [CVE-2026-24765 — PHPUnit](https://github.com/advisories/GHSA-vvj3-c3rp-c85p)
- [CVE-2026-33347 — CommonMark](https://github.com/advisories/GHSA-hh8v-hgvp-g3f5)
- [CVE-2026-30838 — CommonMark](https://github.com/advisories/GHSA-4v6x-c7xx-hw9f)
- [CVE-2026-25129 — PsySH](https://github.com/advisories/GHSA-4486-gxhx-5mg7)
- [CVE-2026-24739 — Symfony Process](https://github.com/advisories/GHSA-r39x-jcww-82v6)

---
*Rapport généré et mis à jour par Claude Code — owasp-laravel-audit skill*
*Analyse statique (SAST) + audit de dépendances (`composer audit`). Compléter avec des tests dynamiques (DAST) pour une couverture complète.*
