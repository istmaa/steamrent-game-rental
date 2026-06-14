<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];
?>

<div class="d-flex justify-content-between align-items-end mb-4 animate-fade-in">
    <h4 class="fw-bold text-white m-0"><i class="bi bi-wallet2 text-success me-2"></i>Isi Saldo Akun (Top Up)</h4>
</div>
<div class="row animate-fade-in">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="user-box p-4 rounded-4 shadow-sm border border-secondary text-white">
            <p class="text-secondary small mb-4">Isi saldo akun SteamRent secara instan untuk melakukan penyewaan game premium tanpa hambatan.</p>
            
            <div class="p-3 mb-4 rounded bg-dark bg-opacity-40 border border-secondary d-flex justify-content-between align-items-center">
                <span class="text-secondary small">Saldo Saat Ini:</span>
                <span class="fs-5 fw-bold text-accent">Rp <?php echo number_format($user_balance, 0, ',', '.'); ?></span>
            </div>

            <form action="proses_topup.php" method="POST">
                <div class="mb-4">
                    <label class="form-label text-white small fw-medium">Jumlah Saldo (Rupiah)</label>
                    <input type="number" name="amount" id="topupAmount" class="form-control auth-form-control bg-dark border-secondary text-white" min="10000" placeholder="Masukkan jumlah minimal Rp 10.000" required>
                </div>
                
                <label class="form-label text-secondary small fw-medium mb-2">Pilih Nominal Cepat</label>
                <div class="row g-2 mb-4">
                    <div class="col-6 col-sm-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="20000">Rp 20k</button>
                    </div>
                    <div class="col-6 col-sm-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="50000">Rp 50k</button>
                    </div>
                    <div class="col-6 col-sm-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="100000">Rp 100k</button>
                    </div>
                    <div class="col-6 col-sm-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 quick-topup-btn fw-bold text-white" data-value="200000">Rp 200k</button>
                    </div>
                </div>

                <!-- Payment Channels -->
                <label class="form-label text-white small fw-medium mb-3">Metode Pembayaran</label>
                
                <!-- Parent Selectors -->
                <div class="row g-2 mb-4">
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="payment_parent" id="parent_bank" value="BANK" checked>
                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_bank">
                            <i class="bi bi-bank fs-4 d-block mb-1"></i>
                            <span class="fw-bold small" style="font-size: 10px;">BANK</span>
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="payment_parent" id="parent_wallet" value="WALLET">
                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_wallet">
                            <i class="bi bi-wallet2 fs-4 d-block mb-1"></i>
                            <span class="fw-bold small" style="font-size: 10px;">E-WALLET</span>
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="payment_parent" id="parent_qris" value="QRIS">
                        <label class="btn btn-outline-secondary w-100 py-3 text-center rounded-3 payment-parent-card text-white border-secondary" for="parent_qris">
                            <i class="bi bi-qr-code-scan fs-4 d-block mb-1"></i>
                            <span class="fw-bold small" style="font-size: 10px;">QRIS</span>
                        </label>
                    </div>
                </div>

                <!-- Sub-sections: Bank -->
                <div id="section_bank" class="payment-sub-section mb-4">
                    <label class="form-label text-white small fw-medium mb-2">Pilih Rekening Bank</label>
                    <div class="row g-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bca" value="BCA" checked>
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bca">
                                <div>
                                    <span class="d-block fw-bold small text-white">BCA</span>
                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                </div>
                                <i class="bi bi-bank text-accent fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bri" value="BRI">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bri">
                                <div>
                                    <span class="d-block fw-bold small text-white">BRI</span>
                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                </div>
                                <i class="bi bi-bank text-info fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_bni" value="BNI">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_bni">
                                <div>
                                    <span class="d-block fw-bold small text-white">BNI</span>
                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                </div>
                                <i class="bi bi-bank text-warning fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_mandiri" value="MANDIRI">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_mandiri">
                                <div>
                                    <span class="d-block fw-bold small text-white">MANDIRI</span>
                                    <small class="text-secondary" style="font-size: 9px;">Virtual Account</small>
                                </div>
                                <i class="bi bi-bank2 text-danger fs-5"></i>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-sections: E-Wallet -->
                <div id="section_wallet" class="payment-sub-section mb-4 d-none">
                    <label class="form-label text-white small fw-medium mb-2">Pilih E-Wallet</label>
                    <div class="row g-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_dana" value="DANA">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_dana">
                                <div>
                                    <span class="d-block fw-bold small text-white">DANA</span>
                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                </div>
                                <i class="bi bi-wallet2 text-primary fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_gopay" value="GOPAY">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_gopay">
                                <div>
                                    <span class="d-block fw-bold small text-white">GoPay</span>
                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                </div>
                                <i class="bi bi-wallet2 text-success fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_ovo" value="OVO">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_ovo">
                                <div>
                                    <span class="d-block fw-bold small text-white">OVO</span>
                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                </div>
                                <i class="bi bi-wallet2 text-info fs-5"></i>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_shopeepay" value="SHOPEEPAY">
                            <label class="btn btn-outline-secondary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-secondary text-white payment-card" for="pay_shopeepay">
                                <div>
                                    <span class="d-block fw-bold small text-white">ShopeePay</span>
                                    <small class="text-secondary" style="font-size: 9px;">Instant Pay</small>
                                </div>
                                <i class="bi bi-wallet2 text-danger fs-5"></i>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sub-sections: QRIS -->
                <div id="section_qris" class="payment-sub-section mb-4 d-none">
                    <input type="radio" class="btn-check" name="payment_method" id="pay_qris_val" value="QRIS">
                    <div class="p-3 rounded border border-success" style="background-color: rgba(34, 197, 94, 0.05); border-style: dashed !important;">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="bi bi-qr-code-scan text-success fs-3"></i>
                            <div>
                                <h6 class="fw-bold text-white mb-0">Scan QRIS Instant</h6>
                                <small class="text-secondary">Dukung semua penyedia pembayaran elektronik</small>
                            </div>
                        </div>
                        <p class="text-light small mb-2 mt-2" style="font-size: 12px; opacity: 0.95;">
                            QRIS dapat digunakan oleh seluruh aplikasi pembayaran yang mendukung QRIS.
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge bg-success bg-opacity-25 text-success">DANA</span>
                            <span class="badge bg-success bg-opacity-25 text-success">GoPay</span>
                            <span class="badge bg-success bg-opacity-25 text-success">OVO</span>
                            <span class="badge bg-success bg-opacity-25 text-success">ShopeePay</span>
                            <span class="badge bg-success bg-opacity-25 text-success">Mobile Banking</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn bg-accent w-100 py-2.5 fw-bold rounded-3 mt-2">Konfirmasi Top Up</button>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const amountInput = document.getElementById('topupAmount');
                    const quickBtns = document.querySelectorAll('.quick-topup-btn');
                    quickBtns.forEach(btn => {
                        btn.addEventListener('click', () => {
                            amountInput.value = btn.getAttribute('data-value');
                        });
                    });

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
        </div>
    </div>
</div>
