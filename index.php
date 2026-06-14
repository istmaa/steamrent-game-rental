<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
include 'koneksi.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Centralized Authentication GuardSecurity
if (in_array($page, ['collections', 'topup', 'rent', 'profile']) && !isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini!'];
    header("Location: login.php");
    exit;
}

$user_balance = 0;
$profile_image = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT balance, profile_image FROM users WHERE id = '$user_id'");
    if ($user_data = mysqli_fetch_assoc($user_query)) {
        $user_balance = $user_data['balance'];
        $profile_image = $user_data['profile_image'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRent - Premium Edition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-vh-100">

    <!-- Preloader Screen Animation -->
    <div id="preloader">
        <div class="loader-spinner-wrapper">
            <div class="loader-spinner"></div>
            <div class="loader-text">SteamRent</div>
        </div>
    </div>

    <!-- Canvas Background Particles -->
    <canvas id="particles-canvas"></canvas>

    <!-- Bootstrap Toasts Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php
    // Flash dynamic toast notification if set in session
    if (isset($_SESSION['toast'])) {
        $t = $_SESSION['toast'];
        $t_type = $t['type'];
        $t_msg = mysqli_real_escape_string($conn, $t['message']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('$t_type', '$t_msg');
            });
        </script>";
        unset($_SESSION['toast']);
    }
    ?>

    <div class="d-flex flex-column flex-md-row min-vh-100">

        <aside class="sidebar glass-panel p-4 d-flex flex-column flex-shrink-0">
            <div class="mb-4 text-center text-md-start">
                <h2 class="fw-bold m-0 text-white">Steam<span class="text-accent">Rent</span></h2>
            </div>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="index.php?page=collections" class="user-box p-3 mb-4 rounded-3 d-flex align-items-center gap-3 text-decoration-none">
                    <?php if (!empty($profile_image) && file_exists('uploads/profile/' . $profile_image)): ?>
                        <img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Profile Picture">
                    <?php else: ?>
                        <i class="bi bi-person-circle fs-2 text-accent"></i>
                    <?php endif; ?>
                    <div class="text-start">
                        <div class="fw-bold text-white lh-1">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <small class="text-accent fw-semibold" style="font-size: 12px;">
                            Saldo: Rp <?php echo number_format($user_balance, 0, ',', '.'); ?>
                        </small>
                    </div>
                </a>
            <?php else: ?>
                <a href="login.php" class="user-box p-3 mb-4 rounded-3 d-flex align-items-center gap-3 text-decoration-none">
                    <i class="bi bi-person-circle fs-2 text-secondary"></i>
                    <div class="text-start">
                        <div class="fw-bold text-white lh-1">Masuk / Login</div>
                        <small class="text-accent fw-semibold" style="font-size: 12px;">Akses Akun Anda</small>
                    </div>
                </a>
            <?php endif; ?>

            <div class="mb-3">
                <button id="themeToggle" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-sun-fill"></i> Bright Mode
                </button>
            </div>

            <nav class="nav flex-column gap-2 mb-auto text-center text-md-start">
                <a class="nav-link nav-item-custom <?php echo ($page == 'home') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=home">
                    <i class="bi bi-house-door-fill"></i> Beranda
                </a>
                <a class="nav-link nav-item-custom <?php echo ($page == 'trending') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=trending">
                    <i class="bi bi-fire"></i> Sedang Tren
                </a>
                <a class="nav-link nav-item-custom <?php echo ($page == 'genre') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=genre">
                    <i class="bi bi-controller"></i> Kategori Genre
                </a>
                <a class="nav-link nav-item-custom <?php echo ($page == 'collections') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=collections">
                    <i class="bi bi-collection-play-fill"></i> Koleksi Game
                </a>
            </nav>

            <a href="logout.php" class="mt-4 text-danger text-decoration-none fw-medium text-center text-md-start px-3 py-2 rounded-2 w-100 d-flex align-items-center justify-content-center justify-content-md-start gap-2" style="background-color: rgba(239, 68, 68, 0.1); transition: 0.3s;">
                <i class="bi bi-box-arrow-left"></i> Keluar Akun
            </a>
        </aside>

        <main class="flex-grow-1 p-4 p-md-5 overflow-auto">

            <!-- Search Bar Component (Visible ONLY on Kategori Genre Page) -->
            <?php if ($page == 'genre'): ?>
                <div class="mb-5">
                    <form action="index.php" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="genre">
                        <?php if (isset($_GET['g'])): ?>
                            <input type="hidden" name="g" value="<?php echo htmlspecialchars($_GET['g']); ?>">
                        <?php endif; ?>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Cari judul game atau genre favorit Anda..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            
                            <select name="price_range" class="form-select bg-dark border-secondary text-white" style="max-width: 200px;" onchange="this.form.submit()">
                                <?php 
                                $price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
                                ?>
                                <option value="" <?php echo ($price_range == '') ? 'selected' : ''; ?>>Semua Kisaran Tarif</option>
                                <option value="under_10k" <?php echo ($price_range == 'under_10k') ? 'selected' : ''; ?>>Di bawah Rp 10.000</option>
                                <option value="10k_15k" <?php echo ($price_range == '10k_15k') ? 'selected' : ''; ?>>Rp 10.000 - Rp 15.000</option>
                                <option value="over_15k" <?php echo ($price_range == 'over_15k') ? 'selected' : ''; ?>>Di atas Rp 15.000</option>
                            </select>
                            
                            <button class="btn bg-accent fw-bold px-4" type="submit">Cari</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php
            // SEARCH QUERY BUILDER
            $search_clause = "";
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search_val = mysqli_real_escape_string($conn, $_GET['search']);
                $search_clause = " AND (title LIKE '%$search_val%' OR genre LIKE '%$search_val%')";
            }

            // PAGE ROUTER
            if ($page == 'home'):
                // Fetch Cyberpunk 2077 details for Hero banner
                $hero_query = mysqli_query($conn, "SELECT g.*, r.avg_rating, r.review_count FROM games g LEFT JOIN (SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count FROM reviews GROUP BY game_id) r ON g.id = r.game_id WHERE g.title = 'Cyberpunk 2077'");
                $hero_game = mysqli_fetch_assoc($hero_query);
            ?>
                <!-- Hero Banner -->
                <?php if ($hero_game): ?>
                    <div class="hero-banner p-4 p-md-5 mb-5 rounded-4 d-flex align-items-center animate-fade-in" style="min-height: 380px; background: linear-gradient(to right, rgba(11, 15, 20, 0.95) 20%, rgba(11, 15, 20, 0.2)), url('<?php echo htmlspecialchars($hero_game['image_url']); ?>') center 20% / cover; border: 1px solid #1f2937;">
                        <div style="max-width: 550px;">
                            <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill"><i class="bi bi-star-fill text-warning me-1"></i> Rekomendasi Game</span>
                            <h1 class="display-5 fw-bold text-white mb-3 lh-sm"><?php echo htmlspecialchars($hero_game['title']); ?></h1>
                            <p class="text-light mb-4" style="font-size: 16px; opacity: 0.9;"><?php echo htmlspecialchars($hero_game['description']); ?></p>
                            <a href="index.php?page=rent&game_id=<?php echo $hero_game['id']; ?>" class="btn bg-accent fw-bold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-play-circle-fill"></i> Sewa - Rp <?php echo number_format($hero_game['price_per_hour'], 0, ',', '.'); ?>/jam
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Why Rent on SteamRent & Customer Benefits -->
                <div class="mb-5 animate-fade-in">
                    <h4 class="fw-bold mb-4"><i class="bi bi-patch-check-fill text-accent me-2"></i>Mengapa Menyewa di SteamRent?</h4>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="user-box glass-panel p-4 rounded-4 h-100 d-flex flex-column justify-content-between benefit-card">
                                <div>
                                    <div class="benefit-icon-wrapper mb-3 text-accent"><i class="bi bi-key-fill fs-3"></i></div>
                                    <h6 class="fw-bold text-white mb-2">Akses Aktivasi Instan</h6>
                                    <p class="text-secondary small mb-0" style="line-height: 1.6;">Dapatkan kode aktivasi langsung setelah menyewa untuk membuka game di platform Steam tanpa menunggu lama.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="user-box glass-panel p-4 rounded-4 h-100 d-flex flex-column justify-content-between benefit-card">
                                <div>
                                    <div class="benefit-icon-wrapper mb-3 text-success"><i class="bi bi-clock-history fs-3"></i></div>
                                    <h6 class="fw-bold text-white mb-2">Durasi Waktu Fleksibel</h6>
                                    <p class="text-secondary small mb-0" style="line-height: 1.6;">Sewa game dengan durasi yang fleksibel mulai dari 1 jam hingga 72 jam sesuai waktu bermain yang Anda inginkan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="user-box glass-panel p-4 rounded-4 h-100 d-flex flex-column justify-content-between benefit-card">
                                <div>
                                    <div class="benefit-icon-wrapper mb-3 text-warning"><i class="bi bi-wallet2 fs-3"></i></div>
                                    <h6 class="fw-bold text-white mb-2">Hemat & Ekonomis</h6>
                                    <p class="text-secondary small mb-0" style="line-height: 1.6;">Nikmati game AAA premium bernilai ratusan ribu rupiah dengan tarif sewa sangat terjangkau mulai dari Rp 5.000/jam.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="user-box glass-panel p-4 rounded-4 h-100 d-flex flex-column justify-content-between benefit-card">
                                <div>
                                    <div class="benefit-icon-wrapper mb-3 text-info"><i class="bi bi-cloud-arrow-up-fill fs-3"></i></div>
                                    <h6 class="fw-bold text-white mb-2">Dukungan Cloud Save</h6>
                                    <p class="text-secondary small mb-0" style="line-height: 1.6;">Progres bermain Anda aman tersimpan langsung melalui Cloud Save Steam asli untuk sesi bermain berikutnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Categories & Popular Genres -->
                <div class="mb-5 animate-fade-in">
                    <h4 class="fw-bold mb-4"><i class="bi bi-grid-fill text-accent me-2"></i>Kategori & Genre Populer</h4>
                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=Action" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-lightning-charge-fill text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">Action</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=RPG" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-shield-shaded text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">RPG</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=Adventure" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-compass-fill text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">Adventure</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=Horror" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-eye-slash-fill text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">Horror</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=Shooter" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-crosshair text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">Shooter</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="index.php?page=genre&g=Sports" class="genre-pill-card user-box glass-panel p-3 rounded-4 text-center text-decoration-none d-block">
                                <i class="bi bi-trophy-fill text-accent fs-3 d-block mb-2"></i>
                                <span class="fw-bold text-white small">Sports</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Trending Section -->
                <div class="d-flex justify-content-between align-items-end mb-4 animate-fade-in">
                    <h4 class="fw-bold m-0"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Terpopuler Saat Ini</h4>
                    <a href="index.php?page=trending" class="text-accent text-decoration-none small fw-semibold">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4 mb-5">
                    <?php
                    // Display popular games sorted by HOT first, then TRENDING
                    $trending_query = mysqli_query($conn, "
                        SELECT g.*, r.avg_rating, r.review_count FROM games g
                        LEFT JOIN (SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count FROM reviews GROUP BY game_id) r ON g.id = r.game_id
                        WHERE g.title IN ('Black Myth: Wukong', 'Grand Theft Auto V', 'Elden Ring', 'Cyberpunk 2077') 
                        ORDER BY (g.badge = 'HOT') DESC, (g.badge = 'TRENDING') DESC, g.price_per_hour DESC 
                        LIMIT 4
                    ");
                    if (mysqli_num_rows($trending_query) > 0) {
                        while ($game = mysqli_fetch_assoc($trending_query)) {
                            include 'card_game.php';
                        }
                    } else {
                        echo '<p class="text-secondary small ms-2">Tidak ada game ditemukan.</p>';
                    }
                    ?>
                </div>

                <!-- New Release Section -->
                <div class="d-flex justify-content-between align-items-end mb-4 mt-5 animate-fade-in">
                    <h4 class="fw-bold m-0"><i class="bi bi-calendar-star-fill text-success me-2"></i>Rilis Terbaru</h4>
                </div>
                <div class="row g-4 mb-5">
                    <?php
                    $new_query = mysqli_query($conn, "SELECT g.*, r.avg_rating, r.review_count FROM games g LEFT JOIN (SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count FROM reviews GROUP BY game_id) r ON g.id = r.game_id WHERE g.badge = 'NEW' LIMIT 4");
                    if (mysqli_num_rows($new_query) > 0) {
                        while ($game = mysqli_fetch_assoc($new_query)) {
                            include 'card_game.php';
                        }
                    } else {
                        echo '<p class="text-secondary small ms-2">Tidak ada game ditemukan.</p>';
                    }
                    ?>
                    
                    <!-- Placeholder Card 1: Coming Soon -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="game-card coming-soon-card p-2 h-100 d-flex flex-column">
                            <span class="badge bg-secondary card-badge rounded-1 shadow-sm px-2 py-1">COMING SOON</span>
                            <div class="empty-image w-100 mb-3" style="height: 220px; background-color: #1a222d; border: 2px dashed rgba(56, 189, 248, 0.2);">
                                <i class="bi bi-clock-history coming-soon-icon mb-2"></i>
                                <span class="text-accent small fw-bold">Segera Rilis</span>
                            </div>
                            <div class="px-2 pb-2 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold text-secondary mb-1">Misteri & Teka-Teki</h6>
                                <span class="text-secondary small mb-3"><i class="bi bi-tags-fill me-1"></i> Petualangan / Sci-Fi</span>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-secondary fs-6">Rp -</span>
                                    <button class="btn btn-sm btn-outline-secondary fw-bold px-3 py-1 rounded-2 disabled" disabled>Sewa</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder Card 2: Coming Soon -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="game-card coming-soon-card p-2 h-100 d-flex flex-column">
                            <span class="badge bg-secondary card-badge rounded-1 shadow-sm px-2 py-1">COMING SOON</span>
                            <div class="empty-image w-100 mb-3" style="height: 220px; background-color: #1a222d; border: 2px dashed rgba(56, 189, 248, 0.2);">
                                <i class="bi bi-controller coming-soon-icon mb-2"></i>
                                <span class="text-accent small fw-bold">Segera Rilis</span>
                            </div>
                            <div class="px-2 pb-2 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold text-secondary mb-1">Pertempuran Taktis</h6>
                                <span class="text-secondary small mb-3"><i class="bi bi-tags-fill me-1"></i> Aksi / Multiplayer</span>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-secondary fs-6">Rp -</span>
                                    <button class="btn btn-sm btn-outline-secondary fw-bold px-3 py-1 rounded-2 disabled" disabled>Sewa</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($page == 'trending'): ?>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h4 class="fw-bold m-0"><i class="bi bi-fire text-warning me-2"></i>Top 10 Game Terpopuler Right Now</h4>
                </div>
                
                <?php
                // Fetch top 10 dynamic trending games from database based on actual rentals
                $trending_query = mysqli_query($conn, "
                    SELECT g.*, 
                           COALESCE(rent_stats.rent_count, 0) as rent_count, 
                           COALESCE(rent_stats.play_hours, 0) as play_hours,
                           r.avg_rating,
                           r.review_count
                    FROM games g
                    LEFT JOIN (
                        SELECT game_id, 
                               COUNT(id) as rent_count, 
                               SUM(duration_hours) as play_hours
                        FROM rentals
                        GROUP BY game_id
                    ) rent_stats ON g.id = rent_stats.game_id
                    LEFT JOIN (
                        SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count
                        FROM reviews
                        GROUP BY game_id
                    ) r ON g.id = r.game_id
                    ORDER BY rent_count DESC, play_hours DESC, g.title ASC
                    LIMIT 10
                ");
                $trending_games = [];
                while ($row = mysqli_fetch_assoc($trending_query)) {
                    $trending_games[] = $row;
                }
                ?>

                <div class="row g-4 mb-4">
                    <?php for ($i = 0; $i < min(3, count($trending_games)); $i++): 
                        $game = $trending_games[$i];
                        $rank = $i + 1;
                        
                        // Style borders based on rank
                        if ($rank == 1) {
                            $border_style = 'border: 2px solid #ffd700; box-shadow: 0 0 15px rgba(255, 215, 0, 0.25);';
                            $badge_class = 'bg-warning text-dark';
                            $rank_text = '👑 RANK 1';
                            $icon_class = 'bi bi-trophy-fill text-warning';
                        } elseif ($rank == 2) {
                            $border_style = 'border: 2px solid #c0c0c0; box-shadow: 0 0 15px rgba(192, 192, 192, 0.15);';
                            $badge_class = 'bg-secondary text-white';
                            $rank_text = '🥈 RANK 2';
                            $icon_class = 'bi bi-award-fill text-secondary';
                        } else {
                            $border_style = 'border: 2px solid #cd7f32; box-shadow: 0 0 15px rgba(205, 127, 50, 0.15);';
                            $badge_class = 'bg-light text-dark';
                            $rank_text = '🥉 RANK 3';
                            $icon_class = 'bi bi-award-fill text-dark';
                        }
                    ?>
                    <div class="col-12 col-md-4">
                        <div class="game-card p-3 h-100 d-flex flex-column glass-panel" style="<?php echo $border_style; ?>">
                            <span class="badge <?php echo $badge_class; ?> card-badge rounded-1 shadow-sm px-2 py-1 fw-bold"><?php echo $rank_text; ?></span>
                            
                            <div class="w-100 mb-3 d-flex align-items-center justify-content-center rounded-3 shadow-sm" style="height: 180px; background-color: #1a222d; border: 1px solid #1f2937; overflow: hidden;">
                                <?php if (!empty($game['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($game['image_url']); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($game['title']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="<?php echo $icon_class; ?> fs-1 mb-2"></i>
                                        <span class="text-secondary small">Poster Belum Ada</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="px-1 pb-1 d-flex flex-column flex-grow-1">
                                <h5 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($game['title']); ?></h5>
                                <span class="text-secondary small mb-2"><i class="bi bi-tags-fill me-1"></i> <?php echo htmlspecialchars($game['genre']); ?></span>
                                
                                <div class="p-2 rounded bg-dark bg-opacity-50 mb-3 small border border-secondary" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between text-secondary mb-1">
                                        <span>Rating Rata-rata:</span>
                                        <span class="text-warning fw-bold">
                                            <?php 
                                            $avg_rating = !empty($game['avg_rating']) ? number_format($game['avg_rating'], 1) : null;
                                            if ($avg_rating) {
                                                echo "<i class='bi bi-star-fill text-warning me-1'></i>" . $avg_rating . " (" . $game['review_count'] . ")";
                                            } else {
                                                echo "Belum ada";
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-secondary mb-1">
                                        <span>Total Dirental:</span>
                                        <span class="text-light fw-bold"><?php echo $game['rent_count']; ?> kali</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-secondary">
                                        <span>Total Durasi:</span>
                                        <span class="text-light fw-bold"><?php echo $game['play_hours']; ?> jam</span>
                                    </div>
                                </div>

                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-accent fs-6">Rp <?php echo number_format($game['price_per_hour'], 0, ',', '.'); ?><small class="text-secondary fw-normal">/jam</small></span>
                                    <a href="index.php?page=rent&game_id=<?php echo $game['id']; ?>" class="btn btn-sm bg-accent fw-bold px-3 py-1 rounded-2">Sewa</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="mt-5">
                    <h5 class="fw-bold text-white mb-3">Peringkat Selanjutnya (Leaderboard)</h5>
                    <div class="d-flex flex-column gap-2 mb-5">
                        <?php for ($i = 3; $i < count($trending_games); $i++): 
                            $game = $trending_games[$i];
                            $rank = $i + 1;
                            
                            // Visual medals for rankings 4, 5, 6
                            $rank_badge = '#' . $rank;
                            $row_border = 'border: 1px solid #1f2937;';
                            if ($rank == 4) {
                                $rank_badge = '🎖️ #4';
                                $row_border = 'border: 1px dashed #ffd700;';
                            } elseif ($rank == 5) {
                                $rank_badge = '🎖️ #5';
                                $row_border = 'border: 1px dashed #c0c0c0;';
                            } elseif ($rank == 6) {
                                $rank_badge = '🎖️ #6';
                                $row_border = 'border: 1px dashed #cd7f32;';
                            }
                        ?>
                            <a href="index.php?page=rent&game_id=<?php echo $game['id']; ?>" class="trending-row d-flex align-items-center justify-content-between p-3 rounded text-decoration-none" style="<?php echo $row_border; ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-bold text-accent fs-5" style="width: 50px;"><?php echo $rank_badge; ?></span>
                                    <div>
                                        <div class="fw-bold text-white"><?php echo htmlspecialchars($game['title']); ?></div>
                                        <small class="text-secondary"><?php echo htmlspecialchars($game['genre']); ?> &bull; Dirental: <strong><?php echo $game['rent_count']; ?>x</strong></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-accent">
                                        Rp <?php echo number_format($game['price_per_hour'], 0, ',', '.'); ?>/jam
                                    </div>
                                    <small class="text-secondary" style="font-size: 11px;"><?php echo $game['play_hours']; ?> jam bermain</small>
                                </div>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>

            <?php elseif ($page == 'genre'):
                $selected_genre = isset($_GET['g']) ? $_GET['g'] : '';
                $genres = ['Action', 'RPG', 'Adventure', 'Magic', 'Shooter', 'Fighting', 'Horror', 'Survival', 'Sports'];
                
                // Get counts dynamically for each genre
                $genre_counts = [];
                foreach ($genres as $g) {
                    $g_escaped = mysqli_real_escape_string($conn, $g);
                    $c_query = mysqli_query($conn, "SELECT COUNT(*) as c FROM games WHERE genre LIKE '%$g_escaped%'");
                    $c_data = mysqli_fetch_assoc($c_query);
                    $genre_counts[$g] = $c_data['c'];
                }
                
                // Price filter clause
                $price_clause = "";
                $price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
                if ($price_range == 'under_10k') {
                    $price_clause = " AND price_per_hour < 10000";
                } elseif ($price_range == '10k_15k') {
                    $price_clause = " AND price_per_hour BETWEEN 10000 AND 15000";
                } elseif ($price_range == 'over_15k') {
                    $price_clause = " AND price_per_hour > 15000";
                }

                // Genre clause
                $genre_clause = "";
                if (!empty($selected_genre)) {
                    $selected_genre_escaped = mysqli_real_escape_string($conn, $selected_genre);
                    $genre_clause = " AND (genre LIKE '%$selected_genre_escaped%')";
                }

                // Get total titles in current query
                $total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM games WHERE 1=1 $genre_clause $price_clause $search_clause");
                $total_data = mysqli_fetch_assoc($total_query);
                $total_titles = $total_data['total'];

                // 12 Games per page pagination logic
                $limit = 12;
                $total_pages = ceil($total_titles / $limit);
                if ($total_pages < 1) $total_pages = 1;
                $current_page = isset($_GET['p']) ? max(1, min($total_pages, intval($_GET['p']))) : 1;
                $offset = ($current_page - 1) * $limit;

                // Build query params string to preserve search filters
                $get_params = $_GET;
                unset($get_params['p']); // remove existing p to append dynamically
                $filter_query_str = http_build_query($get_params);
            ?>
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h4 class="fw-bold m-0"><i class="bi bi-controller text-info me-2"></i>Katalog Game Premium</h4>
                    <span class="text-secondary small">
                        Menampilkan <?php echo ($total_titles > 0 ? $offset + 1 : 0); ?> - <?php echo min($offset + $limit, $total_titles); ?> dari <?php echo $total_titles; ?> Game &bull; Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?>
                    </span>
                </div>
                
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="index.php?page=genre&price_range=<?php echo urlencode($price_range); ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>" class="btn btn-sm btn-outline-light <?php echo empty($selected_genre) ? 'active' : ''; ?>">Semua Kategori</a>
                    <?php foreach ($genres as $g): ?>
                        <a href="index.php?page=genre&g=<?php echo urlencode($g); ?>&price_range=<?php echo urlencode($price_range); ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>" class="btn btn-sm btn-outline-light <?php echo ($selected_genre === $g) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($g); ?> (<?php echo isset($genre_counts[$g]) ? $genre_counts[$g] : 0; ?>)
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4 mb-5">
                    <?php
                    $genre_query = mysqli_query($conn, "SELECT g.*, r.avg_rating, r.review_count FROM games g LEFT JOIN (SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count FROM reviews GROUP BY game_id) r ON g.id = r.game_id WHERE 1=1 $genre_clause $price_clause $search_clause ORDER BY g.title ASC LIMIT $limit OFFSET $offset");
                    if (mysqli_num_rows($genre_query) > 0) {
                        while ($game = mysqli_fetch_assoc($genre_query)) {
                            include 'card_game.php';
                        }
                    } else {
                        echo '<div class="col-12"><p class="text-secondary small ms-2">Tidak ada game ditemukan.</p></div>';
                    }
                    ?>
                </div>

                <!-- Premium Pagination Navigation -->
                <?php if ($total_pages > 1): ?>
                    <nav class="d-flex justify-content-center mt-4">
                        <ul class="pagination pagination-sm gap-1">
                            <!-- Previous Link -->
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-dark border-secondary text-light fw-bold" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo ($current_page - 1); ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($n = 1; $n <= $total_pages; $n++): ?>
                                <li class="page-item <?php echo ($current_page == $n) ? 'active' : ''; ?>">
                                    <a class="page-link border-secondary fw-bold <?php echo ($current_page == $n) ? 'bg-accent text-dark border-accent' : 'bg-dark text-light'; ?>" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo $n; ?>">
                                        <?php echo $n; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Link -->
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-dark border-secondary text-light fw-bold" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo ($current_page + 1); ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php elseif ($page == 'collections'): 
                if (!isset($_SESSION['user_id'])) {
                    echo "<script>alert('Silakan login terlebih dahulu untuk mengakses Koleksi Game Anda!'); window.location='login.php';</script>";
                    exit;
                }

                // Query library summary statistics
                $active_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rentals WHERE user_id = '$user_id' AND status = 'active'");
                $active_count = mysqli_fetch_assoc($active_count_q)['c'];

                $completed_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rentals WHERE user_id = '$user_id' AND status = 'returned'");
                $completed_count = mysqli_fetch_assoc($completed_count_q)['c'];

                $total_hours_q = mysqli_query($conn, "SELECT SUM(duration_hours) as s FROM rentals WHERE user_id = '$user_id'");
                $total_hours = intval(mysqli_fetch_assoc($total_hours_q)['s']);
            ?>
                <!-- Wallet & Avatar Customizer Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-8">
                        <!-- Wallet Balance Banner -->
                        <div class="user-box p-4 rounded-4 h-100 d-flex flex-column justify-content-center wallet-banner">
                            <small class="text-secondary text-uppercase fw-semibold tracking-wider" style="font-size: 11px;">SALDO DOMPET AKUN</small>
                            <h2 class="fw-bold text-white m-0 mt-1 mb-3">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></h2>
                            <div>
                                <a href="index.php?page=topup" class="btn bg-accent fw-bold px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-plus-circle-fill"></i> Top-Up Saldo
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <!-- Avatar Editor Widget -->
                        <div class="user-box p-4 rounded-4 text-center d-flex flex-column align-items-center justify-content-center" style="border: 1px solid #1f2937;">
                            <div class="position-relative mb-2">
                                <?php if (!empty($profile_image) && file_exists('uploads/profile/' . $profile_image)): ?>
                                    <img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle border border-accent border-2" style="width: 70px; height: 70px; object-fit: cover;" alt="Avatar">
                                <?php else: ?>
                                    <i class="bi bi-person-circle text-accent" style="font-size: 60px;"></i>
                                <?php endif; ?>
                            </div>
                            <h6 class="fw-bold text-white mb-2" style="font-size: 14px;">Sesuaikan Foto Profil</h6>
                            <form action="proses_upload.php" method="POST" enctype="multipart/form-data" class="w-100">
                                <div class="mb-2">
                                    <input type="file" name="profile_image" class="form-control form-control-sm bg-dark border-secondary text-white" accept="image/*" style="font-size: 11px;" required>
                                </div>
                                <button type="submit" name="submit_avatar" class="btn btn-sm btn-outline-accent w-100 fw-bold py-1" style="font-size: 11px;">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Perbarui
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Rental Summary Metrics Section -->
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="user-box p-3 rounded-4 text-center" style="border: 1px solid #1f2937;">
                            <span class="text-success fw-bold d-block fs-4 mb-0"><?php echo $active_count; ?></span>
                            <small class="text-secondary" style="font-size: 11px;">Sesi Aktif</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="user-box p-3 rounded-4 text-center" style="border: 1px solid #1f2937;">
                            <span class="text-accent fw-bold d-block fs-4 mb-0"><?php echo $completed_count; ?></span>
                            <small class="text-secondary" style="font-size: 11px;">Sewa Selesai</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="user-box p-3 rounded-4 text-center" style="border: 1px solid #1f2937;">
                            <span class="text-warning fw-bold d-block fs-4 mb-0"><?php echo $total_hours; ?> jam</span>
                            <small class="text-secondary" style="font-size: 11px;">Total Durasi</small>
                        </div>
                    </div>
                </div>

                <!-- Pending Payment Box (Yellow warning style) -->
                <div class="p-3 mb-5 rounded border pending-box" style="border-style: dashed !important;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="text-warning fw-bold small"><i class="bi bi-hourglass-split me-1"></i> Pembayaran Tertunda (Pending)</div>
                            <div class="text-secondary small mt-1">Cyberpunk 2077 <span class="opacity-50">(Durasi Semalam: 6 Jam)</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-secondary small opacity-50">Invoice #SR-9982</span>
                            <div class="text-warning fw-bold">Rp 60.000</div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Game & Riwayat Bermain -->
                <h4 class="fw-bold text-white mb-4"><i class="bi bi-collection-play-fill text-accent me-2"></i>Daftar Game & Riwayat Bermain</h4>
                
                <div class="d-flex flex-column gap-3 mb-5">
                    <?php
                    // Active rentals (queried via SQL view_active_rentals)
                    $active_query = mysqli_query($conn, "SELECT rental_id AS id, user_id, game_id, game_title AS title, game_image_url AS image_url, game_genre AS genre, rent_date, duration_hours, total_price, status FROM view_active_rentals WHERE user_id = '$user_id' ORDER BY rent_date DESC");
                    
                    if ($active_query && mysqli_num_rows($active_query) > 0) {
                        while ($r = mysqli_fetch_assoc($active_query)) {
                            // Calculate remaining time
                            $expiry = strtotime($r['rent_date']) + ($r['duration_hours'] * 3600);
                            $diff = $expiry - time();
                            $remaining_text = "Sesi Berakhir";
                            
                            $total_sec = $r['duration_hours'] * 3600;
                            $elapsed_sec = time() - strtotime($r['rent_date']);
                            $progress_percent = 0;
                            if ($total_sec > 0) {
                                $progress_percent = max(0, min(100, (($total_sec - $elapsed_sec) / $total_sec) * 100));
                            }

                            if ($diff > 0) {
                                $hours = floor($diff / 3600);
                                $minutes = floor(($diff % 3600) / 60);
                                $remaining_text = sprintf("%02d Jam %02d Menit", $hours, $minutes);
                            } else {
                                $progress_percent = 0;
                            }
                            ?>
                             <div class="p-3 rounded active-rental-card glass-panel">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <?php if (!empty($r['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($r['image_url']); ?>" class="rounded" style="width: 50px; height: 65px; object-fit: cover;" alt="<?php echo htmlspecialchars($r['title']); ?>" loading="lazy">
                                        <?php else: ?>
                                            <div class="empty-image rounded d-flex align-items-center justify-content-center bg-secondary" style="width: 50px; height: 65px;">
                                                <small style="font-size: 8px;">Poster</small>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-white"><?php echo htmlspecialchars($r['title']); ?></div>
                                            <small class="text-secondary"><i class="bi bi-tags-fill me-1"></i><?php echo htmlspecialchars($r['genre']); ?> &bull; Masa Rental Aktif <span class="pulse-live-dot ms-1"></span></small>
                                        </div>
                                    </div>
                                    <div class="text-center text-md-end">
                                        <small class="text-secondary d-block" style="font-size: 10px;">Sisa Sesi:</small>
                                        <span class="text-success fw-bold"><i class="bi bi-clock-history me-1"></i><?php echo $remaining_text; ?></span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-primary fw-bold px-3 py-2 rounded-2" onclick="copyActivationCode('STM-<?php echo strtoupper(substr(md5($r['id']), 0, 4) . '-' . substr(md5($r['game_id']), 0, 4) . '-' . substr(md5($r['user_id']), 0, 4)); ?>')"><i class="bi bi-key-fill me-1"></i> Ambil Kode</button>
                                        <a href="proses_kembali.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2 rounded-2 ms-2" onclick="return confirm('Apakah Anda yakin ingin mengembalikan game ini?')">Kembalikan</a>
                                    </div>
                                </div>
                                <!-- dynamic remaining session progress bar -->
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between text-secondary" style="font-size: 10px;">
                                        <span>Progres Rental</span>
                                        <span><?php echo round($progress_percent); ?>% tersisa</span>
                                    </div>
                                    <div class="progress" style="height: 8px; background-color: rgba(255, 255, 255, 0.05); border-radius: 4px; overflow: hidden;">
                                        <div class="progress-bar-custom" style="width: <?php echo $progress_percent; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    
                    // Completed rentals history
                    $completed_query = mysqli_query($conn, "SELECT r.*, g.title, g.image_url, g.genre FROM rentals r JOIN games g ON r.game_id = g.id WHERE r.user_id = '$user_id' AND r.status = 'returned' ORDER BY r.return_date DESC");
                    
                    if (mysqli_num_rows($completed_query) > 0) {
                        while ($r = mysqli_fetch_assoc($completed_query)) {
                            ?>
                                      <div class="d-flex align-items-center justify-content-between p-3 rounded flex-wrap gap-3 completed-rental-item glass-panel">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <?php if (!empty($r['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($r['image_url']); ?>" class="rounded" style="width: 50px; height: 65px; object-fit: cover;" alt="<?php echo htmlspecialchars($r['title']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="empty-image rounded d-flex align-items-center justify-content-center bg-secondary" style="width: 50px; height: 65px;">
                                            <small style="font-size: 8px;">Poster</small>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-white"><?php echo htmlspecialchars($r['title']); ?></div>
                                        <small class="text-secondary"><i class="bi bi-calendar-check-fill me-1"></i>Sewa Selesai (<?php echo date('d M Y H:i', strtotime($r['return_date'])); ?>)</small>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <small class="text-secondary d-block" style="font-size: 10px;">Durasi Lalu:</small>
                                    <span class="text-light fw-semibold"><?php echo $r['duration_hours']; ?> Jam</span>
                                </div>
                                <div>
                                    <?php
                                    $game_id = $r['game_id'];
                                    $review_check_q = mysqli_query($conn, "SELECT id FROM reviews WHERE user_id = '$user_id' AND game_id = '$game_id'");
                                    $already_reviewed = mysqli_num_rows($review_check_q) > 0;
                                    if (!$already_reviewed):
                                    ?>
                                        <button class="btn btn-sm btn-ulas-game fw-bold px-3 py-2 rounded-2 me-2" data-bs-toggle="modal" data-bs-target="#reviewModal" data-game-id="<?php echo $game_id; ?>" data-game-title="<?php echo htmlspecialchars($r['title']); ?>"><i class="bi bi-star-fill me-1"></i> Ulas Game</button>
                                    <?php endif; ?>
                                    <a href="index.php?page=rent&game_id=<?php echo $r['game_id']; ?>" class="btn btn-sm btn-outline-info fw-bold px-3 py-2 rounded-2">Sewa Lagi</a>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    if (mysqli_num_rows($active_query) == 0 && mysqli_num_rows($completed_query) == 0) {
                        ?>
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x text-secondary" style="font-size: 40px;"></i>
                            <p class="text-secondary small mt-2">Anda belum pernah menyewa game apapun.</p>
                            <a href="index.php?page=genre" class="btn btn-sm btn-outline-accent fw-bold px-3 py-1">Cari Game</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- Modal Ulasan Game -->
                <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content glass-panel border border-secondary text-white">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold" id="reviewModalLabel"><i class="bi bi-star-fill text-warning me-2"></i>Berikan Ulasan</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" data-bs-style="color: white;" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="proses_review.php" method="POST">
                                <input type="hidden" name="game_id" id="modal_game_id">
                                <div class="modal-body">
                                    <p class="text-secondary small">Bagikan pengalaman Anda bermain <strong class="text-white" id="modal_game_title">Game</strong>. Ulasan Anda membantu gamer lainnya!</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-white small fw-semibold d-block">Rating Bintang</label>
                                        <div class="star-rating-select">
                                            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 bintang"><i class="bi bi-star-fill"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 bintang"><i class="bi bi-star-fill"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 bintang"><i class="bi bi-star-fill"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 bintang"><i class="bi bi-star-fill"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 bintang"><i class="bi bi-star-fill"></i></label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="comment" class="form-label text-white small fw-semibold">Komentar / Ulasan</label>
                                        <textarea name="comment" id="comment" rows="4" class="form-control bg-dark border-secondary text-white" placeholder="Tulis ulasan Anda di sini..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-sm btn-outline-light px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm bg-accent fw-bold px-3">Kirim Ulasan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const reviewModal = document.getElementById('reviewModal');
                        if (reviewModal) {
                            reviewModal.addEventListener('show.bs.modal', function (event) {
                                const button = event.relatedTarget;
                                const gameId = button.getAttribute('data-game-id');
                                const gameTitle = button.getAttribute('data-game-title');
                                
                                const modalGameIdInput = reviewModal.querySelector('#modal_game_id');
                                const modalGameTitleSpan = reviewModal.querySelector('#modal_game_title');
                                
                                modalGameIdInput.value = gameId;
                                modalGameTitleSpan.textContent = gameTitle;
                            });
                        }
                    });
                </script>

            <?php elseif ($page == 'rent'):
                if (!isset($_SESSION['user_id'])) {
                    echo "<script>alert('Silakan login terlebih dahulu untuk menyewa game!'); window.location='login.php';</script>";
                    exit;
                }
                $rent_game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;
                $rent_game_query = mysqli_query($conn, "
                    SELECT g.*, r.avg_rating, r.review_count 
                    FROM games g 
                    LEFT JOIN (
                        SELECT game_id, AVG(rating) as avg_rating, COUNT(id) as review_count 
                        FROM reviews 
                        GROUP BY game_id
                    ) r ON g.id = r.game_id 
                    WHERE g.id = '$rent_game_id'
                ");
                $rent_game = mysqli_fetch_assoc($rent_game_query);
                if (!$rent_game):
                    echo "<p class=\"text-danger\">Game tidak ditemukan.</p>";
                else:
                    // Fetch PC Specs dynamically
                    $specs_query = mysqli_query($conn, "SELECT * FROM game_specs WHERE game_id = '$rent_game_id'");
                    $specs = mysqli_fetch_assoc($specs_query);

                    // Fetch user reviews scrolling list
                    $reviews_query = mysqli_query($conn, "
                        SELECT r.*, u.username, u.profile_image 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.game_id = '$rent_game_id' 
                        ORDER BY r.created_at DESC
                    ");
            ?>
                <div class="d-flex justify-content-between align-items-end mb-4 animate-fade-in">
                    <h4 class="fw-bold m-0"><i class="bi bi-play-circle-fill text-accent me-2"></i>Form Sewa Game</h4>
                </div>
                
                <div class="row g-4">
                    <!-- Left Column: Game Info & Guidelines -->
                    <div class="col-12 col-lg-8 animate-fade-in">
                        <!-- Premium Game Detail Card -->
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
                            <div class="row g-4">
                                <div class="col-12 col-md-4 text-center text-md-start">
                                    <?php if (!empty($rent_game['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($rent_game['image_url']); ?>" class="rounded-3 shadow w-100 img-fluid" style="max-height: 280px; object-fit: cover;" alt="<?php echo htmlspecialchars($rent_game['title']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="empty-image rounded-3 w-100 d-flex flex-column align-items-center justify-content-center bg-secondary" style="height: 250px;">
                                            <i class="bi bi-image fs-3 mb-1"></i>
                                            <span style="font-size: 11px;">Poster N/A</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-2 py-1"><i class="bi bi-tags-fill me-1"></i> <?php echo htmlspecialchars($rent_game['genre']); ?></span>
                                        <?php 
                                        $stock = intval($rent_game['stock']);
                                        if ($stock <= 0) {
                                            echo '<span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Stok Habis</span>';
                                        } elseif ($stock <= 2) {
                                            echo '<span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Terbatas ('.$stock.')</span>';
                                        } else {
                                            echo '<span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Stok Tersedia ('.$stock.')</span>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <h2 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($rent_game['title']); ?></h2>
                                        <?php 
                                        $avg_rating = !empty($rent_game['avg_rating']) ? number_format($rent_game['avg_rating'], 1) : null;
                                        $review_count = !empty($rent_game['review_count']) ? $rent_game['review_count'] : 0;
                                        if ($avg_rating): 
                                        ?>
                                            <span class="badge bg-warning text-dark d-flex align-items-center gap-1 py-1 px-2 rounded-2" style="font-size: 12px;"><i class="bi bi-star-fill"></i> <?php echo $avg_rating; ?> / 5.0 (<?php echo $review_count; ?> ulasan)</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white-50 d-flex align-items-center gap-1 py-1 px-2 rounded-2" style="font-size: 11px;"><i class="bi bi-star"></i> Belum ada ulasan</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-secondary small mb-3" style="line-height: 1.6;"><?php echo htmlspecialchars($rent_game['description']); ?></p>
                                    
                                    <!-- Specs & Pricing Panel -->
                                    <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-50 mb-3">
                                        <div class="row g-3">
                                            <div class="col-6 col-sm-4">
                                                <small class="text-secondary d-block">Harga Sewa</small>
                                                <span class="text-accent fw-bold fs-5">Rp <?php echo number_format($rent_game['price_per_hour'], 0, ',', '.'); ?><span class="fs-6 text-white fw-normal">/jam</span></span>
                                            </div>
                                            <div class="col-6 col-sm-4">
                                                <small class="text-secondary d-block">Platform</small>
                                                <span class="text-light fw-medium small"><i class="bi bi-windows me-1"></i> PC Windows</span>
                                            </div>
                                            <div class="col-6 col-sm-4">
                                                <small class="text-secondary d-block">Fitur</small>
                                                <span class="text-light fw-medium small"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Cloud Save</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($specs): ?>
                        <!-- PC Specifications Panel -->
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
                            <h5 class="fw-bold text-white mb-3"><i class="bi bi-cpu-fill text-accent me-2"></i>Spesifikasi PC</h5>
                            
                            <ul class="nav nav-tabs border-secondary mb-3" id="specTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold bg-transparent border-0 px-3 pb-2" id="minimum-tab" data-bs-toggle="tab" data-bs-target="#minimum-spec" type="button" role="tab" aria-controls="minimum-spec" aria-selected="true">
                                        <i class="bi bi-hdd-fill me-1"></i> Minimum
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold bg-transparent border-0 px-3 pb-2" id="recommended-tab" data-bs-toggle="tab" data-bs-target="#recommended-spec" type="button" role="tab" aria-controls="recommended-spec" aria-selected="false">
                                        <i class="bi bi-speedometer2 me-1"></i> Rekomendasi
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="specTabsContent">
                                <div class="tab-pane fade show active" id="minimum-spec" role="tabpanel" aria-labelledby="minimum-tab">
                                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                                        <li><strong class="text-white">OS:</strong> <?php echo htmlspecialchars($specs['os_min']); ?></li>
                                        <li><strong class="text-white">Processor:</strong> <?php echo htmlspecialchars($specs['processor_min']); ?></li>
                                        <li><strong class="text-white">RAM:</strong> <?php echo htmlspecialchars($specs['ram_min']); ?></li>
                                        <li><strong class="text-white">GPU:</strong> <?php echo htmlspecialchars($specs['gpu_min']); ?></li>
                                        <li><strong class="text-white">Storage:</strong> <?php echo htmlspecialchars($specs['storage_min']); ?></li>
                                    </ul>
                                </div>
                                <div class="tab-pane fade" id="recommended-spec" role="tabpanel" aria-labelledby="recommended-tab">
                                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                                        <li><strong class="text-white">OS:</strong> <?php echo htmlspecialchars($specs['os_rec']); ?></li>
                                        <li><strong class="text-white">Processor:</strong> <?php echo htmlspecialchars($specs['processor_rec']); ?></li>
                                        <li><strong class="text-white">RAM:</strong> <?php echo htmlspecialchars($specs['ram_rec']); ?></li>
                                        <li><strong class="text-white">GPU:</strong> <?php echo htmlspecialchars($specs['gpu_rec']); ?></li>
                                        <li><strong class="text-white">Storage:</strong> <?php echo htmlspecialchars($specs['storage_rec']); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Rental Guidelines & Terms -->
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4">
                            <h5 class="fw-bold text-white mb-3"><i class="bi bi-info-circle-fill text-accent me-2"></i>Panduan & Ketentuan Rental</h5>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex gap-2 align-items-start">
                                        <i class="bi bi-1-circle text-accent fs-5"></i>
                                        <div>
                                            <h6 class="text-white mb-1 fw-bold small">Dapatkan Kode Aktivasi</h6>
                                            <p class="text-secondary small mb-0">Setelah sewa, ambil kode aktivasi di halaman Koleksi Anda untuk membuka game.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex gap-2 align-items-start">
                                        <i class="bi bi-2-circle text-accent fs-5"></i>
                                        <div>
                                            <h6 class="text-white mb-1 fw-bold small">Durasi Waktu Berjalan</h6>
                                            <p class="text-secondary small mb-0">Waktu rental dihitung secara real-time sejak tombol Sewa ditekan.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex gap-2 align-items-start">
                                        <i class="bi bi-3-circle text-accent fs-5"></i>
                                        <div>
                                            <h6 class="text-white mb-1 fw-bold small">Spesifikasi Minimum PC</h6>
                                            <p class="text-secondary small mb-0">Pastikan perangkat Anda memenuhi spesifikasi sistem game sebelum menyewa.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex gap-2 align-items-start">
                                        <i class="bi bi-4-circle text-accent fs-5"></i>
                                        <div>
                                            <h6 class="text-white mb-1 fw-bold small">Pengembalian Game</h6>
                                            <p class="text-secondary small mb-0">Anda dapat mengembalikan game kapan saja. Sisa durasi yang tidak terpakai tidak dapat direfund.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Reviews & Comments Section -->
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
                            <h5 class="fw-bold text-white mb-4"><i class="bi bi-chat-right-text-fill text-accent me-2"></i>Ulasan & Masukan Pengguna (<?php echo $review_count; ?>)</h5>
                            
                            <?php
                            // Query review distribution
                            $rating_dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                            if ($review_count > 0) {
                                $dist_query = mysqli_query($conn, "SELECT rating, COUNT(*) as count FROM reviews WHERE game_id = '$rent_game_id' GROUP BY rating");
                                if ($dist_query) {
                                    while ($dist_row = mysqli_fetch_assoc($dist_query)) {
                                        $rating_dist[intval($dist_row['rating'])] = intval($dist_row['count']);
                                    }
                                }
                            }
                            ?>
                            
                            <div class="row g-4 mb-4 align-items-center">
                                <div class="col-12 col-md-4 text-center border-end border-secondary border-opacity-25 pb-3 pb-md-0">
                                    <h1 class="display-4 fw-bold text-warning mb-0"><?php echo $avg_rating ? $avg_rating : '0.0'; ?></h1>
                                    <div class="text-warning mb-2" style="font-size: 1.15rem;">
                                        <?php 
                                        $floored_rating = floor(floatval($avg_rating));
                                        for ($star_i = 1; $star_i <= 5; $star_i++) {
                                            if ($star_i <= $floored_rating) {
                                                echo '<i class="bi bi-star-fill star-filled me-1"></i>';
                                            } else {
                                                echo '<i class="bi bi-star star-empty me-1"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <small class="text-secondary d-block"><?php echo $review_count; ?> ulasan pengguna</small>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="d-flex flex-column gap-2">
                                        <?php for ($star_i = 5; $star_i >= 1; $star_i--): 
                                            $count = $rating_dist[$star_i];
                                            $percent = ($review_count > 0) ? ($count / $review_count) * 100 : 0;
                                        ?>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="text-secondary small fw-semibold" style="width: 70px;"><?php echo $star_i; ?> Bintang</span>
                                                <div class="progress flex-grow-1" style="height: 6px; border-radius: 3px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percent; ?>%; border-radius: 3px;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-secondary small" style="width: 30px; text-align: right;"><?php echo $count; ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="border-secondary border-opacity-25 my-4">
                            
                            <?php if (mysqli_num_rows($reviews_query) > 0): ?>
                                <div class="reviews-scroll-container">
                                    <?php while ($rev = mysqli_fetch_assoc($reviews_query)): ?>
                                        <div class="review-card-custom">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="reviewer-avatar-wrapper">
                                                        <?php if (!empty($rev['profile_image']) && file_exists('uploads/profile/' . $rev['profile_image'])): ?>
                                                            <img src="uploads/profile/<?php echo htmlspecialchars($rev['profile_image']); ?>" class="w-100 h-100" style="object-fit: cover;" alt="User Avatar">
                                                        <?php else: ?>
                                                            <i class="bi bi-person text-secondary fs-5"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-white small"><?php echo htmlspecialchars($rev['username']); ?></div>
                                                        <div class="text-warning mt-1" style="font-size: 0.8rem;">
                                                            <?php 
                                                            for ($star_i = 1; $star_i <= 5; $star_i++) {
                                                                if ($star_i <= intval($rev['rating'])) {
                                                                    echo '<i class="bi bi-star-fill star-filled me-1"></i>';
                                                                } else {
                                                                    echo '<i class="bi bi-star star-empty me-1"></i>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-secondary small" style="font-size: 11px;"><i class="bi bi-calendar-event me-1"></i><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                                            </div>
                                            <p class="text-secondary small mb-0 lh-base" style="opacity: 0.95; padding-left: 2px;"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-secondary small">
                                    <i class="bi bi-chat-left-dots fs-3 mb-2 d-block text-secondary"></i>
                                    Belum ada ulasan untuk game ini. Jadilah yang pertama memberikan ulasan!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Checkout Form -->
                    <div class="col-12 col-lg-4">
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="fw-bold text-white mb-4"><i class="bi bi-cart-fill text-accent me-2"></i>Rincian Penyewaan</h5>
                                
                                <?php if ($rent_game['stock'] <= 0): ?>
                                    <div class="alert alert-danger bg-danger bg-opacity-20 text-danger border-0 mb-0">
                                        <i class="bi bi-exclamation-octagon-fill me-2"></i>Stok game ini sedang kosong. Silakan pilih game lain di katalog.
                                    </div>
                                                            <form action="proses_sewa.php" method="POST" id="rentForm">
                                        <input type="hidden" name="game_id" value="<?php echo $rent_game['id']; ?>">
                                        
                                        <!-- Duration Input with Custom Buttons -->
                                        <div class="mb-4">
                                            <label class="form-label text-white small fw-bold">Durasi Sewa (Jam)</label>
                                            <div class="input-group duration-control-custom">
                                                <button type="button" class="btn btn-outline-secondary text-white" id="btnMinus" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="bi bi-dash"></i></button>
                                                <input type="number" name="duration" id="rentDuration" class="form-control auth-form-control bg-dark border-secondary text-white text-center fw-bold" min="1" max="72" value="1" required style="border-radius: 0;">
                                                <button type="button" class="btn btn-outline-secondary text-white" id="btnPlus" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;"><i class="bi bi-plus"></i></button>
                                            </div>
                                            <div class="d-flex justify-content-between text-secondary mt-1" style="font-size: 11px;">
                                                <span>Min. 1 jam</span>
                                                <span>Maks. 72 jam</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Hour Selectors -->
                                        <div class="mb-4">
                                            <label class="form-label text-secondary small d-block mb-2">Pilihan Cepat:</label>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="3">3 Jam</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="6">6 Jam</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="12">12 Jam</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="24">24 Jam</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Balance Status Display Card -->
                                        <div class="p-3 rounded-4 glass-panel border border-secondary mb-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-secondary d-block" style="font-size: 11px;">Saldo Dompet Anda</small>
                                                    <span id="userBalanceDisplay" class="fw-bold fs-5 text-light">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></span>
                                                </div>
                                                <div id="balanceBadge">
                                                    <!-- Managed dynamically -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Receipt Summary Card -->
                                        <div class="p-4 rounded-4 bg-dark bg-opacity-70 border border-secondary mb-4">
                                            <h6 class="text-white fw-bold mb-3" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Ringkasan Pembayaran</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                                <span>Tarif Sewa (<span id="receiptDuration" class="text-accent fw-bold">1</span> Jam)</span>
                                                <span id="receiptRentalCost" class="text-white fw-semibold">Rp <?php echo number_format($rent_game['price_per_hour'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                                <span>Biaya Layanan</span>
                                                <span class="text-white fw-semibold">Rp 2.000</span>
                                            </div>
                                            <hr class="border-secondary border-opacity-50 my-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-white small">Total Pembayaran</span>
                                                <span class="text-accent fw-bold fs-4" id="receiptTotalCost">Rp <?php echo number_format($rent_game['price_per_hour'] + 2000, 0, ',', '.'); ?></span>
                                            </div>
                                        </div>

                                        <!-- Rent Action Button or Top Up recommendation -->
                                        <div id="actionArea">
                                            <!-- Managed dynamically by Javascript -->
                                        </div>
                                    </form>
                                    
                                    <script>
                                        document.addEventListener('DOMContentLoaded', () => {
                                            const durationInput = document.getElementById('rentDuration');
                                            const btnMinus = document.getElementById('btnMinus');
                                            const btnPlus = document.getElementById('btnPlus');
                                            const receiptDuration = document.getElementById('receiptDuration');
                                            const receiptRentalCost = document.getElementById('receiptRentalCost');
                                            const receiptTotalCost = document.getElementById('receiptTotalCost');
                                            const userBalanceDisplay = document.getElementById('userBalanceDisplay');
                                            const balanceBadge = document.getElementById('balanceBadge');
                                            const actionArea = document.getElementById('actionArea');
                                            const quickBtns = document.querySelectorAll('.btn-quick-hour');
 
                                            const rate = <?php echo intval($rent_game['price_per_hour']); ?>;
                                            const userBalance = <?php echo intval($user_balance); ?>;
                                            const serviceFee = 2000;
 
                                            function updatePricing() {
                                                let hours = parseInt(durationInput.value) || 0;
                                                if (hours < 1) {
                                                    hours = 1;
                                                    durationInput.value = 1;
                                                } else if (hours > 72) {
                                                    hours = 72;
                                                    durationInput.value = 72;
                                                }
                                                
                                                const rentalCost = rate * hours;
                                                const totalCost = rentalCost + serviceFee;
 
                                                receiptDuration.textContent = hours;
                                                receiptRentalCost.textContent = 'Rp ' + rentalCost.toLocaleString('id-ID');
                                                receiptTotalCost.textContent = 'Rp ' + totalCost.toLocaleString('id-ID');
 
                                                if (userBalance < totalCost) {
                                                    userBalanceDisplay.className = 'fw-bold fs-5 text-danger';
                                                    balanceBadge.innerHTML = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1" style="font-size: 11px;"><i class="bi bi-x-circle-fill me-1"></i> Saldo Kurang</span>';
                                                    actionArea.innerHTML = `
                                                        <div class="alert alert-warning bg-warning bg-opacity-10 text-warning border-warning border-opacity-25 small mb-3 p-3 rounded-3">
                                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Saldo Anda tidak mencukupi untuk sewa durasi ini. Silakan top up saldo terlebih dahulu.
                                                        </div>
                                                        <a href="index.php?page=topup" class="btn btn-outline-warning w-100 py-2.5 fw-bold rounded-3 hover-scale"><i class="bi bi-wallet2 me-2"></i>Top Up Saldo</a>
                                                    `;
                                                } else {
                                                    userBalanceDisplay.className = 'fw-bold fs-5 text-success';
                                                    balanceBadge.innerHTML = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1" style="font-size: 11px;"><i class="bi bi-check-circle-fill me-1"></i> Saldo Cukup</span>';
                                                    actionArea.innerHTML = `
                                                        <button type="submit" class="btn bg-accent w-100 py-3 fw-bold text-dark rounded-3 shadow-md border-0 hover-scale"><i class="bi bi-play-fill me-1"></i>Bayar & Sewa Sekarang</button>
                                                    `;
                                                }
                                            }
 
                                            if (durationInput) {
                                                durationInput.addEventListener('input', updatePricing);
                                                durationInput.addEventListener('change', updatePricing);
                                            }

                                            if (btnMinus && btnPlus) {
                                                btnMinus.addEventListener('click', () => {
                                                    let val = parseInt(durationInput.value) || 1;
                                                    if (val > 1) {
                                                        durationInput.value = val - 1;
                                                        updatePricing();
                                                    }
                                                });
                                                btnPlus.addEventListener('click', () => {
                                                    let val = parseInt(durationInput.value) || 1;
                                                    if (val < 72) {
                                                        durationInput.value = val + 1;
                                                        updatePricing();
                                                    }
                                                });
                                            }
 
                                            quickBtns.forEach(btn => {
                                                btn.addEventListener('click', () => {
                                                    const hours = btn.getAttribute('data-hours');
                                                    durationInput.value = hours;
                                                    
                                                    // Toggle active class on quick buttons
                                                    quickBtns.forEach(b => {
                                                        b.classList.remove('btn-accent', 'text-dark');
                                                        b.classList.add('btn-outline-secondary', 'text-white');
                                                    });
                                                    btn.classList.remove('btn-outline-secondary', 'text-white');
                                                    btn.classList.add('btn-accent', 'text-dark');
 
                                                    updatePricing();
                                                });
                                            });
 
                                            // Initial invocation
                                            updatePricing();
                                        });
                                    </script>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php elseif ($page == 'topup'):
                if (!isset($_SESSION['user_id'])) {
                    echo "<script>alert('Silakan login terlebih dahulu untuk melakukan top up!'); window.location='login.php';</script>";
                    exit;
                }
            ?>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h4 class="fw-bold m-0"><i class="bi bi-wallet2 text-success me-2"></i>Isi Saldo Akun (Top Up)</h4>
                </div>
                <div class="row">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary">
                            <p class="text-secondary small mb-4">Isi saldo akun SteamRent secara instan untuk melakukan penyewaan game premium tanpa hambatan.</p>
                            
                            <div class="p-3 mb-4 rounded bg-dark bg-opacity-50 border border-secondary d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Saldo Saat Ini:</span>
                                <span class="fs-5 fw-bold text-accent">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></span>
                            </div>

                            <form action="proses_topup.php" method="POST">
                                <div class="mb-4">
                                    <label class="form-label text-white small fw-medium">Jumlah Saldo (Rupiah)</label>
                                    <input type="number" name="amount" id="topupAmount" class="form-control auth-form-control bg-dark border-secondary text-white" min="10000" placeholder="Masukkan jumlah minimal Rp 10.000" required>
                                </div>
                                
                                <label class="form-label text-secondary small fw-medium mb-2">Pilih Nominal Cepat</label>
                                <div class="row g-2 mb-4">
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold" data-value="20000">Rp 20k</button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold" data-value="50000">Rp 50k</button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold" data-value="100000">Rp 100k</button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold" data-value="200000">Rp 200k</button>
                                    </div>
                                </div>

                                <!-- Payment Channel Grid -->
                                <label class="form-label text-white small fw-medium mb-3">Metode Pembayaran</label>
                                
                                <!-- Parent Selectors -->
                                <div class="row g-2 mb-4">
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="payment_parent" id="parent_bank" value="BANK" checked>
                                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_bank">
                                            <i class="bi bi-bank fs-4 d-block mb-1"></i>
                                            <span class="fw-bold small" style="font-size: 10px;">BANK</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="payment_parent" id="parent_wallet" value="WALLET">
                                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_wallet">
                                            <i class="bi bi-wallet2 fs-4 d-block mb-1"></i>
                                            <span class="fw-bold small" style="font-size: 10px;">E-WALLET</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="payment_parent" id="parent_qris" value="QRIS">
                                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_qris">
                                            <i class="bi bi-qr-code-scan fs-4 d-block mb-1"></i>
                                            <span class="fw-bold small" style="font-size: 10px;">QRIS</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Sub-section: Bank Transfer -->
                                <div id="section_bank" class="payment-sub-section mb-4">
                                    <label class="form-label text-white small fw-medium mb-2">Pilih Rekening Bank</label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_bca" value="BCA" checked>
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bca">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">BCA</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                                </div>
                                                <i class="bi bi-bank text-accent fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_bri" value="BRI">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bri">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">BRI</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                                </div>
                                                <i class="bi bi-bank text-info fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_bni" value="BNI">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bni">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">BNI</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                                </div>
                                                <i class="bi bi-bank text-warning fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_mandiri" value="MANDIRI">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_mandiri">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">MANDIRI</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                                </div>
                                                <i class="bi bi-bank2 text-danger fs-5"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-section: E-Wallet -->
                                <div id="section_wallet" class="payment-sub-section mb-4 d-none">
                                    <label class="form-label text-white small fw-medium mb-2">Pilih E-Wallet</label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_dana" value="DANA">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_dana">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">DANA</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                                </div>
                                                <i class="bi bi-wallet2 text-primary fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_gopay" value="GOPAY">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_gopay">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">GoPay</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                                </div>
                                                <i class="bi bi-wallet2 text-success fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_ovo" value="OVO">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_ovo">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">OVO</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                                </div>
                                                <i class="bi bi-wallet2 text-info fs-5"></i>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="pay_shopeepay" value="SHOPEEPAY">
                                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_shopeepay">
                                                <div>
                                                    <span class="d-block fw-bold small text-white">ShopeePay</span>
                                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                                </div>
                                                <i class="bi bi-wallet2 text-danger fs-5"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-section: QRIS -->
                                <div id="section_qris" class="payment-sub-section mb-4 d-none">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_qris" value="QRIS">
                                    <div class="p-3 rounded border border-success" style="background-color: rgba(34, 197, 94, 0.05); border-style: dashed !important;">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <i class="bi bi-qr-code-scan text-success fs-3"></i>
                                            <div>
                                                <h6 class="fw-bold text-white mb-0">Scan QRIS Instant</h6>
                                                <small class="text-secondary">Dukung semua penyedia pembayaran elektronik</small>
                                            </div>
                                        </div>
                                        <p class="text-light small mb-2 mt-2" style="font-size: 12px; opacity: 0.95;">
                                            QRIS dapat digunakan oleh seluruh aplikasi pembayaran yang mendukung QRIS.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="badge bg-success bg-opacity-25 text-success">DANA</span>
                                            <span class="badge bg-success bg-opacity-25 text-success">GoPay</span>
                                            <span class="badge bg-success bg-opacity-25 text-success">OVO</span>
                                            <span class="badge bg-success bg-opacity-25 text-success">ShopeePay</span>
                                            <span class="badge bg-success bg-opacity-25 text-success">Mobile Banking</span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn bg-accent w-100 py-2 fw-bold rounded-3 shadow-sm mt-2">Konfirmasi Top Up</button>
                            </form>
                            <script>
                                const amountInput = document.getElementById('topupAmount');
                                const quickBtns = document.querySelectorAll('.quick-topup-btn');
                                quickBtns.forEach(btn => {
                                    btn.addEventListener('click', () => {
                                        amountInput.value = btn.getAttribute('data-value');
                                    });
                                });

                                // Dynamic Payment Parent Selector Handler
                                const parentBank = document.getElementById('parent_bank');
                                const parentWallet = document.getElementById('parent_wallet');
                                const parentQris = document.getElementById('parent_qris');

                                const sectionBank = document.getElementById('section_bank');
                                const sectionWallet = document.getElementById('section_wallet');
                                const sectionQris = document.getElementById('section_qris');

                                const qrisInput = document.getElementById('pay_qris');

                                function togglePaymentSections() {
                                    if (parentBank.checked) {
                                        sectionBank.classList.remove('d-none');
                                        sectionWallet.classList.add('d-none');
                                        sectionQris.classList.add('d-none');
                                        document.getElementById('pay_bca').checked = true;
                                    } else if (parentWallet.checked) {
                                        sectionBank.classList.add('d-none');
                                        sectionWallet.classList.remove('d-none');
                                        sectionQris.classList.add('d-none');
                                        document.getElementById('pay_dana').checked = true;
                                    } else if (parentQris.checked) {
                                        sectionBank.classList.add('d-none');
                                        sectionWallet.classList.add('d-none');
                                        sectionQris.classList.remove('d-none');
                                        qrisInput.checked = true;
                                    }
                                }

                                if (parentBank && parentWallet && parentQris) {
                                    parentBank.addEventListener('change', togglePaymentSections);
                                    parentWallet.addEventListener('change', togglePaymentSections);
                                    parentQris.addEventListener('change', togglePaymentSections);
                                }
                            </script>
                        </div>
                    </div>
                </div>

            <?php elseif ($page == 'profile'):
                echo "<script>window.location='index.php?page=collections';</script>";
                exit;
            endif;
            ?>

            <div class="pb-5"></div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>