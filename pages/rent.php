<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];
$rent_game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

// Ambil detail game lengkap dengan rating rata-rata
$rent_game_query = mysqli_query($conn, "
    SELECT g.*, r.avg_rating, r.review_count 
    FROM game g 
    LEFT JOIN (
        SELECT GameID, AVG(Rating) as avg_rating, COUNT(ReviewID) as review_count 
        FROM reviews 
        GROUP BY GameID
    ) r ON g.GameID = r.GameID 
    WHERE g.GameID = '$rent_game_id'
");
$rent_game = mysqli_fetch_assoc($rent_game_query);

if (!$rent_game) {
    echo "<p class=\"text-danger\">Game tidak ditemukan.</p>";
} else {
    // Ambil ulasan ulasan game
    $reviews_query = mysqli_query($conn, "
        SELECT r.*, u.Name, u.Profile_Image 
        FROM reviews r 
        JOIN users u ON r.UserID = u.UserID 
        WHERE r.GameID = '$rent_game_id' 
        ORDER BY r.CreatedAt DESC
    ");
    $review_count = $rent_game['review_count'] ? $rent_game['review_count'] : 0;
?>
<div class="d-flex justify-content-between align-items-end mb-4 animate-fade-in text-white">
    <h4 class="fw-bold m-0"><i class="bi bi-play-circle-fill text-accent me-2"></i>Form Sewa Game</h4>
</div>

<div class="row g-4 text-white">
    <!-- Left Column: Game Info & Reviews -->
    <div class="col-12 col-lg-8 animate-fade-in">
        <!-- Game Details Card -->
        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
            <div class="row g-4">
                <div class="col-12 col-md-4 text-center text-md-start">
                    <?php if (!empty($rent_game['Image_URL'])): ?>
                        <img src="<?php echo htmlspecialchars($rent_game['Image_URL']); ?>" class="rounded-3 shadow w-100 img-fluid" style="max-height: 280px; object-fit: cover;" alt="<?php echo htmlspecialchars($rent_game['Game_Name']); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="empty-image rounded-3 w-100 d-flex flex-column align-items-center justify-content-center bg-secondary" style="height: 250px;">
                            <i class="bi bi-image fs-3 mb-1"></i>
                            <span style="font-size: 11px;">Poster N/A</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-8">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-2 py-1"><i class="bi bi-tags-fill me-1"></i> <?php echo htmlspecialchars($rent_game['Genre']); ?></span>
                        <?php 
                        $stock = intval($rent_game['Stock']);
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
                        <h2 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($rent_game['Game_Name']); ?></h2>
                        <?php 
                        $avg_rating = !empty($rent_game['avg_rating']) ? number_format($rent_game['avg_rating'], 1) : null;
                        if ($avg_rating): 
                        ?>
                            <span class="badge bg-warning text-dark d-flex align-items-center gap-1 py-1 px-2 rounded-2" style="font-size: 12px;"><i class="bi bi-star-fill"></i> <?php echo $avg_rating; ?> / 5.0 (<?php echo $review_count; ?> ulasan)</span>
                        <?php else: ?>
                            <span class="badge bg-secondary text-white-50 d-flex align-items-center gap-1 py-1 px-2 rounded-2" style="font-size: 11px;"><i class="bi bi-star"></i> Belum ada ulasan</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-secondary small mb-3" style="line-height: 1.6;"><?php echo htmlspecialchars($rent_game['Description']); ?></p>
                    
                    <!-- Specs Info Box -->
                    <div class="p-3 rounded bg-dark bg-opacity-40 border border-secondary border-opacity-50 mb-3">
                        <div class="row g-3">
                            <div class="col-6 col-sm-4">
                                <small class="text-secondary d-block">Harga Sewa</small>
                                <span class="text-accent fw-bold fs-5">Rp <?php echo number_format($rent_game['Hourly_Price'], 0, ',', '.'); ?><span class="fs-6 text-white fw-normal">/jam</span></span>
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

        <!-- PC Specifications (Elegant Presentation) -->
        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-cpu-fill text-accent me-2"></i>Spesifikasi PC (Rekomendasi Umum)</h5>
            <div class="row g-3 text-secondary small">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded bg-dark bg-opacity-30 border border-secondary border-opacity-50">
                        <h6 class="text-white fw-bold mb-2">Minimum:</h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                            <li><strong>OS:</strong> Windows 10 64-bit</li>
                            <li><strong>CPU:</strong> Intel Core i5-6600 or AMD Ryzen 5 1400</li>
                            <li><strong>RAM:</strong> 12 GB RAM</li>
                            <li><strong>GPU:</strong> NVIDIA GTX 1060 6GB or Radeon RX 580</li>
                            <li><strong>Storage:</strong> 70 GB SSD Space</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded bg-dark bg-opacity-30 border border-secondary border-opacity-50">
                        <h6 class="text-white fw-bold mb-2">Rekomendasi:</h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                            <li><strong>OS:</strong> Windows 10/11 64-bit</li>
                            <li><strong>CPU:</strong> Intel Core i7-8700 or AMD Ryzen 5 3600</li>
                            <li><strong>RAM:</strong> 16 GB RAM</li>
                            <li><strong>GPU:</strong> NVIDIA RTX 2060 or AMD RX 5700 XT</li>
                            <li><strong>Storage:</strong> 70 GB NVMe SSD</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ulasan Section -->
        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-chat-right-text-fill text-accent me-2"></i>Ulasan Pengguna (<?php echo $review_count; ?>)</h5>
            
            <?php if (mysqli_num_rows($reviews_query) > 0): ?>
                <div class="reviews-scroll-container">
                    <?php while ($rev = mysqli_fetch_assoc($reviews_query)): ?>
                        <div class="review-card-custom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="reviewer-avatar-wrapper">
                                        <?php if (!empty($rev['Profile_Image']) && file_exists('uploads/profile/' . $rev['Profile_Image'])): ?>
                                            <img src="uploads/profile/<?php echo htmlspecialchars($rev['Profile_Image']); ?>" class="w-100 h-100" style="object-fit: cover;" alt="User Avatar">
                                        <?php else: ?>
                                            <i class="bi bi-person text-secondary fs-5"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white small"><?php echo htmlspecialchars($rev['Name']); ?></div>
                                        <div class="text-warning mt-1" style="font-size: 0.8rem;">
                                            <?php 
                                            for ($star_i = 1; $star_i <= 5; $star_i++) {
                                                if ($star_i <= intval($rev['Rating'])) {
                                                    echo '<i class="bi bi-star-fill star-filled me-1"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star star-empty me-1"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-secondary small" style="font-size: 11px;"><i class="bi bi-calendar-event me-1"></i><?php echo date('d M Y', strtotime($rev['CreatedAt'])); ?></span>
                            </div>
                            <p class="text-secondary small mb-0 lh-base" style="opacity: 0.95;"><?php echo nl2br(htmlspecialchars($rev['Comment'])); ?></p>
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
        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary h-100 d-flex flex-column justify-content-between glass-panel">
            <div>
                <h5 class="fw-bold text-white mb-4"><i class="bi bi-cart-fill text-accent me-2"></i>Rincian Penyewaan</h5>
                
                <?php if ($rent_game['Stock'] <= 0): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-20 text-danger border-0 mb-0">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>Stok game ini sedang kosong. Silakan pilih game lain di katalog.
                    </div>
                <?php else: ?>
                    <form action="proses_sewa.php" method="POST" id="rentForm">
                        <input type="hidden" name="game_id" value="<?php echo $rent_game['GameID']; ?>">
                        
                        <!-- Duration Control -->
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
                        
                        <!-- Quick presets -->
                        <div class="mb-4">
                            <label class="form-label text-secondary small d-block mb-2">Pilihan Cepat:</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="3">3 Jam</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="6">6 Jam</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="12">12 Jam</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-3 btn-quick-hour" data-hours="24">24 Jam</button>
                            </div>
                        </div>
                        
                        <!-- Wallet display -->
                        <div class="p-3 rounded-4 glass-panel border border-secondary mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-secondary d-block" style="font-size: 11px;">Saldo Dompet Anda</small>
                                    <span id="userBalanceDisplay" class="fw-bold fs-5 text-light">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></span>
                                </div>
                                <div id="balanceBadge"></div>
                            </div>
                        </div>

                        <!-- Reciept summary -->
                        <div class="p-4 rounded-4 bg-dark bg-opacity-50 border border-secondary mb-4">
                            <h6 class="text-white fw-bold mb-3" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Ringkasan Pembayaran</h6>
                            
                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                <span>Tarif Sewa (<span id="receiptDuration" class="text-accent fw-bold">1</span> Jam)</span>
                                <span id="receiptRentalCost" class="text-white fw-semibold">Rp <?php echo number_format($rent_game['Hourly_Price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                <span>Biaya Layanan</span>
                                <span class="text-white fw-semibold">Rp 2.000</span>
                            </div>
                            <hr class="border-secondary border-opacity-50 my-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-white small">Total Pembayaran</span>
                                <span class="text-accent fw-bold fs-4" id="receiptTotalCost">Rp <?php echo number_format($rent_game['Hourly_Price'] + 2000, 0, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div id="actionArea"></div>
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

                            const rate = <?php echo intval($rent_game['Hourly_Price']); ?>;
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
                                    
                                    quickBtns.forEach(b => {
                                        b.classList.remove('btn-accent', 'text-dark');
                                        b.classList.add('btn-outline-secondary', 'text-white');
                                    });
                                    btn.classList.remove('btn-outline-secondary', 'text-white');
                                    btn.classList.add('btn-accent', 'text-dark');

                                    updatePricing();
                                });
                            });

                            updatePricing();
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>
