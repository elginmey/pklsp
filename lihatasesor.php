<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Ambil semua TUK
$stmt = $conn->prepare("SELECT * FROM data_asesor");
$stmt->execute();
$data_asesor = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>LSP - SDM TIK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/octicons/16.1.1/build.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" />
    <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="css/dripicons.css" />
    <link rel="stylesheet" href="css/slick.css" />
    <link rel="stylesheet" href="css/meanmenu.css" />
    <link rel="stylesheet" href="css/default.css" />
    <link rel="stylesheet" href="css/stylee.css" />
    <link rel="stylesheet" href="css/animate.min.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />
    <link rel="stylesheet" href="css/responsive.css" />
    <link rel="shortcut icon" type="image/x-icon" href="lsp.png" />
    <style>

    </style>
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">
          
          <img src="lsp.png" alt="Logo" width="150" height="auto" class="logo-lsp" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasNavbarLabel">LSP SDM TIK</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" href="index.html"><i class="fas fa-home"></i> Home</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profilDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i class="fas fa-user"></i> Profil </a>
                <ul class="dropdown-menu" aria-labelledby="profilDropdown">
                  <li><a class="dropdown-item" href="visi-misi.html">Visi-Misi</a></li>
                  <li><a class="dropdown-item" href="struktur organisasi.html">Struktur Organisasi</a></li>
                  <li><a class="dropdown-item" href="lihatmitra.php">Mitra Kerja</a></li>
                  <li><a class="dropdown-item" href="tentanglsp.html">Tentang LSP</a></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="sertifikasiDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i class="fas fa-certificate"></i> Sertifikasi </a>
                <ul class="dropdown-menu" aria-labelledby="sertifikasiDropdown">
                  <li><a class="dropdown-item" href="skema.html">Skema Sertifikasi</a></li>
                  <li><a class="dropdown-item" href="lihat_tuk.php">Tempat Uji Kompetensi</a></li>
                  <li><a class="dropdown-item" href="lihatasesor.php">Asesor Kompetensi</a></li>
                  <li><a class="dropdown-item" href="skkni.html">SKKNI</a></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="mediaDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i class="fas fa-photo-video"></i> Media </a>
                <ul class="dropdown-menu" aria-labelledby="mediaDropdown">
                  <li>
                    <a class="dropdown-item" href="berita.html"><i class="fas fa-newspaper"></i> Berita</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="gallery.html"><i class="fas fa-images"></i> Galeri</a>
                  </li>
                  <li><a class="dropdown-item" href="https://www.linkedin.com/in/lsp-sdmtik-898955308/?originalSubdomain=id"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                  <li><a class="dropdown-item" href="https://www.instagram.com/lsp_sdmtik?igsh=ajJxMmQwYnR2cWFk"><i class="fab fa-instagram"></i> Instagram</a></li>
                  <li><a class="dropdown-item" href="https://youtube.com/@lspsdmtik4858?si=HNf4TLqGRYRS4Nbr"><i class="fab fa-youtube"></i> YouTube</a></li>
                  <li><a class="dropdown-item" href="https://facebook.com/usernameFacebook"><i class="fab fa-facebook"></i> Facebook</a></li>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.html">Home</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Profil </a>
              <ul class="dropdown-menu" aria-labelledby="profilDropdown">
                <li><a class="dropdown-item" href="visi-misi.html">Visi-Misi</a></li>
                <li><a class="dropdown-item" href="struktur organisasi.html">Struktur Organisasi</a></li>
                <li><a class="dropdown-item" href="lihatmitra.php">Partnership / Mitra Kerja</a></li>
                <li><a class="dropdown-item" href="tentanglsp.html">Tentang LSP</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="sertifikasiDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Informasi Sertifikasi </a>
              <ul class="dropdown-menu" aria-labelledby="sertifikasiDropdown">
                <li><a class="dropdown-item" href="skema.html">Skema Sertifikasi</a></li>
                <li><a class="dropdown-item" href="lihat_tuk.php">Tempat Uji Kompetensi</a></li>
                <li><a class="dropdown-item" href="lihatasesor.php">Asesor Kompetensi</a></li>
                <li><a class="dropdown-item" href="skkni.html">SKKNI</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="mediaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Media </a>
              <ul class="dropdown-menu" aria-labelledby="mediaDropdown">
                <li>
                  <a class="dropdown-item" href="berita.html"><i class="fas fa-newspaper"></i> Berita</a>
                </li>
                <li>
                  <a class="dropdown-item" href="gallery.html"><i class="fas fa-images"></i> Galeri</a>
                </li>
            <li><a class="dropdown-item" href="https://www.linkedin.com/in/lsp-sdmtik-898955308/?originalSubdomain=id"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
            <li><a class="dropdown-item" href="https://www.instagram.com/lsp_sdmtik?igsh=ajJxMmQwYnR2cWFk"><i class="fab fa-instagram"></i> Instagram</a></li>
            <li><a class="dropdown-item" href="https://youtube.com/@lspsdmtik4858?si=HNf4TLqGRYRS4Nbr"><i class="fab fa-youtube"></i> YouTube</a></li>
            <li><a class="dropdown-item" href="https://facebook.com/usernameFacebook"><i class="fab fa-facebook"></i> Facebook</a></li>
                </li>
              </ul>
            </li>
          </ul>
          
        </div>
      </div>
    </nav>

<body>
<div class="container-fluid mt-5">
    <h2 class="mb-4">Data Asesor</h2>
    <a href="https://forms.gle/nJXKUbBERpJJNX1n7" target="_blank" class="btn btn-primary mb-3">Pendaftaran Asesor</a>
    
    <div class="table-responsive">
        <table id="asesorTable" class="table table-striped table-bordered nowrap">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Asesor</th>
                    <th>No Registrasi</th>
                    <th>Alamat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_asesor as $index => $asesor): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $asesor['nama_asesor'] ?></td>
                    <td><?= $asesor['no_registrasi'] ?></td>
                    <td><?= $asesor['alamat'] ?></td>
                    <td><?= $asesor['status'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- FOOTER -->
   <footer class="bg-dark">
    <div class="footer-top">
      <div class="container">
        <div class="row justify-content-between">
          
          <div class="col-lg-3 col-sm-6">
           
              <img src="logo 3.png"  alt="" width="250px" height="auto" />
            <div class="line"></div>
            <p>
              LSP SDM TIK adalah Lembaga Sertifikasi Profesi Pihak 3 yang terlisensi oleh BNSP dengan surat keputusan No . KEP.1342/BNSP/VII/2021 dan lisensi No.BNSPLSP1977-ID.
            </p>
            <div class="social-icons pb-4">
              <a href="https://youtube.com/@lspsdmtik4858?si=HNf4TLqGRYRS4Nbr" target="_blank">
                <i class="ri-youtube-fill"></i> <!-- YouTube Icon -->
              </a>
              <a href="https://twitter.com/usernameTwitter" target="_blank">
                <i class="ri-twitter-fill"></i> <!-- Twitter Icon -->
              </a>
              <a href="https://facebook.com/usernameFacebook" target="_blank">
                <i class="ri-facebook-fill"></i> <!-- Facebook Icon -->
              </a>
              <a href="https://www.linkedin.com/in/lsp-sdmtik-898955308/?originalSubdomain=id" target="_blank">
                <i class="ri-linkedin-fill"></i> <!-- LinkedIn Icon -->
              </a>
              <a href="https://www.instagram.com/lsp_sdmtik?igsh=ajJxMmQwYnR2cWFk" target="_blank">
                <i class="ri-instagram-fill"></i> <!-- Instagram Icon -->
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6">
            <h5 class="mb-0 text-white">ABOUT</h5>
            <div class="line"></div>
            <ul>
              <li><a href="tentanglsp.html">Tentang LSP</a></li><hr>
              <li><a href="visi-misi.html">VISI </a></li><hr>
              <li><a href="visi-misi.html">MISI </a></li><hr>
              
            </ul>
          </div>

          <div class="col-lg-3 col-sm-6">
            <h5 class="mb-0 text-white">CONTACT</h5>
            <div class="line"></div>
            <ul>
              <li>
                <i class="fas fa-map-marker-alt"> <br></i> Ruko Ketapang Indah, Blok B2, Jl. Kyai Haji Zainul Arifin No.33 & 34, 
                RT.4/RW.7 Krukut, Kec. Taman Sari, 
                Kota Jakarta Barat, Kota/Kabupaten Kota Administrasi 
                Jakarta Barat, Provinsi DKI Jakarta.
              </li>
              <br>
              <li><i class="fas fa-phone-alt"></i> 021-6340960 / 0811 292 5599</li>
              <br>
              <li><i class="fas fa-envelope"></i> lspmsdmtik@gmail.com</li>
            </ul>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    
    <style>
    .table-responsive {
        overflow-x: auto;
    }
    #asesorTable {
        width: 100% !important;
    }
    #asesorTable th, #asesorTable td {
        white-space: normal;
        word-wrap: break-word;
        min-width: 100px; /* Sesuaikan nilai ini sesuai kebutuhan */
    }
    #asesorTable td:nth-child(4) { /* Kolom alamat */
        min-width: 200px; /* Sesuaikan nilai ini sesuai kebutuhan */
    }
    @media screen and (max-width: 767px) {
        #asesorTable th, #asesorTable td {
            font-size: 14px; /* Ukuran font lebih kecil untuk layar kecil */
        }
    }

    </style>
    <script>
    $(document).ready(function() {
        $('#asesorTable').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            responsive: true,
            scrollX: true,
            autoWidth: false,
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // No.
                { responsivePriority: 2, targets: 1 }, // Nama Asesor
                { responsivePriority: 3, targets: 4 }, // Status
                { responsivePriority: 4, targets: 2 }, // No Registrasi
                { responsivePriority: 5, targets: 3 }  // Alamat
            ]
        });
    });
</script>

    
<script>
      $(document).ready(function () {
        $(".buttons").click(function () {
          $(this).addClass("active").siblings().removeClass("active");

          var filter = $(this).attr("data-filter");

          if (filter == "all") {
            $(".image").show(400);
          } else {
            $(".image")
              .not("." + filter)
              .hide(200);
            $(".image")
              .filter("." + filter)
              .show(400);
          }
        });

        $(".gallery").magnificPopup({
          delegate: "a",
          type: "image",
          gallery: {
            enabled: true,
          },
        });
      });
      window.addEventListener("scroll", function () {
        var navbar = document.querySelector(".navbar");
        if (window.scrollY > 50) {
          navbar.classList.add("fixed-top");
        } else {
          navbar.classList.remove("fixed-top");
        }
      });
    </script>

    
</body>
</html>