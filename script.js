document.addEventListener("DOMContentLoaded", () => {
    // PRELOADER HIDER
    const preloader = document.getElementById("preloader");
    if (preloader) {
        // Sempatkan sedikit delay agar loading transisi terlihat smooth
        setTimeout(() => {
            preloader.classList.add("fade-out");
            // Completely hide preloader from layout after transition completes
            setTimeout(() => {
                preloader.style.display = "none";
            }, 400);
        }, 300);
    }

    // CANVAS PARTICLES ENGINE
    const canvas = document.getElementById("particles-canvas");
    if (canvas) {
        const ctx = canvas.getContext("2d");
        let particles = [];
        const particleCount = 45;

        const resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor() {
                this.reset();
            }
            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 0.5;
                this.speedX = Math.random() * 0.4 - 0.2;
                this.speedY = Math.random() * 0.4 - 0.2;
                this.opacity = Math.random() * 0.4 + 0.15;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) {
                    this.reset();
                }
            }
            draw() {
                // Gunakan warna accent (sky-blue / #38bdf8)
                ctx.fillStyle = `rgba(56, 189, 248, ${this.opacity})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        const animate = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animate);
        };
        animate();
    }

    const toggleBtn = document.getElementById("themeToggle");
    const body = document.body;

    // Sinkronisasi Visual Struktur Komponen Tombol Tema
    const refreshThemeUI = () => {
        if (!toggleBtn) return;
        if (body.classList.contains("light-mode")) {
            toggleBtn.innerHTML = '<i class="bi bi-moon-fill text-dark"></i> Dark Mode';
            toggleBtn.classList.replace("btn-outline-light", "btn-outline-dark");
        } else {
            toggleBtn.innerHTML = '<i class="bi bi-sun-fill text-warning"></i> Bright Mode';
            toggleBtn.classList.replace("btn-outline-dark", "btn-outline-light");
        }
    };

    // Pemuatan Awal Preferensi Tema Penyimpanan Lokal
    const currentPreferences = localStorage.getItem("theme");
    if (currentPreferences === "light") {
        body.classList.add("light-mode");
    } else {
        body.classList.remove("light-mode");
    }
    refreshThemeUI();

    // Event Pengubah Tema
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            body.classList.toggle("light-mode");
            if (body.classList.contains("light-mode")) {
                localStorage.setItem("theme", "light");
            } else {
                localStorage.setItem("theme", "dark");
            }
            refreshThemeUI();
        });
    }

    // REAL-TIME PASSWORD VALIDATOR CHECKLIST
    const passwordInput = document.getElementById("regPassword");
    const confirmInput = document.getElementById("regConfirmPassword");

    if (passwordInput && confirmInput) {
        const chkLength = document.getElementById("chk-length");
        const chkLetters = document.getElementById("chk-letters");
        const chkNumbers = document.getElementById("chk-numbers");
        const chkMatch = document.getElementById("chk-match");
        const strengthBar = document.getElementById("strengthBar");

        const updateRule = (element, isMet) => {
            if (!element) return;
            const icon = element.querySelector(".check-icon");
            if (isMet) {
                element.classList.remove("text-danger");
                element.classList.add("text-success");
                if (icon) {
                    icon.classList.remove("bi-x-circle-fill");
                    icon.classList.add("bi-check-circle-fill");
                }
            } else {
                element.classList.remove("text-success");
                element.classList.add("text-danger");
                if (icon) {
                    icon.classList.remove("bi-check-circle-fill");
                    icon.classList.add("bi-x-circle-fill");
                }
            }
        };

        const validatePasswords = () => {
            const passVal = passwordInput.value;
            const confirmVal = confirmInput.value;

            const isLengthMet = passVal.length >= 8;
            const isLettersMet = /[A-Za-z]/.test(passVal);
            const isNumbersMet = /[0-9]/.test(passVal);
            const isMatchMet = passVal === confirmVal && passVal !== "";

            // 1. Min 8 characters
            updateRule(chkLength, isLengthMet);

            // 2. Contains letters
            updateRule(chkLetters, isLettersMet);

            // 3. Contains numbers
            updateRule(chkNumbers, isNumbersMet);

            // 4. Matches confirm password
            updateRule(chkMatch, isMatchMet);

            // Calculate Password Strength Score
            let score = 0;
            if (passVal.length > 0) {
                if (isLengthMet) score++;
                if (isLettersMet) score++;
                if (isNumbersMet) score++;
                if (isMatchMet) score++;
            }

            if (strengthBar) {
                let color = "#ef4444"; // red
                let width = "0%";
                if (score === 1) {
                    width = "25%";
                    color = "#ef4444";
                } else if (score === 2) {
                    width = "50%";
                    color = "#f59e0b"; // orange
                } else if (score === 3) {
                    width = "75%";
                    color = "#10b981"; // green
                } else if (score === 4) {
                    width = "100%";
                    color = "#059669"; // strong emerald green
                }
                strengthBar.style.width = width;
                strengthBar.style.backgroundColor = color;
            }
        };

        passwordInput.addEventListener("input", validatePasswords);
        confirmInput.addEventListener("input", validatePasswords);
    }
});

// GLOBAL BOOTSTRAP TOAST UTILITY
function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastId = 'toast_' + Date.now();
    const bgClass = type === 'success' ? 'border-success' : 'border-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';

    const toastHtml = `
        <div id="${toastId}" class="toast glass-panel border ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="toast-header bg-transparent border-0 text-white d-flex justify-content-between align-items-center pt-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i>
                    <strong class="me-auto text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">SteamRent</strong>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body text-white px-3 pb-3">
                ${message}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    if (toastElement) {
        const bsToast = new bootstrap.Toast(toastElement);
        bsToast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}
window.showToast = showToast;

// CLIPBOARD COPY UTILITY
function copyActivationCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('success', 'Kode aktivasi berhasil disalin ke clipboard! <br><strong>' + code + '</strong>');
    }).catch(err => {
        showToast('error', 'Gagal menyalin kode aktivasi.');
    });
}
window.copyActivationCode = copyActivationCode;