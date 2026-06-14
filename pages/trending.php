<?php
include_once 'includes/config.php';

// Query data top 10 most rented games
$trending_query = mysqli_query($conn, "
    SELECT g.GameID, g.Game_Name, g.Genre, g.Image_URL, g.Hourly_Price, g.Description,
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

$games_list = [];
if ($trending_query) {
    while ($row = mysqli_fetch_assoc($trending_query)) {
        $games_list[] = $row;
    }
}
?>

<div class="row g-4 mb-4 animate-fade-in text-white">
    <div class="col-12">
        <div class="user-box p-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-white mb-1"><i class="bi bi-graph-up-arrow text-accent me-2"></i>Tren Popularitas Game (Top 10)</h4>
                <p class="text-secondary small mb-0">Statistik real-time game yang paling banyak disewa dan dimainkan minggu ini.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($games_list)): ?>
    <div class="d-flex flex-column gap-3 animate-fade-in mb-5 text-white">
        <?php
        for ($idx = 0; $idx < count($games_list); $idx++) {
            $g = $games_list[$idx];
            $g_rating = floatval($g['avg_rating']);
            $rank = $idx + 1;
            
            if ($rank === 1) {
                $row_class = "trending-row-item d-flex align-items-center justify-content-between gap-3 border rounded py-3 px-3 glass-panel rank-1-premium";
                $badge_html = '<span class="badge-custom-rank badge-gold-solid"><i class="bi bi-trophy-fill me-1"></i>🥇 TOP 1</span>';
                $img_style = 'width: 55px; height: 72px; object-fit: cover;';
                $title_class = 'fw-bold text-white fs-5 text-truncate mb-0';
                $genre_style = 'font-size: 14px;';
            } elseif ($rank === 2) {
                $row_class = "trending-row-item d-flex align-items-center justify-content-between gap-3 border rounded py-2.5 px-3 glass-panel rank-2-silver";
                $badge_html = '<span class="badge-custom-rank badge-silver-solid"><i class="bi bi-award-fill me-1"></i>🥈 TOP 2</span>';
                $img_style = 'width: 48px; height: 62px; object-fit: cover;';
                $title_class = 'fw-bold text-white text-truncate mb-0';
                $genre_style = 'font-size: 13px;';
            } elseif ($rank === 3) {
                $row_class = "trending-row-item d-flex align-items-center justify-content-between gap-3 border rounded py-2.5 px-3 glass-panel rank-3-bronze";
                $badge_html = '<span class="badge-custom-rank badge-bronze-solid"><i class="bi bi-award-fill me-1"></i>🥉 TOP 3</span>';
                $img_style = 'width: 48px; height: 62px; object-fit: cover;';
                $title_class = 'fw-bold text-white text-truncate mb-0';
                $genre_style = 'font-size: 13px;';
            } else {
                $row_class = "trending-row-item d-flex align-items-center justify-content-between gap-3 border border-secondary border-opacity-25 rounded py-2 px-3 glass-panel";
                $badge_html = '<div class="rank-number">#' . $rank . '</div>';
                $img_style = 'width: 45px; height: 58px; object-fit: cover;';
                $title_class = 'fw-bold text-white text-truncate mb-0';
                $genre_style = 'font-size: 13px;';
            }
            ?>
            <div class="<?php echo $row_class; ?>">
                
                <!-- Rank Column -->
                <div class="trending-rank">
                    <?php echo $badge_html; ?>
                </div>

                <!-- Game Info Column -->
                <div class="trending-info">
                    <?php if (!empty($g['Image_URL'])): ?>
                        <img src="<?php echo htmlspecialchars($g['Image_URL']); ?>" class="rounded shadow-sm flex-shrink-0" style="<?php echo $img_style; ?>" alt="">
                    <?php endif; ?>
                    <div class="min-w-0">
                        <div class="<?php echo $title_class; ?>"><?php echo htmlspecialchars($g['Game_Name']); ?></div>
                        <span class="text-secondary d-block text-truncate" style="<?php echo $genre_style; ?>">
                            <i class="bi bi-tags-fill me-1 text-accent"></i><?php echo htmlspecialchars($g['Genre']); ?>
                        </span>
                    </div>
                </div>

                <!-- Total Rentals Column -->
                <div class="trending-stat d-none d-sm-block">
                    <small class="text-secondary d-block" style="font-size: 10px;">Total Disewa:</small>
                    <span class="fw-bold text-light fs-6"><?php echo $g['total_rentals']; ?> Sesi</span>
                </div>

                <!-- Rating Column -->
                <div class="trending-rating d-none d-md-block">
                    <small class="text-secondary d-block" style="font-size: 10px;">Penilaian:</small>
                    <?php if ($g_rating > 0): ?>
                        <span class="text-warning small d-inline-flex align-items-center fw-semibold" style="font-size: 13px;">
                            <i class="bi bi-star-fill me-1"></i><?php echo number_format($g_rating, 1); ?>
                        </span>
                    <?php else: ?>
                        <span class="rating-text-subtle small">Belum ada rating</span>
                    <?php endif; ?>
                </div>

                <!-- Price Column -->
                <div class="trending-price">
                    <small class="text-secondary d-block" style="font-size: 10px;">Harga Sewa:</small>
                    <span class="fw-bold text-accent fs-6">Rp <?php echo number_format($g['Hourly_Price'], 0, ',', '.'); ?><small class="text-secondary fw-normal">/jam</small></span>
                </div>

                <!-- Action Button Column -->
                <div class="trending-action">
                    <a href="index.php?page=rent&game_id=<?php echo $g['GameID']; ?>" class="btn btn-sm bg-accent fw-bold px-3 py-2 rounded-2 text-dark shadow-sm" style="min-width: 80px;">Sewa</a>
                </div>

            </div>
            <?php
        }
        ?>
    </div>
<?php else: ?>
    <div class="text-center py-5 user-box rounded-4">
        <i class="bi bi-graph-down text-secondary" style="font-size: 40px;"></i>
        <p class="text-secondary small mt-2">Belum ada data persewaan untuk membuat tren.</p>
    </div>
<?php endif; ?>
