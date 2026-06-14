<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];

// Ambil profil user lengkap
$profile_query = mysqli_query($conn, "SELECT Name, Email, CreatedAt, Profile_Image, Balance FROM users WHERE UserID = '$user_id'");
$user = mysqli_fetch_assoc($profile_query);

if (!$user) {
    echo "<p class='text-danger'>User tidak ditemukan.</p>";
    exit;
}

// Ambil jumlah total rental (Subquery/Agregat)
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total_rentals FROM rental WHERE UserID = '$user_id'");
$count_data = mysqli_fetch_assoc($count_query);
$total_rentals = $count_data['total_rentals'];

// Ambil riwayat rental selesai (terbaru 5 data) untuk ringkasan
$completed_query = mysqli_query($conn, "
    SELECT r.*, g.Game_Name, g.Image_URL, g.Genre 
    FROM rental r 
    JOIN game g ON r.GameID = g.GameID 
    WHERE r.UserID = '$user_id' AND r.Status = 'returned' 
    ORDER BY r.End_Time DESC 
    LIMIT 5
");
?>

<div class="row g-4 animate-fade-in">
    <!-- Kolom Kiri: Kartu Profil Utama -->
    <div class="col-12 col-md-5 col-lg-4">
        <div class="user-box p-4 rounded-4 text-center text-white d-flex flex-column align-items-center">
            <!-- Avatar Display -->
            <div class="position-relative mb-3">
                <?php if (!empty($user['Profile_Image']) && file_exists('uploads/profile/' . $user['Profile_Image'])): ?>
                    <img src="uploads/profile/<?php echo htmlspecialchars($user['Profile_Image']); ?>" class="rounded-circle border border-accent border-3" style="width: 110px; height: 110px; object-fit: cover;" alt="Avatar">
                <?php else: ?>
                    <i class="bi bi-person-circle text-accent" style="font-size: 100px;"></i>
                <?php endif; ?>
            </div>

            <h4 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($user['Name']); ?></h4>
            <p class="text-secondary small mb-3"><?php echo htmlspecialchars($user['Email']); ?></p>

            <div class="w-100 p-3 rounded bg-dark bg-opacity-40 mb-3 text-start small border border-secondary border-opacity-50">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Tanggal Bergabung:</span>
                    <span class="text-white fw-medium"><?php echo date('d M Y', strtotime($user['CreatedAt'])); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Total Transaksi Sewa:</span>
                    <span class="text-white fw-bold"><?php echo $total_rentals; ?> Kali</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Sisa Saldo:</span>
                    <span class="text-accent fw-bold">Rp <?php echo number_format($user['Balance'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <!-- Avatar Editor Widget -->
            <div class="w-100 border-top border-secondary border-opacity-25 pt-3 mt-2">
                <h6 class="fw-bold text-white text-start mb-2" style="font-size: 13px;">Ganti Foto Profil:</h6>
                <form action="proses_upload.php" method="POST" enctype="multipart/form-data" class="w-100 text-start">
                    <div class="mb-2">
                        <input type="file" name="profile_image" class="form-control form-control-sm bg-dark border-secondary text-white" accept="image/*" style="font-size: 11px;" required>
                    </div>
                    <button type="submit" name="submit_avatar" class="btn btn-sm bg-accent w-100 fw-bold py-1.5" style="font-size: 11px;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Perbarui Avatar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Ringkasan Riwayat Rental -->
    <div class="col-12 col-md-7 col-lg-8 text-white">
        <div class="user-box p-4 rounded-4 h-100 border border-secondary">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-clock-history text-accent me-2"></i>Ringkasan Riwayat Bermain (Selesai)</h5>

            <div class="d-flex flex-column gap-3">
                <?php
                if ($completed_query && mysqli_num_rows($completed_query) > 0) {
                    while ($r = mysqli_fetch_assoc($completed_query)) {
                        ?>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded flex-wrap gap-3 completed-rental-item glass-panel">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <?php if (!empty($r['Image_URL'])): ?>
                                    <img src="<?php echo htmlspecialchars($r['Image_URL']); ?>" class="rounded" style="width: 50px; height: 65px; object-fit: cover;" alt="<?php echo htmlspecialchars($r['Game_Name']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="empty-image rounded d-flex align-items-center justify-content-center bg-secondary" style="width: 50px; height: 65px;">
                                        <small style="font-size: 8px;">Poster</small>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($r['Game_Name']); ?></div>
                                    <small class="text-secondary">
                                        <i class="bi bi-calendar-check-fill me-1"></i>Selesai (<?php echo date('d M Y H:i', strtotime($r['End_Time'])); ?>)
                                    </small>
                                </div>
                            </div>
                            <div class="text-center text-md-end">
                                <small class="text-secondary d-block" style="font-size: 10px;">Durasi Bermain:</small>
                                <span class="text-light fw-semibold"><?php echo $r['Duration']; ?> Jam</span>
                            </div>
                            <div>
                                <!-- Tulis Ulasan Trigger Modal -->
                                <?php
                                $game_id = $r['GameID'];
                                $review_check_q = mysqli_query($conn, "SELECT ReviewID FROM reviews WHERE UserID = '$user_id' AND GameID = '$game_id'");
                                $already_reviewed = mysqli_num_rows($review_check_q) > 0;
                                if (!$already_reviewed):
                                ?>
                                    <button class="btn btn-sm btn-ulas-game fw-bold px-3 py-2 rounded-2 me-2" data-bs-toggle="modal" data-bs-target="#reviewModal" data-game-id="<?php echo $game_id; ?>" data-game-title="<?php echo htmlspecialchars($r['Game_Name']); ?>">
                                        <i class="bi bi-star-fill me-1"></i> Ulas Game
                                    </button>
                                <?php endif; ?>
                                <a href="index.php?page=rent&game_id=<?php echo $r['GameID']; ?>" class="btn btn-sm btn-outline-info fw-bold px-3 py-2 rounded-2">Sewa Lagi</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x text-secondary" style="font-size: 30px;"></i>
                        <p class="text-secondary small mt-2 mb-0">Belum ada riwayat sewa game yang selesai.</p>
                    </div>
                    <?php
                }
                ?>
            </div>

            <?php if ($total_rentals > 5): ?>
                <div class="text-center mt-4 border-top border-secondary border-opacity-25 pt-3">
                    <a href="index.php?page=rental-history" class="text-accent text-decoration-none small fw-semibold">
                        Lihat Seluruh Riwayat Rental <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Ulasan Game -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-panel border border-secondary text-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="reviewModalLabel"><i class="bi bi-star-fill text-warning me-2"></i>Berikan Ulasan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
