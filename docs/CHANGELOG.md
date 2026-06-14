# Changelog - SteamRent Platform

All notable changes to the SteamRent project are documented in this file.

## [v2.0] - 2026-06-13
### Changed
- **Migrated Game Images to Local Assets**: Relocated all game posters from remote Pinterest URLs to local storage under `assets/images/games/`, reducing page load latency and removing dependency on external network requests.
- **Rebalanced Rental Prices**: Rebalanced the hourly pricing structure across all 50 games to a realistic rental range of Rp 1.000 - Rp 5.000, aligning with real service expectations.
- **Updated Catalog Price Filters**: Modified price filters on `pages/games.php` to match the new pricing tiers (Rp 1.000 - Rp 2.000, Rp 2.001 - Rp 3.000, Rp 3.001 - Rp 4.000, Rp 4.001 - Rp 5.000).
- **Reviewed Game Badges**: Trimmed and reviewed game badges (`HOT`, `TRENDING`, `NEW`) to highlight only appropriate, high-interest titles instead of assigning badges to all entries.

## [v1.9 Hero Banner Preparation] - 2026-06-13
### Added
- **Support Dedicated Banner Assets**: Sourced widescreen landscape banners with PNG format compatibility and 16:6 aspect ratio.
- **Separated Banner and Poster Assets**: Created structural segregation between card-based vertical game posters and hero landscape backgrounds under `assets/images/banners/`.
- **Responsive Hero Improvements**: Sized hero height dynamically (Desktop: 580px, Tablet: 460px, Mobile: 320px) and set scale behavior to cover centering.
- **Fallback Background Support**: Integrated an automated filesystem existence check to display a clean neutral dark linear gradient if a banner asset is missing.

## [v1.9.1 - Hero Banner Upgrade] - 2026-06-13
### Added
- **Separated Banner Assets**: Created a dedicated folder `assets/images/banners/` for storing horizontal wide-screen banner backgrounds.
- **Improved Hero Image Rendering**: Configured the homepage hero carousel to load cinematic landscape banner files for recommended games instead of stretching vertical posters.
- **Added Cinematic Overlay**: Replaced standard overlays with a stronger linear gradient (`linear-gradient(90deg, rgba(0,0,0,0.80) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.10) 100%)`) to enhance text readability.
- **Improved Responsiveness**: Replaced fixed heights on `.carousel-item-content` with viewport-specific height boundaries (Desktop: 560px, Tablet: 460px, Mobile: 320px).

## [v1.9] - 2026-06-13
### Removed
- **Testing Users**: Removed all development/testing accounts (`Anonimus`, `anonimus2`, `anonimuss`) from the `users` table, resetting the auto-increment starting index back to 1.
- **Testing Transactions & Logs**: Cleaned up potential legacy transaction entries, ensuring `rental`, `payment`, `sessionlog`, `reviews`, and `topup` tables are completely empty.

### Preserved
- **Game Catalog**: Maintained all 50 default game catalog records (names, genres, descriptions, prices, image URLs, badges, and stocks).
- **Database Objects**: Verified that all schemas, primary/foreign keys, indexes, views (`view_active_rentals`, `view_revenue_summary`), procedures (`create_rental_transaction`), functions (`calculate_rental_cost`), and triggers (`after_rental_insert_trg`, `after_rental_update_trg`) remain fully intact.

## [v1.8.1] - 2026-06-13
### Changed
- **Trending Top 3 Podium Cards**: Redesigned the Top 3 to render as a row of three equal-width compact cards. TOP 1 is highlighted with a gold border and subtle shadow, and TOP 2/3 with silver/bronze borders, eliminating massive size or height discrepancies.
- **Removed Podium Labels**: Replaced wording like "Champion", "Runner-Up", and "Third Place" with clean "TOP 1", "TOP 2", and "TOP 3" badges.

## [v1.8] - 2026-06-13
### Changed
- **Homepage Section Links**: Converted "Lihat Semua" and "Lihat Semua Katalog" button-styled components to text-only links (`Lihat Semua →`) without border, background, or button shapes, featuring Steam blue coloring and hover transitions.
- **Trending Leaderboard Redesign**: Removed oversized champion, runner-up, and third-place podium cards. Unified the Top 3 entries into the same compact table-like row family as ranks 4-10, with rank-specific highlights (TOP 1 gold, TOP 2 silver, TOP 3 bronze) and proper rank badges.
- **Rating Visibility**: Increased contrast for "Belum ada rating" label using `#94a3b8` (dark mode) / `#64748b` (light mode) color tokens.
- **Row Columns Alignment**: Styled ranks 1-10 rows with fixed-width columns for ranks, statistics, ratings, prices, and rent buttons, eliminating empty whitespace and ensuring consistent vertical alignment.

## [v1.7] - 2026-06-13
### Added
- **Autocomplete Search Keyboard Support**: Added full keyboard navigation keybindings (arrow up, arrow down, enter) inside `pages/games.php` autocomplete dropdown.
- **Top 3 Visual Rankings**: Themed podium ranking styles for rank 🥇 #1, 🥈 #2, and 🥉 #3 games with gold, silver, and bronze custom accent borders/glows in `pages/trending.php`.
- **Dedicated Top-Up Page Routing**: Restored the dedicated Top-Up router page in `index.php` and modified redirection states on the dashboard.

### Changed
- **Sidebar Menu Restructure**: Simplified sidebar options to only include Beranda, Sedang Tren, Katalog Game, and (if logged in) Koleksi Game.
- **Consolidated Dashboard**: Restructured the Collections page to act as the consolidated user dashboard containing active library rentals, past rental logs, session activity lists, and top-up log histories.
- **Simplified Settings Profile**: Replaced profile statistics and summaries with a modern, simple Steam-style account settings profile (non-editable full name/email, editable username/password).
- **Home Page Spacing**: Adjusted typography headers to large emoji sizes (`⚡ Terpopuler Saat Ini`, `🚀 Rilis Terbaru`) and balanced spacing.

## [v1.6] - 2026-06-13
### Added
- **Top-Up Sidebar Link**: Integrated "Top-Up Saldo" into the sidebar navigation layout in `includes/header.php`.

### Fixed
- **Redirect Target after Top-Up**: Configured `proses_topup.php` to redirect users back to their `profile` page on success so they can view their updated balance, instead of routing them to `collections`.
- **Fail-Safe Preloader**: Verified preloader removal immediately upon loading and verified smooth theme/carousel integration.

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

---

## SteamRent Development History

The following is a comprehensive summary of key milestones across previous SteamRent revisions:

### v1.0 & v1.1 - Core Platform & Schema Layout
- Initial database schema creation and implementation of basic user session auth modules (login/register).
- Created standard dynamic catalog views and added initial rentals functionality, hourly price selectors, and account balance top-ups.

### v1.2 - Native Stored Logic & Theme Redesign
- **Database Upgrades**: Standardized schemas to singular naming conventions. Implemented native stored procedure `create_rental_transaction`, function `calculate_rental_cost`, views (`view_active_rentals`, `view_revenue_summary`), and triggers (`after_rental_insert_trg`, `after_rental_update_trg`) for robust transaction constraints.
- **Folder Restructuring**: Re-architected project directories into clean directories (`/assets/`, `/includes/`, `/pages/`, `/database/`, `/docs/`).
- **UI/UX Facelift**: Deprecated legacy animated stars/particle canvas backgrounds. Replaced with dark navy backdrop theme and structured bright/light mode sheets with blue accents. Added responsive 4-column game cards layout.
- **Interactive Auto Carousel**: Integrated home page banner showing featured recommended titles with a 5-second interval timer, manual sliding arrow controls, and indicator circles.

### v1.4 & v1.6 - Loader Fail-Safes & Route Refinements
- **Loader Fixes**: Configured dynamic event-listeners and a fail-safe fallback timer (2000ms limit) to resolve the infinite loading loop, allowing fallback rendering if assets fail to download.
- **Config Include Path Fix**: Updated relative includes in headers using absolute-path checks (`__DIR__`).
- **Top-Up Redirection**: Redirected user balance updates to the profile detail page for instant feedback.

### v1.7 - Keyboard Navigation & Consolidated Dashboard
- **Profile Page Separation**: Simplified account details page, keeping it editable for usernames/passwords but read-only for emails/full names. Removed dashboard summaries.
- **Unified Collections Dashboard**: Merged active countdowns, completion review modals, login session logs, and top-up transaction history into a consolidated `collections.php` dashboard.
- **Keyboard Navigation Autocomplete**: Enabled ArrowUp, ArrowDown, and Enter key bindings inside the catalog search dropdown list.
- **Podium Ranking Prototype**: Added distinct gold, silver, and bronze highlighting borders/glows for the Top 3 trending items.

### v1.8 - Final Leaderboard Polish
- **Steam-style Text Links**: Removed button shapes, borders, and backgrounds from homepage section links.
- **Unified Leaderboard list**: Restructured Top 10 items into a single row layout with fixed column widths, removing oversized podium cards.
- **Subtle Rating Visibility**: Updated low contrast labels to be readable in both theme modes.

### v1.9 - Database Cleanup
- Removed all testing/development user accounts.
- Empty table records for rental, payment, sessionlog, reviews, and topups.
- Preserved default 50-game catalog entries with proper descriptions and images.
- Validated views, functions, stored procedures, and triggers.

### v1.9 - Hero Banner Preparation
- Support Dedicated Banner Assets: Widescreen landscape banners with PNG compatibility and 16:6 aspect ratio.
- Separated Banner and Poster Assets: Created structural segregation between card-based vertical game posters and hero landscape backgrounds under `assets/images/banners/`.
- Responsive Hero Improvements: Sized hero height dynamically (Desktop: 580px, Tablet: 460px, Mobile: 320px) and set scale behavior to cover centering.
- Fallback Background Support: Integrated an automated filesystem existence check to display a clean neutral dark linear gradient if a banner asset is missing.

### v1.9.1 - Hero Banner Upgrade
- Separated Banner Assets: Created a dedicated folder `assets/images/banners/` for storing horizontal wide-screen banner backgrounds.
- Improved Hero Image Rendering: Configured the homepage hero carousel to load cinematic landscape banner files for recommended games instead of stretching vertical posters.
- Added Cinematic Overlay: Replaced standard overlays with a stronger linear gradient to enhance text readability.
- Improved Responsiveness: Replaced fixed heights on `.carousel-item-content` with viewport-specific height boundaries (Desktop: 560px, Tablet: 460px, Mobile: 320px).

### v2.0 - Game Asset & Pricing Update
- Migrated game posters from remote Pinterest URLs to local storage.
- Rebalanced the hourly pricing structure across all 50 games to Rp 1.000 - Rp 5.000.
- Updated price filters in `games.php` to match the new ranges.
- Reviewed and trimmed game badges to avoid visual noise.
