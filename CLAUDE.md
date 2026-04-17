# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based web GUI for [AS-Stats](https://github.com/manuelkasper/AS-Stats), displaying BGP/AS traffic statistics and internet exchange (IX) peering data. It renders graphs via RRDtool and pulls AS/IX metadata from PeeringDB and a local `asinfo.txt` database.

## Deployment

No build step — PHP files are served directly. Deploy the repository as the `www` directory of an AS-Stats installation. Requires:
- PHP with SQLite3 and curl extensions
- RRDtool binary (default: `/usr/bin/rrdtool`)
- whois binary (default: `/usr/bin/whois`)
- AS-Stats data files (RRD files, SQLite DB, knownlinks config)

Configuration lives in `config.inc` (user-editable). Defaults are in `config_defaults.inc` (do not edit).

## Architecture

### Request Flow

Pages render HTML with `<img>` tags pointing to graph-generating PHP scripts. The browser then fetches those URLs separately, which invoke RRDtool and return PNG images directly.

- `index.php` → queries SQLite (`$daystatsfile`) for top N ASes → renders page with image URLs → browser fetches `gengraph.php?asn=...` → RRDtool outputs PNG
- `linkusage.php` → similar but per-link stats → `linkgraph.php` generates stacked area charts

### Key Files

| File | Role |
|---|---|
| `func.inc` | All shared logic: AS lookup, RRD path resolution, SQLite queries, graph URL builders, nav/footer HTML |
| `config_defaults.inc` | Default config values (graph sizes, intervals) |
| `config.inc` | Local overrides (paths, ASN, feature flags) |
| `gengraph.php` | Generates AS traffic graphs (PNG output) |
| `linkgraph.php` | Generates per-link stacked area graphs (PNG output) |
| `lib/class/peeringdb.php` | PeeringDB REST API wrapper |
| `asinfo.txt` | AS database: `ASN\tName\tDescription\tCountry` |

### Data Sources

1. **SQLite3** (`$daystatsfile`) — `stats` table with columns `asn`, `{link}_in/out`, `{link}_v6_in/out`
2. **RRD files** (`$rrdpath/{hex(asn%256)}/{asn}.rrd`) — time-series data for graphs
3. **asinfo.txt** — loaded in full on each request into `$asinfodb` global via `readasinfodb()`
4. **knownlinks file** (`$knownlinksfile`) — tab-separated: `routerip\tifindex\ttag\tdescription\tcolor`
5. **PeeringDB API** — queried live for IX member data in `ix.php`

### Input Validation

Graph-generating scripts (`gengraph.php`, `linkgraph.php`) validate inputs (ASN as integer, link tags via regex against known links) before passing to RRDtool. SQLite queries in `func.inc` use string concatenation — be careful when modifying queries to avoid injection.

### Caching

AS-SET whois lookups (`getASSET()` in `func.inc`) are cached to disk for 7 days (default `$asset_cache_time`).
