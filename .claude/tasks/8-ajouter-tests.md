# Task 8: Ajouter tests unitaires/intégration

**Priorité:** 🟢 BAS (Maintenabilité/Régression)  
**Type:** Infrastructure de test

## Problème

Aucun test n'existe. Les refactors (Tasks 1-6) sont risqués sans tests de régression.

## Solution

Implémenter **PHPUnit** ou **Pest** pour tests automatisés.

## Choix: PHPUnit vs Pest

| Critère | PHPUnit | Pest |
|---------|---------|------|
| Maturité | Très mature | Nouveau (2020+) |
| Syntaxe | Verbeux | Élégant |
| Performance | Bonne | Très bonne |
| Courbe d'apprentissage | Moyenne | Douce |
| Recommandé | Oui | Oui (moderne) |

**Recommandation:** Pest (plus moderne, plus lisible)

## Étapes

### 1. Installer Pest

```bash
composer require --dev pestphp/pest
php artisan pest:install  # Si Laravel, sinon:
./vendor/bin/pest --init   # Créer tests/
```

### 2. Structure tests

```
tests/
├── Unit/
│   ├── FuncTest.php          # Tests de func.inc
│   ├── GenGraphTest.php      # Tests de gengraph.php
│   └── PeeringDBTest.php     # Tests de PeeringDB class
├── Integration/
│   ├── IndexPageTest.php     # Test page d'accueil
│   ├── GraphGenerationTest.php
│   └── LinkUsageTest.php
└── Pest.php                  # Configuration globale
```

### 3. Tests unitaires

`tests/Unit/FuncTest.php`:
```php
<?php

use function Tests\setup;

describe('getASInfo', function () {
    it('returns AS info from database', function () {
        $result = getASInfo(64999);
        
        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['name', 'descr', 'country']);
    });
    
    it('returns default for unknown AS', function () {
        $result = getASInfo(999999999);
        
        expect($result['name'])->toBe('AS999999999');
    });
});

describe('format_bytes', function () {
    it('formats bytes correctly', function () {
        expect(format_bytes(1024))->toBe('1 KB');
        expect(format_bytes(1048576))->toBe('1.00 MB');
        expect(format_bytes(1073741824))->toBe('1.00 GB');
    });
});

describe('isMobileDevice', function () {
    it('detects iPhone', function () {
        $_SERVER['HTTP_USER_AGENT'] = 'iPhone OS 14';
        expect(isMobileDevice())->toBeTrue();
    });
    
    it('detects Android mobile', function () {
        $_SERVER['HTTP_USER_AGENT'] = 'Android Mobile';
        expect(isMobileDevice())->toBeTrue();
    });
    
    it('does not detect iPad as mobile', function () {
        $_SERVER['HTTP_USER_AGENT'] = 'iPad';
        expect(isMobileDevice())->toBeFalse();
    });
});
```

### 4. Tests d'intégration

`tests/Integration/GenGraphTest.php`:
```php
<?php

describe('gengraph.php', function () {
    it('generates valid PNG for valid AS', function () {
        $response = file_get_contents('http://localhost/gengraph.php?as=64999&v=4');
        
        // Vérifier PNG magic bytes
        expect(substr($response, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });
    
    it('rejects invalid ASN', function () {
        // Devrait retourner erreur ou die
        $response = file_get_contents('http://localhost/gengraph.php?as=invalid');
        
        expect($response)->toContain('Invalid AS');
    });
    
    it('rejects CSRF token missing (future)', function () {
        // Une fois CSRF implémenté
        // $response should fail without token
    });
});

describe('index.php', function () {
    it('loads top ASes page', function () {
        $response = file_get_contents('http://localhost/');
        
        expect($response)->toContain('Top N AS');
        expect($response)->toContain('<img');
    });
});
```

### 5. Fixtures (données de test)

`tests/Fixtures/StatsDatabase.php`:
```php
<?php

class StatsDatabase {
    public static function create(): string {
        $db = new SQLite3(':memory:');
        $db->exec("CREATE TABLE stats (
            asn INTEGER PRIMARY KEY,
            link1_in INTEGER,
            link1_out INTEGER,
            link1_v6_in INTEGER,
            link1_v6_out INTEGER
        )");
        
        // Insérer données de test
        $db->exec("INSERT INTO stats VALUES (64999, 1000, 2000, 0, 0)");
        
        return $db;
    }
}
```

### 6. Configuration test

`tests/Pest.php`:
```php
<?php

use PHPUnit\Framework\TestCase;

// Setup PHP path
define('BASEPATH', dirname(__DIR__));
require_once BASEPATH . '/func.inc.php';

// Mock globals si nécessaire
$_SERVER['HTTP_USER_AGENT'] = '';
$_GET = [];
$_POST = [];
```

## Exécuter tests

```bash
# Lancer tous les tests
./vendor/bin/pest

# Lancer avec rapport couverture
./vendor/bin/pest --coverage

# Lancer tests spécifiques
./vendor/bin/pest tests/Unit/FuncTest.php
```

## CI Integration

Ajouter `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
          extensions: sqlite3, curl
      - run: composer install
      - run: ./vendor/bin/pest
```

## Étapes prioritaires

1. [ ] Installer Pest: `composer require --dev pestphp/pest`
2. [ ] Initialiser: `./vendor/bin/pest --init`
3. [ ] Créer tests unitaires pour `func.inc.php`
4. [ ] Créer tests intégration pour `gengraph.php`
5. [ ] Créer fixtures/mocks SQLite
6. [ ] Lancer: `./vendor/bin/pest`
7. [ ] Ajouter à CI/CD

## Tests prioritaires

### Phase 1 (essentiels)
- [ ] `getASInfo()` — avec/sans DB
- [ ] `format_bytes()` — divers formats
- [ ] `isMobileDevice()` / `isTabletDevice()` — UA detection
- [ ] `gengraph.php` — output PNG valid

### Phase 2 (importants)
- [ ] `getasstats_top()` — requête top N
- [ ] `getknownlinks()` — parsing fichier
- [ ] `PeeringDB::GetInfo()` — requête API
- [ ] Input validation (ASN, links)

### Phase 3 (complets)
- [ ] CSRF token validation (après Task 4)
- [ ] Tous les helpers de HTML generation
- [ ] Gestion erreurs (CSRF fail, DB error)

## Couverture cible

- **Phase 1:** 50% couverture
- **Phase 2:** 70% couverture
- **Phase 3:** 80%+ couverture

## Notes

- Commencer par tests simples (format_bytes)
- Puis tester parsing/conversion
- Puis tester requêtes
- Mock/Stub dépendances externes (API, fichiers)
- Database: utiliser SQLite in-memory pour tests rapides
- Ne pas tester vendor/ ou bootstrap/
