<?php
/**
 * Mnemo - Installateur autonome
 * Aucune dependance requise. Ouvrez dans le navigateur et c'est parti.
 */

const VERSION = '1.0.0';
const MIN_PHP = '8.3.0';

set_time_limit(300);
ini_set('max_execution_time', 300);

// Racine du projet Laravel (un niveau au-dessus de public/)
define('ROOT', rtrim(realpath(__DIR__ . '/..'), '/'));

// Deja installe
if (file_exists(ROOT . '/.env') && file_exists(ROOT . '/vendor/autoload.php')) {
    $env = parse_ini_file(ROOT . '/.env');
    if (!empty($env['APP_KEY'])) {
        header('Location: /');
        exit;
    }
}

// --- Helper : reponse JSON ---
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    exit(json_encode($data));
}

// --- Helper : lire l'input JSON ---
function get_input($key) {
    static $body = null;
    if ($body === null) {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return $body[$key] ?? null;
}

// --- Helper : trouver le binaire PHP CLI (evite php-fpm) ---
function find_php_cli() {
    // Candidats du plus specifique au plus generique
    $candidates = [
        'php8.5', 'php8.4', 'php8.3', 'php8.2',
        'php-cli8.5', 'php-cli8.4', 'php-cli8.3',
        'php-cli', 'php'
    ];

    foreach ($candidates as $cmd) {
        $path = trim((string) shell_exec("which $cmd 2>/dev/null"));
        if (!$path) continue;
        // Verifier que c'est bien le CLI et pas FPM
        $sapi = trim((string) shell_exec("$path -r 'echo PHP_SAPI;' 2>/dev/null"));
        if ($sapi && strpos($sapi, 'fpm') === false && strpos($sapi, 'cgi') === false) {
            return $path;
        }
    }

    // Dernier recours : PHP_BINARY (peut etre fpm, mais on essaie)
    return PHP_BINARY;
}

// --- Helper : forcer PHP_BINARY vers le CLI (evite que le kernel Artisan utilise php-fpm) ---
function fix_php_binary() {
    $cli = find_php_cli();
    if ($cli && strpos($cli, 'fpm') === false) {
        putenv('PHP_BINARY=' . $cli);
        $_SERVER['PHP_BINARY'] = $cli;
        $_ENV['PHP_BINARY']    = $cli;
    }
}

// --- API ---
$action = get_input('action') ?? ($_GET['action'] ?? null);

// -- Verification prerequis --
if ($action === 'check') {
    $checks = [
        'php_version' => [
            'name'   => 'PHP ' . MIN_PHP . '+',
            'passed' => version_compare(PHP_VERSION, MIN_PHP, '>='),
            'value'  => PHP_VERSION,
        ],
        'gd'       => ['name' => 'Extension GD',       'passed' => extension_loaded('gd')],
        'curl'     => ['name' => 'Extension cURL',     'passed' => extension_loaded('curl')],
        'json'     => ['name' => 'Extension JSON',     'passed' => extension_loaded('json')],
        'pdo'      => ['name' => 'Extension PDO',      'passed' => extension_loaded('pdo')],
        'pdo_mysql'=> ['name' => 'Extension PDO MySQL','passed' => extension_loaded('pdo_mysql')],
        'zip'      => ['name' => 'Extension ZIP',      'passed' => extension_loaded('zip')],
        'mbstring' => ['name' => 'Extension mbstring', 'passed' => extension_loaded('mbstring')],
        'openssl'  => ['name' => 'Extension OpenSSL',  'passed' => extension_loaded('openssl')],
        'storage'  => ['name' => 'storage/ accessible','passed' => is_writable(ROOT . '/storage')],
        'bootstrap'=> ['name' => 'bootstrap/ accessible','passed' => is_writable(ROOT . '/bootstrap/cache')],
    ];

    $phpCli = find_php_cli();
    $checks['php_cli'] = [
        'name'   => 'PHP CLI disponible',
        'passed' => (bool) $phpCli && strpos($phpCli, 'fpm') === false,
        'value'  => $phpCli,
    ];

    $allPassed = array_reduce($checks, fn($c, $ch) => $c && $ch['passed'], true);
    json_response(['checks' => $checks, 'all_passed' => $allPassed]);
}

// -- Test connexion BDD --
if ($action === 'test_db') {
    $driver = get_input('db_driver') ?? 'mysql';
    $host   = get_input('db_host') ?? '127.0.0.1';
    $port   = get_input('db_port') ?? '3306';
    $name   = get_input('db_name') ?? '';
    $user   = get_input('db_user') ?? '';
    $pass   = get_input('db_pass') ?? '';

    try {
        if ($driver === 'sqlite') {
            $path = ROOT . '/database/database.sqlite';
            if (!file_exists($path)) {
                touch($path);
            }
            new PDO('sqlite:' . $path);
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
            new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        }
        json_response(['success' => true]);
    } catch (Exception $e) {
        json_response(['success' => false, 'error' => $e->getMessage()]);
    }
}

// -- Installation --
if ($action === 'install') {
    $step = get_input('step');

    // Etape : telecharger et installer Composer
    if ($step === 'composer') {
        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            $phpCli = find_php_cli();

            // Telecharger composer.phar directement (sans passer par l'installeur)
            if (!file_exists(ROOT . '/composer.phar')) {
                $ch = curl_init('https://getcomposer.org/composer-stable.phar');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $phar = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (!$phar || $httpCode !== 200) {
                    json_response(['success' => false, 'error' => 'Impossible de telecharger composer.phar (HTTP ' . $httpCode . ')'], 400);
                }
                file_put_contents(ROOT . '/composer.phar', $phar);
            }

            ob_start();
            $cmd = 'cd ' . escapeshellarg(ROOT) . ' && ' . escapeshellarg($phpCli) . ' composer.phar install --no-dev --optimize-autoloader --no-interaction 2>&1';
            system($cmd, $ret);
            $output = ob_get_clean();

            if ($ret !== 0 || !file_exists(ROOT . '/vendor/autoload.php')) {
                json_response(['success' => false, 'error' => 'composer install a echoue : ' . $output], 400);
            }
        }
        json_response(['success' => true]);
    }

    // Etape : creer le fichier .env
    if ($step === 'env') {
        $driver = get_input('db_driver') ?? 'mysql';
        $host   = get_input('db_host') ?? '127.0.0.1';
        $port   = get_input('db_port') ?? '3306';
        $name   = get_input('db_name') ?? '';
        $user   = get_input('db_user') ?? '';
        $pass   = get_input('db_pass') ?? '';
        $appUrl = rtrim(get_input('app_url') ?? 'http://localhost', '/');
        $debug  = get_input('app_debug') ? 'true' : 'false';

        if ($driver === 'sqlite') {
            $sqlitePath = ROOT . '/database/database.sqlite';
            if (!file_exists($sqlitePath)) touch($sqlitePath);
            $dbConnection = 'sqlite';
            $dbDatabase   = $sqlitePath;
            $dbHost = $dbPort = $dbUser = $dbPass = '';
        } else {
            $dbConnection = 'mysql';
            $dbDatabase   = $name;
            $dbHost       = $host;
            $dbPort       = $port;
            $dbUser       = $user;
            $dbPass       = $pass;
        }

        $envContent = "APP_NAME=Mnemo\n"
            . "APP_ENV=production\n"
            . "APP_KEY=\n"
            . "APP_DEBUG=$debug\n"
            . "APP_TIMEZONE=UTC\n"
            . "APP_URL=$appUrl\n"
            . "APP_LOCALE=fr\n"
            . "APP_FALLBACK_LOCALE=fr\n"
            . "APP_FAKER_LOCALE=fr_FR\n"
            . "\n"
            . "LOG_CHANNEL=stack\n"
            . "LOG_DEPRECATIONS_CHANNEL=null\n"
            . "LOG_LEVEL=debug\n"
            . "\n"
            . "DB_CONNECTION=$dbConnection\n"
            . ($dbHost ? "DB_HOST=$dbHost\n" : "")
            . ($dbPort ? "DB_PORT=$dbPort\n" : "")
            . "DB_DATABASE=$dbDatabase\n"
            . ($dbUser ? "DB_USERNAME=$dbUser\n" : "")
            . ($dbPass !== '' ? "DB_PASSWORD=$dbPass\n" : "DB_PASSWORD=\n")
            . "\n"
            . "SESSION_DRIVER=database\n"
            . "SESSION_LIFETIME=120\n"
            . "SESSION_ENCRYPT=false\n"
            . "SESSION_PATH=/\n"
            . "SESSION_DOMAIN=null\n"
            . "\n"
            . "BROADCAST_CONNECTION=log\n"
            . "FILESYSTEM_DISK=local\n"
            . "QUEUE_CONNECTION=database\n"
            . "\n"
            . "CACHE_STORE=database\n"
            . "\n"
            . "MAIL_MAILER=log\n"
            . "MAIL_FROM_ADDRESS=hello@example.com\n"
            . "MAIL_FROM_NAME=\"Mnemo\"\n";

        if (!file_put_contents(ROOT . '/.env', $envContent)) {
            json_response(['success' => false, 'error' => 'Impossible d\'ecrire le fichier .env - verifiez les permissions'], 400);
        }

        json_response(['success' => true]);
    }

    // Etape : generer la cle APP_KEY
    if ($step === 'key') {
        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            json_response(['success' => false, 'error' => 'Les dependances ne sont pas installees'], 400);
        }
        try {
            fix_php_binary();
            ob_start();
            require_once ROOT . '/vendor/autoload.php';
            $app = require ROOT . '/bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->call('key:generate', ['--force' => true]);
            ob_end_clean();
            json_response(['success' => true]);
        } catch (Throwable $e) {
            ob_end_clean();
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    // Etape : migrations
    if ($step === 'migrate') {
        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            json_response(['success' => false, 'error' => 'Les dependances ne sont pas installees'], 400);
        }
        try {
            fix_php_binary();
            ob_start();
            require_once ROOT . '/vendor/autoload.php';
            $app = require ROOT . '/bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            // Vider le cache de config pour s'assurer que le .env est bien lu
            $kernel->call('config:clear');
            $status = $kernel->call('migrate', ['--force' => true]);
            $output = ob_get_clean();
            if ($status !== 0) {
                json_response(['success' => false, 'error' => 'Migration echouee. Sortie : ' . $output], 400);
            }
            json_response(['success' => true, 'output' => $output]);
        } catch (Throwable $e) {
            ob_end_clean();
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    // Etape : lien de stockage
    if ($step === 'storage') {
        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            json_response(['success' => false, 'error' => 'Les dependances ne sont pas installees'], 400);
        }
        try {
            fix_php_binary();
            ob_start();
            require_once ROOT . '/vendor/autoload.php';
            $app = require ROOT . '/bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->call('storage:link');
            ob_end_clean();
            json_response(['success' => true]);
        } catch (Throwable $e) {
            ob_end_clean();
            // Lien deja existant : pas une erreur bloquante
            json_response(['success' => true, 'warning' => $e->getMessage()]);
        }
    }

    // Etape : creer le compte admin
    if ($step === 'admin') {
        $adminName  = get_input('name');
        $adminEmail = get_input('email');
        $adminPass  = get_input('password');

        if (!$adminName || !$adminEmail || !$adminPass) {
            json_response(['success' => false, 'error' => 'Tous les champs sont obligatoires'], 400);
        }

        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            json_response(['success' => false, 'error' => 'Les dependances ne sont pas installees'], 400);
        }

        try {
            ob_start();
            require_once ROOT . '/vendor/autoload.php';
            $app = require ROOT . '/bootstrap/app.php';
            ob_end_clean();

            $db = $app->make('db');
            $existing = $db->table('users')->where('email', $adminEmail)->first();
            if ($existing) {
                json_response(['success' => false, 'error' => 'Un utilisateur avec cet email existe deja'], 400);
            }

            $now = date('Y-m-d H:i:s');
            $db->table('users')->insert([
                'name'               => $adminName,
                'email'              => $adminEmail,
                'password'           => password_hash($adminPass, PASSWORD_BCRYPT),
                'email_verified_at'  => $now,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            json_response(['success' => true]);
        } catch (Throwable $e) {
            ob_end_clean();
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    // Etape : nettoyage
    if ($step === 'cleanup') {
        @unlink(__FILE__);
        json_response(['success' => true]);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mnemo - Installation</title>
    <style>
        :root {
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --bg: #0f172a;
            --bg2: #1e293b;
            --card: #1a1a1a;
            --border: #374151;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg) 0%, var(--bg2) 100%);
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
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 640px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 0.25rem;
            letter-spacing: -1px;
        }

        .tagline {
            color: var(--muted);
            font-size: 0.875rem;
        }

        /* Barre de progression par etapes */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 14px;
            left: 14px;
            right: 14px;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            position: relative;
            z-index: 1;
        }

        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.3s;
            border: 2px solid var(--border);
        }

        .step-dot.active {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .step-dot.done {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step-label {
            font-size: 0.7rem;
            color: var(--muted);
            white-space: nowrap;
        }

        .step-item.active .step-label {
            color: var(--accent);
            font-weight: 600;
        }

        /* Zones de contenu */
        .panel {
            display: none;
        }
        .panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 1.375rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--muted);
            font-size: 0.875rem;
            margin-bottom: 1.75rem;
        }

        /* Checks */
        .check-list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            margin-bottom: 1.5rem;
        }

        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .check-item.pass { border-color: rgba(16,185,129,.3); }
        .check-item.fail { border-color: rgba(239,68,68,.3); }

        .check-name { font-weight: 500; }
        .check-value { font-size: 0.75rem; color: var(--muted); margin-top: 1px; }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 100px;
        }
        .badge-ok   { background: rgba(16,185,129,.15); color: #6ee7b7; }
        .badge-fail { background: rgba(239,68,68,.15); color: #fca5a5; }

        /* Formulaires */
        .form-group { margin-bottom: 1.25rem; }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        input, select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(99,102,241,0.08);
        }

        select option { background: var(--card); color: var(--text); }

        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Alertes */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: none;
        }
        .alert.show { display: block; }
        .alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #a7f3d0; }
        .alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
        .alert-info    { background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.3); color: #c7d2fe; }

        /* Log d'installation */
        .install-log {
            background: #0a0a0a;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.8rem;
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 1.25rem;
        }

        .log-line { margin-bottom: 0.25rem; }
        .log-ok   { color: var(--success); }
        .log-err  { color: var(--error); }
        .log-wait { color: var(--muted); }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid var(--accent);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            vertical-align: middle;
            margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Boutons */
        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .btn-primary {
            background: var(--accent);
            color: white;
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(99,102,241,.35);
        }

        .btn-outline {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            margin-top: 0.75rem;
        }
        .btn-outline:hover:not(:disabled) {
            color: var(--text);
            border-color: var(--muted);
        }

        /* Succes final */
        .success-icon {
            width: 64px;
            height: 64px;
            background: rgba(16,185,129,.15);
            border: 2px solid var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1.5rem;
        }

        /* Recapitulatif */
        .recap {
            background: rgba(255,255,255,.02);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        .recap-row {
            display: flex;
            justify-content: space-between;
            padding: 0.3rem 0;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        .recap-row:last-child { border-bottom: none; }
        .recap-key   { color: var(--muted); }
        .recap-value { font-weight: 500; color: var(--accent); }

        .footer-note {
            text-align: center;
            color: var(--muted);
            font-size: 0.75rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="logo">Mnemo</div>
        <p class="tagline">Assistant d'installation</p>
    </div>

    <div class="steps" id="steps-bar">
        <div class="step-item active" id="si-1">
            <div class="step-dot active" id="sd-1">1</div>
            <span class="step-label">Verification</span>
        </div>
        <div class="step-item" id="si-2">
            <div class="step-dot" id="sd-2">2</div>
            <span class="step-label">Configuration</span>
        </div>
        <div class="step-item" id="si-3">
            <div class="step-dot" id="sd-3">3</div>
            <span class="step-label">Installation</span>
        </div>
        <div class="step-item" id="si-4">
            <div class="step-dot" id="sd-4">4</div>
            <span class="step-label">Compte admin</span>
        </div>
        <div class="step-item" id="si-5">
            <div class="step-dot" id="sd-5">&#10003;</div>
            <span class="step-label">Termine</span>
        </div>
    </div>

    <!-- PANNEAU 1 : Verification -->
    <div class="panel active" id="panel-1">
        <h2>Verification du serveur</h2>
        <p class="subtitle">Vos extensions PHP et les permissions sont verifiees automatiquement.</p>
        <div class="check-list" id="check-list"></div>
        <div class="alert" id="alert-1"></div>
        <button class="btn btn-primary" id="btn-check" onclick="runChecks()">Verifier les prerequis</button>
    </div>

    <!-- PANNEAU 2 : Configuration -->
    <div class="panel" id="panel-2">
        <h2>Configuration</h2>
        <p class="subtitle">Configurez la base de donnees et les parametres generaux.</p>

        <div class="form-group">
            <label>URL de l'application</label>
            <input type="text" id="app_url" placeholder="https://monsite.fr" value="<?= htmlspecialchars('http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>">
        </div>

        <div class="form-group">
            <label>Mode debug</label>
            <select id="app_debug">
                <option value="0">Desactive (recommande en production)</option>
                <option value="1">Active</option>
            </select>
        </div>

        <div class="form-group">
            <label>Type de base de donnees</label>
            <select id="db_driver" onchange="toggleDbFields()">
                <option value="mysql">MySQL / MariaDB</option>
                <option value="sqlite">SQLite (fichier local)</option>
            </select>
        </div>

        <div id="mysql-fields">
            <div class="row2">
                <div class="form-group">
                    <label>Hote</label>
                    <input type="text" id="db_host" value="127.0.0.1" placeholder="127.0.0.1">
                </div>
                <div class="form-group">
                    <label>Port</label>
                    <input type="text" id="db_port" value="3306" placeholder="3306">
                </div>
            </div>
            <div class="form-group">
                <label>Nom de la base</label>
                <input type="text" id="db_name" placeholder="mnemo">
            </div>
            <div class="row2">
                <div class="form-group">
                    <label>Utilisateur</label>
                    <input type="text" id="db_user" placeholder="root">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" id="db_pass" placeholder="(vide si aucun)">
                </div>
            </div>
        </div>

        <div class="alert" id="alert-2"></div>
        <button class="btn btn-primary" id="btn-config" onclick="saveConfig()">Tester et continuer</button>
    </div>

    <!-- PANNEAU 3 : Installation -->
    <div class="panel" id="panel-3">
        <h2>Installation</h2>
        <p class="subtitle">Cliquez sur le bouton ci-dessous pour lancer l'installation.</p>
        <div class="install-log" id="install-log" style="display:none;"></div>
        <div class="alert" id="alert-3"></div>
        <button class="btn btn-primary" id="btn-install" onclick="runInstall()">Lancer l'installation</button>
    </div>

    <!-- PANNEAU 4 : Compte admin -->
    <div class="panel" id="panel-4">
        <h2>Compte administrateur</h2>
        <p class="subtitle">Creez le compte qui vous permettra de vous connecter.</p>

        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" id="admin_name" placeholder="Jean Dupont">
        </div>
        <div class="form-group">
            <label>Adresse email</label>
            <input type="email" id="admin_email" placeholder="admin@monsite.fr">
        </div>
        <div class="row2">
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" id="admin_pass" placeholder="Min. 8 caracteres">
            </div>
            <div class="form-group">
                <label>Confirmation</label>
                <input type="password" id="admin_pass2" placeholder="Retapez le mot de passe">
            </div>
        </div>

        <div class="alert" id="alert-4"></div>
        <button class="btn btn-primary" id="btn-admin" onclick="createAdmin()">Creer le compte et terminer</button>
    </div>

    <!-- PANNEAU 5 : Termine -->
    <div class="panel" id="panel-5">
        <div class="success-icon">&#10003;</div>
        <h2 style="text-align:center;margin-bottom:.5rem;">Installation terminee !</h2>
        <p class="subtitle" style="text-align:center;">Mnemo est pret. Vous pouvez vous connecter.</p>
        <div class="recap" id="recap-box"></div>
        <button class="btn btn-primary" onclick="window.location='/'">Ouvrir Mnemo</button>
    </div>

    <p class="footer-note">Mnemo &mdash; Installateur &bull; PHP <?= PHP_VERSION ?></p>
</div>

<script>
let config = {};
let currentPanel = 1;

function goTo(n) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + n).classList.add('active');

    for (let i = 1; i <= 5; i++) {
        const dot   = document.getElementById('sd-' + i);
        const item  = document.getElementById('si-' + i);
        dot.classList.remove('active', 'done');
        item.classList.remove('active');
        if (i < n) { dot.classList.add('done'); dot.innerHTML = '&#10003;'; }
        else if (i === n) { dot.classList.add('active'); item.classList.add('active'); dot.innerHTML = i < 5 ? i : '&#10003;'; }
        else { dot.innerHTML = i < 5 ? i : '&#10003;'; }
    }
    currentPanel = n;
    window.scrollTo(0, 0);
}

function showAlert(id, msg, type = 'error') {
    const el = document.getElementById('alert-' + id);
    el.className = 'alert show alert-' + (type === 'error' ? 'error' : type === 'success' ? 'success' : 'info');
    el.innerHTML = msg;
}
function hideAlert(id) {
    document.getElementById('alert-' + id).className = 'alert';
}

function toggleDbFields() {
    const driver = document.getElementById('db_driver').value;
    document.getElementById('mysql-fields').style.display = driver === 'sqlite' ? 'none' : 'block';
}

async function post(action, data = {}) {
    const res = await fetch('?' + new URLSearchParams({action}), {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data),
    });
    return res.json();
}

// --- ETAPE 1 : Checks ---
async function runChecks() {
    const btn = document.getElementById('btn-check');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Verification...';
    hideAlert(1);

    const data = await post('check');
    const list = document.getElementById('check-list');
    list.innerHTML = '';

    let allOk = true;
    for (const [, ch] of Object.entries(data.checks)) {
        if (!ch.passed) allOk = false;
        list.innerHTML += `
            <div class="check-item ${ch.passed ? 'pass' : 'fail'}">
                <div>
                    <div class="check-name">${ch.name}</div>
                    ${ch.value ? '<div class="check-value">' + ch.value + '</div>' : ''}
                </div>
                <span class="badge ${ch.passed ? 'badge-ok' : 'badge-fail'}">${ch.passed ? 'OK' : 'ECHEC'}</span>
            </div>`;
    }

    btn.disabled = false;
    btn.innerHTML = 'Verifier les prerequis';

    if (allOk) {
        showAlert(1, '&#10003; Tous les prerequis sont satisfaits. Passage a la configuration...', 'success');
        setTimeout(() => goTo(2), 1400);
    } else {
        showAlert(1, '&#10007; Certains prerequis ne sont pas satisfaits. Corrigez-les avant de continuer.', 'error');
    }
}

// --- ETAPE 2 : Configuration ---
async function saveConfig() {
    const btn = document.getElementById('btn-config');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Test de la connexion...';
    hideAlert(2);

    const driver = document.getElementById('db_driver').value;
    config = {
        app_url:   document.getElementById('app_url').value,
        app_debug: document.getElementById('app_debug').value === '1',
        db_driver: driver,
        db_host:   document.getElementById('db_host')?.value || '127.0.0.1',
        db_port:   document.getElementById('db_port')?.value || '3306',
        db_name:   document.getElementById('db_name')?.value || '',
        db_user:   document.getElementById('db_user')?.value || '',
        db_pass:   document.getElementById('db_pass')?.value || '',
    };

    const res = await post('test_db', config);
    btn.disabled = false;
    btn.innerHTML = 'Tester et continuer';

    if (res.success) {
        showAlert(2, '&#10003; Connexion a la base de donnees reussie.', 'success');
        setTimeout(() => goTo(3), 1000);
    } else {
        showAlert(2, '&#10007; Connexion echouee : ' + (res.error || 'Erreur inconnue'), 'error');
    }
}

// --- ETAPE 3 : Installation ---
async function runInstall() {
    const btn = document.getElementById('btn-install');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Installation en cours...';
    hideAlert(3);

    const log = document.getElementById('install-log');
    log.style.display = 'block';
    log.innerHTML = '';

    function addLog(msg, type = 'wait') {
        log.innerHTML += `<div class="log-line log-${type}">${msg}</div>`;
        log.scrollTop = log.scrollHeight;
    }

    const steps = [
        { step: 'composer', label: 'Telechargement et installation de Composer...' },
        { step: 'env',      label: 'Creation du fichier .env...',      extra: config },
        { step: 'key',      label: 'Generation de la cle applicative...' },
        { step: 'migrate',  label: 'Migrations de la base de donnees...' },
        { step: 'storage',  label: 'Creation du lien de stockage...' },
    ];

    for (const s of steps) {
        addLog('<span class="spinner"></span>' + s.label, 'wait');
        const res = await post('install', { step: s.step, ...config });

        // Remplacer le spinner par le resultat
        const lines = log.querySelectorAll('.log-line');
        const last  = lines[lines.length - 1];

        if (res.success) {
            last.className = 'log-line log-ok';
            last.innerHTML = '&#10003; ' + s.label.replace('...', '');
        } else {
            last.className = 'log-line log-err';
            last.innerHTML = '&#10007; ' + s.label.replace('...', '') + ' - ECHEC';
            addLog('Erreur : ' + (res.error || 'Inconnue'), 'err');
            showAlert(3, '&#10007; ' + (s.step === 'composer' ? 'Installation de Composer' : 'Migration base de donnees') + ' : ECHEC<br><small>' + (res.error || '') + '</small>', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Relancer l\'installation';
            return;
        }
    }

    addLog('Installation terminee avec succes !', 'ok');
    showAlert(3, '&#10003; Tout est installe. Passez a la creation du compte admin.', 'success');
    setTimeout(() => goTo(4), 1200);
}

// --- ETAPE 4 : Compte admin ---
async function createAdmin() {
    const btn  = document.getElementById('btn-admin');
    const name  = document.getElementById('admin_name').value.trim();
    const email = document.getElementById('admin_email').value.trim();
    const pass  = document.getElementById('admin_pass').value;
    const pass2 = document.getElementById('admin_pass2').value;

    hideAlert(4);

    if (!name || !email || !pass) {
        showAlert(4, 'Tous les champs sont obligatoires.', 'error'); return;
    }
    if (pass !== pass2) {
        showAlert(4, 'Les mots de passe ne correspondent pas.', 'error'); return;
    }
    if (pass.length < 8) {
        showAlert(4, 'Le mot de passe doit contenir au moins 8 caracteres.', 'error'); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Creation du compte...';

    const res = await post('install', { step: 'admin', name, email, password: pass });

    if (!res.success) {
        showAlert(4, '&#10007; ' + (res.error || 'Erreur inconnue'), 'error');
        btn.disabled = false;
        btn.innerHTML = 'Creer le compte et terminer';
        return;
    }

    // Nettoyage silencieux
    await post('install', { step: 'cleanup' });

    // Recapitulatif
    document.getElementById('recap-box').innerHTML = `
        <div class="recap-row"><span class="recap-key">Nom</span><span class="recap-value">${name}</span></div>
        <div class="recap-row"><span class="recap-key">Email</span><span class="recap-value">${email}</span></div>
        <div class="recap-row"><span class="recap-key">URL</span><span class="recap-value">${config.app_url || '/'}</span></div>
        <div class="recap-row"><span class="recap-key">Base de donnees</span><span class="recap-value">${(config.db_driver || 'mysql').toUpperCase()}</span></div>
    `;

    goTo(5);
}
</script>
</body>
</html>
