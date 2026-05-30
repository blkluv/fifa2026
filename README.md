# World Cup Predictor 2026 — WordPress Plugin

**Plugin Slug:** `wc26-predictor`  
**Namespace:** `WC26Predictor`  
**PHP:** 8.2+ | **WordPress:** 6.4+  
**Version:** 1.0.0

---

## Overview

A production-grade, scalable FIFA World Cup 2026 prediction platform built as a WordPress plugin. Designed for large traffic, REST-API-first, and extensible to Telegram Mini App, PWA, and mobile apps.

---

## Architecture

```
wc26-predictor/
├── wc26-predictor.php          # Entry point, constants, autoloader boot
├── includes/
│   ├── Autoloader.php          # PSR-4 class loader
│   ├── Plugin.php              # DI container + bootstrap
│   ├── Database/
│   │   └── Migrator.php        # dbDelta migrations for all 12 tables
│   ├── Repositories/
│   │   ├── AbstractRepository.php
│   │   └── Repositories.php    # All concrete repos (Team, Match, Prediction, etc.)
│   ├── Services/
│   │   ├── MatchService.php
│   │   ├── PredictionService.php
│   │   ├── ScoringService.php
│   │   ├── StandingsService.php
│   │   └── Services.php        # Leaderboard, Notification, Badge, League services
│   ├── REST/
│   │   └── Router.php          # All REST API endpoints
│   ├── Admin/
│   │   └── AdminLoader.php     # Admin menu, AJAX, CSV import
│   ├── Frontend/
│   │   └── FrontendLoader.php  # Shortcodes + asset enqueue
│   └── Events/
│       └── EventManager.php    # Hook-based event bus
├── assets/
│   ├── css/frontend.css        # Dark sports-platform UI
│   ├── css/admin.css
│   └── js/admin.js
├── templates/
│   ├── admin/                  # Admin page templates
│   └── frontend/               # Shortcode templates (Alpine.js)
└── migrations/
    ├── sample-teams.csv
    └── sample-groups.csv
```

---

## Database Tables (prefix: `wp_wc26_`)

| Table | Purpose |
|---|---|
| `teams` | 32 national teams with FIFA IDs and flags |
| `groups` | Tournament groups (A–L for 2026) |
| `matches` | All fixtures with kickoff times and results |
| `predictions` | User score predictions (UNIQUE: user+match) |
| `standings` | Live group standings (denormalised for speed) |
| `leaderboards` | Pre-aggregated user totals + ranks |
| `scoring_rules` | Dynamic point values (no hardcoding) |
| `mini_leagues` | Private invite-code leagues |
| `mini_league_members` | League memberships |
| `badges` | Badge definitions |
| `user_badges` | Awarded badges |
| `notifications` | In-app notification queue |

---

## REST API Endpoints

Base: `/wp-json/wc26/v1/`

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/matches` | Public | All matches with teams + lock status |
| GET | `/matches/{id}` | Public | Single match |
| GET | `/groups` | Public | All groups |
| GET | `/standings?group_id=N` | Public | Group standings |
| POST | `/predict` | User | Submit/update prediction |
| GET | `/my-predictions` | User | User's predictions |
| GET | `/leaderboard?limit=N` | Public | Global leaderboard |
| POST | `/leagues` | User | Create mini league |
| POST | `/leagues/join` | User | Join with invite code |
| GET | `/leagues/{id}/leaderboard` | User | Mini league rankings |
| GET | `/notifications` | User | Unread notifications |
| POST | `/notifications/read` | User | Mark all read |
| POST | `/admin/matches/{id}/result` | Admin | Submit result + trigger scoring |

Authentication uses `X-WP-Nonce` header (WordPress REST nonce).

---

## Shortcodes

| Shortcode | Attributes | Description |
|---|---|---|
| `[wc26_predictor]` | `stage=""` | Full prediction UI |
| `[wc26_leaderboard]` | `limit="50"` | Live leaderboard table |
| `[wc26_standings]` | `group_id="1"` | Group standings table |
| `[wc26_my_leagues]` | — | Create/join/view mini leagues |

---

## Scoring Engine

Scoring is driven by the `wc26_scoring_rules` database table (no hardcoded values):

| Rule | Default Points |
|---|---|
| Exact Score | 10 |
| Goal Difference (correct margin) | 5 |
| Correct Draw | 4 |
| Correct Winner | 3 |
| One Team Correct | 1 |

**Joker** doubles earned points for one match per tournament.

**3-State Logic:**
1. `pred == real` → Exact (10 pts)
2. `pred_diff == real_diff` → Goal Difference (5 pts)  
3. Same winner/draw → Winner/Draw (3-4 pts)

---

## Event-Driven Hooks

```php
// Fired when admin submits a match result
do_action('wc26_match_finished', $match_id, $home_score, $away_score);

// Fired after all predictions for a match are scored
do_action('wc26_match_scored', $match_id);

// Fired when a user submits a prediction
do_action('wc26_prediction_submitted', $pred_id, $user_id, $match_id);

// Fired when a user earns a badge
do_action('wc26_badge_earned', $user_id, $badge_id);
```

---

## Installation

1. Upload `wc26-predictor/` to `/wp-content/plugins/`
2. Activate in **Plugins** → **Activate**
3. On activation: all 12 tables are created, default scoring rules and badges are seeded
4. Import teams via **WC26 Predictor → CSV Import** using `sample-teams.csv`
5. Import groups, then matches
6. Add shortcodes to pages:
   - `[wc26_predictor]` — prediction page
   - `[wc26_leaderboard]` — rankings page
   - `[wc26_standings group_id="1"]` — group table
   - `[wc26_my_leagues]` — user leagues

---

## Performance Notes

- Leaderboards are **pre-aggregated** (no `SUM()` on every request)
- Standings are recalculated only **after a match result is submitted**
- All hot reads use `wp_cache_get/set` with appropriate TTLs
- MySQL indexes on all foreign keys and commonly filtered columns
- Match lock check is **server-side** (kickoff_at - 1 minute)

---

## Future Extensions

This architecture is designed to support:

- **Telegram Mini App** — REST API is Telegram-ready; no changes needed
- **PWA** — All data via REST; add service worker externally
- **UEFA Euro / Champions League** — Change `season` field; all logic is tournament-agnostic
- **Real-time** — Replace polling with WP Server-Sent Events or pusher integration via `wc26_match_scored` hook
- **Rate limiting** — Add nonce rate limiting middleware to prediction endpoint
