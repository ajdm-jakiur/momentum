# Progress Track

A personal learning management system for long-horizon, multi-domain self-education. Built to replace static HTML roadmaps with a structured, trackable, and AI-importable system.

---

## What it does

Progress Track organises everything you're learning into a hierarchy of **Sectors → Roadmaps → Phases → Blocks → Items**, giving you per-item checkboxes, daily streaks, and weekly/monthly reports — all in one dark, distraction-free interface.

| Concept | Description |
|---|---|
| **Sector** | A life domain — e.g. Systems Engineering, DSA, Dev Practice |
| **Roadmap** | A structured learning plan under a sector, with phases and a timeline |
| **Phase** | A time-boxed chunk of a roadmap (e.g. "Phase 1 — Weeks 1–8") with an optional milestone |
| **Block** | A focused topic inside a phase (e.g. "C Programming & Memory Model") |
| **Item** | A completable unit inside a block — project, problem, drill, habit, community action, etc. |

A block is only complete when every required resource **and** every required item is checked off. Progress cascades up: block → phase → roadmap. No vague percentages — always shows you exactly what's still blocking completion.

---

## Features

- **Roadmap viewer** — collapsible phase/block accordion with inline checkboxes on every completable row
- **JSON import** — paste AI-generated roadmap JSON, get a live preview, confirm to persist
- **Config-driven item kinds** — add new kinds (`drill`, `habit`, `practice`, `milestone`, …) by editing one config file, zero code changes
- **Daily check-in** — log work per sector/roadmap/block/task with minutes and notes
- **Streaks** — per-sector streak counters, recomputed on every check-in
- **Tasks** — ad hoc recurring work (OSS, blog, Reddit answers, etc.) independent of roadmap structure
- **Weekly & monthly reports** — aggregated views of hours logged, problems solved, community actions
- **Dashboard** — per-sector progress %, current streak, this-week summary, next undone block
- **Referral-only registration** — new accounts require an invite link; each user gets their own referral URL
- **Push notifications** — Web Push API via service worker; daily digest via `notify:daily` artisan command
- **Roadmap trash** — soft-delete with restore and permanent delete
- **PWA-ready** — `manifest.json`, service worker, OLED dark theme

---

## Tech stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 + PHP 8.3 |
| Frontend | Livewire 3 + Volt + Alpine.js |
| Styling | Tailwind CSS v3 |
| Fonts | JetBrains Mono + Syne (Google Fonts) |
| Database | SQLite (dev) — MySQL-compatible schema |
| Push | `minishlink/web-push` + VAPID |

---

## Local setup

```bash
git clone <repo>
cd progress-track

composer install
npm install

cp .env.example .env
php artisan key:generate

# Generate VAPID keys for push notifications
php artisan web-push:vapid

php artisan migrate --seed
npm run build

php artisan serve
```

Open `http://localhost:8000`. Log in with the seeded user or use an invite link.

### Environment variables

```env
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:you@example.com
```

---

## Importing a roadmap

Progress Track is designed to accept AI-generated roadmaps as JSON. The schema is fully documented:

```bash
# Print the schema + field guide to terminal
php artisan roadmap:schema

# Save a starter file
php artisan roadmap:schema --no-cheatsheet > my-roadmap.json
```

Or copy the schema from the **Import Roadmap** page in the UI and paste it into an AI prompt:

> *"Generate a 12-week roadmap for [topic] following this exact JSON shape."*

Paste the result back into the import page → preview → confirm.

### Item kinds

Defined in `config/roadmap.php`. Add new kinds here without touching any other code:

```php
'item_kinds' => [
    'project'   => ['label' => 'Project',   'color' => 'accent', 'checkin_type' => 'project', 'section' => 'Projects & Builds'],
    'problem'   => ['label' => 'Problem',   'color' => 'warn',   'checkin_type' => 'study',   'section' => 'Practice Problems'],
    'drill'     => ['label' => 'Drill',     'color' => 'ok',     'checkin_type' => 'study',   'section' => 'Drills & Exercises'],
    // add more here
],
```

---

## Daily notifications

A scheduled artisan command sends a Web Push digest to all subscribed devices:

```bash
# Manual trigger
php artisan notify:daily

# Runs automatically at 08:00 via Laravel scheduler
php artisan schedule:run
```

---

## Running tests

```bash
php artisan test
```

43 tests, all green.

---

## Data model (abbreviated)

```
users              id, name, email, referral_code, referred_by
sectors            id, name, slug, icon, color, sort_order
roadmaps           id, sector_id, title, color, total_weeks, progress_percent, is_complete, deleted_at
phases             id, roadmap_id, title, duration_label, milestone, sort_order
blocks             id, phase_id, title, weeks_label, icon, pattern_text, sort_order
block_resources    id, block_id, name, kind, is_required, is_done, done_at
block_items        id, block_id, kind, title, meta(JSON), is_required, is_done, done_at
block_daily_notes  id, block_id, body, sort_order
tasks              id, user_id, sector_id?, roadmap_id?, title, type, recurrence, target_per_period
checkins           id, user_id, date, sector_id?, roadmap_id?, block_id?, task_id?, minutes_spent, checkin_type
streaks            id, user_id, sector_id, current_streak, longest_streak, last_checkin_date
push_subscriptions id, user_id, endpoint, public_key, auth_token
```

Progress is computed bottom-up by `CompletionService` on every checkbox toggle — never stale, never a separate "mark complete" step.

---

## Project structure

```
app/
  Livewire/
    Dashboard.php          Per-sector summary
    ProfilePage.php        Invite link + referred users
    Roadmaps/
      Show.php             Roadmap viewer + checkbox toggles
      Import.php           JSON paste → preview → confirm
      Trash.php            Soft-deleted roadmaps
    Sectors/Index.php      Sector list
    Tasks/Index.php        Recurring tasks
    Checkins/Daily.php     Daily log
    Reports/Weekly.php
    Reports/Monthly.php
  Services/
    RoadmapImportService.php
    CompletionService.php
    CheckinService.php
  Console/Commands/
    SendDailyNotifications.php
    RoadmapSchemaCommand.php
config/
  roadmap.php              Item kinds registry (single source of truth)
database/
  migrations/              14 files, no add-on alters
  seeders/
    SectorSeeder.php
    RoadmapSeeder.php
resources/
  schemas/roadmap-sample.json
public/
  sw.js                    Service worker (push + offline)
  manifest.json            PWA manifest
```
