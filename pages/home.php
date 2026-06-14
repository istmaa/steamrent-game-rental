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

// Fetch games for the grid layout below
$games_query = mysqli_query($conn, "
    SELECT g.*, r.avg_rating, r.review_count 
    FROM game g 
    LEFT JOIN (
        SELECT GameID, AVG(Rating) as avg_rating, COUNT(ReviewID) as review_count 
        FROM reviews 
        GROUP BY GameID
    ) r ON g.GameID = r.GameID 
    ORDER BY g.GameID DESC 
    LIMIT 12
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
            <?php foreach ($carousel_games as $index => $game): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="carousel-item-content position-relative" style="background-image: url('<?php echo htmlspecialchars($game['Image_URL']); ?>');">
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
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
<?php endif; ?>

<!-- Game Section Title -->
<div class="d-flex justify-content-between align-items-end mb-4 animate-fade-in">
    <h4 class="fw-bold m-0">
        <i class="bi bi-fire text-accent me-2"></i> Game Populer Tersedia
    </h4>
    <a href="index.php?page=games" class="text-accent text-decoration-none small fw-semibold">
        Lihat Semua Katalog <i class="bi bi-arrow-right"></i>
    </a>
</div>

<!-- Game Cards Grid Layout -->
<!-- Responsive settings: desktop (lg) 4 columns, tablet (md) 2 columns, mobile 1 column -->
<div class="row g-4 mb-5">
    <?php
    if ($games_query && mysqli_num_rows($games_query) > 0) {
        while ($game = mysqli_fetch_assoc($games_query)) {
            // Include game card logic inside the grid columns.
            // Note: Each card will be wrapped inside includes/card_game.php
            // We need to make sure the column size is defined within card_game or outside.
            // In card_game.php, it has: <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            // Wait! The user request specifies:
            // "Grid layout: desktop: 4 columns, tablet: 2 columns, mobile: 1 column"
            // Let's make sure the card wrapper matches exactly!
            // In Bootstrap:
            // - mobile: col-12 (1 column)
            // - tablet: col-md-6 (2 columns)
            // - desktop: col-lg-3 (4 columns)
            // Let's override the column wrapping in home.php and games.php by rendering the card layout directly,
            // or let's use the includes/card_game.php file which has col-12 col-sm-6 col-lg-4 col-xl-3.
            // Wait, col-xl-3 is 4 columns on desktop, col-lg-4 is 3 columns on desktop, col-sm-6 is 2 columns on tablet, col-12 is 1 column on mobile.
            // Let's modify includes/card_game.php so it uses:
            // - col-xl-3 (desktop: 4 columns)
            // - col-lg-3 (desktop/laptop: 4 columns)
            // - col-md-6 (tablet: 2 columns)
            // - col-12 (mobile: 1 column)
            // This matches the lecturer's grid requirement perfectly! Let's update includes/card_game.php.
            include 'includes/card_game.php';
        }
    } else {
        echo '<div class="col-12"><p class="text-secondary small">Tidak ada game ditemukan.</p></div>';
    }
    ?>
</div>
