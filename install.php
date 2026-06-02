<?php
/**
 * Mnemo Installation Wizard
 * Single-file installer - No dependencies required
 * Delete this file after installation is complete
 */

const MIN_PHP_VERSION = '8.3';

$requiredExtensions = ['curl', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'gd', 'zip'];

set_error_handler(function ($level, $message, $file = 'unknown', $line = 0) {
    http_response_code(500);
    exit(json_encode(['error' => "Error: {$message} ({$file}:{$line})"]));
});

// ===== Helper Functions =====

function sendJson($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    exit(json_encode($data));
}

function getInput($key, $default = null) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    return $data[$key] ?? $_GET[$key] ?? $default;
}

function isInstalled() {
    return file_exists('.env') && file_exists('vendor/autoload.php');
}

function checkPrerequisites() {
    $checks = [
        'php_version' => [
            'name' => 'PHP ' . MIN_PHP_VERSION . '+',
            'passed' => version_compare(PHP_VERSION, MIN_PHP_VERSION, '>='),
            'value' => PHP_VERSION,
        ],
    ];

    foreach ($requiredExtensions as $ext) {
        $checks["ext_$ext"] = [
            'name' => "Extension: $ext",
            'passed' => extension_loaded($ext),
        ];
    }

    $checks['writable_storage'] = [
        'name' => 'Writable: storage/',
        'passed' => is_writable('storage'),
    ];

    $checks['writable_bootstrap'] = [
        'name' => 'Writable: bootstrap/',
        'passed' => is_writable('bootstrap'),
    ];

    return $checks;
}

function runComposer() {
    if (file_exists('vendor/autoload.php')) {
        return ['success' => true, 'message' => 'Dependencies already installed'];
    }

    $output = [];
    $return = 0;

    exec('composer install 2>&1', $output, $return);

    if ($return !== 0) {
        return ['success' => false, 'error' => implode("\n", $output)];
    }

    return ['success' => true, 'message' => 'Dependencies installed successfully'];
}

function setupEnv() {
    if (file_exists('.env')) {
        return ['success' => true];
    }

    if (!copy('.env.example', '.env')) {
        return ['success' => false, 'error' => 'Failed to create .env file'];
    }

    return ['success' => true];
}

function generateKey() {
    require 'vendor/autoload.php';

    try {
        $app = require 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->call('key:generate', ['--force' => true]);
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function runMigrations() {
    require 'vendor/autoload.php';

    try {
        $app = require 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->call('migrate', ['--force' => true]);
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function createAdmin($name, $email, $password) {
    require 'vendor/autoload.php';

    try {
        $app = require 'bootstrap/app.php';

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $dbPath = __DIR__ . '/database/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $now = date('Y-m-d H:i:s');
        $stmt->execute([$name, $email, $hashedPassword, $now, $now, $now]);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function deleteInstaller() {
    return unlink(__FILE__);
}

// ===== API Endpoints =====

$action = getInput('action');

if ($action === 'check') {
    sendJson(['checks' => checkPrerequisites()]);
}

if ($action === 'install') {
    $step = getInput('step');

    switch ($step) {
        case 'composer':
            sendJson(runComposer());
        case 'env':
            sendJson(setupEnv());
        case 'key':
            sendJson(generateKey());
        case 'migrate':
            sendJson(runMigrations());
        case 'admin':
            $name = getInput('name');
            $email = getInput('email');
            $password = getInput('password');

            if (!$name || !$email || !$password) {
                sendJson(['success' => false, 'error' => 'Missing fields'], 400);
            }

            sendJson(createAdmin($name, $email, $password));
        case 'complete':
            if (deleteInstaller()) {
                sendJson(['success' => true, 'message' => 'Installation complete']);
            } else {
                sendJson(['success' => false, 'error' => 'Could not delete installer']);
            }
        default:
            sendJson(['error' => 'Unknown step'], 400);
    }
}

// ===== HTML UI =====

if (isInstalled()) {
    http_response_code(200);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mnemo - Already Installed</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0f172a; color: #e5e7eb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .container { background: #1a1a1a; border: 1px solid #374151; border-radius: 12px; padding: 3rem; max-width: 500px; text-align: center; }
            h1 { color: #10b981; }
            a { color: #6366f1; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>✓ Mnemo is Already Installed</h1>
            <p>This installer file can be safely deleted.</p>
            <p><a href="/">Go to Mnemo</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mnemo Installation</title>
    <style>
        :root {
            --accent: #6366f1;
            --card-bg: #1a1a1a;
            --text-primary: #e5e7eb;
            --text-muted: #9ca3af;
            --card-border: #374151;
            --success: #10b981;
            --error: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 2rem;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .step {
            display: none;
            animation: slideIn 0.3s ease;
        }

        .step.active {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .progress {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .progress-bar {
            flex: 1;
            height: 4px;
            background: var(--card-border);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar.active {
            background: var(--accent);
        }

        .progress-bar.done {
            background: var(--success);
        }

        .check-list {
            margin-bottom: 2rem;
        }

        .check-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            justify-content: space-between;
        }

        .check-item.pass {
            border-color: rgba(16, 185, 129, 0.3);
        }

        .check-item.fail {
            border-color: rgba(239, 68, 68, 0.3);
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .check-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .check-item.pass .check-icon {
            color: var(--success);
        }

        .check-item.fail .check-icon {
            color: var(--error);
        }

        .check-status {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(99, 102, 241, 0.1);
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .message.show {
            display: block;
        }

        .message.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #a7f3d0;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        button {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--accent);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .step-number {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📚 Mnemo</div>
            <h1>Installation</h1>
            <p class="subtitle">Welcome to Mnemo! Let's get you set up.</p>
        </div>

        <div class="progress">
            <div class="progress-bar active" id="progress-1"></div>
            <div class="progress-bar" id="progress-2"></div>
            <div class="progress-bar" id="progress-3"></div>
            <div class="progress-bar" id="progress-4"></div>
            <div class="progress-bar" id="progress-5"></div>
        </div>

        <!-- Step 1: Prerequisites -->
        <div class="step active" id="step-1">
            <h2>System Requirements</h2>
            <div class="check-list" id="checks-list"></div>
            <div class="message" id="message-1"></div>
            <div class="buttons">
                <button class="btn-primary" onclick="checkRequirements()">Check Requirements</button>
            </div>
        </div>

        <!-- Step 2: Dependencies -->
        <div class="step" id="step-2">
            <h2>Installing Dependencies</h2>
            <div class="message success show">
                <strong>Installing composer packages...</strong><br>
                This may take a few minutes
            </div>
            <div id="composer-progress"></div>
            <div class="step-number">Step 2/5</div>
        </div>

        <!-- Step 3: Configuration -->
        <div class="step" id="step-3">
            <h2>Configuring Application</h2>
            <div id="config-progress"></div>
            <div class="step-number">Step 3/5</div>
        </div>

        <!-- Step 4: Admin Account -->
        <div class="step" id="step-4">
            <h2>Create Admin Account</h2>
            <form onsubmit="createAdmin(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="admin-name" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="admin-email" placeholder="admin@mnemo.local" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="admin-password" placeholder="••••••••" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="admin-password-confirm" placeholder="••••••••" required minlength="8">
                </div>
                <div class="message" id="message-4"></div>
                <div class="buttons">
                    <button type="submit" class="btn-primary">Create Account</button>
                </div>
            </form>
            <div class="step-number">Step 4/5</div>
        </div>

        <!-- Step 5: Complete -->
        <div class="step" id="step-5">
            <div style="text-align: center; padding: 2rem 0;">
                <div style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;">✓</div>
                <h2>Installation Complete!</h2>
                <p style="color: var(--text-muted); margin-top: 1rem;">Your Mnemo instance is ready to use.</p>
            </div>
            <div class="buttons">
                <button class="btn-primary" onclick="goToApp()">Go to Mnemo</button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let installationSteps = ['composer', 'env', 'key', 'migrate'];
        let currentInstallStep = 0;

        function updateProgress() {
            for (let i = 1; i <= 5; i++) {
                const bar = document.getElementById(`progress-${i}`);
                if (i < currentStep) {
                    bar.className = 'progress-bar done';
                } else if (i === currentStep) {
                    bar.className = 'progress-bar active';
                } else {
                    bar.className = 'progress-bar';
                }
            }
        }

        function goToStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(`step-${step}`).classList.add('active');
            currentStep = step;
            updateProgress();
        }

        function showMessage(stepId, text, type = 'error') {
            const msg = document.getElementById(`message-${stepId}`);
            msg.className = `message show ${type}`;
            msg.innerHTML = text;
        }

        async function checkRequirements() {
            try {
                const res = await fetch('?action=check');
                const data = await res.json();

                const list = document.getElementById('checks-list');
                let allPass = true;

                list.innerHTML = '';
                for (const [key, check] of Object.entries(data.checks)) {
                    const div = document.createElement('div');
                    div.className = `check-item ${check.passed ? 'pass' : 'fail'}`;
                    div.innerHTML = `
                        <div class="check-label">
                            <div class="check-icon">${check.passed ? '✓' : '✗'}</div>
                            <span>${check.name}</span>
                        </div>
                        <div class="check-status">${check.value || (check.passed ? 'OK' : 'FAIL')}</div>
                    `;
                    list.appendChild(div);
                    if (!check.passed) allPass = false;
                }

                if (allPass) {
                    showMessage(1, '✓ All requirements met! Starting installation...', 'success');
                    setTimeout(() => startInstallation(), 1500);
                } else {
                    showMessage(1, '✗ Some requirements are not met. Please check your server setup.', 'error');
                }
            } catch (e) {
                showMessage(1, 'Error: ' + e.message, 'error');
            }
        }

        async function startInstallation() {
            goToStep(2);
            await runInstallationStep();
        }

        async function runInstallationStep() {
            if (currentInstallStep >= installationSteps.length) {
                goToStep(4);
                return;
            }

            const step = installationSteps[currentInstallStep];
            const progress = document.getElementById('composer-progress');

            progress.innerHTML = `<div style="padding: 1rem 0;"><div class="loading"></div> Running: ${step}...</div>`;

            try {
                const res = await fetch('?action=install', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ step })
                });

                const data = await res.json();

                if (data.success) {
                    progress.innerHTML += `<div style="color: var(--success);">✓ ${step} completed</div>`;
                    currentInstallStep++;

                    if (currentInstallStep >= installationSteps.length) {
                        goToStep(3);
                        setTimeout(() => goToStep(4), 1500);
                    } else {
                        setTimeout(runInstallationStep, 500);
                    }
                } else {
                    progress.innerHTML = `<div style="color: var(--error);">Error: ${data.error}</div>`;
                }
            } catch (e) {
                progress.innerHTML = `<div style="color: var(--error);">Error: ${e.message}</div>`;
            }
        }

        function createAdmin(e) {
            e.preventDefault();

            const name = document.getElementById('admin-name').value;
            const email = document.getElementById('admin-email').value;
            const password = document.getElementById('admin-password').value;
            const confirm = document.getElementById('admin-password-confirm').value;

            if (password !== confirm) {
                showMessage(4, 'Passwords do not match', 'error');
                return;
            }

            fetch('?action=install', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 'admin', name, email, password })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    fetch('?action=install', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ step: 'complete' })
                    })
                    .then(() => goToStep(5))
                    .catch(e => showMessage(4, e.message, 'error'));
                } else {
                    showMessage(4, data.error || 'Failed to create admin', 'error');
                }
            })
            .catch(e => showMessage(4, e.message, 'error'));
        }

        function goToApp() {
            window.location.href = '/';
        }
    </script>
</body>
</html>
