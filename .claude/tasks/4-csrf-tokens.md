# Task 4: Implémenter protection CSRF sur formulaires

**Priorité:** 🔴 HAUTE (Sécurité)  
**Fichiers:** `index.php`, `linkusage.php`

## Problème

Les formulaires n'ont pas de tokens CSRF (Cross-Site Request Forgery):

```php
<form method='get'>
    <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
    <!-- Pas de CSRF token -->
</form>
```

Un attaquant peut forcer un utilisateur authentifié à faire une requête non désirée.

## Impact

- Modification de paramètres de session utilisateur
- Suppression ou modification de cache sans consentement
- Actions non intentionnées

## Solution

Implémenter tokens CSRF avec session PHP:

### 1. Initialiser la session et générer le token

```php
<?php
session_start();

// Générer token CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
```

### 2. Ajouter le token dans les formulaires

```php
<form method='get' action='index.php'>
    <input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token']; ?>'/>
    <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
    <!-- Autres inputs -->
</form>
```

### 3. Valider le token à la réception

```php
<?php
session_start();

// Valider CSRF token pour les requêtes POST/modifiantes
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
}
?>
```

### 4. Helper function (à ajouter dans `func.inc.php`)

```php
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token = null) {
    if ($token === null && isset($_REQUEST['csrf_token'])) {
        $token = $_REQUEST['csrf_token'];
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
```

## Étapes

1. [ ] Ajouter `session_start()` au début de `func.inc.php`
2. [ ] Créer helpers `csrf_token()` et `verify_csrf_token()`
3. [ ] Ajouter hidden input dans tous les formulaires:
   - `index.php:79`
   - `linkusage.php` (chercher `<form`)
   - Autres pages avec formulaires
4. [ ] Ajouter validation côté serveur
5. [ ] Tester: soumettre formulaire, vérifier token invalide échoue

## Templates affectés

- `templates/header.inc.php` — ajouter `session_start()` si besoin
- Formulaires de filtre (link selection)
- Formulaires de navigation

## Tests

```bash
# Valide
GET /index.php?numhours=24&csrf_token=<valid_token>

# Invalide (doit échouer)
GET /index.php?numhours=24&csrf_token=invalid

# Sans token (doit échouer)
GET /index.php?numhours=24
```

## Notes

- Utiliser `hash_equals()` pour comparaison sûre (timing attack protection)
- GET est peu sûr pour CSRF mais accepté si token présent
- Envisager POST pour les actions modifiantes
- Token régénération après authentification (pas applicable ici)
