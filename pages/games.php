<?php
include_once 'includes/config.php';

// Handle AJAX request for autocomplete search suggestions
if (isset($_GET['ajax_search'])) {
    $keyword = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
    $suggestions = [];
    if (strlen($keyword) >= 1) {
        $suggest_query = mysqli_query($conn, "
            SELECT GameID, Game_Name, Genre, Image_URL, Hourly_Price 
            FROM game 
            WHERE Game_Name LIKE '%$keyword%' OR Genre LIKE '%$keyword%' 
            LIMIT 5
        ");
        if ($suggest_query) {
            while ($row = mysqli_fetch_assoc($suggest_query)) {
                $suggestions[] = [
                    'id' => $row['GameID'],
                    'name' => $row['Game_Name'],
                    'genre' => $row['Genre'],
                    'price' => number_format($row['Hourly_Price'], 0, ',', '.'),
                    'image' => $row['Image_URL']
                ];
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode($suggestions);
    exit;
}

$selected_genre = isset($_GET['g']) ? $_GET['g'] : '';
$genres = ['Action', 'RPG', 'Adventure', 'Magic', 'Shooter', 'Fighting', 'Horror', 'Survival', 'Sports'];

// Count games dynamically for each genre
$genre_counts = [];
foreach ($genres as $g) {
    $g_escaped = mysqli_real_escape_string($conn, $g);
    $c_query = mysqli_query($conn, "SELECT COUNT(*) as c FROM game WHERE Genre LIKE '%$g_escaped%'");
    $c_data = mysqli_fetch_assoc($c_query);
    $genre_counts[$g] = $c_data['c'];
}

// Search filter
$search_clause = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_val = mysqli_real_escape_string($conn, $_GET['search']);
    $search_clause = " AND (Game_Name LIKE '%$search_val%' OR Genre LIKE '%$search_val%')";
}

// Price filter
$price_clause = "";
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
if ($price_range == 'under_10k') {
    $price_clause = " AND Hourly_Price < 10000";
} elseif ($price_range == '10k_15k') {
    $price_clause = " AND Hourly_Price BETWEEN 10000 AND 15000";
} elseif ($price_range == 'over_15k') {
    $price_clause = " AND Hourly_Price > 15000";
}

// Genre filter
$genre_clause = "";
if (!empty($selected_genre)) {
    $selected_genre_escaped = mysqli_real_escape_string($conn, $selected_genre);
    $genre_clause = " AND (Genre LIKE '%$selected_genre_escaped%')";
}

// Count total items
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM game WHERE 1=1 $genre_clause $price_clause $search_clause");
$total_data = mysqli_fetch_assoc($total_query);
$total_titles = $total_data['total'];

// Pagination
$limit = 12;
$total_pages = ceil($total_titles / $limit);
if ($total_pages < 1) $total_pages = 1;
$current_page = isset($_GET['p']) ? max(1, min($total_pages, intval($_GET['p']))) : 1;
$offset = ($current_page - 1) * $limit;

// Keep params
$get_params = $_GET;
unset($get_params['p']);
$filter_query_str = http_build_query($get_params);
?>

<div class="mb-5 animate-fade-in">
    <!-- Search Bar Component -->
    <form action="index.php" method="GET" class="d-flex gap-2">
        <input type="hidden" name="page" value="games">
        <?php if (!empty($selected_genre)): ?>
            <input type="hidden" name="g" value="<?php echo htmlspecialchars($selected_genre); ?>">
        <?php endif; ?>
        <div class="input-group shadow-sm position-relative">
            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
            
            <div class="position-relative flex-grow-1">
                <input type="text" name="search" id="gameSearchInput" class="form-control bg-dark border-secondary text-white w-100 pe-5" placeholder="Cari judul game atau genre favorit Anda..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" autocomplete="off" style="border-radius: 0;">
                <!-- Clear Button 'X' -->
                <button type="button" id="btnClearSearch" class="btn border-0 position-absolute end-0 top-50 translate-middle-y text-secondary <?php echo (isset($_GET['search']) && !empty($_GET['search'])) ? '' : 'd-none'; ?>" style="z-index: 5; background: transparent; right: 10px !important;"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <select name="price_range" class="form-select bg-dark border-secondary text-white" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="" <?php echo ($price_range == '') ? 'selected' : ''; ?>>Semua Kisaran Tarif</option>
                <option value="under_10k" <?php echo ($price_range == 'under_10k') ? 'selected' : ''; ?>>Di bawah Rp 10.000</option>
                <option value="10k_15k" <?php echo ($price_range == '10k_15k') ? 'selected' : ''; ?>>Rp 10.000 - Rp 15.000</option>
                <option value="over_15k" <?php echo ($price_range == 'over_15k') ? 'selected' : ''; ?>>Di atas Rp 15.000</option>
            </select>
            
            <button class="btn bg-accent fw-bold px-4" type="submit">Cari</button>

            <!-- Autocomplete Suggestion Dropdown Overlay -->
            <div id="searchSuggestions" class="suggestions-dropdown d-none position-absolute w-100 bg-dark border border-secondary rounded-bottom shadow-lg overflow-auto" style="top: 100%; left: 0; z-index: 1000; max-height: 250px;"></div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 animate-fade-in">
    <h4 class="fw-bold text-white m-0"><i class="bi bi-controller text-info me-2"></i>Katalog Game Premium</h4>
    <span class="text-secondary small">
        Menampilkan <?php echo ($total_titles > 0 ? $offset + 1 : 0); ?> - <?php echo min($offset + $limit, $total_titles); ?> dari <?php echo $total_titles; ?> Game &bull; Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?>
    </span>
</div>

<!-- Genre Filter Buttons -->
<div class="d-flex flex-wrap gap-2 mb-4 animate-fade-in">
    <a href="index.php?page=games&price_range=<?php echo urlencode($price_range); ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>" class="btn btn-sm btn-outline-light <?php echo empty($selected_genre) ? 'active' : ''; ?>">Semua Kategori</a>
    <?php foreach ($genres as $g): ?>
        <a href="index.php?page=games&g=<?php echo urlencode($g); ?>&price_range=<?php echo urlencode($price_range); ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>" class="btn btn-sm btn-outline-light <?php echo ($selected_genre === $g) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($g); ?> (<?php echo isset($genre_counts[$g]) ? $genre_counts[$g] : 0; ?>)
        </a>
    <?php endforeach; ?>
</div>

<!-- Catalog Grid -->
<div class="row g-4 mb-5">
    <?php
    $genre_query = mysqli_query($conn, "
        SELECT g.*, r.avg_rating, r.review_count 
        FROM game g 
        LEFT JOIN (
            SELECT GameID, AVG(Rating) as avg_rating, COUNT(ReviewID) as review_count 
            FROM reviews 
            GROUP BY GameID
        ) r ON g.GameID = r.GameID 
        WHERE 1=1 $genre_clause $price_clause $search_clause 
        ORDER BY g.Game_Name ASC 
        LIMIT $limit OFFSET $offset
    ");
    
    if ($genre_query && mysqli_num_rows($genre_query) > 0) {
        while ($game = mysqli_fetch_assoc($genre_query)) {
            include 'includes/card_game.php';
        }
    } else {
        echo '<div class="col-12"><p class="text-secondary small ms-2">Tidak ada game ditemukan.</p></div>';
    }
    ?>
</div>

<!-- Pagination Navigation -->
<?php if ($total_pages > 1): ?>
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm gap-1">
            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link bg-dark border-secondary text-light fw-bold" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo ($current_page - 1); ?>">
                    <i class="bi bi-chevron-left"></i> Previous
                </a>
            </li>

            <?php for ($n = 1; $n <= $total_pages; $n++): ?>
                <li class="page-item <?php echo ($current_page == $n) ? 'active' : ''; ?>">
                    <a class="page-link border-secondary fw-bold <?php echo ($current_page == $n) ? 'bg-accent text-dark border-accent' : 'bg-dark text-light'; ?>" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo $n; ?>">
                        <?php echo $n; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link bg-dark border-secondary text-light fw-bold" href="index.php?<?php echo $filter_query_str; ?>&p=<?php echo ($current_page + 1); ?>">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Client-side script for autocomplete and clear search -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('gameSearchInput');
    const btnClear = document.getElementById('btnClearSearch');
    const suggestionsBox = document.getElementById('searchSuggestions');
    let currentFocus = -1;

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            currentFocus = -1;
            if (query.length > 0) {
                btnClear.classList.remove('d-none');
                
                fetch(`index.php?page=games&ajax_search=1&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let html = '';
                            data.forEach(game => {
                                html += `
                                    <a href="index.php?page=rent&game_id=${game.id}" class="d-flex align-items-center gap-3 p-2 text-decoration-none border-bottom border-secondary border-opacity-25 suggestion-item" style="transition: 0.2s; background: rgba(15, 19, 34, 0.95);">
                                        <img src="${game.image}" class="rounded" style="width: 35px; height: 45px; object-fit: cover;" alt="">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold text-white text-truncate small">${game.name}</div>
                                            <small class="text-secondary d-block text-truncate" style="font-size: 10px;">${game.genre}</small>
                                        </div>
                                        <div class="text-accent fw-bold small">Rp ${game.price}</div>
                                    </a>
                                `;
                            });
                            suggestionsBox.innerHTML = html;
                            suggestionsBox.classList.remove('d-none');
                        } else {
                            suggestionsBox.innerHTML = '<div class="p-3 text-secondary small bg-dark">Tidak ada hasil cocok</div>';
                            suggestionsBox.classList.remove('d-none');
                        }
                    });
            } else {
                btnClear.classList.add('d-none');
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('d-none');
            }
        });

        // Keyboard navigation support
        searchInput.addEventListener('keydown', (e) => {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus++;
                setActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus--;
                setActive(items);
            } else if (e.key === 'Enter') {
                if (currentFocus > -1) {
                    e.preventDefault();
                    if (items[currentFocus]) {
                        items[currentFocus].click();
                    }
                }
            }
        });

        function setActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = items.length - 1;
            
            items[currentFocus].classList.add('bg-secondary', 'bg-opacity-25');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }

        function removeActive(items) {
            for (let i = 0; i < items.length; i++) {
                items[i].classList.remove('bg-secondary', 'bg-opacity-25');
            }
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });

        // Focus event
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length > 0) {
                suggestionsBox.classList.remove('d-none');
            }
        });
    }

    if (btnClear) {
        btnClear.addEventListener('click', () => {
            searchInput.value = '';
            btnClear.classList.add('d-none');
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('d-none');
            
            // Redirect to clear search parameter from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                urlParams.delete('search');
                let newUrl = 'index.php?page=games';
                if (urlParams.has('g')) newUrl += '&g=' + urlParams.get('g');
                if (urlParams.has('price_range')) newUrl += '&price_range=' + urlParams.get('price_range');
                window.location.href = newUrl;
            }
        });
    }
});
</script>
