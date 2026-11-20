<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Alumni Ditolak — SIPALA</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f7f6f0; color: #222222; }
        .outer-wrapper { background-color: #f7f6f0; padding: 30px 15px; }
        .container { max-width: 620px; margin: 0 auto; background-color: #f7f6f0; }
        .institution-name { font-size: 12px; color: #737373; text-align: left; margin-bottom: 8px; }
        .header-title { font-size: 22px; font-weight: 700; color: #1e6b47; margin: 0 0 4px 0; text-align: center; }
        .header-subtitle { font-size: 14px; font-weight: 600; color: #4b5563; margin: 0 0 18px 0; text-align: center; }
        .card-box { background-color: #ffffff; border: 1px solid #dcd7c9; border-radius: 6px; padding: 32px 36px; }
        .greeting { font-size: 16px; font-weight: 700; color: #111111; margin-bottom: 14px; }
        .intro-text { font-size: 14px; line-height: 1.6; color: #333333; margin-bottom: 16px; }
        .status-box { font-size: 14px; line-height: 1.5; color: #972a22; background-color: #fbe2df; padding: 12px 16px; border-radius: 4px; border-left: 3px solid #d8463b; margin-bottom: 22px; }
        .details-list { font-size: 14px; line-height: 1.7; color: #333333; margin-bottom: 22px; }
        .detail-row { margin-bottom: 4px; }
        .detail-label { font-weight: 700; color: #111111; }
        .btn-cta { display: inline-block; padding: 11px 22px; background-color: #0a5e3d; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 700; }
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
                <div class="greeting">Halo, {{ strtoupper($registration->nama) }}!</div>
                <div class="intro-text">
                    Mohon maaf, pengajuan pendaftaran akun <strong>Alumni SIPALA</strong> Anda belum dapat disetujui.
                </div>

                <div class="status-box">
                    <strong>Alasan Penolakan dari Admin:</strong><br>
                    {{ $registration->rejection_reason }}
                </div>

                <div class="details-list">
                    <div class="detail-row"><span class="detail-label">NIM:</span> {{ $registration->nim }}</div>
                    <div class="detail-row"><span class="detail-label">Email:</span> {{ $registration->email }}</div>
                </div>

                <div class="intro-text">
                    Anda dapat mengajukan pendaftaran ulang setelah melengkapi atau memperbaiki data/dokumen sesuai catatan di atas.
                </div>

                <a href="{{ $registerUrl }}" class="btn-cta">Daftar Ulang Akun Alumni</a>
            </div>

            <div class="footer-section">
                SIPALA &bull; Sistem Pengaduan Layanan Akademik<br>
                UIN Siber Syekh Nurjati Cirebon (UINSSC)
            </div>
        </div>
    </div>
</body>
</html>

