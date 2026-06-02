<?php
/**
 * Mnemo - Standalone Installer
 * No dependencies. No commands. Just open and install.
 * Based on Azuriom's simplicity approach.
 */

const VERSION = '1.0.0';
const MIN_PHP = '8.3.0';

set_time_limit(300);
ini_set('max_execution_time', 300);

// Check if already installed
if (file_exists('.env') && file_exists('vendor/autoload.php') && file_exists('database/database.sqlite')) {
    header('Location: /');
    exit;
}

// Helper: Send JSON response
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    exit(json_encode($data));
}

// Helper: Get input
function get_input($key) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    return $data[$key] ?? null;
}

// API Routes
$action = get_input('action') ?? ($_GET['action'] ?? null);

if ($action === 'check') {
    $checks = [
        'php_version' => [
            'name' => 'PHP 8.3+',
            'passed' => version_compare(PHP_VERSION, MIN_PHP, '>='),
            'value' => PHP_VERSION,
        ],
        'gd' => ['name' => 'GD Extension', 'passed' => extension_loaded('gd')],
        'curl' => ['name' => 'cURL Extension', 'passed' => extension_loaded('curl')],
        'json' => ['name' => 'JSON Extension', 'passed' => extension_loaded('json')],
        'pdo' => ['name' => 'PDO Extension', 'passed' => extension_loaded('pdo')],
        'zip' => ['name' => 'ZIP Extension', 'passed' => extension_loaded('zip')],
        'mbstring' => ['name' => 'mbstring Extension', 'passed' => extension_loaded('mbstring')],
        'openssl' => ['name' => 'OpenSSL Extension', 'passed' => extension_loaded('openssl')],
        'storage' => ['name' => 'storage/ writable', 'passed' => is_writable('storage')],
        'bootstrap' => ['name' => 'bootstrap/ writable', 'passed' => is_writable('bootstrap')],
    ];

    $allPassed = array_reduce($checks, fn($c, $check) => $c && $check['passed'], true);
    json_response(['checks' => $checks, 'all_passed' => $allPassed]);
}

if ($action === 'install') {
    $step = get_input('step');

    if ($step === 'composer') {
        // Download and run composer
        if (!file_exists('composer.phar')) {
            if (!function_exists('curl_init')) {
                json_response(['success' => false, 'error' => 'cURL not available'], 400);
            }

            $ch = curl_init('https://getcomposer.org/installer');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $installer = curl_exec($ch);

            if (!$installer) {
                json_response(['success' => false, 'error' => 'Failed to download composer'], 400);
            }

            file_put_contents('composer_installer.php', $installer);

            // Run installer
            ob_start();
            system('php composer_installer.php --quiet 2>&1', $ret);
            $output = ob_get_clean();

            @unlink('composer_installer.php');

            if ($ret !== 0 && !file_exists('composer.phar')) {
                json_response(['success' => false, 'error' => 'Failed to install composer: ' . $output], 400);
            }
        }

        // Install dependencies
        $composer = file_exists('composer.phar') ? 'php composer.phar' : 'composer';
        ob_start();
        system($composer . ' install --no-dev --optimize-autoloader 2>&1', $ret);
        $output = ob_get_clean();

        if ($ret !== 0) {
            json_response(['success' => false, 'error' => 'Composer install failed: ' . $output], 400);
        }

        json_response(['success' => true]);
    }

    if ($step === 'env') {
        if (!file_exists('.env')) {
            if (!copy('.env.example', '.env')) {
                json_response(['success' => false, 'error' => 'Failed to create .env'], 400);
            }
        }
        json_response(['success' => true]);
    }

    if ($step === 'key') {
        require 'vendor/autoload.php';
        try {
            $app = require 'bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->call('key:generate', ['--force' => true]);
            json_response(['success' => true]);
        } catch (Exception $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    if ($step === 'migrate') {
        require 'vendor/autoload.php';
        try {
            $app = require 'bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->call('migrate', ['--force' => true]);
            json_response(['success' => true]);
        } catch (Exception $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    if ($step === 'admin') {
        $name = get_input('name');
        $email = get_input('email');
        $password = get_input('password');

        if (!$name || !$email || !$password) {
            json_response(['success' => false, 'error' => 'Missing fields'], 400);
        }

        try {
            $dbPath = realpath('database/database.sqlite');
            if (!$dbPath) {
                $dbPath = 'database/database.sqlite';
            }
            $pdo = new PDO('sqlite:' . $dbPath);

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $now = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare('
                INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$name, $email, $hashedPassword, $now, $now, $now]);

            json_response(['success' => true]);
        } catch (Exception $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    if ($step === 'cleanup') {
        // Remove this installer
        @unlink(__FILE__);
        json_response(['success' => true]);
    }
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
            --bg: #0f172a;
            --card: #1a1a1a;
            --border: #374151;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --success: #10b981;
            --error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg) 0%, #1e293b 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .progress-item {
            flex: 1;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-item.active {
            background: var(--accent);
        }

        .progress-item.done {
            background: var(--success);
        }

        .checks {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .check {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 8px;
            justify-content: space-between;
        }

        .check.pass {
            border-color: rgba(16, 185, 129, 0.3);
        }

        .check.fail {
            border-color: rgba(239, 68, 68, 0.3);
        }

        .check-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .check-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .check.pass .check-icon { color: var(--success); }
        .check.fail .check-icon { color: var(--error); }

        .check-text {
            display: flex;
            flex-direction: column;
        }

        .check-name {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .check-value {
            font-size: 0.8rem;
            color: var(--muted);
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
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
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
            animation: slideIn 0.3s ease;
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

        .button {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .button:hover:not(:disabled) {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(99, 102, 241, 0.3);
        }

        .button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .success-icon {
            font-size: 3rem;
            color: var(--success);
            text-align: center;
            margin: 2rem 0;
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .step-info {
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📚 Mnemo</div>
            <h1>Installation Wizard</h1>
            <p class="subtitle">Welcome! Let's get Mnemo ready.</p>
        </div>

        <div class="progress">
            <div class="progress-item active" id="p-1"></div>
            <div class="progress-item" id="p-2"></div>
            <div class="progress-item" id="p-3"></div>
            <div class="progress-item" id="p-4"></div>
            <div class="progress-item" id="p-5"></div>
        </div>

        <!-- Step 1: Check -->
        <div class="step active" id="step-1">
            <h2>System Requirements</h2>
            <div class="checks" id="checks-list"></div>
            <div class="message" id="msg-1"></div>
            <button class="button" onclick="checkRequirements()">Check & Continue</button>
            <div class="step-info">Step 1 of 5</div>
        </div>

        <!-- Step 2: Install -->
        <div class="step" id="step-2">
            <h2>Installing...</h2>
            <div id="install-progress"></div>
            <div class="step-info">Step 2 of 5 - This may take a minute</div>
        </div>

        <!-- Step 3: Admin -->
        <div class="step" id="step-3">
            <h2>Create Admin Account</h2>
            <form onsubmit="createAdmin(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="name" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="admin@mnemo.local" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" placeholder="••••••••" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="password_confirm" placeholder="••••••••" required minlength="8">
                </div>
                <div class="message" id="msg-3"></div>
                <button type="submit" class="button">Create Account</button>
            </form>
            <div class="step-info">Step 3 of 5</div>
        </div>

        <!-- Step 4: Complete -->
        <div class="step" id="step-4">
            <div class="success-icon">✓</div>
            <h2 style="text-align: center; margin-bottom: 1rem;">Installation Complete!</h2>
            <p style="text-align: center; color: var(--muted); margin-bottom: 1.5rem;">Your Mnemo instance is ready to use.</p>
            <button class="button" onclick="window.location='/'">Open Mnemo</button>
            <div class="step-info">Step 4 of 5</div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const steps = ['composer', 'env', 'key', 'migrate'];
        let stepIndex = 0;

        function updateProgress() {
            for (let i = 1; i <= 5; i++) {
                const el = document.getElementById(`p-${i}`);
                if (i < currentStep) {
                    el.className = 'progress-item done';
                } else if (i === currentStep) {
                    el.className = 'progress-item active';
                } else {
                    el.className = 'progress-item';
                }
            }
        }

        function goToStep(n) {
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            document.getElementById(`step-${n}`).classList.add('active');
            currentStep = n;
            updateProgress();
            window.scrollTo(0, 0);
        }

        function showMsg(stepId, text, type = 'error') {
            const el = document.getElementById(`msg-${stepId}`);
            el.className = `message show ${type}`;
            el.innerHTML = text;
        }

        async function checkRequirements() {
            const res = await fetch('?action=check');
            const data = await res.json();

            const list = document.getElementById('checks-list');
            list.innerHTML = '';

            for (const [key, check] of Object.entries(data.checks)) {
                const div = document.createElement('div');
                div.className = `check ${check.passed ? 'pass' : 'fail'}`;
                div.innerHTML = `
                    <div class="check-left">
                        <div class="check-icon">${check.passed ? '✓' : '✗'}</div>
                        <div class="check-text">
                            <div class="check-name">${check.name}</div>
                            ${check.value ? `<div class="check-value">${check.value}</div>` : ''}
                        </div>
                    </div>
                `;
                list.appendChild(div);
            }

            if (data.all_passed) {
                showMsg(1, '✓ All requirements met! Starting installation...', 'success');
                setTimeout(startInstall, 1500);
            } else {
                showMsg(1, '✗ Some requirements not met. Check your server setup.', 'error');
            }
        }

        async function startInstall() {
            goToStep(2);
            await runStep();
        }

        async function runStep() {
            if (stepIndex >= steps.length) {
                goToStep(3);
                return;
            }

            const step = steps[stepIndex];
            const progress = document.getElementById('install-progress');

            progress.innerHTML += `<p><span class="loading"></span>${step}...</p>`;

            try {
                const res = await fetch('?action=install', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ step })
                });

                const data = await res.json();

                if (data.success) {
                    progress.innerHTML = progress.innerHTML.replace('<span class="loading"></span>', '✓ ');
                    stepIndex++;
                    setTimeout(runStep, 500);
                } else {
                    progress.innerHTML += `<p style="color: var(--error);">✗ Error: ${data.error}</p>`;
                }
            } catch (e) {
                progress.innerHTML += `<p style="color: var(--error);">✗ ${e.message}</p>`;
            }
        }

        function createAdmin(e) {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;

            if (password !== confirm) {
                showMsg(3, 'Passwords do not match', 'error');
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
                        body: JSON.stringify({ step: 'cleanup' })
                    }).then(() => goToStep(4));
                } else {
                    showMsg(3, data.error || 'Failed to create account', 'error');
                }
            })
            .catch(e => showMsg(3, e.message, 'error'));
        }
    </script>
</body>
</html>
