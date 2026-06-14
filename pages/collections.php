<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];

// Ambil data statistik dari database untuk ringkasan koleksi
$active_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rental WHERE UserID = '$user_id' AND Status = 'active'");
$active_count = mysqli_fetch_assoc($active_count_q)['c'];

$completed_count_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM rental WHERE UserID = '$user_id' AND Status = 'returned'");
$completed_count = mysqli_fetch_assoc($completed_count_q)['c'];

$total_hours_q = mysqli_query($conn, "SELECT SUM(Duration) as s FROM rental WHERE UserID = '$user_id'");
$total_hours = intval(mysqli_fetch_assoc($total_hours_q)['s']);
?>

<div class="row g-4 mb-4 animate-fade-in">
    <div class="col-12">
        <!-- Wallet Balance Banner -->
        <div class="user-box p-4 rounded-4 h-100 d-flex flex-column justify-content-center wallet-banner text-white">
            <small class="text-secondary text-uppercase fw-semibold tracking-wider" style="font-size: 11px;">SALDO DOMPET AKUN</small>
            <h2 class="fw-bold text-white m-0 mt-1 mb-3">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></h2>
            <div>
                <a href="index.php?page=topup" class="btn bg-accent fw-bold px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Top-Up Saldo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Rental Summary Metrics Section -->
<div class="row g-3 mb-5 animate-fade-in">
    <div class="col-4">
        <div class="user-box p-3 rounded-4 text-center text-white">
            <span class="text-success fw-bold d-block fs-4 mb-0"><?php echo $active_count; ?></span>
            <small class="text-secondary" style="font-size: 11px;">Sesi Aktif</small>
        </div>
    </div>
    <div class="col-4">
        <div class="user-box p-3 rounded-4 text-center text-white">
            <span class="text-accent fw-bold d-block fs-4 mb-0"><?php echo $completed_count; ?></span>
            <small class="text-secondary" style="font-size: 11px;">Sewa Selesai</small>
        </div>
    </div>
    <div class="col-4">
        <div class="user-box p-3 rounded-4 text-center text-white">
            <span class="text-warning fw-bold d-block fs-4 mb-0"><?php echo $total_hours; ?> jam</span>
            <small class="text-secondary" style="font-size: 11px;">Total Durasi</small>
        </div>
    </div>
</div>

<!-- Daftar Game Aktif -->
<h4 class="fw-bold text-white mb-4"><i class="bi bi-collection-play-fill text-accent me-2"></i>Pustaka Game Aktif Anda</h4>

<div class="d-flex flex-column gap-3 mb-5 animate-fade-in">
    <?php
    // Query data rental aktif dari view view_active_rentals
    $active_query = mysqli_query($conn, "
        SELECT RentalID, UserID, GameID, Game_Name, Genre, Image_URL, Start_Time, End_Time, Duration, Status 
        FROM view_active_rentals 
        WHERE UserID = '$user_id' 
        ORDER BY Start_Time DESC
    ");
    
    if ($active_query && mysqli_num_rows($active_query) > 0) {
        while ($r = mysqli_fetch_assoc($active_query)) {
            // Kalkulasi sisa waktu sewa secara real-time
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
             <div class="p-3 rounded active-rental-card glass-panel">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
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
                                <i class="bi bi-tags-fill me-1"></i><?php echo htmlspecialchars($r['Genre']); ?> &bull; Masa Rental Aktif <span class="pulse-live-dot ms-1"></span>
                            </small>
                        </div>
                    </div>
                    <div class="text-center text-md-end text-white">
                        <small class="text-secondary d-block" style="font-size: 10px;">Sisa Sesi:</small>
                        <span class="text-success fw-bold"><i class="bi bi-clock-history me-1"></i><?php echo $remaining_text; ?></span>
                    </div>
                    <div>
                        <!-- Ambil Kode Aktivasi -->
                        <button class="btn btn-sm btn-primary fw-bold px-3 py-2 rounded-2" onclick="copyActivationCode('STM-<?php echo strtoupper(substr(md5($r['RentalID']), 0, 4) . '-' . substr(md5($r['GameID']), 0, 4) . '-' . substr(md5($r['UserID']), 0, 4)); ?>')">
                            <i class="bi bi-key-fill me-1"></i> Ambil Kode
                        </button>
                        <!-- Kembalikan Game -->
                        <a href="proses_kembali.php?id=<?php echo $r['RentalID']; ?>" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2 rounded-2 ms-2" onclick="return confirm('Apakah Anda yakin ingin mengembalikan game ini?')">Kembalikan</a>
                    </div>
                </div>
                <!-- Progress Bar Sisa Waktu -->
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
    } else {
        ?>
        <div class="text-center py-5 user-box rounded-4">
            <i class="bi bi-collection text-secondary" style="font-size: 40px;"></i>
            <p class="text-secondary small mt-2">Tidak ada game yang sedang disewa saat ini.</p>
            <a href="index.php?page=games" class="btn btn-sm btn-outline-accent fw-bold px-3 py-1 mt-2">Mulai Menyewa</a>
        </div>
        <?php
    }
    ?>
</div>

<div class="mt-4 animate-fade-in text-center text-md-start">
    <a href="index.php?page=rental-history" class="btn btn-sm btn-outline-secondary text-white border-secondary-subtle px-4 py-2">
        <i class="bi bi-journal-text me-1"></i> Lihat Riwayat Rental Lengkap Anda
    </a>
</div>
