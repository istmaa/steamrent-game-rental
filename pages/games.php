<?php
include_once 'includes/config.php';

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
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Cari judul game atau genre favorit Anda..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            
            <select name="price_range" class="form-select bg-dark border-secondary text-white" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="" <?php echo ($price_range == '') ? 'selected' : ''; ?>>Semua Kisaran Tarif</option>
                <option value="under_10k" <?php echo ($price_range == 'under_10k') ? 'selected' : ''; ?>>Di bawah Rp 10.000</option>
                <option value="10k_15k" <?php echo ($price_range == '10k_15k') ? 'selected' : ''; ?>>Rp 10.000 - Rp 15.000</option>
                <option value="over_15k" <?php echo ($price_range == 'over_15k') ? 'selected' : ''; ?>>Di atas Rp 15.000</option>
            </select>
            
            <button class="btn bg-accent fw-bold px-4" type="submit">Cari</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 animate-fade-in">
    <h4 class="fw-bold m-0"><i class="bi bi-controller text-info me-2"></i>Katalog Game Premium</h4>
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
