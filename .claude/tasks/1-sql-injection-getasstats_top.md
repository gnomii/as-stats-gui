# Task 1: Corriger SQL Injection dans getasstats_top()

**Priorité:** 🔴 HAUTE (Sécurité)  
**Fichier:** `func.inc.php`  
**Ligne:** 104-163

## Problème

La fonction `getasstats_top()` construit une requête SQL dynamique en concaténant directement les colonnes:

```php
$query = "SELECT asn, $query_links $query_total as total FROM stats WHERE asn IN ( $where )";
```

Les variables `$query_links` et `$query_total` sont construites en boucle à partir de `$selected_links`, ce qui pourrait permettre une injection SQL si la validation est contournée.

## Impact

- Risque d'accès non autorisé aux données SQLite
- Exécution de commandes arbitraires sur la base de données
- Corruption potentielle des données

## Solution

Utiliser **prepared statements SQLite3** avec paramètres liés:

```php
// Avant (DANGEREUX)
$query = "SELECT asn, $query_links $query_total as total FROM stats WHERE asn IN ( $where )";
$asn = $db->query($query);

// Après (SÛRE)
// Les colonnes dynamiques restent en concaténation (ne peuvent pas être paramétrées)
// Mais les ASN sont liés:
$placeholders = implode(',', array_fill(0, count($list_asn), '?'));
$query = "SELECT asn, $query_links $query_total as total FROM stats WHERE asn IN ( $placeholders )";
$stmt = $db->prepare($query);
if (!$stmt) {
    error_log("SQL prepare failed: " . $db->lastErrorMsg());
    return array();
}
foreach (array_values($list_asn) as $idx => $asn_val) {
    $stmt->bindValue($idx + 1, (int)$asn_val, SQLITE3_INTEGER);
}
$asn = $stmt->execute();
```

## Étapes

1. [ ] Modifier la fonction `getasstats_top()` pour utiliser prepared statements
2. [ ] Valider que les colonnes dynamiques restent sûres (vérifier allowlist)
3. [ ] Tester avec différentes valeurs: ASN simples, listes, valeurs limites
4. [ ] Vérifier performance (prepared statements vs query directe)
5. [ ] Documenter la méthode

## Tests

```bash
# Tester avec liste d'ASN valides
GET /index.php?n=10

# Tester avec injection potentielle
GET /index.php?n=10&link_test' OR '1'='1
```

## Dépendances

Aucune. Utilise SQLite3 déjà présent.

## Notes

- La validation `array_intersect()` ligne 116 réduit le risque
- Les prepared statements vont aussi améliorer la performance en cas de requêtes répétées
