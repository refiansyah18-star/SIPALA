<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket: {{ $ticket->ticket_number }} telah diverifikasi</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f7f6f0;
            color: #222222;
            -webkit-font-smoothing: antialiased;
        }
        .outer-wrapper {
            background-color: #f7f6f0;
            padding: 30px 15px;
        }
        .container {
            max-width: 620px;
            margin: 0 auto;
            background-color: #f7f6f0;
        }
        .institution-name {
            font-size: 12px;
            color: #737373;
            letter-spacing: 0.3px;
            text-align: left;
            margin-bottom: 8px;
        }
        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e6b47;
            margin: 0 0 4px 0;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
            margin: 0 0 18px 0;
            text-align: center;
            letter-spacing: 0.2px;
        }
        .card-box {
            background-color: #ffffff;
            border: 1px solid #dcd7c9;
            border-radius: 4px;
            padding: 32px 36px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 14px;
        }
        .intro-text {
            font-size: 14px;
            line-height: 1.5;
            color: #333333;
            margin-bottom: 16px;
        }
        .status-box {
            font-size: 14px;
            line-height: 1.5;
            color: #0f766e;
            margin-bottom: 24px;
            background-color: #f0fdfa;
            padding: 12px 16px;
            border-radius: 4px;
            border-left: 3px solid #0d9488;
        }
        .details-list {
            font-size: 14px;
            line-height: 1.7;
            color: #333333;
        }
        .detail-row {
            margin-bottom: 4px;
        }
        .detail-label {
            font-weight: 700;
            color: #111111;
        }
        .footer-section {
            padding: 20px 10px;
            text-align: center;
            font-size: 12px;
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="outer-wrapper">
        <div class="container">
            <div class="institution-name">
                UIN Siber Syekh Nurjati Cirebon
            </div>

            <div class="header-title">
                SIPALA
            </div>
            <div class="header-subtitle">
                Sistem Pengaduan Layanan Akademik
            </div>

            <div class="card-box">
                <div class="greeting">
                    Halo, {{ strtoupper($user->nama) }}!
                </div>

                <div class="intro-text">
                    Tiket pengaduan layanan akademik Anda telah <strong>diverifikasi</strong> oleh Admin SIPALA dan saat ini berstatus <strong>DIVERIFIKASI</strong>.
                </div>

                <div class="status-box">
                    <strong>Keterangan Verifikasi:</strong><br>
                    {{ $note }}
                </div>

                <div class="details-list">
                    <div class="detail-row">
                        <span class="detail-label">Nomor tiket:</span> {{ $ticket->ticket_number }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Judul:</span> {{ $ticket->subject }}
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Kategori:</span> {{ $ticket->category->name }} &rsaquo; {{ $service->name }}
                    </div>
                    @if($user->programStudy)
                    <div class="detail-row">
                        <span class="detail-label">Fakultas/Jurusan:</span> {{ $user->programStudy->name }}
                    </div>
                    @elseif($ticket->programStudy)
                    <div class="detail-row">
                        <span class="detail-label">Fakultas/Jurusan:</span> {{ $ticket->programStudy->name }}
                    </div>
                    @endif
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

