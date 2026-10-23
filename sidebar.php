<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE SIDEBAR PRO MAX (GEMINI STYLE TOGGLE)
 * File          : sidebar.php
 * Deskripsi     : Top Navigation Bar + Drawer Sidebar Buka/Tutup untuk SEMUA FITUR
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_login = isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : 'Administrator';
$page_active = basename($_SERVER['PHP_SELF']);
?>

<!-- CSS UNIVERSAL GEMINI SIDEBAR -->
<style>
    :root {
        --sidebar-width: 280px;
        --sidebar-bg: #101014;
        --gold-primary: #d4af37;
        --gold-gradient: linear-gradient(135deg, #fce080 0%, #d4af37 50%, #aa8c2c 100%);
        --text-main: #f4f4f5;
        --text-muted: #a1a1aa;
        --border-color: #27272a;
    }

    /* Top Navigation Bar (Selalu Muncul di Atas) */
    .top-navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: #09090b;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 9999;
    }

    .navbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    /* Tombol Toggle Buka/Tutup Gaya Gemini */
    .btn-toggle-sidebar {
        background: transparent;
        border: none;
        color: var(--text-main);
        font-size: 24px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .btn-toggle-sidebar:hover {
        background: #1f1f23;
        color: var(--gold-primary);
    }

    .brand-title {
        font-weight: 800;
        font-size: 16px;
        background: var(--gold-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: 0.5px;
    }

    .user-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #141418;
        border: 1px solid var(--border-color);
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        color: var(--gold-primary);
    }

    /* Drawer Sidebar Sliding */
    .sidebar-drawer {
        position: fixed;
        top: 60px;
        left: calc(-1 * var(--sidebar-width));
        width: var(--sidebar-width);
        height: calc(100vh - 60px);
        background: var(--sidebar-bg);
        border-right: 1px solid var(--border-color);
        box-shadow: 10px 0 30px rgba(0,0,0,0.5);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 9998;
        overflow-y: auto;
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
    }

    /* Class saat sidebar dibuka */
    .sidebar-drawer.open {
        transform: translateX(var(--sidebar-width));
    }

    /* Overlay Gelap Latar Belakang */
    .sidebar-overlay {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9997;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Menu & Grouping Sidebar */
    .sidebar-section-title {
        font-size: 10px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 18px 0 8px 10px;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin-bottom: 4px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .sidebar-menu a:hover {
        background: rgba(212, 175, 55, 0.1);
        color: var(--gold-primary);
    }

    .sidebar-menu a.active {
        background: var(--gold-gradient);
        color: #000;
        font-weight: 800;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    }

    /* Penyesuaian Ruang Kosong Konten Utama (Padding-Top) */
    body {
        padding-top: 60px !important;
    }
</style>

<!-- TOP NAVBAR DENGAN BUTTON TOGGLE GEMINI STYLE -->
<div class="top-navbar">
    <div class="navbar-left">
        <button type="button" class="btn-toggle-sidebar" id="btnToggleSidebar" title="Buka/Tutup Sidebar">
            ☰
        </button>
        <span class="brand-title">SMK NEGERI 1 LUMAJANG</span>
    </div>
    <div class="user-pill">
        👑 <?= htmlspecialchars($user_login); ?>
    </div>
</div>

<!-- OVERLAY GELAP SAAT SIDEBAR BUKA -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR DRAWER BUKA/TUTUP -->
<aside class="sidebar-drawer" id="sidebarDrawer">
    
    <div class="sidebar-section-title">MENU UTAMA INSTANSI</div>
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?= ($page_active == 'index.php') ? 'active' : ''; ?>">
                <span>📊</span> Dashboard Utama
            </a>
        </li>
        <li>
            <a href="data_siswa.php" class="<?= ($page_active == 'data_siswa.php') ? 'active' : ''; ?>">
                <span>👨‍🎓</span> Data Siswa & Master
            </a>
        </li>
        <li>
            <a href="data_guru.php" class="<?= ($page_active == 'data_guru.php') ? 'active' : ''; ?>">
                <span>👨‍🏫</span> Data Guru & Staf
            </a>
        </li>
        <li>
            <a href="kelas.php" class="<?= ($page_active == 'kelas.php') ? 'active' : ''; ?>">
                <span>🏫</span> Manajemen Kelas
            </a>
        </li>
        <li>
            <a href="prestasi.php" class="<?= ($page_active == 'prestasi.php') ? 'active' : ''; ?>">
                <span>🏆</span> Prestasi Siswa
            </a>
        </li>
    </ul>

    <div class="sidebar-section-title">AKADEMIK & KEUANGAN</div>
    <ul class="sidebar-menu">
        <li>
            <a href="absensi.php" class="<?= ($page_active == 'absensi.php') ? 'active' : ''; ?>">
                <span>📅</span> Presensi Kehadiran
            </a>
        </li>
        <li>
            <a href="spp.php" class="<?= ($page_active == 'spp.php') ? 'active' : ''; ?>">
                <span>💰</span> Pembayaran SPP
            </a>
        </li>
        <li>
            <a href="kartu.php" class="<?= ($page_active == 'kartu.php') ? 'active' : ''; ?>">
                <span>🪪</span> Cetak Kartu Pelajar
            </a>
        </li>
    </ul>

    <div class="sidebar-section-title">SISTEM & AKUN</div>
    <ul class="sidebar-menu">
        <li>
            <a href="logout.php" style="color: #ef4444;" onclick="return confirm('Yakin ingin keluar dari sistem?');">
                <span>🚪</span> Keluar Sistem
            </a>
        </li>
    </ul>

</aside>

<!-- JAVASCRIPT TOGGLE OTOMATIS GEMINI STYLE -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const btnToggle = document.getElementById("btnToggleSidebar");
        const drawer = document.getElementById("sidebarDrawer");
        const overlay = document.getElementById("sidebarOverlay");

        function toggleSidebar() {
            drawer.classList.toggle("open");
            overlay.classList.toggle("active");
        }

        btnToggle.addEventListener("click", toggleSidebar);
        overlay.addEventListener("click", toggleSidebar);
    });
</script>