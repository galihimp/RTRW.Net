<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINET BILLING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e3f2fd, #bbdefb);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .launcher-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .launcher-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 480px;
            text-align: center;
        }
        .launcher-logo {
            font-size: 3.5rem;
            color: #1976d2;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 8px;
        }
        .text-muted {
            color: #607d8b !important;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .progress-container {
            margin: 20px 0;
        }
        .progress {
            height: 6px;
            background-color: #e3f2fd;
            border-radius: 3px;
        }
        .progress-bar {
            background-color: #1976d2;
        }
        .status-text {
            font-size: 14px;
            margin: 20px 0;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #546e7a;
        }
        .spinner {
            margin-right: 10px;
        }
        .btn-launch {
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 30px;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            max-width: 220px;
            margin: 10px auto;
            transition: background-color 0.3s ease-in-out;
        }
        .btn-launch:hover {
            background-color: #1565c0;
        }
        .version-info {
            margin-top: 25px;
            padding: 15px;
            background: #f1f8ff;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #455a64;
            text-align: left;
        }
        .fa-check-circle {
            color: #26a69a;
        }
        .fa-exclamation-triangle {
            color: #e53935;
        }
        .fa-wifi {
            color: #1e88e5;
        }
    </style>
</head>
<body>
    <div class="launcher-container">
        <div class="launcher-card">
            <div class="launcher-logo">
                <i class="fas fa-network-wired"></i>
            </div>
            <h2>Kinet Billing</h2>
            <p class="text-muted">Memulai aplikasi dan memeriksa update...</p>

            <div class="progress-container">
                <div class="progress" style="display: none;" id="progress-bar">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <div class="status-text" id="status-text">
                <div class="spinner-border spinner-border-sm spinner" role="status"></div>
                Memeriksa update...
            </div>

            <button class="btn btn-launch" id="launch-btn" onclick="launchApp()" style="display: none;">
                <i class="fas fa-sign-in-alt me-2"></i>
                Masuk ke Aplikasi
            </button>

            <div class="version-info" id="version-info" style="display: none;"></div>
        </div>
    </div>

    <script>
        function updateStatus(message, showSpinner = true) {
            const statusEl = document.getElementById('status-text');
            statusEl.innerHTML = showSpinner
                ? `<div class="spinner-border spinner-border-sm spinner" role="status"></div>${message}`
                : message;
        }

        function updateProgress(percent) {
            const progressBar = document.getElementById('progress-bar');
            const progressFill = progressBar.querySelector('.progress-bar');

            if (percent > 0) {
                progressBar.style.display = 'block';
                progressFill.style.width = percent + '%';
            } else {
                progressBar.style.display = 'none';
            }
        }

        function showLaunchButton(versionInfo = '') {
            document.getElementById('launch-btn').style.display = 'inline-block';
            if (versionInfo) {
                const versionEl = document.getElementById('version-info');
                versionEl.innerHTML = versionInfo;
                versionEl.style.display = 'block';
            }
        }

        async function checkForUpdates() {
            try {
                const response = await fetch('launcher.php?action=check');
                const data = await response.json();

                const versionInfo = `<strong>Versi Lokal:</strong> ${data.local_version}<br><strong>Versi Server:</strong> ${data.server_version || 'Tidak tersedia'}`;

                if (data.update_available) {
                    updateStatus('Update tersedia! Mendownload file...', true);
                    updateProgress(10);

                    setTimeout(() => {
                        performPatch();
                    }, 1000);
                } else {
                    updateStatus('<i class="fas fa-check-circle me-2"></i>Aplikasi sudah versi terbaru', false);
                    showLaunchButton(versionInfo);
                }
            } catch (error) {
                console.error('Error checking updates:', error);
                updateStatus('<i class="fas fa-wifi me-2"></i>Tidak dapat memeriksa update, melanjutkan...', false);
                showLaunchButton('<strong>Status:</strong> Offline Mode');
            }
        }

        async function performPatch() {
            try {
                updateProgress(30);
                updateStatus('Mendownload file langsung dari server...', true);

                const response = await fetch('launcher.php?action=patch');
                const result = await response.json();

                updateProgress(90);

                if (result.status === 'success') {
                    updateStatus('<i class="fas fa-check-circle me-2"></i>Update berhasil! ' + result.message, false);
                    updateProgress(100);

                    setTimeout(() => {
                        showLaunchButton(`<strong>Update berhasil!</strong><br>File diupdate: ${result.updated_files}/${result.total_files}`);
                    }, 1000);
                } else {
                    updateStatus('<i class="fas fa-exclamation-triangle me-2"></i>' + result.message, false);
                    showLaunchButton('<strong>Status:</strong> Update gagal, melanjutkan dengan versi lama');
                }
            } catch (error) {
                console.error('Error during patch:', error);
                updateStatus('<i class="fas fa-exclamation-triangle me-2"></i>Gagal melakukan update', false);
                showLaunchButton('<strong>Status:</strong> Update gagal, melanjutkan dengan versi lama');
            }
        }

        function launchApp() {
            updateStatus('Membuka aplikasi...', true);
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(checkForUpdates, 1000);
        });
    </script>
</body>
</html>
