<?php
session_start();
include "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}
?>
<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Total produk
$q_produk = mysqli_query($conn, "SELECT COUNT(*) as total_produk FROM product");
$data_produk = mysqli_fetch_assoc($q_produk);

// Total stok
$q_stok = mysqli_query($conn, "SELECT SUM(stock) as total_stok FROM product");
$data_stok = mysqli_fetch_assoc($q_stok);

// Total kategori
$q_kategori = mysqli_query($conn, "SELECT COUNT(*) as total_kategori FROM categories");
$data_kategori = mysqli_fetch_assoc($q_kategori);

// Barang Masuk per hari (bulan ini)
$q_masuk = mysqli_query($conn, "
    SELECT DAY(created_at) as hari, SUM(qty) as total
    FROM stock_logs
    WHERE change_type='ADD'
    AND MONTH(created_at)=MONTH(CURRENT_DATE())
    AND YEAR(created_at)=YEAR(CURRENT_DATE())
    GROUP BY DAY(created_at)
");

// Barang Keluar per hari (bulan ini)
$q_keluar = mysqli_query($conn, "
    SELECT DAY(created_at) as hari, SUM(qty) as total
    FROM stock_logs
    WHERE change_type='REDUCE'
    AND MONTH(created_at)=MONTH(CURRENT_DATE())
    AND YEAR(created_at)=YEAR(CURRENT_DATE())
    GROUP BY DAY(created_at)
");

// Siapkan array 1–31 (default 0)
$masuk = array_fill(1, 31, 0);
$keluar = array_fill(1, 31, 0);

// isi data masuk
while ($row = mysqli_fetch_assoc($q_masuk)) {
  $masuk[$row['hari']] = (int)$row['total'];
}

// isi data keluar
while ($row = mysqli_fetch_assoc($q_keluar)) {
  $keluar[$row['hari']] = (int)$row['total'];
}

$query = mysqli_query($conn, "
    SELECT p.product_name, p.stock, c.category_name
    FROM product p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
");

// ambil produk dengan stok <= min_stock
$q_menipis = mysqli_query($conn, "
    SELECT product_name, stock, min_stock
    FROM product
    WHERE stock <= min_stock
    ORDER BY stock ASC
    LIMIT 5
");

$q_aktivitas = mysqli_query($conn, "
    SELECT sl.*, p.product_name, u.name as user_name
    FROM stock_logs sl
    JOIN product p ON sl.product_id = p.id
    JOIN users u ON sl.created_by = u.id
    ORDER BY sl.created_at DESC
    LIMIT 5
");

function waktu_lalu(string $datetime)
{
  $selisih = time() - strtotime($datetime);

  // kalau negatif, anggap 0
  if ($selisih < 0) $selisih = 0;

  $menit = floor($selisih / 60);
  $jam   = floor($selisih / 3600);
  $hari  = floor($selisih / 86400);

  if ($menit < 60) {
    return $menit . " menit lalu";
  } elseif ($jam < 24) {
    return $jam . " jam lalu";
  } else {
    return $hari . " hari lalu";
  }
}
?>
<?php $page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - inventory rasya</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/ilventory3.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/ilventory3.png" alt="">
        <span class="d-none d-lg-block">inventory rasya</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0"
            href="#"
            data-bs-toggle="dropdown">
            <img
              src="assets/img/provile.jpeg"
              alt="Profile"
              class="rounded-circle" />
          </a>
          <!-- End Profile Image Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>
                <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?>
              </h6>
              <span>
                <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Role'; ?>
              </span>
            </li>

            <li>
              <hr class="dropdown-divider" />
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>
          </ul>
          <!-- End Profile Dropdown Items -->
        </li>
        <!-- End Profile Nav -->
      </ul>
    </nav>
    <!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link <?= ($page == 'index.php') ? '' : 'collapsed' ?>" href="index.php">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <!-- Kategori Produk -->
    <li class="nav-item">
      <a class="nav-link <?= ($page == 'kategori_produk.php') ? '' : 'collapsed' ?>" href="kategori_produk.php">
        <i class="bi bi-tags"></i>
        <span>Kategori Produk</span>
      </a>
    </li>

    <!-- Data Produk -->
    <li class="nav-item">
      <a class="nav-link <?= ($page == 'produk.php') ? '' : 'collapsed' ?>" href="produk.php">
        <i class="bi bi-box-seam"></i>
        <span>Data Produk</span>
      </a>
    </li>

    <!-- Laporan -->
    <li class="nav-item">
      <a class="nav-link <?= ($page == 'laporan.php') ? '' : 'collapsed' ?>" href="laporan.php">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan</span>
      </a>
    </li>

    <!-- Manajemen User -->
    <li class="nav-item">
      <a class="nav-link <?= ($page == 'users.php') ? '' : 'collapsed' ?>" href="users.php">
        <i class="bi bi-people"></i>
        <span>Manajemen User</span>
      </a>
    </li>

  </ul>
</aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- LEFT SIDE -->
        <div class="col-lg-8">
          <div class="row">

            <!-- TOTAL PRODUK -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card">

                <div class="card-body">
                  <h5 class="card-title">Produk <span>| Total</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-box"></i>
                    </div>

                    <div class="ps-3">
                      <h6><?= $data_produk['total_produk']; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Produk</span>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- TOTAL STOK -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card">

                <div class="card-body">
                  <h5 class="card-title">Stok <span>| Total</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-archive"></i>
                    </div>

                    <div class="ps-3">
                      <h6><?= $data_stok['total_stok'] ?? 0; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Jumlah Semua Stok</span>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- KATEGORI -->
            <div class="col-xxl-4 col-md-12">
              <div class="card info-card">

                <div class="card-body">
                  <h5 class="card-title">Kategori <span>| Total</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-tags"></i>
                    </div>

                    <div class="ps-3">
                      <h6><?= $data_kategori['total_kategori']; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total Kategori</span>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- REPORT / GRAFIK -->
            <div class="col-12">
              <div class="card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots"></i>
                  </a>

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                    <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                    <li><a class="dropdown-item" href="#">Tahun Ini</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Laporan Barang <span>| Bulan Ini</span></h5>

                  <div id="reportsChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {

                      const dataMasuk = <?= json_encode(array_values($masuk)); ?>;
                      const dataKeluar = <?= json_encode(array_values($keluar)); ?>;

                      new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                            name: 'Barang Masuk',
                            data: dataMasuk
                          },
                          {
                            name: 'Barang Keluar',
                            data: dataKeluar
                          }
                        ],

                        chart: {
                          height: 350,
                          type: 'area',
                          toolbar: {
                            show: false
                          }
                        },

                        markers: {
                          size: 4
                        },

                        colors: ['#4154f1', '#ff771d'],

                        fill: {
                          type: "gradient",
                          gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.4,
                            stops: [0, 90, 100]
                          }
                        },

                        dataLabels: {
                          enabled: false
                        },

                        stroke: {
                          curve: 'smooth',
                          width: 2
                        },

                        xaxis: {
                          categories: [...Array(31).keys()].map(i => i + 1)
                        },

                        tooltip: {
                          x: {
                            format: 'dd/MM/yy'
                          }
                        }

                      }).render();

                    });
                  </script>

                </div>
              </div>
            </div>

            <!-- PRODUK TERBARU -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Produk Terbaru <span>| Latest</span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php
                      $no = 1;
                      while ($row = mysqli_fetch_assoc($query)) :
                      ?>

                        <tr>
                          <th><?= $no++; ?></th>
                          <td><?= $row['product_name']; ?></td>
                          <td><?= $row['category_name']; ?></td>
                          <td><?= $row['stock']; ?></td>
                        </tr>

                      <?php endwhile; ?>
                    </tbody>
                  </table>

                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-lg-4">

          <!-- STOK MENIPIS -->
          <div class="card top-selling overflow-auto">

            <div class="card-body pb-0">
              <h5 class="card-title">Stok Menipis <span>| Warning</span></h5>

              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th>Produk</th>
                    <th>Stok</th>
                    <th>Status</th>
                  </tr>
                </thead>

                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($q_menipis)) : ?>

                    <tr>
                      <td><?= $row['product_name']; ?></td>
                      <td><?= $row['stock']; ?></td>

                      <td>
                        <?php if ($row['stock'] == 0) : ?>
                          <span class="badge bg-danger">Habis</span>

                        <?php elseif ($row['stock'] <= ($row['min_stock'] / 2)) : ?>
                          <span class="badge bg-danger">Hampir Habis</span>

                        <?php else : ?>
                          <span class="badge bg-warning">Menipis</span>
                        <?php endif; ?>
                      </td>
                    </tr>

                  <?php endwhile; ?>
                </tbody>
              </table>

            </div>
          </div>

          <!-- AKTIVITAS -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Aktivitas Barang</h5>

              <div class="activity">

                <?php while ($row = mysqli_fetch_assoc($q_aktivitas)) :

                  if ($row['change_type'] == 'ADD') {
                    $text = "Penambahan stok";
                    $color = "text-success";
                  } elseif ($row['change_type'] == 'REDUCE') {
                    $text = "Pengeluaran barang";
                    $color = "text-danger";
                  } else {
                    $text = "Perubahan stok";
                    $color = "text-primary";
                  }

                ?>

                  <div class="activity-item d-flex">

                    <div class="activite-label">
                      <?= waktu_lalu($row['created_at']); ?>
                    </div>

                    <i class="bi bi-circle-fill activity-badge <?= $color ?> align-self-start"></i>

                    <div class="activity-content">
                      <?= $text; ?>
                      <span class="fw-bold text-dark">
                        "<?= $row['product_name']; ?>"
                      </span>
                    </div>

                  </div>

                <?php endwhile; ?>

              </div>

            </div>
          </div>

        </div>

      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>inventory rasya</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
      Designed by <a href="https://www.instagram.com/rastysm?igsh=d3o5dWpydDd2OGw3">Rasyaandi</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>