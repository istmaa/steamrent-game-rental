# Changelog - SteamRent Platform

All notable changes to the SteamRent project are documented in this file.

---

## [v1.4] - 2026-06-13
### Added
- **Fail-Safe Loading Screen**: Updated `assets/js/script.js` to disable the preloader immediately if document is ready or after a maximum 2000ms fallback timeout, resolving the infinite loader screen issue.
- **Config Include Path Fix**: Changed relative config include to absolute-path based check (`include_once __DIR__ . '/config.php'`) in `includes/header.php`.
- **Database Schema Verification**: Successfully drop old tables (including leftover tables `game_specs` and `rentals`) using disabled foreign key constraint validations and compiled all UAS stored procedures, triggers, views, and functions.

## [v1.2] - 2026-06-13
### Added
- **Auto Carousel Banner** on the homepage featuring 4 famous games: Cyberpunk 2077, Elden Ring, Red Dead Redemption 2, and GTA V (automatic cycling with 5s intervals, manual indicator arrows, indicator dots, and responsive metadata display).
- **Dedicated Profile Page (`profile.php`)**: Separates user identity management from active libraries, showing join dates, total rentals stats, avatar uploading controls, and past rental summaries.
- **SQL Trigger `after_rental_insert_trg`**: Automatically creates an initial billing payment log and starts active sessions in `sessionlog` upon rental insert.
- **SQL Trigger `after_rental_update_trg`**: Automatically registers logout times in `sessionlog` when game rental statuses change to returned, restores game stock, and updates payment totals if duration changes.
- **SQL Stored Function `calculate_rental_cost()`**: Standardizes rental cost calculations across client-side logic and stored procedures.
- **SQL Stored Procedure `create_rental_transaction()`**: Wraps stock check validation, balance validation, rental creations, and database mutations in a clean transaction.
- **SQL View `view_active_rentals`**: Provides details of current active rentals (JOINs user, game, and rental).
- **SQL View `view_revenue_summary`**: Summarizes performance counts and financial revenue totals per game.
- **Suggested Git Commits Guide**: Added standard commit descriptions for releases.

### Changed
- **Folder Restructuring**: Organized files into `/assets/css/`, `/assets/js/`, `/includes/`, `/pages/`, `/database/`, and `/docs/`.
- **Database Schema Rename**: Updated database tables to singular names (`game`, `rental`, `payment`, `sessionlog`), and changed column names to follow standard formats like `UserID` and `GameID`.
- **UI/UX Re-design**: Replaced floating animated backgrounds and moving particles with a clean, solid dark navy background.
- **Bright Mode Redesign**: Added soft white styles, light gray structures, readable dark texts, and blue accents to maintain accessible contrast.
- **Grid Layout**: Adjusted game card grids to be fully responsive (4 columns on desktop, 2 columns on tablet, and 1 column on mobile).
- **Comments Cleanliness**: Replaced AI-style code comments with developer-like Indonesian code comments (e.g. `// cek login user`, `// ambil data game`).

### Removed
- Animated star backgrounds, floating canvas particles, and glowing card animations.
- "Why Rent on SteamRent?" cards and "Popular Genres" pills from the homepage.
- Duplicate legacy files (`report.md`, `rendered_home.html`).

---

## [v1.1] - 2026-06-02
### Added
- Game rental features, duration selectors, and wallet top-ups.
- Core balance log schemas.

---

## [v1.0] - 2026-06-01
### Added
- Core platform layout, user registration, account sign-in modules, and catalog search selectors.

---

## Git Commit Guideline

Please use these prefix guidelines when performing GitHub commits:
- `feat: redesign homepage layout`
- `feat: add game carousel`
- `feat: separate profile page`
- `feat: implement rental procedure`
- `feat: add revenue summary view`
- `style: improve dark and light theme`
- `refactor: reorganize project folders`
