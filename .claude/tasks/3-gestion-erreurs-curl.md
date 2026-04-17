# Task 3: Ajouter gestion d'erreurs curl dans PeeringDB

**Priorité:** 🔴 HAUTE (Sécurité/Résilience)  
**Fichier:** `lib/class/peeringdb.php`  
**Ligne:** 16-23

## Problème

La méthode `sendRequest()` ne gère pas les erreurs curl:

```php
protected function sendRequest( $url ) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
```

## Risques

- **Requêtes suspendues** — pas de timeout, peut bloquer 5+ minutes
- **Pas de détection d'erreur** — `curl_exec()` retourne `false` en cas d'erreur, silencieusement
- **Absence de validation HTTP** — pas de vérification du code HTTP 200/404
- **DoS interne** — si PeeringDB est lent/indisponible, toute l'app ralentit

## Solution

Ajouter gestion d'erreurs complète:

```php
protected function sendRequest( $url ) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Timeout 10 secondes
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);    // Connexion max 5s
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Suivre redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    
    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Gestion d'erreurs
    if ($curl_errno) {
        error_log("CURL Error ($curl_errno): $curl_error for URL: $url");
        return null;  // ou false
    }
    
    if ($http_code !== 200) {
        error_log("HTTP Error ($http_code) for URL: $url");
        return null;
    }
    
    return $output;
}
```

## Étapes

1. [ ] Ajouter CURLOPT_TIMEOUT et CURLOPT_CONNECTTIMEOUT
2. [ ] Ajouter vérification curl_errno() et curl_error()
3. [ ] Ajouter vérification HTTP status code
4. [ ] Ajouter error_log() ou logging système
5. [ ] Mettre à jour les appelants pour gérer `null` retourné
6. [ ] Tester avec PeeringDB indisponible (blocage réseau)

## Appelants à mettre à jour

Les méthodes `GetInfo()`, `GetIX()`, etc. doivent gérer `null`:

```php
public function GetInfo( $asn = NULL ) {
    if ( !$asn ) return null;
    $json = $this->sendRequest($this->url."/net?asn=".$asn);
    if ( !$json ) return null;  // Nouvelle gestion
    $json = json_decode($json);
    if ( isset($json->meta->error) ) { return null; }
    else { return $json->data[0]; }
}
```

## Tests

```bash
# Tester timeout (serveur lent)
# Tester réponse 404
# Tester URL invalide
# Tester réseaux bloqués
```

## Notes

- Considérer circuit breaker si PeeringDB est souvent indisponible
- Ajouter cache pour réduire appels externes
- Documenter les timeouts dans config
