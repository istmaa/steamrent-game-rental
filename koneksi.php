<?php

$conn = mysqli_connect("localhost", "root", "", "steamrent");

if(!$conn){
    die("Koneksi gagal");
}

// Inisialisasi Database game_specs secara dinamis jika belum ada
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'game_specs'");
if (mysqli_num_rows($table_check) == 0) {
    $create_table = "CREATE TABLE `game_specs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `game_id` INT NOT NULL,
      `os_min` VARCHAR(255) NOT NULL,
      `processor_min` VARCHAR(255) NOT NULL,
      `ram_min` VARCHAR(255) NOT NULL,
      `gpu_min` VARCHAR(255) NOT NULL,
      `storage_min` VARCHAR(255) NOT NULL,
      `os_rec` VARCHAR(255) NOT NULL,
      `processor_rec` VARCHAR(255) NOT NULL,
      `ram_rec` VARCHAR(255) NOT NULL,
      `gpu_rec` VARCHAR(255) NOT NULL,
      `storage_rec` VARCHAR(255) NOT NULL,
      FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    mysqli_query($conn, $create_table);
}

// Seed spesifikasi jika data spesifikasi game belum ada
$games_query = mysqli_query($conn, "SELECT id, title, genre FROM games");
if ($games_query && mysqli_num_rows($games_query) > 0) {
    $custom_specs = [
        // 1. Cyberpunk 2077
        1 => [
            'os_min' => 'Windows 10 64-bit',
            'processor_min' => 'Intel Core i7-6700 or AMD Ryzen 5 1600',
            'ram_min' => '12 GB RAM',
            'gpu_min' => 'NVIDIA GeForce GTX 1060 6GB or AMD Radeon RX 580 8GB',
            'storage_min' => '70 GB SSD',
            'os_rec' => 'Windows 10/11 64-bit',
            'processor_rec' => 'Intel Core i7-12700 or AMD Ryzen 7 7800X3D',
            'ram_rec' => '16 GB RAM',
            'gpu_rec' => 'NVIDIA GeForce RTX 2060 SUPER or AMD Radeon RX 5700 XT',
            'storage_rec' => '70 GB NVMe SSD'
        ],
        // 2. Elden Ring
        2 => [
            'os_min' => 'Windows 10 64-bit',
            'processor_min' => 'Intel Core i5-8400 or AMD Ryzen 3 3300X',
            'ram_min' => '12 GB RAM',
            'gpu_min' => 'NVIDIA GeForce GTX 1060 3GB or AMD Radeon RX 580 4GB',
            'storage_min' => '60 GB HDD',
            'os_rec' => 'Windows 10/11 64-bit',
            'processor_rec' => 'Intel Core i7-8700K or AMD Ryzen 5 3600X',
            'ram_rec' => '16 GB RAM',
            'gpu_rec' => 'NVIDIA GeForce GTX 1070 8GB or AMD Radeon RX Vega 56 8GB',
            'storage_rec' => '60 GB SSD'
        ],
        // 3. Hogwarts Legacy
        3 => [
            'os_min' => 'Windows 10 64-bit',
            'processor_min' => 'Intel Core i5-6600 or AMD Ryzen 5 1400',
            'ram_min' => '16 GB RAM',
            'gpu_min' => 'NVIDIA GeForce GTX 960 4GB or AMD Radeon RX 470 4GB',
            'storage_min' => '85 GB HDD',
            'os_rec' => 'Windows 10/11 64-bit',
            'processor_rec' => 'Intel Core i7-8700 or AMD Ryzen 5 3600',
            'ram_rec' => '16 GB RAM',
            'gpu_rec' => 'NVIDIA GeForce GTX 1080 Ti or AMD Radeon RX 5700 XT',
            'storage_rec' => '85 GB SSD'
        ],
        // 10. Black Myth: Wukong
        10 => [
            'os_min' => 'Windows 10 64-bit',
            'processor_min' => 'Intel Core i5-8400 or AMD Ryzen 5 1600',
            'ram_min' => '16 GB RAM',
            'gpu_min' => 'NVIDIA GeForce GTX 1060 6GB or AMD Radeon RX 580 8GB',
            'storage_min' => '130 GB HDD / SSD recommended',
            'os_rec' => 'Windows 10/11 64-bit',
            'processor_rec' => 'Intel Core i7-9700 or AMD Ryzen 5 5500',
            'ram_rec' => '16 GB RAM',
            'gpu_rec' => 'NVIDIA GeForce RTX 2060 or AMD Radeon RX 5700 XT',
            'storage_rec' => '130 GB NVMe SSD'
        ],
        // 11. Grand Theft Auto V
        11 => [
            'os_min' => 'Windows 8.1 64-bit / Windows 10 64-bit',
            'processor_min' => 'Intel Core 2 Quad CPU Q6600 @ 2.40GHz / AMD Phenom 9850 Quad-Core',
            'ram_min' => '4 GB RAM',
            'gpu_min' => 'NVIDIA 9800 GT 1GB / AMD HD 4870 1GB',
            'storage_min' => '72 GB HDD',
            'os_rec' => 'Windows 10 64-bit',
            'processor_rec' => 'Intel Core i5 3470 @ 3.2GHz / AMD X8 FX-8350 @ 4GHz',
            'ram_rec' => '8 GB RAM',
            'gpu_rec' => 'NVIDIA GTX 660 2GB / AMD HD 7870 2GB',
            'storage_rec' => '72 GB SSD'
        ],
        // 12. Red Dead Redemption 2
        12 => [
            'os_min' => 'Windows 7 - Service Pack 1',
            'processor_min' => 'Intel Core i5-2500K or AMD FX-6300',
            'ram_min' => '8 GB RAM',
            'gpu_min' => 'NVIDIA GeForce GTX 770 2GB or AMD Radeon R9 280 3GB',
            'storage_min' => '150 GB HDD',
            'os_rec' => 'Windows 10 64-bit',
            'processor_rec' => 'Intel Core i7-4770K or AMD Ryzen 5 1500X',
            'ram_rec' => '12 GB RAM',
            'gpu_rec' => 'NVIDIA GeForce GTX 1060 6GB or AMD Radeon RX 480 4GB',
            'storage_rec' => '150 GB SSD'
        ]
    ];

    while ($game = mysqli_fetch_assoc($games_query)) {
        $game_id = $game['id'];
        $title = $game['title'];
        $genre = $game['genre'];

        $spec_check = mysqli_query($conn, "SELECT id FROM game_specs WHERE game_id = $game_id");
        if ($spec_check && mysqli_num_rows($spec_check) == 0) {
            // Jika ada custom spec
            if (isset($custom_specs[$game_id])) {
                $s = $custom_specs[$game_id];
            } else {
                // Generate fallback spec based on genre/title
                if (stripos($genre, 'Horror') !== false || stripos($genre, 'Survival') !== false) {
                    $s = [
                        'os_min' => 'Windows 10 64-bit',
                        'processor_min' => 'Intel Core i5-4460 or AMD Ryzen 3 1200',
                        'ram_min' => '8 GB RAM',
                        'gpu_min' => 'NVIDIA GeForce GTX 960 or AMD Radeon RX 560',
                        'storage_min' => '50 GB HDD',
                        'os_rec' => 'Windows 10/11 64-bit',
                        'processor_rec' => 'Intel Core i7-8700 or AMD Ryzen 5 3600',
                        'ram_rec' => '16 GB RAM',
                        'gpu_rec' => 'NVIDIA GeForce GTX 1070 or AMD Radeon RX 5700',
                        'storage_rec' => '50 GB SSD'
                    ];
                } else if (stripos($genre, 'Racing') !== false || stripos($genre, 'Simulation') !== false) {
                    $s = [
                        'os_min' => 'Windows 10 64-bit',
                        'processor_min' => 'Intel Core i5-3470 or AMD FX-8350',
                        'ram_min' => '8 GB RAM',
                        'gpu_min' => 'NVIDIA GeForce GTX 970 or AMD Radeon RX 470',
                        'storage_min' => '80 GB HDD',
                        'os_rec' => 'Windows 10/11 64-bit',
                        'processor_rec' => 'Intel Core i7-6700K or AMD Ryzen 5 2600X',
                        'ram_rec' => '16 GB RAM',
                        'gpu_rec' => 'NVIDIA GeForce GTX 1080 or AMD Radeon RX Vega 56',
                        'storage_rec' => '80 GB SSD'
                    ];
                } else {
                    // Default Fallback
                    $s = [
                        'os_min' => 'Windows 10 64-bit',
                        'processor_min' => 'Intel Core i5-4460 or AMD FX-6300',
                        'ram_min' => '8 GB RAM',
                        'gpu_min' => 'NVIDIA GeForce GTX 760 or AMD Radeon R7 260x',
                        'storage_min' => '60 GB HDD',
                        'os_rec' => 'Windows 10/11 64-bit',
                        'processor_rec' => 'Intel Core i7-4790 or AMD Ryzen 5 1500X',
                        'ram_rec' => '16 GB RAM',
                        'gpu_rec' => 'NVIDIA GeForce GTX 1060 or AMD Radeon RX 480',
                        'storage_rec' => '60 GB SSD'
                    ];
                }
            }

            // Insert into game_specs
            $stmt = mysqli_prepare($conn, "INSERT INTO game_specs (game_id, os_min, processor_min, ram_min, gpu_min, storage_min, os_rec, processor_rec, ram_rec, gpu_rec, storage_rec) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssssssss", 
                $game_id, 
                $s['os_min'], 
                $s['processor_min'], 
                $s['ram_min'], 
                $s['gpu_min'], 
                $s['storage_min'], 
                $s['os_rec'], 
                $s['processor_rec'], 
                $s['ram_rec'], 
                $s['gpu_rec'], 
                $s['storage_rec']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

?>