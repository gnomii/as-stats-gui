# Task 7: Standardiser style de code avec phpcs

**Priorité:** 🟢 BAS (Maintenabilité)  
**Fichiers:** Tous les fichiers PHP

## Problème

Le code mélange plusieurs styles:
- Indentation tabs vs spaces
- Espaces/pas d'espaces autour opérateurs
- Ponctuation inconsistante (`;` facultatif)
- Noms variables camelCase et snake_case

## Solution

Implémenter **PHP CodeSniffer (phpcs)** avec **PSR-12**:

## Étapes

### 1. Installer phpcs

```bash
cd /Users/mpatteri/PROJECTS/PERSO/LAB/as-stats-gui
composer require --dev squizlabs/php_codesniffer
```

### 2. Créer `.phpcs.xml`

```xml
<?xml version="1.0"?>
<ruleset name="as-stats-gui">
    <description>PHP CodeSniffer ruleset for as-stats-gui</description>
    
    <!-- PSR-12 standard -->
    <rule ref="PSR12">
        <!-- Exceptions/relaxations si nécessaire -->
    </rule>
    
    <!-- Target files -->
    <file>.</file>
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/bootstrap/*</exclude-pattern>
    <exclude-pattern>*/dist/*</exclude-pattern>
    <exclude-pattern>*/plugins/*</exclude-pattern>
    
    <!-- Tab width -->
    <arg name="tab-width" value="4"/>
</ruleset>
```

### 3. Ajouter script composer

Dans `composer.json`:
```json
{
    "scripts": {
        "lint": "phpcs .",
        "lint:fix": "phpcbf ."
    }
}
```

### 4. Lancer audit

```bash
composer lint
```

### 5. Fixer automatiquement

```bash
composer lint:fix
```

### 6. Vérifier résultats

```bash
composer lint
```

## CI Integration

Ajouter à `.github/workflows/lint.yml` (si GitHub Actions):

```yaml
name: Lint

on: [push, pull_request]

jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - run: composer install
      - run: composer lint
```

## Configuration.inc exclusion (optionnel)

Si `config.inc` est utilisateur-éditable, exclure de vérification:

```xml
<exclude-pattern>config.inc.php</exclude-pattern>
```

## Étapes détaillées

1. [ ] Installer phpcs: `composer require --dev squizlabs/php_codesniffer`
2. [ ] Créer `.phpcs.xml`
3. [ ] Lancer audit initial: `composer lint`
4. [ ] Lire rapport d'erreurs
5. [ ] Fixer automatiquement: `composer lint:fix`
6. [ ] Vérifier changements (git diff)
7. [ ] Commit: "style: apply PSR-12 formatting with phpcs"
8. [ ] (Optionnel) Ajouter pre-commit hook pour vérifier avant commit

## Pre-commit Hook

Créer `.git/hooks/pre-commit`:

```bash
#!/bin/bash
composer lint || exit 1
```

## Fichiers affectés

Tous les `.php` seront potentiellement reformatés:
- `func.inc.php`
- `gengraph.php`
- `linkgraph.php`
- `index.php`
- `linkusage.php`
- `history.php`
- `ix.php`
- `asset.php`
- `lib/class/peeringdb.php`
- etc.

## Tests après standardisation

```bash
# Vérifier que l'app fonctionne toujours
curl http://localhost/

# Vérifier pas de syntax errors
php -l func.inc.php
php -l gengraph.php
# ...
```

## Notes

- **Destructive** — va reformater tous les fichiers
- Commit séparé recommandé (aucun changement fonctionnel)
- PSR-12 c'est le standard PHP moderne
- Peut causer conflits git si travail parallèle
