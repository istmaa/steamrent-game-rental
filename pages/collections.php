<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];

// Ambil data user terbaru untuk saldo
$user_db_query = mysqli_query($conn, "SELECT Balance FROM users WHERE UserID = '$user_id'");
$user_db_data = mysqli_fetch_assoc($user_db_query);
$user_balance = $user_db_data ? $user_db_data['Balance'] : 0;

// 1. Ambil data statistik koleksi
$active_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rental WHERE UserID = '$user_id' AND Status = 'active'");
$active_count = mysqli_fetch_assoc($active_count_q)['c'];

$completed_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rental WHERE UserID = '$user_id' AND Status = 'returned'");
$completed_count = mysqli_fetch_assoc($completed_count_q)['c'];

$total_hours_q = mysqli_query($conn, "SELECT SUM(Duration) as s FROM rental WHERE UserID = '$user_id'");
$total_hours = intval(mysqli_fetch_assoc($total_hours_q)['s']);

// 2. Ambil data rental aktif dari view view_active_rentals
$active_query = mysqli_query($conn, "
    SELECT RentalID, UserID, GameID, Game_Name, Genre, Image_URL, Start_Time, End_Time, Duration, Status 
    FROM view_active_rentals 
    WHERE UserID = '$user_id' 
    ORDER BY Start_Time DESC
");

// 3. Ambil data riwayat rental selesai (JOIN rental + game + payment)
$history_query = mysqli_query($conn, "
    SELECT r.RentalID, r.GameID, r.Start_Time, r.End_Time, r.Duration, r.Status,
           g.Game_Name, g.Genre, g.Image_URL, p.Paid_Amount, p.Method, p.Payment_Date
    FROM rental r
    JOIN game g ON r.GameID = g.GameID
    JOIN payment p ON r.RentalID = p.RentalID
    WHERE r.UserID = '$user_id' AND r.Status = 'returned'
    ORDER BY r.End_Time DESC
    LIMIT 10
");

// 4. Ambil data session log
$session_query = mysqli_query($conn, "
    SELECT s.*, g.Game_Name 
    FROM sessionlog s 
    JOIN rental r ON s.RentalID = r.RentalID 
    JOIN game g ON r.GameID = g.GameID 
    WHERE r.UserID = '$user_id' 
    ORDER BY s.Login_Time DESC 
    LIMIT 10
");

// 5. Ambil data riwayat top up
$topup_history_query = mysqli_query($conn, "
    SELECT * 
    FROM topup 
    WHERE UserID = '$user_id' 
    ORDER BY Topup_Date DESC 
    LIMIT 10
");
?>

<div class="row g-4 mb-4 animate-fade-in text-white">
    <div class="col-12">
        <div class="user-box p-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-white mb-1"><i class="bi bi-collection-play-fill text-accent me-2"></i>Dashboard Koleksi Saya</h4>
                <p class="text-secondary small mb-0">Kelola pustaka game aktif, saldo dompet, riwayat persewaan, dan aktivitas sesi Anda.</p>
            </div>
            <div class="d-flex gap-3">
                <div class="text-center p-2 px-3 rounded bg-dark bg-opacity-40 border border-secondary border-opacity-50">
                    <span class="text-success fw-bold d-block fs-5"><?php echo $active_count; ?></span>
                    <small class="text-secondary" style="font-size: 10px;">Sesi Aktif</small>
                </div>
                <div class="text-center p-2 px-3 rounded bg-dark bg-opacity-40 border border-secondary border-opacity-50">
                    <span class="text-accent fw-bold d-block fs-5"><?php echo $completed_count; ?></span>
                    <small class="text-secondary" style="font-size: 10px;">Sewa Selesai</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 text-white">
    <!-- Left Column: Active Games, History, Session Log -->
    <div class="col-12 col-lg-8 animate-fade-in">
        
        <!-- SECTION: Game Sedang Disewa -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-play-circle text-success me-2"></i>Game Sedang Disewa</h5>
            <div class="d-flex flex-column gap-3">
                <?php
                if ($active_query && mysqli_num_rows($active_query) > 0) {
                    while ($r = mysqli_fetch_assoc($active_query)) {
                        $expiry = strtotime($r['Start_Time']) + ($r['Duration'] * 3600);
                        $diff = $expiry - time();
                        $remaining_text = "Sesi Berakhir";
                        
                        $total_sec = $r['Duration'] * 3600;
                        $elapsed_sec = time() - strtotime($r['Start_Time']);
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
                        <div class="p-3 rounded border border-secondary border-opacity-50" style="background-color: rgba(56, 189, 248, 0.02);">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <?php if (!empty($r['Image_URL'])): ?>
                                        <img src="<?php echo htmlspecialchars($r['Image_URL']); ?>" class="rounded" style="width: 45px; height: 60px; object-fit: cover;" alt="" loading="lazy">
                                    <?php else: ?>
                                        <div class="empty-image rounded d-flex align-items-center justify-content-center bg-secondary" style="width: 45px; height: 60px;">
                                            <small style="font-size: 8px;">Poster</small>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-white small"><?php echo htmlspecialchars($r['Game_Name']); ?></div>
                                        <small class="text-secondary d-block" style="font-size: 11px;">
                                            Masa Rental Aktif <span class="pulse-live-dot ms-1"></span>
                                        </small>
                                    </div>
                                </div>
                                <div class="text-start text-md-center">
                                    <small class="text-secondary d-block" style="font-size: 10px;">Sisa Sesi:</small>
                                    <span class="text-success fw-bold small"><i class="bi bi-clock-history me-1"></i><?php echo $remaining_text; ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary fw-bold px-3 py-1.5 rounded-2" style="font-size: 11px;" onclick="copyActivationCode('STM-<?php echo strtoupper(substr(md5($r['RentalID']), 0, 4) . '-' . substr(md5($r['GameID']), 0, 4) . '-' . substr(md5($r['UserID']), 0, 4)); ?>')">
                                        <i class="bi bi-key-fill me-1"></i> Kode
                                    </button>
                                    <a href="proses_kembali.php?id=<?php echo $r['RentalID']; ?>" class="btn btn-sm btn-outline-danger fw-bold px-3 py-1.5 rounded-2" style="font-size: 11px;" onclick="return confirm('Apakah Anda yakin ingin mengembalikan game ini?')">Kembalikan</a>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 6px; background-color: rgba(255, 255, 255, 0.05); border-radius: 3px; overflow: hidden;">
                                <div class="progress-bar-custom" style="width: <?php echo $progress_percent; ?>%;"></div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="text-center py-4 text-secondary small">
                        <i class="bi bi-collection fs-3 d-block mb-1 text-secondary"></i>
                        Tidak ada game yang sedang disewa saat ini.
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- SECTION: Riwayat Rental (Past Rentals) -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-journal-text text-accent me-2"></i>Riwayat Rental Game</h5>
            <div class="d-flex flex-column gap-2">
                <?php
                if ($history_query && mysqli_num_rows($history_query) > 0) {
                    while ($r = mysqli_fetch_assoc($history_query)) {
                        ?>
                        <div class="p-3 rounded border border-secondary border-opacity-20 d-flex flex-wrap align-items-center justify-content-between gap-3 text-secondary small" style="background-color: rgba(255,255,255,0.01);">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($r['Image_URL'])): ?>
                                    <img src="<?php echo htmlspecialchars($r['Image_URL']); ?>" class="rounded" style="width: 35px; height: 45px; object-fit: cover;" alt="">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($r['Game_Name']); ?></div>
                                    <small style="font-size: 10px;"><?php echo date('d M Y H:i', strtotime($r['Start_Time'])); ?> &bull; Selesai</small>
                                </div>
                            </div>
                            <div class="text-start text-md-center">
                                <small class="d-block" style="font-size: 9px;">Durasi:</small>
                                <span class="text-light fw-medium"><?php echo $r['Duration']; ?> Jam</span>
                            </div>
                            <div class="text-start text-md-center">
                                <small class="d-block" style="font-size: 9px;">Biaya:</small>
                                <span class="text-accent fw-bold">Rp <?php echo number_format($r['Paid_Amount'], 0, ',', '.'); ?></span>
                            </div>
                            <div>
                                <?php
                                $game_id = $r['GameID'];
                                $review_check_q = mysqli_query($conn, "SELECT ReviewID FROM reviews WHERE UserID = '$user_id' AND GameID = '$game_id'");
                                $already_reviewed = mysqli_num_rows($review_check_q) > 0;
                                if (!$already_reviewed):
                                ?>
                                    <button class="btn btn-sm btn-ulas-game fw-bold px-3 py-1 rounded-2" style="font-size: 10px;" data-bs-toggle="modal" data-bs-target="#reviewModal" data-game-id="<?php echo $game_id; ?>" data-game-title="<?php echo htmlspecialchars($r['Game_Name']); ?>">
                                        <i class="bi bi-star-fill me-1"></i> Ulas
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 small" style="font-size: 9px;">Sudah Diulas</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="text-center py-4 text-secondary small">
                        Belum ada riwayat rental game sebelumnya.
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- SECTION: Session Log -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Log Aktivitas Sesi</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0 small text-secondary">
                    <thead>
                        <tr class="border-bottom border-secondary border-opacity-25 text-white">
                            <th>Game</th>
                            <th>Waktu Mulai (Login)</th>
                            <th>Waktu Selesai (Logout)</th>
                            <th>Status Sesi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($session_query && mysqli_num_rows($session_query) > 0) {
                            while ($s = mysqli_fetch_assoc($session_query)) {
                                $logout_time = $s['Logout_Time'] ? date('d M Y H:i', strtotime($s['Logout_Time'])) : '<span class="text-success small fw-semibold">Sesi Aktif</span>';
                                $status_badge = $s['Logout_Time'] ? '<span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25">SELESAI</span>' : '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">AKTIF</span>';
                                ?>
                                <tr>
                                    <td class="text-white fw-semibold"><?php echo htmlspecialchars($s['Game_Name']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($s['Login_Time'])); ?></td>
                                    <td><?php echo $logout_time; ?></td>
                                    <td><?php echo $status_badge; ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada aktivitas sesi tercatat</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Right Column: Wallet Balance & Inline Top-Up Form -->
    <div class="col-12 col-lg-4">
        <!-- Balance Display Card -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <small class="text-secondary text-uppercase fw-semibold tracking-wider d-block mb-1" style="font-size: 10px;">SALDO DOMPET AKUN</small>
            <h2 class="fw-bold text-white m-0">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></h2>
        </div>

        <!-- Inline Top-Up Form Card -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-wallet2 text-success me-2"></i>Form Top-Up Saldo</h5>
            <form action="proses_topup.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-white small fw-medium">Jumlah Nominal (Rupiah)</label>
                    <input type="number" name="amount" id="topupAmount" class="form-control auth-form-control bg-dark border-secondary text-white" min="10000" placeholder="Masukkan minimal Rp 10.000" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium d-block mb-2">Pilihan Nominal Cepat</label>
                    <div class="row g-2">
                        <div class="col-6"><button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="20000" style="font-size: 11px;">Rp 20k</button></div>
                        <div class="col-6"><button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="50000" style="font-size: 11px;">Rp 50k</button></div>
                        <div class="col-6"><button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="100000" style="font-size: 11px;">Rp 100k</button></div>
                        <div class="col-6"><button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="200000" style="font-size: 11px;">Rp 200k</button></div>
                    </div>
                </div>

                <!-- Payment Parent Buttons -->
                <div class="mb-3">
                    <label class="form-label text-white small fw-medium mb-2">Metode Pembayaran</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="payment_parent" id="parent_bank" value="BANK" checked>
                            <label class="btn btn-outline-secondary w-100 py-2.5 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_bank" style="font-size: 10px;">
                                <i class="bi bi-bank d-block mb-1 fs-6"></i>BANK
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="payment_parent" id="parent_wallet" value="WALLET">
                            <label class="btn btn-outline-secondary w-100 py-2.5 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_wallet" style="font-size: 10px;">
                                <i class="bi bi-wallet2 d-block mb-1 fs-6"></i>WALLET
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="payment_parent" id="parent_qris" value="QRIS">
                            <label class="btn btn-outline-secondary w-100 py-2.5 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_qris" style="font-size: 10px;">
                                <i class="bi bi-qr-code-scan d-block mb-1 fs-6"></i>QRIS
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-sections: Bank -->
                <div id="section_bank" class="payment-sub-section mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bca" value="BCA" checked>
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bca" style="font-size: 11px;">
                                <span>BCA VA</span><i class="bi bi-bank text-accent fs-6"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bri" value="BRI">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bri" style="font-size: 11px;">
                                <span>BRI VA</span><i class="bi bi-bank text-info fs-6"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bni" value="BNI">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bni" style="font-size: 11px;">
                                <span>BNI VA</span><i class="bi bi-bank text-warning fs-6"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_mandiri" value="MANDIRI">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_mandiri" style="font-size: 11px;">
                                <span>MANDIRI</span><i class="bi bi-bank2 text-danger fs-6"></i>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-sections: E-Wallet -->
                <div id="section_wallet" class="payment-sub-section mb-3 d-none">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_dana" value="DANA">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_dana" style="font-size: 11px;">
                                <span>DANA</span><i class="bi bi-wallet2 text-primary fs-6"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_gopay" value="GOPAY">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_gopay" style="font-size: 11px;">
                                <span>GoPay</span><i class="bi bi-wallet2 text-success fs-6"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_ovo" value="OVO">
                            <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_ovo" style="font-size: 11px;">
                                <span>OVO</span><i class="bi bi-wallet2 text-info fs-6"></i>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-sections: QRIS -->
                <div id="section_qris" class="payment-sub-section mb-3 d-none">
                    <input type="radio" class="btn-check" name="payment_method" id="pay_qris_val" value="QRIS">
                    <div class="p-2.5 rounded border border-success border-opacity-50 small text-secondary" style="background-color: rgba(34, 197, 94, 0.03); border-style: dashed !important; font-size: 11px;">
                        <span class="text-white fw-bold d-block mb-1"><i class="bi bi-qr-code-scan text-success me-1"></i>QRIS Instant</span>
                        Mendukung DANA, GoPay, OVO, ShopeePay, dan Mobile Banking.
                    </div>
                </div>

                <button type="submit" class="btn bg-accent w-100 py-2.5 fw-bold text-dark rounded-3 mt-2 hover-scale">Konfirmasi Top Up</button>
            </form>
        </div>

        <!-- SECTION: Riwayat Top Up -->
        <div class="user-box p-4 rounded-4 border border-secondary mb-4 glass-panel">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-clock-history text-info me-2"></i>Riwayat Top-Up</h5>
            <div class="d-flex flex-column gap-2">
                <?php
                if ($topup_history_query && mysqli_num_rows($topup_history_query) > 0) {
                    while ($t = mysqli_fetch_assoc($topup_history_query)) {
                        ?>
                        <div class="p-2.5 rounded border border-secondary border-opacity-10 d-flex justify-content-between align-items-center small text-secondary" style="background-color: rgba(255,255,255,0.01); font-size: 11px;">
                            <div>
                                <span class="d-block text-white fw-semibold">+ Rp <?php echo number_format($t['Amount'], 0, ',', '.'); ?></span>
                                <small style="font-size: 9px;"><?php echo date('d M Y H:i', strtotime($t['Topup_Date'])); ?></small>
                            </div>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-light px-2 py-1" style="font-size: 9px;"><?php echo htmlspecialchars($t['Payment_Method']); ?></span>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="text-center py-3 text-secondary small">
                        Belum ada riwayat top-up.
                    </div>
                    <?php
                }
                ?>
            </div>
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
    // Review Modal Setup
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

    // Top-up Preset Buttons
    const amountInput = document.getElementById('topupAmount');
    const quickBtns = document.querySelectorAll('.quick-topup-btn');
    quickBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            amountInput.value = btn.getAttribute('data-value');
        });
    });

    // Toggle Payment Channels
    const parentBank = document.getElementById('parent_bank');
    const parentWallet = document.getElementById('parent_wallet');
    const parentQris = document.getElementById('parent_qris');

    const sectionBank = document.getElementById('section_bank');
    const sectionWallet = document.getElementById('section_wallet');
    const sectionQris = document.getElementById('section_qris');

    const qrisInput = document.getElementById('pay_qris_val');

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
});
</script>
