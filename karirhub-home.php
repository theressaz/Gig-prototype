<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['siapkerja_email']) && empty($_SESSION['username'])) {
    header('Location: siapkerja-login.php?redirect=karirhub-home');
    exit;
}

$displayName = (string)($_SESSION['siapkerja_name'] ?? $_SESSION['username'] ?? 'Pengguna');
$userInitials = strtoupper(substr(preg_replace('/\s+/', '', $displayName), 0, 2));
if (strlen($userInitials) < 2) {
    $userInitials = 'US';
}

$trendingJobs = [
    ['title' => 'Accountant', 'company' => 'PT. Surya Indah', 'loc' => 'Jakarta', 'pay' => 'Rp 8–12 jt', 'logo' => 'SI'],
    ['title' => 'Graphic Designer', 'company' => 'CV. Kreatif Digital', 'loc' => 'Bandung', 'pay' => 'Rp 6–9 jt', 'logo' => 'KD'],
    ['title' => 'Software Engineer', 'company' => 'PT. Nusantara Tech', 'loc' => 'Surabaya', 'pay' => 'Rp 12–18 jt', 'logo' => 'NT'],
    ['title' => 'HR Specialist', 'company' => 'PT. Talenta Prima', 'loc' => 'Jakarta', 'pay' => 'Rp 7–10 jt', 'logo' => 'TP'],
];

$categories = [
    ['name' => 'Pengadaan', 'count' => '344', 'icon' => '📦'],
    ['name' => 'Pemasaran', 'count' => '340', 'icon' => '📣'],
    ['name' => 'Akuntansi', 'count' => '450', 'icon' => '📊'],
    ['name' => 'Manufaktur', 'count' => '1.120', 'icon' => '🏭'],
    ['name' => 'TI & Software', 'count' => '860', 'icon' => '💻'],
    ['name' => 'Desain & Kreatif', 'count' => '520', 'icon' => '🎨'],
    ['name' => 'Konstruksi', 'count' => '680', 'icon' => '🏗️'],
    ['name' => 'Logistik', 'count' => '410', 'icon' => '🚚'],
    ['name' => 'Perhotelan', 'count' => '290', 'icon' => '🏨'],
    ['name' => 'Kesehatan', 'count' => '375', 'icon' => '⚕️'],
];

$testimonials = [
    ['name' => 'Ariffudin', 'role' => 'Software Developer', 'text' => 'Karirhub memudahkan saya menemukan lowongan yang sesuai kompetensi dan lokasi.'],
    ['name' => 'Siti Khodijah', 'role' => 'HR Officer', 'text' => 'Proses lamaran jelas dan cepat. Saya bisa memantau status dari satu platform.'],
    ['name' => 'Budi Santoso', 'role' => 'Marketing Lead', 'text' => 'Antarmuka modern dan banyak pilihan perusahaan terpercaya di dalam negeri.'],
    ['name' => 'Dewi Lestari', 'role' => 'UI/UX Designer', 'text' => 'Fitur pencarian lokasi sangat membantu untuk pekerja remote dan hybrid.'],
    ['name' => 'Rizky Pratama', 'role' => 'Data Analyst', 'text' => 'Rekomendasi lowongan trending relevan dengan profil SIAPkerja saya.'],
    ['name' => 'Maya Anggraini', 'role' => 'Admin Operasional', 'text' => 'Platform rekrutmen terbaik dari ekosistem Kemnaker yang saya percaya.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karirhub · Beranda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --blue: #1657c1;
      --blue-dark: #0f3d8c;
      --blue-light: #e8f0fe;
      --sky: #18b5ea;
      --text: #0f172a;
      --muted: #64748b;
      --border: #e2e8f0;
      --bg: #f8fafc;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: var(--text); background: #fff; line-height: 1.5; }
    a { color: inherit; text-decoration: none; }
    .container { max-width: 1180px; margin: 0 auto; padding: 0 20px; }

    /* Navbar */
    .topbar {
      position: sticky; top: 0; z-index: 50;
      background: #fff; border-bottom: 1px solid var(--border);
      box-shadow: 0 1px 0 rgba(15,23,42,.04);
    }
    .nav-inner {
      height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.15rem; }
    .brand-mark {
      width: 36px; height: 36px; border-radius: 10px;
      background: linear-gradient(135deg, var(--sky), var(--blue));
      display: grid; place-items: center; color: #fff; font-size: 0.75rem; font-weight: 800;
    }
    .eco-badge {
      font-size: 0.65rem; font-weight: 700; color: var(--muted);
      border: 1px solid var(--border); border-radius: 6px; padding: 2px 6px; margin-left: 4px;
    }
    .nav-links { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .nav-links a {
      padding: 8px 12px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; color: #334155;
    }
    .nav-links a:hover { background: var(--blue-light); color: var(--blue); }
    .nav-links a.active { color: var(--blue); background: var(--blue-light); }
    .nav-links a.gig {
      color: var(--blue-dark); border: 1px solid #bfdbfe; background: #eff6ff;
    }
    .nav-links a.gig:hover { background: #dbeafe; }
    .nav-user {
      width: 38px; height: 38px; border-radius: 50%; background: var(--blue);
      color: #fff; display: grid; place-items: center; font-size: 0.78rem; font-weight: 800;
      border: 2px solid #fff; box-shadow: 0 0 0 1px var(--border);
    }

    /* Hero */
    .hero {
      position: relative; overflow: hidden;
      background: linear-gradient(180deg, #f0f9ff 0%, #fff 55%);
      padding: 36px 0 0;
    }
    .hero-wave {
      position: absolute; top: 0; left: 0; right: 0; height: 120px;
      background: radial-gradient(ellipse at 50% -20%, rgba(24,181,234,.25), transparent 70%);
      pointer-events: none;
    }
    .hero-grid {
      display: grid; grid-template-columns: 1fr 1.2fr 1fr; align-items: end; gap: 12px;
      min-height: 320px;
    }
    .hero-person {
      height: 280px; border-radius: 20px 20px 0 0; object-fit: cover; width: 100%;
      background: linear-gradient(180deg, #dbeafe, #eff6ff);
    }
    .hero-center { text-align: center; padding-bottom: 24px; z-index: 1; }
    .hero-center h1 {
      font-size: clamp(1.6rem, 3vw, 2.35rem); font-weight: 800; line-height: 1.2;
      margin-bottom: 22px; letter-spacing: -0.02em;
    }
    .search-bar {
      display: flex; flex-wrap: wrap; gap: 0; background: #fff; border: 1px solid var(--border);
      border-radius: 14px; padding: 8px; box-shadow: 0 12px 30px rgba(15,23,42,.08);
      max-width: 720px; margin: 0 auto;
    }
    .search-field {
      flex: 1; min-width: 180px; display: flex; align-items: center; gap: 8px;
      padding: 10px 14px; border-right: 1px solid var(--border);
    }
    .search-field:last-of-type { border-right: none; }
    .search-field input {
      border: none; outline: none; width: 100%; font: inherit; font-size: 0.9rem;
    }
    .search-btn {
      background: var(--blue); color: #fff; border: none; border-radius: 10px;
      padding: 12px 20px; font-weight: 700; cursor: pointer; font: inherit; white-space: nowrap;
    }
    .search-btn:hover { background: var(--blue-dark); }

    /* Trending */
    .trending {
      background: linear-gradient(135deg, #1d6fd8, #1657c1);
      color: #fff; border-radius: 24px 24px 0 0; margin-top: 28px; padding: 36px 0 40px;
    }
    .trending-grid { display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start; }
    .trending h2 { font-size: 1.45rem; font-weight: 800; margin-bottom: 10px; }
    .trending p { opacity: .92; font-size: 0.9rem; margin-bottom: 18px; }
    .btn-white {
      display: inline-block; background: #fff; color: var(--blue); font-weight: 700;
      padding: 10px 16px; border-radius: 10px; font-size: 0.88rem;
    }
    .job-scroll { display: flex; gap: 14px; overflow-x: auto; padding-bottom: 8px; scroll-snap-type: x mandatory; }
    .job-card {
      flex: 0 0 240px; scroll-snap-align: start; background: #fff; color: var(--text);
      border-radius: 14px; padding: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .job-logo {
      width: 40px; height: 40px; border-radius: 10px; background: var(--blue-light);
      color: var(--blue); display: grid; place-items: center; font-weight: 800; font-size: 0.75rem; margin-bottom: 10px;
    }
    .job-card h3 { font-size: 0.95rem; margin-bottom: 4px; }
    .job-card .meta { font-size: 0.78rem; color: var(--muted); margin-bottom: 8px; }
    .job-card .pay { font-size: 0.82rem; font-weight: 700; color: var(--blue); margin-bottom: 12px; }
    .job-card .detail {
      display: inline-block; font-size: 0.78rem; font-weight: 700; color: var(--blue);
      border: 1px solid #bfdbfe; padding: 6px 10px; border-radius: 8px;
    }

    /* Sections */
    section.block { padding: 48px 0; }
    section.block.alt { background: var(--bg); }
    .section-title { font-size: 1.35rem; font-weight: 800; margin-bottom: 20px; text-align: center; }
    .cat-grid {
      display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px;
    }
    .cat-card {
      background: #fff; border: 1px solid var(--border); border-radius: 12px;
      padding: 16px 12px; text-align: center; transition: box-shadow .2s;
    }
    .cat-card:hover { box-shadow: var(--shadow, 0 8px 20px rgba(15,23,42,.06)); }
    .cat-card .ico { font-size: 1.4rem; margin-bottom: 8px; }
    .cat-card .name { font-size: 0.82rem; font-weight: 700; margin-bottom: 4px; }
    .cat-card .count { font-size: 0.72rem; color: var(--muted); }
    .section-links { text-align: center; margin-top: 18px; font-size: 0.88rem; }
    .section-links a { color: var(--blue); font-weight: 700; margin: 0 8px; }

    .split {
      display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: center;
    }
    .split img {
      width: 100%; border-radius: 16px; aspect-ratio: 4/3; object-fit: cover;
      background: #e2e8f0;
    }
    .eyebrow { color: var(--blue); font-weight: 700; font-size: 0.82rem; margin-bottom: 8px; }
    .split h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; line-height: 1.25; }
    .split p { color: var(--muted); margin-bottom: 18px; font-size: 0.92rem; }
    .btn-primary {
      display: inline-block; background: var(--blue); color: #fff; font-weight: 700;
      padding: 11px 18px; border-radius: 10px; font-size: 0.88rem;
    }
    .features { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: center; }
    .feature-list { display: grid; gap: 14px; }
    .feature-item { display: flex; gap: 12px; align-items: flex-start; }
    .feature-item .dot {
      width: 36px; height: 36px; border-radius: 10px; background: var(--blue-light);
      color: var(--blue); display: grid; place-items: center; font-weight: 800; flex-shrink: 0;
    }
    .loc-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .loc-card {
      background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 24px;
    }
    .loc-card h3 { font-size: 1.05rem; font-weight: 800; margin-bottom: 12px; line-height: 1.35; }
    .test-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .test-card {
      background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 18px;
    }
    .test-card strong { display: block; margin-bottom: 2px; }
    .test-card span { font-size: 0.78rem; color: var(--muted); display: block; margin-bottom: 8px; }
    .test-card p { font-size: 0.85rem; color: #334155; }

    .cta-band {
      background: linear-gradient(135deg, #1a5ec4, #2777f2);
      color: #fff; border-radius: 20px; padding: 36px 24px; text-align: center; margin: 24px 0 0;
    }
    .cta-band h2 { font-size: 1.4rem; margin-bottom: 14px; }
    .cta-band .btn-white { color: var(--blue-dark); }

    footer.site-footer {
      background: #fff; border-top: 1px solid var(--border); padding: 40px 0 24px; margin-top: 40px;
    }
    .footer-grid {
      display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 24px; margin-bottom: 24px;
    }
    .footer-grid h4 { font-size: 0.9rem; margin-bottom: 10px; }
    .footer-grid p, .footer-grid li { font-size: 0.82rem; color: var(--muted); list-style: none; }
    .store-btns { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
    .store-btns span {
      border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-size: 0.75rem; font-weight: 700;
    }
    .social { display: flex; gap: 8px; margin-top: 10px; }
    .social a {
      width: 32px; height: 32px; border-radius: 8px; background: var(--bg);
      display: grid; place-items: center; font-size: 0.7rem; font-weight: 800; color: var(--muted);
    }
    .copyright { text-align: center; font-size: 0.78rem; color: var(--muted); padding-top: 16px; border-top: 1px solid var(--border); }

    @media (max-width: 960px) {
      .hero-grid { grid-template-columns: 1fr; }
      .hero-person { display: none; }
      .trending-grid { grid-template-columns: 1fr; }
      .cat-grid { grid-template-columns: repeat(2, 1fr); }
      .split, .features, .loc-cards, .test-grid, .footer-grid { grid-template-columns: 1fr; }
      .nav-links { display: none; }
    }
  </style>
</head>
<body>

  <header class="topbar">
    <div class="container nav-inner">
      <a href="karirhub-home.php" class="brand">
        <span class="brand-mark">Kh</span>
        <span>Karir<span style="color:var(--sky)">hub</span></span>
        <span class="eco-badge">SIAPkerja</span>
      </a>
      <nav class="nav-links" aria-label="Navigasi utama">
        <a href="karirhub-home.php" class="active">Beranda</a>
        <a href="#lowongan">Lowongan Pekerjaan</a>
        <a href="gig-workers-go.php" class="gig">Gig Workers</a>
        <a href="#pelatihan">Pelatihan</a>
        <a href="#entitas">Entitas</a>
      </nav>
      <div class="nav-user" title="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    </div>
  </header>

  <section class="hero">
    <div class="hero-wave"></div>
    <div class="container hero-grid">
      <img class="hero-person" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=500&fit=crop" alt="" />
      <div class="hero-center">
        <h1>Dapatkan pekerjaan<br />dari seluruh dunia</h1>
        <form class="search-bar" action="#lowongan" method="get">
          <label class="search-field">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" placeholder="Cari kata kunci lowongan" />
          </label>
          <label class="search-field">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <input type="text" name="loc" placeholder="Pilih lokasi" />
          </label>
          <button type="submit" class="search-btn">Cari Pekerjaan</button>
        </form>
      </div>
      <img class="hero-person" src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&h=500&fit=crop" alt="" />
    </div>
  </section>

  <section class="trending" id="lowongan">
    <div class="container trending-grid">
      <div>
        <h2>Lowongan kerja yang sedang trending</h2>
        <p>Temukan peluang karier terbaru dari perusahaan terpercaya di seluruh Indonesia.</p>
        <a class="btn-white" href="#lowongan">Lihat selengkapnya</a>
      </div>
      <div class="job-scroll">
        <?php foreach ($trendingJobs as $job): ?>
          <article class="job-card">
            <div class="job-logo"><?php echo htmlspecialchars($job['logo'], ENT_QUOTES, 'UTF-8'); ?></div>
            <h3><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <div class="meta"><?php echo htmlspecialchars($job['company'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($job['loc'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="pay"><?php echo htmlspecialchars($job['pay'], ENT_QUOTES, 'UTF-8'); ?></div>
            <span class="detail">Lihat Detail</span>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="block alt">
    <div class="container">
      <h2 class="section-title">Bidang pekerjaan terpopuler</h2>
      <div class="cat-grid">
        <?php foreach ($categories as $cat): ?>
          <a href="#lowongan" class="cat-card">
            <div class="ico"><?php echo $cat['icon']; ?></div>
            <div class="name"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="count"><?php echo htmlspecialchars($cat['count'], ENT_QUOTES, 'UTF-8'); ?> lowongan</div>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="section-links">
        <a href="#lowongan">Lihat semua bidang pekerjaan</a>
        ·
        <a href="#lowongan">Mulai pencarian</a>
      </div>
    </div>
  </section>

  <section class="block">
    <div class="container split">
      <img src="https://images.unsplash.com/photo-1521737711867-e3b97375c902?w=800&h=600&fit=crop" alt="" />
      <div>
        <div class="eyebrow">Info Hubungan Industrial</div>
        <h2>Hadiri job fair kami dan temukan peluang karirmu!</h2>
        <p>Bertemu langsung dengan perusahaan mitra Kemnaker dan dapatkan informasi rekrutmen terkini.</p>
        <a class="btn-primary" href="#lowongan">Lihat Jadwal Job Fair</a>
      </div>
    </div>
  </section>

  <section class="block alt">
    <div class="container features">
      <div>
        <h2 class="section-title" style="text-align:left;margin-bottom:12px;">Platform rekrutmen terbaik</h2>
        <p style="color:var(--muted);font-size:0.92rem;">Karirhub terhubung dengan ekosistem SIAPkerja untuk mempermudah pencarian kerja dan rekrutmen yang aman.</p>
        <a class="btn-primary" href="pilih-pendaftaran.php" style="margin-top:16px;">Daftar Karirhub sekarang</a>
      </div>
      <div class="feature-list">
        <div class="feature-item"><span class="dot">1</span><div><strong>Proses cepat & transparan</strong><p style="font-size:0.85rem;color:var(--muted);">Lamar lowongan dengan profil SIAPkerja yang terverifikasi.</p></div></div>
        <div class="feature-item"><span class="dot">2</span><div><strong>Pilihan perusahaan beragam</strong><p style="font-size:0.85rem;color:var(--muted);">Dari UMKM hingga korporasi besar di berbagai sektor.</p></div></div>
        <div class="feature-item"><span class="dot">3</span><div><strong>Sistem terintegrasi & aman</strong><p style="font-size:0.85rem;color:var(--muted);">Data pelamar dilindungi sesuai standar Kemnaker.</p></div></div>
      </div>
    </div>
  </section>

  <section class="block">
    <div class="container loc-cards">
      <article class="loc-card">
        <h3>Temukan banyak kesempatan karir di dalam negeri</h3>
        <a class="btn-primary" href="#lowongan">Mulai mencari</a>
      </article>
      <article class="loc-card">
        <h3>Jelajahi dunia dan temukan karir impianmu di luar negeri</h3>
        <a class="btn-primary" href="#lowongan">Mulai mencari</a>
      </article>
    </div>
  </section>

  <section class="block alt">
    <div class="container">
      <h2 class="section-title">Yang mereka katakan tentang Karirhub</h2>
      <div class="test-grid">
        <?php foreach ($testimonials as $t): ?>
          <article class="test-card">
            <strong><?php echo htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span><?php echo htmlspecialchars($t['role'], ENT_QUOTES, 'UTF-8'); ?></span>
            <p><?php echo htmlspecialchars($t['text'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="block">
    <div class="container">
      <div class="cta-band">
        <h2>Temukan pekerjaan yang diinginkan</h2>
        <a class="btn-white" href="#lowongan">Cari lowongan sekarang</a>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <h4>Unduh aplikasi dan jangan lewatkan</h4>
          <div class="store-btns">
            <span>App Store</span>
            <span>Google Play</span>
          </div>
        </div>
        <div>
          <h4>Kementerian Ketenagakerjaan RI</h4>
          <p>Jl. Jenderal Gatot Subroto Kav. 4, Jakarta 12710</p>
          <p>Telp: (021) 5255737 · Email: call120@kemnaker.go.id</p>
        </div>
        <div>
          <h4>Ikuti kami</h4>
          <div class="social">
            <a href="#">YT</a><a href="#">IG</a><a href="#">FB</a><a href="#">X</a>
          </div>
        </div>
      </div>
      <p class="copyright">© <?php echo date('Y'); ?> Karirhub · Ekosistem SIAPkerja Kemnaker</p>
    </div>
  </footer>

</body>
</html>
