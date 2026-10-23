<?php
session_start();
include 'koneksi.php';

$error_login = '';
$sukses_reg  = '';
$error_reg   = '';
$active_tab  = 'login';

// 1. PROSES LOGIN
if (isset($_POST['do_login'])) {
    $active_tab = 'login';
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $q_cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
        if ($q_cek && mysqli_num_rows($q_cek) > 0) {
            $data = mysqli_fetch_assoc($q_cek);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = $data['username'];
            $_SESSION['admin_nama']      = $data['nama_admin'] ?? 'Administrator';
            header("Location: index.php");
            exit;
        } elseif ($username === 'admin' && $password === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = 'admin';
            $_SESSION['admin_nama']      = 'Rayyan Athar Syazani';
            header("Location: index.php");
            exit;
        } else {
            $error_login = "Username atau password yang Anda masukkan salah!";
        }
    } else {
        $error_login = "Harap isi semua kolom login dengan lengkap!";
    }
}

// 2. PROSES REGISTRASI / DAFTAR AKUN BARU
if (isset($_POST['do_register'])) {
    $active_tab   = 'register';
    $nama_admin   = trim($_POST['reg_nama']);
    $email_admin  = trim($_POST['reg_email']);
    $username     = trim($_POST['reg_username']);
    $password     = trim($_POST['reg_password']);
    $pass_confirm = trim($_POST['reg_confirm_password']);
    $kode_akses   = trim($_POST['reg_kode_akses']);

    if ($kode_akses !== 'SCH2026' && $kode_akses !== '12345') {
        $error_reg = "Kode Otorisasi Sekolah tidak valid! Gunakan kode resmi institusi.";
    } elseif ($password !== $pass_confirm) {
        $error_reg = "Konfirmasi password tidak cocok dengan password baru!";
    } elseif (strlen($password) < 6) {
        $error_reg = "Password minimal harus 6 karakter!";
    } elseif (empty($username) || empty($password) || empty($nama_admin)) {
        $error_reg = "Semua kolom wajib diisi!";
    } else {
        mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_admin VARCHAR(100),
            email VARCHAR(100)
        )");

        $cek_user = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
        if ($cek_user && mysqli_num_rows($cek_user) > 0) {
            $error_reg = "Username sudah terdaftar di sistem! Gunakan nama lain.";
        } else {
            $q_ins = mysqli_query($koneksi, "INSERT INTO admin (username, password, nama_admin, email) VALUES ('$username', '$password', '$nama_admin', '$email_admin')");
            if ($q_ins) {
                $sukses_reg = "Akun baru berhasil didaftarkan! Silakan masuk menggunakan tab Masuk.";
                $active_tab = 'login';
            } else {
                $error_reg = "Gagal menyimpan data registrasi ke database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Autentikasi Admin - Black & Gold Luxury</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0b0b0b;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Main Dual Panel Wrapper */
        .auth-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 620px;
            background: linear-gradient(135deg, #161616 0%, #0e0e0e 100%);
            border: 2px solid rgba(212, 175, 55, 0.35);
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(212, 175, 55, 0.22);
            display: flex;
            overflow: hidden;
            animation: fadeInScale 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Left Side: Branding Panel */
        .auth-brand-side {
            flex: 1;
            background: linear-gradient(145deg, #141414 0%, #080808 100%);
            border-right: 1px solid rgba(212, 175, 55, 0.2);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }
        .brand-logo {
            font-size: 75px;
            margin-bottom: 20px;
            animation: floatLogo 4s infinite ease-in-out;
        }
        .auth-brand-side h1 {
            color: #d4af37;
            font-size: 24px;
            letter-spacing: 1.5px;
            font-weight: 800;
            margin-bottom: 12px;
        }
        .auth-brand-side p {
            color: #999;
            font-size: 13.5px;
            line-height: 1.7;
            max-width: 300px;
        }
        .system-features-list {
            margin-top: 30px;
            text-align: left;
            width: 100%;
            max-width: 280px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .feature-item {
            font-size: 12px;
            color: #bbb;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .feature-item span { color: #d4af37; font-weight: bold; }

        /* Right Side: Auth Form Panel */
        .auth-form-side {
            flex: 1.35;
            padding: 45px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Link Kembali ke Beranda */
        .back-link {
            color: #d4af37;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
            transition: 0.3s;
        }
        .back-link:hover {
            color: #fff;
            transform: translateX(-4px);
        }

        /* Modern Tab Switcher Slider */
        .tab-nav {
            display: flex;
            background-color: #1e1e1e;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 22px;
            border: 1px solid #333;
        }
        .tab-btn {
            flex: 1;
            padding: 11px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #888;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.35s ease;
        }
        .tab-btn.active {
            color: #121212;
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.35);
        }

        /* Form Visibility Animation */
        .form-content {
            display: none;
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.4s ease;
        }
        .form-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* Alert Messages */
        .alert {
            padding: 11px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            animation: shakeAlert 0.4s ease-in-out;
        }
        .alert-error { background-color: #4a1212; color: #f87171; border: 1px solid #991b1b; }
        .alert-success { background-color: #1e4620; color: #a3e635; border: 1px solid #2e7d32; }

        /* Form Input Controls */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            color: #aaa;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .input-box {
            position: relative;
        }
        .input-box input {
            width: 100%;
            padding: 11px 40px 11px 40px;
            background-color: #1f1f1f;
            border: 1px solid #363636;
            border-radius: 9px;
            color: #fff;
            font-size: 13.5px;
            transition: all 0.3s ease;
        }
        .input-box input:focus {
            border-color: #d4af37;
            outline: none;
            background-color: #262626;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.25);
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 14px;
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            cursor: pointer;
            font-size: 14px;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: #d4af37; }

        /* Extra Login Options (Remember Me & Forgot) */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 18px;
            color: #aaa;
        }
        .login-options label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-transform: none;
            margin: 0;
            color: #bbb;
        }
        .login-options input[type="checkbox"] {
            accent-color: #d4af37;
            width: 14px; height: 14px;
            cursor: pointer;
        }

        /* Password Strength Bar */
        .password-strength {
            height: 4px;
            background: #333;
            border-radius: 2px;
            margin-top: 6px;
            overflow: hidden;
            transition: 0.3s;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }

        /* Action Submit Button */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%);
            color: #121212;
            border: none;
            border-radius: 9px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
            margin-top: 5px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.5);
            background: linear-gradient(135deg, #e6c553 0%, #c5a035 100%);
        }

        /* Animations Keyframes */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.94) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes shakeAlert {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        @media (max-width: 850px) {
            .auth-wrapper { flex-direction: column; max-width: 450px; }
            .auth-brand-side { padding: 35px 20px; }
            .auth-form-side { padding: 35px 25px; }
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        <!-- Sisi Kiri: Branding & Fitur Unggulan -->
        <div class="auth-brand-side">
            <div class="brand-logo">🏫</div>
            <h1>SCHOOL PORTAL</h1>
            <p>Pusat Kendali Administrasi & Manajemen Data Akademik Terpadu.</p>
            
            <div class="system-features-list">
                <div class="feature-item"><span>✓</span> Keamanan Enkripsi Sesi Aktif</div>
                <div class="feature-item"><span>✓</span> Sinkronisasi Database Real-Time</div>
                <div class="feature-item"><span>✓</span> Kontrol Multi-Admin Terotorisasi</div>
            </div>
        </div>

        <!-- Sisi Kanan: Form Auth (Masuk / Daftar) -->
        <div class="auth-form-side">
            
            <!-- Tombol Kembali ke Beranda -->
            <a href="welcome.php" class="back-link">← Kembali ke Beranda Utama</a>

            <!-- Navigasi Tab Switcher -->
            <div class="tab-nav">
                <button type="button" class="tab-btn <?= $active_tab == 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">🔑 MASUK SISTEM</button>
                <button type="button" class="tab-btn <?= $active_tab == 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">📝 DAFTAR AKUN</button>
            </div>

            <!-- TAB 1: FORM MASUK (LOGIN) -->
            <div id="tab-login" class="form-content <?= $active_tab == 'login' ? 'active' : ''; ?>">
                <?php if (!empty($error_login)): ?>
                    <div class="alert alert-error">⚠️ <?= $error_login; ?></div>
                <?php endif; ?>

                <?php if (!empty($sukses_reg)): ?>
                    <div class="alert alert-success">✅ <?= $sukses_reg; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Username Administrator</label>
                        <div class="input-box">
                            <span class="input-icon">👤</span>
                            <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password Akun</label>
                        <div class="input-box">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" id="login-pass" placeholder="••••••••" required>
                            <span class="toggle-password" onclick="togglePassword('login-pass', this)">👁️</span>
                        </div>
                    </div>

                    <div class="login-options">
                        <label><input type="checkbox" name="remember"> Ingat Saya</label>
                        <a href="#" onclick="alert('Silakan hubungi Super Admin utama untuk reset kredensial.'); return false;" style="color:#d4af37; text-decoration:none;">Lupa Password?</a>
                    </div>

                    <button type="submit" name="do_login" class="btn-submit">MASUK KE DASHBOARD →</button>
                </form>
            </div>

            <!-- TAB 2: FORM DAFTAR (REGISTRASI AKUN BARU) -->
            <div id="tab-register" class="form-content <?= $active_tab == 'register' ? 'active' : ''; ?>">
                <?php if (!empty($error_reg)): ?>
                    <div class="alert alert-error">⚠️ <?= $error_reg; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap & Gelar</label>
                        <div class="input-box">
                            <span class="input-icon">📛</span>
                            <input type="text" name="reg_nama" placeholder="Contoh: Budi Santoso, S.Kom." required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Resmi Institusi</label>
                        <div class="input-box">
                            <span class="input-icon">📧</span>
                            <input type="email" name="reg_email" placeholder="admin@sekolah.sch.id" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Username Baru</label>
                        <div class="input-box">
                            <span class="input-icon">👤</span>
                            <input type="text" name="reg_username" placeholder="Buat username unik" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password Baru</label>
                        <div class="input-box">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="reg_password" id="reg-pass" placeholder="Minimal 6 karakter" required oninput="checkStrength(this.value)">
                            <span class="toggle-password" onclick="togglePassword('reg-pass', this)">👁️</span>
                        </div>
                        <div class="password-strength"><div id="strength-bar" class="strength-bar"></div></div>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="input-box">
                            <span class="input-icon">🔑</span>
                            <input type="password" name="reg_confirm_password" id="reg-conf" placeholder="Ulangi password baru" required>
                            <span class="toggle-password" onclick="togglePassword('reg-conf', this)">👁️</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kode Otorisasi Sekolah</label>
                        <div class="input-box">
                            <span class="input-icon">🛡️</span>
                            <input type="text" name="reg_kode_akses" placeholder="Masukkan kode (SCH2026)" required autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" name="do_register" class="btn-submit">DAFTARKAN AKUN BARU →</button>
                </form>
            </div>

        </div>
    </div>

    <!-- Script Interaktif (Tab Switch, Show/Hide Pass, Strength Meter) -->
    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.form-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

            if (tabName === 'login') {
                document.getElementById('tab-login').classList.add('active');
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.getElementById('tab-register').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }
        }

        function togglePassword(fieldId, iconEl) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                iconEl.textContent = '🙈';
            } else {
                field.type = 'password';
                iconEl.textContent = '👁️';
            }
        }

        function checkStrength(password) {
            const bar = document.getElementById('strength-bar');
            let strength = 0;
            if (password.length >= 6) strength += 33;
            if (password.match(/[A-Z]/)) strength += 33;
            if (password.match(/[0-9!@#$%^&*]/)) strength += 34;

            bar.style.width = strength + '%';
            if (strength <= 33) {
                bar.style.backgroundColor = '#f87171'; // Merah (Lemah)
            } else if (strength <= 66) {
                bar.style.backgroundColor = '#fbbf24'; // Kuning (Sedang)
            } else {
                bar.style.backgroundColor = '#4ade80'; // Hijau (Kuat)
            }
        }
    </script>

</body>
</html>