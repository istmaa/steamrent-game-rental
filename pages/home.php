<?php
include_once 'includes/config.php';

// Fetch the 4 famous games for the Auto Carousel Banner
$carousel_ids = [1, 2, 12, 11]; // Cyberpunk 2077, Elden Ring, Red Dead Redemption 2, GTA V
$ids_str = implode(',', $carousel_ids);
$carousel_query = mysqli_query($conn, "
    SELECT g.*, r.avg_rating, r.review_count 
    FROM game g 
    LEFT JOIN (
        SELECT GameID, AVG(Rating) as avg_rating, COUNT(ReviewID) as review_count 
        FROM reviews 
        GROUP BY GameID
    ) r ON g.GameID = r.GameID 
    WHERE g.GameID IN ($ids_str) 
    ORDER BY FIELD(g.GameID, 1, 2, 12, 11)
");

$carousel_games = [];
if ($carousel_query) {
    while ($row = mysqli_fetch_assoc($carousel_query)) {
        $carousel_games[] = $row;
    }
}

// Fetch 4 top trending games based on rental count for "Terpopuler Saat Ini"
$trending_home_query = mysqli_query($conn, "
    SELECT g.GameID, g.Game_Name, g.Genre, g.Image_URL, g.Hourly_Price,
           COALESCE(rentals.total_rentals, 0) AS total_rentals,
           COALESCE(rev.avg_rating, 0) AS avg_rating,
           COALESCE(rev.review_count, 0) AS review_count
    FROM game g
    LEFT JOIN (
        SELECT GameID, COUNT(RentalID) AS total_rentals
        FROM rental
        GROUP BY GameID
    ) rentals ON g.GameID = rentals.GameID
    LEFT JOIN (
        SELECT GameID, AVG(Rating) AS avg_rating, COUNT(ReviewID) AS review_count
        FROM reviews
        GROUP BY GameID
    ) rev ON g.GameID = rev.GameID
    ORDER BY total_rentals DESC, g.Game_Name ASC
    LIMIT 4
");

// Fetch 8 newest games for "Rilis Terbaru"
$newest_home_query = mysqli_query($conn, "
    SELECT g.*, r.avg_rating, r.review_count 
    FROM game g 
    LEFT JOIN (
        SELECT GameID, AVG(Rating) as avg_rating, COUNT(ReviewID) as review_count 
        FROM reviews 
        GROUP BY GameID
    ) r ON g.GameID = r.GameID 
    ORDER BY g.GameID DESC 
    LIMIT 8
");
?>

<!-- Auto Carousel Banner Section -->
<?php if (!empty($carousel_games)): ?>
    <div id="heroCarousel" class="carousel slide hero-banner mb-5" data-bs-ride="carousel" data-bs-interval="5000">
        <!-- Indicators/Dots -->
        <div class="carousel-indicators">
            <?php for ($i = 0; $i < count($carousel_games); $i++): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : ''; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>

        <!-- Slides -->
        <div class="carousel-inner">
            <?php foreach ($carousel_games as $index => $game): 
                $banner_path = '';
                if ($game['GameID'] == 1) {
                    $banner_path = 'assets/images/banners/cyberpunk_banner.png';
                } elseif ($game['GameID'] == 2) {
                    $banner_path = 'assets/images/banners/elden_ring_banner.png';
                } elseif ($game['GameID'] == 12) {
                    $banner_path = 'assets/images/banners/rdr2_banner.png';
                } elseif ($game['GameID'] == 11) {
                    $banner_path = 'assets/images/banners/gta_v_banner.png';
                }
                
                // Check if banner file exists, else use neutral dark gradient fallback
                $banner_exists = !empty($banner_path) && file_exists(dirname(__DIR__) . '/' . $banner_path);
                
                $inline_style = $banner_exists 
                    ? "background-image: url('" . htmlspecialchars($banner_path) . "');" 
                    : "background: linear-gradient(135deg, #151a2e 0%, #0d111b 100%);";
            ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="carousel-item-content position-relative" style="<?php echo $inline_style; ?>">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-text-wrapper text-white">
                            <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill">
                                <i class="bi bi-star-fill text-warning me-1"></i> Rekomendasi Game
                            </span>
                            <h1 class="display-5 fw-bold mb-2 lh-sm"><?php echo htmlspecialchars($game['Game_Name']); ?></h1>
                            <p class="text-light opacity-75 mb-4" style="font-size: 15px;"><?php echo htmlspecialchars($game['Description']); ?></p>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="fw-bold text-accent fs-4">Rp <?php echo number_format($game['Hourly_Price'], 0, ',', '.'); ?>/jam</span>
                                <a href="index.php?page=rent&game_id=<?php echo $game['GameID']; ?>" class="btn bg-accent fw-bold px-4 py-2.5 rounded-3 d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-play-circle-fill"></i> Sewa Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Manual Arrows -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="z-index: 10;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="z-index: 10;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
<?php endif; ?>

<!-- Section 1: Terpopuler Saat Ini -->
<div class="d-flex justify-content-between align-items-end mb-4 mt-5 pt-3 animate-fade-in text-white">
    <h3 class="fw-bold m-0 fs-3">
        ⚡ Terpopuler Saat Ini
    </h3>
    <a href="index.php?page=trending" class="section-link-custom">
        Lihat Semua &rarr;
    </a>
</div>

<div class="row g-4 mb-5">
    <?php
    if ($trending_home_query && mysqli_num_rows($trending_home_query) > 0) {
        while ($game = mysqli_fetch_assoc($trending_home_query)) {
            ?>
            <div class="col-12 col-md-6 col-lg-3 animate-fade-in">
                <div class="game-card p-2 h-100 d-flex flex-column glass-panel">
                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 card-badge rounded-1 shadow-sm px-2 py-1">
                        <i class="bi bi-fire me-1"></i> <?php echo $game['total_rentals']; ?> Disewa
                    </span>
                    
                    <?php if (!empty($game['Image_URL'])): ?>
                        <div class="card-img-wrapper rounded-3 mb-3 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($game['Image_URL']); ?>" class="card-img-custom w-100 h-100" alt="<?php echo htmlspecialchars($game['Game_Name']); ?>" loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="empty-image w-100 mb-3">
                            <i class="bi bi-image fs-2 mb-2"></i>
                            <span>Poster Belum Ada</span>
                        </div>
                    <?php endif; ?>

                    <div class="px-2 pb-2 d-flex flex-column flex-grow-1">
                        <h6 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($game['Game_Name']); ?></h6>
                        <span class="text-secondary small mb-2"><i class="bi bi-tags-fill me-1"></i> <?php echo htmlspecialchars($game['Genre']); ?></span>
                        
                        <div class="d-flex align-items-center mb-3 flex-wrap gap-1">
                            <?php 
                            $avg_rating = !empty($game['avg_rating']) ? number_format($game['avg_rating'], 1) : null;
                            $review_count = !empty($game['review_count']) ? $game['review_count'] : 0;
                            if ($avg_rating): 
                                $rating_val = floatval($game['avg_rating']);
                                $full_stars = floor($rating_val);
                                $half_star = ($rating_val - $full_stars) >= 0.5 ? 1 : 0;
                                $empty_stars = 5 - $full_stars - $half_star;
                            ?>
                                <span class="text-warning small d-inline-flex align-items-center">
                                    <?php
                                    for ($i = 0; $i < $full_stars; $i++) echo '<i class="bi bi-star-fill me-1"></i>';
                                    if ($half_star) echo '<i class="bi bi-star-half me-1"></i>';
                                    for ($i = 0; $i < $empty_stars; $i++) echo '<i class="bi bi-star me-1"></i>';
                                    ?>
                                </span>
                                <span class="text-secondary ms-1" style="font-size: 0.8rem;"><?php echo $avg_rating; ?> (<?php echo $review_count; ?>)</span>
                            <?php else: ?>
                                <span class="text-muted small d-inline-flex align-items-center">
                                    <?php for ($i = 0; $i < 5; $i++) echo '<i class="bi bi-star me-1"></i>'; ?>
                                </span>
                                <span class="text-secondary ms-1" style="font-size: 0.8rem;">(0)</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-accent fs-6">Rp <?php echo number_format($game['Hourly_Price'], 0, ',', '.'); ?><small class="text-secondary fw-normal">/jam</small></span>
                            <a href="index.php?page=rent&game_id=<?php echo $game['GameID']; ?>" class="btn btn-sm bg-accent fw-bold px-3 py-1 rounded-2">Sewa</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><p class="text-secondary small">Tidak ada game terpopuler.</p></div>';
    }
    ?>
</div>

<!-- Section 2: Rilis Terbaru -->
<div class="d-flex justify-content-between align-items-end mb-4 mt-5 pt-3 animate-fade-in text-white">
    <h3 class="fw-bold m-0 fs-3">
        🚀 Rilis Terbaru
    </h3>
    <a href="index.php?page=games" class="section-link-custom">
        Lihat Semua Katalog &rarr;
    </a>
</div>

<div class="row g-4 mb-5">
    <?php
    if ($newest_home_query && mysqli_num_rows($newest_home_query) > 0) {
        while ($game = mysqli_fetch_assoc($newest_home_query)) {
            // Gunakan standard card_game.php
            include 'includes/card_game.php';
        }
    } else {
        echo '<div class="col-12"><p class="text-secondary small">Tidak ada game terbaru.</p></div>';
    }
    ?>
</div>
