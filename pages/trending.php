<?php
include_once 'includes/config.php';

// Query data top 10 most rented games
$trending_query = mysqli_query($conn, "
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
    LIMIT 10
");
?>

<div class="row g-4 mb-4 animate-fade-in">
    <div class="col-12">
        <div class="user-box p-4 rounded-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-white mb-1"><i class="bi bi-graph-up-arrow text-accent me-2"></i>Sedang Tren (Top 10 Terpopuler)</h4>
                <p class="text-secondary small mb-0">Daftar game yang paling banyak disewa dan dimainkan oleh komunitas gamer SteamRent.</p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column gap-3 animate-fade-in text-white">
    <?php
    if ($trending_query && mysqli_num_rows($trending_query) > 0) {
        $rank = 1;
        while ($game = mysqli_fetch_assoc($trending_query)) {
            $avg_rating = floatval($game['avg_rating']);
            $review_count = intval($game['review_count']);
            ?>
            <div class="p-3 rounded glass-panel d-flex flex-wrap align-items-center justify-content-between gap-3 border border-secondary border-opacity-25 trending-row-item">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- Rank badge -->
                    <div class="rank-number d-flex align-items-center justify-content-center fw-bold fs-4 text-secondary" style="width: 40px;">
                        #<?php echo $rank++; ?>
                    </div>
                    
                    <?php if (!empty($game['Image_URL'])): ?>
                        <img src="<?php echo htmlspecialchars($game['Image_URL']); ?>" class="rounded" style="width: 50px; height: 65px; object-fit: cover;" alt="<?php echo htmlspecialchars($game['Game_Name']); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="empty-image rounded d-flex align-items-center justify-content-center bg-secondary" style="width: 50px; height: 65px;">
                            <small style="font-size: 8px;">Poster</small>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <div class="fw-bold text-white fs-5"><?php echo htmlspecialchars($game['Game_Name']); ?></div>
                        <small class="text-secondary">
                            <i class="bi bi-tags-fill me-1"></i><?php echo htmlspecialchars($game['Genre']); ?>
                        </small>
                    </div>
                </div>

                <!-- Total Rentals Stat -->
                <div class="text-start text-md-center">
                    <small class="text-secondary d-block" style="font-size: 10px;">Total Disewa:</small>
                    <span class="fw-bold text-accent"><i class="bi bi-play-circle-fill me-1"></i><?php echo $game['total_rentals']; ?> Kali</span>
                </div>

                <!-- Rating -->
                <div class="text-start text-md-center">
                    <small class="text-secondary d-block" style="font-size: 10px;">Penilaian:</small>
                    <?php if ($avg_rating > 0): ?>
                        <span class="text-warning small d-inline-flex align-items-center">
                            <?php
                            $full_stars = floor($avg_rating);
                            $half_star = ($avg_rating - $full_stars) >= 0.5 ? 1 : 0;
                            for ($i = 0; $i < $full_stars; $i++) echo '<i class="bi bi-star-fill me-1"></i>';
                            if ($half_star) echo '<i class="bi bi-star-half me-1"></i>';
                            for ($i = 0; $i < (5 - $full_stars - $half_star); $i++) echo '<i class="bi bi-star me-1"></i>';
                            ?>
                            <span class="text-secondary ms-1">(<?php echo number_format($avg_rating, 1); ?>)</span>
                        </span>
                    <?php else: ?>
                        <span class="text-muted small">Belum ada rating</span>
                    <?php endif; ?>
                </div>

                <!-- Hourly price & Rental Action -->
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <small class="text-secondary d-block" style="font-size: 9px;">Mulai dari:</small>
                        <span class="fw-bold text-white">Rp <?php echo number_format($game['Hourly_Price'], 0, ',', '.'); ?>/jam</span>
                    </div>
                    <a href="index.php?page=rent&game_id=<?php echo $game['GameID']; ?>" class="btn btn-sm bg-accent fw-bold px-3 py-2 rounded-2">Sewa</a>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="text-center py-5 user-box rounded-4">
            <i class="bi bi-graph-down text-secondary" style="font-size: 40px;"></i>
            <p class="text-secondary small mt-2">Belum ada data persewaan untuk membuat daftar tren.</p>
        </div>
        <?php
    }
    ?>
</div>
