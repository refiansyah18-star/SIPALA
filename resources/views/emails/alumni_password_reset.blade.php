<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi Alumni — SIPALA</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f7f6f0; color: #222222; }
        .outer-wrapper { background-color: #f7f6f0; padding: 30px 15px; }
        .container { max-width: 620px; margin: 0 auto; background-color: #f7f6f0; }
        .institution-name { font-size: 12px; color: #737373; text-align: left; margin-bottom: 8px; }
        .header-title { font-size: 22px; font-weight: 700; color: #1e6b47; margin: 0 0 4px 0; text-align: center; }
        .header-subtitle { font-size: 14px; font-weight: 600; color: #4b5563; margin: 0 0 18px 0; text-align: center; }
        .card-box { background-color: #ffffff; border: 1px solid #dcd7c9; border-radius: 6px; padding: 32px 36px; }
        .greeting { font-size: 16px; font-weight: 700; color: #111111; margin-bottom: 14px; }
        .intro-text { font-size: 14px; line-height: 1.6; color: #333333; margin-bottom: 18px; }
        .btn-cta { display: inline-block; padding: 12px 24px; background-color: #0a5e3d; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 700; margin-bottom: 18px; }
        .note-text { font-size: 12.5px; line-height: 1.5; color: #666666; border-top: 1px solid #ece8dc; padding-top: 14px; margin-top: 8px; word-break: break-all; }
        .footer-section { padding: 20px 10px; text-align: center; font-size: 12px; color: #888888; }
    </style>
</head>
<body>
    <div class="outer-wrapper">
        <div class="container">
            <div class="institution-name">UIN Siber Syekh Nurjati Cirebon</div>
            <div class="header-title">SIPALA</div>
            <div class="header-subtitle">Sistem Pengaduan Layanan Akademik</div>

            <div class="card-box">
                <div class="greeting">Halo, {{ strtoupper($user->nama) }}!</div>
                <div class="intro-text">
                    Kami menerima permintaan untuk mengatur ulang kata sandi akun <strong>Alumni SIPALA</strong> Anda ({{ $user->email }}). Klik tombol di bawah ini untuk membuat kata sandi baru:
                </div>

                <a href="{{ $resetUrl }}" class="btn-cta">Atur Ulang Kata Sandi</a>

                <div class="intro-text" style="margin-bottom: 8px;">
                    Tautan ini hanya berlaku selama <strong>60 menit</strong>. Jika Anda tidak merasa meminta pengaturan ulang kata sandi, abaikan email ini.
                </div>

                <div class="note-text">
                    Jika tombol tidak dapat diklik, salin tautan berikut ke peramban Anda:<br>
                    {{ $resetUrl }}
                </div>
            </div>

            <div class="footer-section">
                SIPALA &bull; Sistem Pengaduan Layanan Akademik<br>
                UIN Siber Syekh Nurjati Cirebon (UINSSC)
            </div>
        </div>
    </div>
</body>
</html>

