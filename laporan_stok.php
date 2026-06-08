<?php
session_start();
include "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>
<?php $page = basename($_SERVER['PHP_SELF']); ?>
<?php
include 'koneksi.php';
// total stok
$total_item = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM product"));

// Total transaksi barang masuk
$total_barang_masuk = mysqli_num_rows(mysqli_query(
  $conn,
  "SELECT id FROM stock_logs WHERE change_type = 'ADD'"
));

// Total transaksi barang keluar
$total_barang_keluar = mysqli_num_rows(mysqli_query(
  $conn,
  "SELECT id FROM stock_logs WHERE change_type = 'REDUCE'"
));

// Total item dengan stok kritis / minimum
$total_stok_kritis = mysqli_num_rows(mysqli_query(
  $conn,
  "SELECT id FROM product WHERE stock <= min_stock"
));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>laporan - Inventory Rasya</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/RezStore1.jpeg" rel="icon">
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
        <img src="assets/img/RezStore1.jpeg" alt="">
        <span class="d-none d-lg-block">Inventory Rasya</span>
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
              src="assets/img/Rezfah.jpeg"
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
      <h1>Laporan</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Laporan</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">

        <!-- Laporan Stok Barang -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Laporan Stok Barang</h5>
              <p class="text-muted">
                Menampilkan seluruh data stok barang saat ini.
              </p>

              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary">
                  Total Item: <?= $total_item; ?>
                </span>

                <a href="laporan_stok.php"
                  class="btn btn-sm btn-primary"
                  target="_blank">
                  Lihat Laporan
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Laporan Barang Masuk -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Laporan Barang Masuk</h5>
              <p class="text-muted">
                Riwayat barang yang masuk ke gudang.
              </p>

              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-success">
                  Total Transaksi: <?= $total_barang_masuk; ?>
                </span>

                <a href="laporan_barang_masuk.php"
                  class="btn btn-sm btn-success"
                  target="_blank">
                  Lihat Laporan
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Laporan Barang Keluar -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Laporan Barang Keluar</h5>
              <p class="text-muted">
                Riwayat barang yang keluar dari gudang.
              </p>

              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger">
                  Total Transaksi: <?= $total_barang_keluar; ?>
                </span>

                <a href="laporan_barang_keluar.php"
                  class="btn btn-sm btn-danger"
                  target="_blank">
                  Lihat Laporan
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Laporan Stok Minimum -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Stok Minimum</h5>
              <p class="text-muted">
                Barang dengan stok hampir habis.
              </p>

              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-warning">
                  Item Kritis: <?= $total_stok_kritis; ?>
                </span>

                <a href="laporan_stok_minimum.php"
                  class="btn btn-sm btn-warning"
                  target="_blank">
                  Lihat Laporan
                </a>
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
      &copy; Copyright <strong><span>Inventory Rasya</span></strong>. All Rights Reserved
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