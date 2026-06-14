<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
    <div class="game-card p-2 h-100 d-flex flex-column glass-panel">
        <?php if (!empty($game['badge'])): ?>
            <span class="badge <?php 
                if ($game['badge'] == 'HOT') echo 'badge-premium-hot';
                elseif ($game['badge'] == 'TRENDING') echo 'badge-premium-trending';
                elseif ($game['badge'] == 'NEW') echo 'badge-premium-new';
                else echo 'badge-premium-default';
            ?> card-badge rounded-1 shadow-sm"><?php echo htmlspecialchars($game['badge']); ?></span>
        <?php endif; ?>
        
        <?php if (!empty($game['image_url'])): ?>
            <div class="card-img-wrapper rounded-3 mb-3 overflow-hidden">
                <img src="<?php echo htmlspecialchars($game['image_url']); ?>" class="card-img-custom w-100 h-100" alt="<?php echo htmlspecialchars($game['title']); ?>" loading="lazy">
            </div>
        <?php else: ?>
            <div class="empty-image w-100 mb-3">
                <i class="bi bi-image fs-2 mb-2"></i>
                <span>Poster Belum Ada</span>
            </div>
        <?php endif; ?>

        <div class="px-2 pb-2 d-flex flex-column flex-grow-1">
            <h6 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($game['title']); ?></h6>
            <span class="text-secondary small mb-2"><i class="bi bi-tags-fill me-1"></i> <?php echo htmlspecialchars($game['genre']); ?></span>
            
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
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="bi bi-star-fill me-1"></i>';
                        }
                        if ($half_star) {
                            echo '<i class="bi bi-star-half me-1"></i>';
                        }
                        for ($i = 0; $i < $empty_stars; $i++) {
                            echo '<i class="bi bi-star me-1"></i>';
                        }
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
                <span class="fw-bold text-accent fs-6">Rp <?php echo number_format($game['price_per_hour'], 0, ',', '.'); ?><small class="text-secondary fw-normal">/jam</small></span>
                <a href="index.php?page=rent&game_id=<?php echo $game['id']; ?>" class="btn btn-sm bg-accent fw-bold px-3 py-1 rounded-2">Sewa</a>
            </div>
        </div>
    </div>
</div>
