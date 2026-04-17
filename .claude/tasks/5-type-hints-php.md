# Task 5: Ajouter type hints PHP 7+

**Priorité:** 🟡 MOYENNE (Qualité du code)  
**Fichier principal:** `func.inc.php`  
**Autres:** `gengraph.php`, `linkgraph.php`

## Problème

Le code PHP manque complètement de type hints, ce qui rend difficile:
- Autocomplétion IDE
- Détection statique d'erreurs
- Documentation du code
- Refactoring sûr

```php
// Avant (pas de types)
function getASInfo($asnum) {
    global $asinfodb;
    // ...
}

function readasinfodb() {
    // ...
}
```

## Solution

Ajouter type hints pour les paramètres et valeurs retournées:

```php
// Après (avec types)
function getASInfo(int|string $asnum): array {
    global $asinfodb;
    if (!isset($asinfodb))
        $asinfodb = readasinfodb();
    if (!empty($asinfodb[$asnum]))
        return $asinfodb[$asnum];
    else
        return array('name' => "AS$asnum", 'descr' => "AS $asnum");
}

function readasinfodb(): array {
    global $asinfofile;
    if (!file_exists($asinfofile))
        return array();
    // ...
}
```

## Fonctions prioritaires dans `func.inc.php`

1. `isMobileDevice(): bool`
2. `isTabletDevice(): bool`
3. `getASInfo(int|string $asnum): array`
4. `readasinfodb(): array`
5. `getknownlinks(): array`
6. `getasstats_top(int $ntop, string $statfile, array $selected_links, ?array $list_asn = null, ?int $v = null): array`
7. `format_bytes(int|float $bytes): string`
8. `getRRDFileForAS(int|string $as, int $peer = 0): string`
9. `getASSET(string $asset): ?array`
10. `clearCacheFileASSET(string $asset): void`
11. `getHTMLUrl(int|string $as, int $ipversion, string $desc, int $start, int $end, int $peerusage, array $selected_links = []): string`
12. `getHTMLImg(int|string $as, int $ipversion, string $desc, int $start, int $end, int $peerusage, string $alt = '', string $class = '', bool $history = false, array $selected_links = []): string`

## Fonctions dans `gengraph.php`

1. Valider types de paramètres GET reçus

## Étapes

1. [ ] Ajouter type hints aux fonctions principales de `func.inc.php`
2. [ ] Utiliser union types `int|string` pour flexibility
3. [ ] Utiliser `?type` pour nullable (ex: `?array`)
4. [ ] Ajouter `void` pour fonctions sans retour
5. [ ] Ajouter `strict_types=1` au début des fichiers si compatible
6. [ ] Tester: lancer PHP type checking ou IDE inspection
7. [ ] Mettre à jour autres fichiers (`gengraph.php`, `linkgraph.php`, etc.)

## Exemple complet

```php
<?php
// Optionnel mais recommandé (strict typing)
declare(strict_types=1);

require_once("config_defaults.inc.php");
require_once('config.inc.php');

function isMobileDevice(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool) preg_match('/Mobile|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
}

function isTabletDevice(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool) preg_match('/iPad|Android(?!.*Mobile)|Tablet|Kindle|Silk/i', $ua);
}
```

## Tools utiles

- **PHPStorm/VS Code** — inspections intégrées
- **phpstan** — analyse statique (`composer require --dev phpstan/phpstan`)
- **psalm** — psalm checking

## Tests

```bash
# Vérifier avec phpstan (optionnel)
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse func.inc.php
```

## Notes

- `declare(strict_types=1)` peut casser du code existant — tester bien
- Union types (`int|string`) nécessitent PHP 8.0+
- `?type` = nullable (PHP 7.1+)
- Void type (PHP 7.1+)
- Array type: `array` ou `array<key, value>` (PHP 7.0+)
