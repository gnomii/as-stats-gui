# Task 2: Valider ASN strictement (entier uniquement)

**Priorité:** 🔴 HAUTE (Sécurité)  
**Fichier:** `gengraph.php`  
**Ligne:** 10-12

## Problème

La validation de l'ASN est trop permissive:

```php
$as = $_GET['as'];
if (!preg_match("/^[0-9a-zA-Z]+$/", $as))
    die("Invalid AS");
```

Accepte `[0-9a-zA-Z]+` alors que les ASN sont **toujours des entiers**.

## Risques

- Confusion sur le type de données dans le reste du code
- Injection SQL indirecte si la valeur est utilisée ailleurs
- Path traversal potentiel

## Solution

Valider strictement en tant qu'entier:

```php
// Avant
if (!preg_match("/^[0-9a-zA-Z]+$/", $as))
    die("Invalid AS");

// Après
if (!preg_match("/^\d+$/", $as)) {
    die("Invalid AS - must be numeric");
}
$as = (int)$as;  // Conversion explicite
```

## Fichiers affectés

Vérifier et corriger la validation dans:
- [ ] `gengraph.php:10-12`
- [ ] `linkgraph.php` (même pattern?)
- [ ] `history.php` (si reçoit `as` en paramètre)
- [ ] Toute autre page qui accepte `?as=`

## Étapes

1. [ ] Mettre à jour regex dans `gengraph.php`
2. [ ] Ajouter cast explicite `(int)$as`
3. [ ] Chercher et corriger autres occurrences
4. [ ] Tester avec: ASN valide (64999), invalide ("A123"), limites (0, 4294967295)

## Tests

```bash
# Valide
GET /gengraph.php?as=64999

# Invalide (doit échouer)
GET /gengraph.php?as=AS64999
GET /gengraph.php?as=64999a
GET /gengraph.php?as=../../../etc/passwd
```

## Notes

- ASN 16-bit: 0-65535
- ASN 32-bit: 0-4294967295
- Ne jamais accepter format "AS1234" (les ASN sont numériques)
