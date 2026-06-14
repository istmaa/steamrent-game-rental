<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];

// Ambil seluruh data sewa pengguna beserta log pembayaran (Complex Query: JOIN rental + game + payment)
$history_query = mysqli_query($conn, "
    SELECT 
        r.RentalID,
        r.Start_Time,
        r.End_Time,
        r.Duration,
        r.Status,
        g.Game_Name,
        g.Genre,
        g.Image_URL,
        p.Paid_Amount,
        p.Method,
        p.Payment_Date
    FROM rental r
    JOIN game g ON r.GameID = g.GameID
    JOIN payment p ON r.RentalID = p.RentalID
    WHERE r.UserID = '$user_id'
    ORDER BY r.Start_Time DESC
");

// Total pengeluaran pengguna (Subquery total revenue per user)
$revenue_query = mysqli_query($conn, "
    SELECT COALESCE(SUM(Paid_Amount), 0) as total_spent 
    FROM payment 
    WHERE RentalID IN (SELECT RentalID FROM rental WHERE UserID = '$user_id')
");
$revenue_data = mysqli_fetch_assoc($revenue_query);
$total_spent = $revenue_data['total_spent'];
?>

<div class="row g-4 mb-4 animate-fade-in">
    <div class="col-12">
        <div class="user-box p-4 rounded-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-white mb-1"><i class="bi bi-journal-text text-accent me-2"></i>Log Riwayat Penyewaan</h4>
                <p class="text-secondary small mb-0">Daftar lengkap transaksi penyewaan game dan log pembayaran akun Anda.</p>
            </div>
            <div class="text-md-end">
                <small class="text-secondary d-block">Total Akumulasi Pembayaran:</small>
                <span class="fs-4 fw-bold text-accent">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column gap-3 animate-fade-in">
    <?php
    if ($history_query && mysqli_num_rows($history_query) > 0) {
        while ($r = mysqli_fetch_assoc($history_query)) {
            $status_badge = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">AKTIF</span>';
            if ($r['Status'] == 'returned') {
                $status_badge = '<span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-50">SELESAI</span>';
            }
            ?>
            <div class="p-3 rounded glass-panel text-white d-flex flex-wrap align-items-center justify-content-between gap-3 border border-secondary border-opacity-25">
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
                            <i class="bi bi-tags-fill me-1"></i><?php echo htmlspecialchars($r['Genre']); ?> &bull; 
                            Mulai: <strong><?php echo date('d M Y H:i', strtotime($r['Start_Time'])); ?></strong>
                        </small>
                    </div>
                </div>

                <div class="text-start text-md-center">
                    <small class="text-secondary d-block" style="font-size: 10px;">Durasi Sewa:</small>
                    <span class="fw-semibold text-light"><?php echo $r['Duration']; ?> Jam</span>
                </div>

                <div class="text-start text-md-center">
                    <small class="text-secondary d-block" style="font-size: 10px;">Biaya Dibayar:</small>
                    <span class="fw-bold text-accent">Rp <?php echo number_format($r['Paid_Amount'], 0, ',', '.'); ?></span>
                </div>

                <div class="text-start text-md-center">
                    <small class="text-secondary d-block" style="font-size: 10px;">Metode Bayar:</small>
                    <span class="small text-white-50"><i class="bi bi-wallet2 me-1"></i><?php echo htmlspecialchars($r['Method']); ?></span>
                </div>

                <div class="text-md-end text-start">
                    <small class="text-secondary d-block" style="font-size: 10px;">Status Sesi:</small>
                    <?php echo $status_badge; ?>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="text-center py-5 user-box rounded-4">
            <i class="bi bi-journal-x text-secondary" style="font-size: 40px;"></i>
            <p class="text-secondary small mt-2">Tidak ditemukan riwayat penyewaan.</p>
        </div>
        <?php
    }
    ?>
</div>
