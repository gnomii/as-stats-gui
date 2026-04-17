# Task 6: Migrer asinfo.txt vers SQLite ou cache

**Priorité:** 🟡 MOYENNE (Performance)  
**Fichier:** `func.inc.php` (`readasinfodb()`, `getASInfo()`)

## Problème

`asinfo.txt` est entièrement chargé en mémoire à chaque requête:

```php
function readasinfodb() {
    global $asinfofile;
    if (!file_exists($asinfofile))
        return array();
    $fd = fopen($asinfofile, "r");
    $asinfodb = array();
    while (!feof($fd)) {
        $line = trim(fgets($fd));
        // Parse chaque ligne
        $asinfodb[$asn] = array('name' => $asname, ...);
    }
    fclose($fd);
    return $asinfodb;  // Tableau entier en mémoire
}
```

## Impacts

- **Mémoire** — pour 100k ASN, ~10+ MB par requête
- **CPU** — parsing de fichier à chaque requête
- **Scalabilité** — impossible de paginer, filtrer côté DB
- **Maintenance** — fichier texte peu flexible

## Solutions

### Option A: SQLite (recommandée)

Créer table:
```sql
CREATE TABLE asinfo (
    asn INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    descr TEXT,
    country TEXT
);
CREATE INDEX idx_asn ON asinfo(asn);
```

Avantages:
- Requêtes rapides par ASN
- Pagination/filtrage possibles
- Intègre avec stats.db existant

### Option B: APCu/Redis Cache

Cache en mémoire avec TTL:
```php
$asinfo = apcu_fetch("asinfo_$asn");
if (!$asinfo) {
    // Charger du fichier/DB et cacher
    apcu_store("asinfo_$asn", $asinfo, 3600);
}
```

Avantages:
- Simple, pas de migration DB
- Très rapide

Inconvénients:
- Memory-bound
- Pas persistant

## Étapes (Option A - SQLite recommandée)

1. [ ] Créer migration: `createAsinfoDB.php`
   - Parser `asinfo.txt`
   - Insérer dans SQLite
   - Créer index sur ASN

2. [ ] Modifier `getASInfo()`:
   ```php
   function getASInfo(int|string $asnum): array {
       global $asinfodbfile;
       $db = new SQLite3($asinfodbfile);
       $stmt = $db->prepare("SELECT name, descr, country FROM asinfo WHERE asn = ?");
       $stmt->bindValue(1, (int)$asnum, SQLITE3_INTEGER);
       $result = $stmt->execute();
       if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
           return $row;
       }
       return array('name' => "AS$asnum", 'descr' => "AS $asnum");
   }
   ```

3. [ ] Supprimer `readasinfodb()` — plus nécessaire

4. [ ] Mettre à jour `config_defaults.inc.php`:
   ```php
   $asinfodbfile = "/path/to/asinfo.db";  // remplace $asinfofile
   ```

5. [ ] Créer script de migration pour imports futurs

6. [ ] Tester requêtes par ASN

## Étapes (Option B - APCu Cache)

1. [ ] Vérifier APCu installé: `php -m | grep apcu`
2. [ ] Modifier `readasinfodb()` pour cacher:
   ```php
   function readasinfodb(): array {
       $cached = apcu_fetch("asinfodb");
       if ($cached !== false) return $cached;
       
       // Charger depuis fichier
       $asinfodb = [...];
       apcu_store("asinfodb", $asinfodb, 3600);
       return $asinfodb;
   }
   ```
3. [ ] Ajouter CLI pour invalidate cache: `apcu_delete('asinfodb')`

## Tests

```bash
# Option A: Vérifier requête rapide
curl 'http://localhost/?as=64999' # Doit être instant

# Option B: Vérifier cache
php -r "echo apcu_fetch('asinfodb') ? 'Cached' : 'Not cached';"
```

## Configuration

Ajouter à `config.inc.php`:
```php
// Option A
$use_asinfo_sqlite = true;
$asinfodbfile = '/var/lib/as-stats/asinfo.db';

// Option B (mutually exclusive)
$use_asinfo_cache = true;
$asinfo_cache_ttl = 3600;
```

## Notes

- Option A meilleure pour scalabilité
- Option B meilleure si pas d'accès direct DB
- Garder `asinfo.txt` comme source de vérité
- Script de sync asinfo.txt → DB pour mises à jour
- Considérer import automatique nightly

## Performance attendue

| Approche | Mémoire | CPU | Scalabilité |
|----------|---------|-----|-------------|
| Fichier texte | 10+ MB | Moyen | Mauvaise |
| SQLite | ~1 MB (index) | Très bon | Bonne |
| APCu cache | 5-10 MB | Excellent | Moyenne |
