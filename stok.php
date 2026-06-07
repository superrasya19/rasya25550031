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
include "koneksi.php";

if (isset($_POST['submit'])) {

  $product_id = $_POST['product_id'];
  $change_type = $_POST['change_type'];
  $qty = intval($_POST['qty']);
  $note = $_POST['note'];
  $user_id = $_SESSION['user_id'];

  // ambil stok sekarang
  $q = mysqli_query($conn, "SELECT stock FROM product WHERE id='$product_id'");
  $data = mysqli_fetch_assoc($q);

  $stock_before = $data['stock'];

  // hitung stok baru
  if ($change_type == "ADD") {

    $stock_after = $stock_before + $qty;
  } else {

    $stock_after = $stock_before - $qty;

    // cek stok minus
    if ($stock_after < 0) {
      echo "<script>alert('Stok tidak cukup!');</script>";
      exit;
    }
  }

  // update stok produk
  mysqli_query($conn, "
        UPDATE product 
        SET stock='$stock_after' 
        WHERE id='$product_id'
    ");

  // simpan riwayat stok
  mysqli_query($conn, "
        INSERT INTO stock_logs
        (product_id, change_type, qty, stock_before, stock_after, note, created_by)
        VALUES
        ('$product_id', '$change_type', '$qty', '$stock_before', '$stock_after', '$note', '$user_id')
    ");

  // redirect
  header("Location: stok.php?success=1");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Manajemen Stok - Inventory Rasya</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/logo1.png" rel="icon">
  <link href="assets/img/logo1.png" rel="apple-touch-icon">

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
  <?php if (isset($_GET['success'])): ?>

    <script>
      alert('Stok berhasil diperbarui!');
    </script>

  <?php endif; ?>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo1.png" alt="">
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
              src="assets/img/logo2.jpeg"
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
      <h1>Manajemen Stok</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
          <li class="breadcrumb-item">Data Produk</li>
          <li class="breadcrumb-item active">Manajemen Stok</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">

        <!-- FORM MANAJEMEN STOK -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Manajemen Stok</h5>

              <form method="POST">

                <!-- PILIH PRODUK -->
                <div class="mb-3">
                  <label class="form-label">Pilih Produk</label>
                  <select name="product_id" class="form-select" required>
                    <option selected disabled>-- Pilih Produk --</option>

                    <?php
                    include "koneksi.php";

                    $produk = mysqli_query($conn, "SELECT * FROM product");

                    while ($p = mysqli_fetch_assoc($produk)) {
                      echo "<option value='{$p['id']}'>{$p['product_name']}</option>";
                    }
                    ?>
                  </select>
                </div>

                <!-- JENIS AKSI -->
                <div class="mb-3">
                  <label class="form-label">Jenis Aksi</label>

                  <select name="change_type" class="form-select">
                    <option value="ADD">Tambah Stok</option>
                    <option value="REDUCE">Kurangi Stok</option>
                  </select>
                </div>

                <!-- JUMLAH -->
                <div class="mb-3">
                  <label class="form-label">Jumlah</label>
                  <input type="number" name="qty" class="form-control" required>
                </div>

                <!-- CATATAN -->
                <div class="mb-3">
                  <label class="form-label">Catatan</label>
                  <textarea name="note" class="form-control" rows="2"></textarea>
                </div>

                <!-- BUTTON -->
                <button type="submit" name="submit" class="btn btn-primary w-100">
                  Simpan Perubahan
                </button>

              </form>
            </div>
          </div>
        </div>

        <!-- RIWAYAT STOK -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Riwayat Stok</h5>

              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Aksi</th>
                    <th>Qty</th>
                    <th>User</th>
                  </tr>
                </thead>

                <tbody>

                  <?php
                  $query = mysqli_query($conn, "
                                SELECT sl.*, p.product_name, u.name
                                FROM stock_logs sl
                                JOIN product p ON sl.product_id = p.id
                                JOIN users u ON sl.created_by = u.id
                                ORDER BY sl.created_at DESC
                            ");

                  while ($row = mysqli_fetch_assoc($query)) {

                    $badge = $row['change_type'] == 'ADD'
                      ? "<span class='badge bg-success'>+(ADD)</span>"
                      : "<span class='badge bg-danger'>(REDUCE)</span>";

                    echo "
                                    <tr>
                                        <td>" . date('d M Y', strtotime($row['created_at'])) . "</td>
                                        <td>{$row['product_name']}</td>
                                        <td>$badge</td>
                                        <td>{$row['qty']}</td>
                                        <td>{$row['name']}</td>
                                    </tr>
                                ";
                  }
                  ?>

                </tbody>
              </table>

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
      Designed by <a href="https://www.instagram.com/rastysm?igsh=d3o5dWpydDd2OGw3">RasyaAndi</a>
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