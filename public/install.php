<?php
/**
 * Mnémo Installer
 * Installateur autonome inspiré de AzuriomInstaller
 */

define('ROOT_PATH', dirname(__DIR__));
define('LOCK_FILE', ROOT_PATH . '/storage/installed.lock');
define('ENV_FILE', ROOT_PATH . '/.env');

// ─── Protection si déjà installé ────────────────────────────────────────────
if (file_exists(LOCK_FILE)) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mnémo - Déjà installé</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { background: #13162b; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', system-ui, sans-serif; }
            .card { background: #181c2a; border: 1px solid #2d3454; border-radius: 16px; }
        </style>
    </head>
    <body>
        <div class="text-center p-5">
            <div class="card p-5 shadow-lg">
                <i class="bi bi-shield-lock-fill text-warning fs-1 mb-3"></i>
                <h2 class="fw-bold mb-2">Application déjà installée</h2>
                <p class="text-secondary mb-4">L'installateur a été verrouillé après une installation réussie.</p>
                <a href="/" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-left me-2"></i>Retour à l'application
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function checkRequirements(): array
{
    $results = [];

    // PHP version
    $results['php'] = [
        'label'   => 'PHP >= 8.2',
        'ok'      => version_compare(PHP_VERSION, '8.2.0', '>='),
        'current' => PHP_VERSION,
    ];

    // Extensions
    $extensions = [
        'pdo'       => 'PDO',
        'pdo_sqlite'=> 'PDO SQLite',
        'mbstring'  => 'Mbstring',
        'openssl'   => 'OpenSSL',
        'tokenizer' => 'Tokenizer',
        'xml'       => 'XML',
        'ctype'     => 'Ctype',
        'json'      => 'JSON',
        'bcmath'    => 'BCMath',
        'fileinfo'  => 'Fileinfo',
    ];

    foreach ($extensions as $ext => $label) {
        $results['ext_' . $ext] = [
            'label'   => $label,
            'ok'      => extension_loaded($ext),
            'current' => extension_loaded($ext) ? 'Chargée' : 'Manquante',
        ];
    }

    // Permissions en écriture
    $paths = [
        '.env'              => ROOT_PATH . '/.env.example',
        'storage/'          => ROOT_PATH . '/storage',
        'bootstrap/cache/'  => ROOT_PATH . '/bootstrap/cache',
    ];

    foreach ($paths as $name => $path) {
        if ($name === '.env') {
            // Vérifie si on peut créer/écrire le .env dans ROOT_PATH
            $writable = is_writable(ROOT_PATH);
        } else {
            $writable = is_writable($path);
        }
        $results['perm_' . md5($name)] = [
            'label'   => 'Écriture : ' . $name,
            'ok'      => $writable,
            'current' => $writable ? 'Accessible' : 'Non accessible',
            'is_perm' => true,
        ];
    }

    return $results;
}

function allRequirementsMet(array $reqs): bool
{
    foreach ($reqs as $r) {
        if (!$r['ok']) return false;
    }
    return true;
}

function generateAppKey(): string
{
    return 'base64:' . base64_encode(random_bytes(32));
}

function buildEnvContent(array $cfg): string
{
    $dbConnection = $cfg['db_connection'] ?? 'sqlite';
    $debug        = ($cfg['debug'] ?? 'false') === 'true' ? 'true' : 'false';
    $appKey       = generateAppKey();
    $appName      = addslashes($cfg['app_name'] ?? 'Mnémo');
    $appUrl       = rtrim($cfg['app_url'] ?? 'http://localhost:8000', '/');

    if ($dbConnection === 'sqlite') {
        $dbPath  = ROOT_PATH . '/database/database.sqlite';
        $dbBlock = "DB_CONNECTION=sqlite\nDB_DATABASE=" . $dbPath;
    } else {
        $dbHost     = $cfg['db_host']     ?? '127.0.0.1';
        $dbPort     = $cfg['db_port']     ?? '3306';
        $dbDatabase = $cfg['db_database'] ?? 'mnemo';
        $dbUsername = $cfg['db_username'] ?? 'root';
        $dbPassword = $cfg['db_password'] ?? '';
        $dbBlock = <<<ENV
DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbDatabase}
DB_USERNAME={$dbUsername}
DB_PASSWORD={$dbPassword}
ENV;
    }

    return <<<ENV
APP_NAME="{$appName}"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG={$debug}
APP_URL={$appUrl}
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR
APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

{$dbBlock}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
ENV;
}

function runCommand(string $cmd, string $cwd): array
{
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($cmd, $descriptors, $pipes, $cwd);

    if (!is_resource($process)) {
        return ['success' => false, 'output' => 'Impossible de lancer la commande.'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [
        'success' => $exitCode === 0,
        'output'  => trim($stdout . "\n" . $stderr),
        'code'    => $exitCode,
    ];
}

// ─── Gestion des étapes POST ─────────────────────────────────────────────────

$step    = (int) ($_GET['step'] ?? 1);
$errors  = [];
$success = [];
$logs    = [];

// POST step 2 → 3 : validation config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'configure') {
    $cfg = [
        'app_name'      => trim($_POST['app_name'] ?? 'Mnémo'),
        'app_url'       => trim($_POST['app_url'] ?? 'http://localhost:8000'),
        'db_connection' => $_POST['db_connection'] ?? 'sqlite',
        'debug'         => $_POST['debug'] ?? 'false',
        'db_host'       => trim($_POST['db_host'] ?? '127.0.0.1'),
        'db_port'       => trim($_POST['db_port'] ?? '3306'),
        'db_database'   => trim($_POST['db_database'] ?? 'mnemo'),
        'db_username'   => trim($_POST['db_username'] ?? 'root'),
        'db_password'   => $_POST['db_password'] ?? '',
    ];

    if (empty($cfg['app_name'])) $errors[] = "Le nom de l'application est requis.";
    if (empty($cfg['app_url']))  $errors[] = "L'URL de l'application est requise.";

    if (empty($errors)) {
        session_start();
        $_SESSION['install_cfg'] = $cfg;
        header('Location: install.php?step=3');
        exit;
    }
    $step = 2;
}

// POST step 3 : installation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    session_start();
    $cfg = $_SESSION['install_cfg'] ?? [];

    if (empty($cfg)) {
        header('Location: install.php?step=2');
        exit;
    }

    $installErrors = [];
    $installLogs   = [];

    // 1. Écriture du .env
    $envContent = buildEnvContent($cfg);
    if (file_put_contents(ENV_FILE, $envContent) === false) {
        $installErrors[] = "Impossible d'écrire le fichier .env. Vérifiez les permissions.";
    } else {
        $installLogs[] = ['ok' => true, 'msg' => 'Fichier .env créé avec succès.'];
    }

    // 2. Créer le fichier SQLite si nécessaire
    if (empty($installErrors) && ($cfg['db_connection'] ?? 'sqlite') === 'sqlite') {
        $sqliteDir = ROOT_PATH . '/database';
        $sqliteFile = $sqliteDir . '/database.sqlite';
        if (!file_exists($sqliteFile)) {
            if (!is_dir($sqliteDir)) mkdir($sqliteDir, 0755, true);
            touch($sqliteFile);
            $installLogs[] = ['ok' => true, 'msg' => 'Base de données SQLite créée.'];
        }
    }

    // 3. Migration
    if (empty($installErrors)) {
        $phpBin = PHP_BINARY ?: 'php';
        $result = runCommand("{$phpBin} artisan migrate --force 2>&1", ROOT_PATH);
        $installLogs[] = [
            'ok'  => $result['success'],
            'msg' => 'Migration base de données : ' . ($result['success'] ? 'OK' : 'ÉCHEC'),
            'detail' => $result['output'],
        ];
        if (!$result['success']) {
            $installErrors[] = "La migration a échoué. Consultez les logs ci-dessous.";
        }
    }

    // 4. Storage link
    if (empty($installErrors)) {
        $phpBin = PHP_BINARY ?: 'php';
        $result = runCommand("{$phpBin} artisan storage:link --force 2>&1", ROOT_PATH);
        $installLogs[] = [
            'ok'    => $result['success'],
            'msg'   => 'Lien de stockage : ' . ($result['success'] ? 'OK' : 'Avertissement (non bloquant)'),
            'detail'=> $result['output'],
        ];
    }

    // 5. Fichier lock
    if (empty($installErrors)) {
        $storageDir = ROOT_PATH . '/storage';
        if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);
        file_put_contents(LOCK_FILE, date('Y-m-d H:i:s'));
        $installLogs[] = ['ok' => true, 'msg' => 'Installateur verrouillé (installed.lock).'];
    }

    if (empty($installErrors)) {
        session_destroy();
        $step  = 4;
        $logs  = $installLogs;
    } else {
        $errors = $installErrors;
        $logs   = $installLogs;
        $step   = 3;
    }
}

// Session pour step 3
if ($step === 3 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    session_start();
}

// Pré-remplissage config depuis session
$savedCfg = [];
if ($step === 2 || $step === 3) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $savedCfg = $_SESSION['install_cfg'] ?? [];
}

$requirements = ($step === 1) ? checkRequirements() : [];
$allOk        = ($step === 1) ? allRequirementsMet($requirements) : true;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mnémo - Installation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-base:    #13162b;
            --bg-card:    #181c2a;
            --bg-card2:   #1e2235;
            --border:     #2d3454;
            --accent:     #6366f1;
            --accent-h:   #4f51d9;
            --accent-glow:rgba(99,102,241,0.25);
            --text:       #e2e8f0;
            --text-muted: #8892b0;
            --success:    #10b981;
            --danger:     #ef4444;
            --warning:    #f59e0b;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg-base);
            color: var(--text);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 2rem 1rem 4rem;
        }

        /* Logo */
        .mnemo-logo {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .mnemo-logo span {
            display: inline-block;
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            border-radius: 10px;
            margin-right: 8px;
            vertical-align: middle;
            position: relative;
            top: -2px;
        }
        .mnemo-logo span::after {
            content: 'M';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 900;
            -webkit-text-fill-color: #fff;
        }

        /* Card principale */
        .install-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            max-width: 760px;
            margin: 0 auto;
        }

        /* Stepper */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 2rem 2rem 0;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px;
            left: calc(50% + 18px);
            right: calc(-50% + 18px);
            height: 2px;
            background: var(--border);
            z-index: 0;
        }
        .step-item.completed:not(:last-child)::after {
            background: var(--accent);
        }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
            border: 2px solid var(--border);
            background: var(--bg-card2);
            color: var(--text-muted);
            position: relative; z-index: 1;
            transition: all .3s;
        }
        .step-item.active .step-circle {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 0 4px var(--accent-glow);
        }
        .step-item.completed .step-circle {
            border-color: var(--success);
            background: var(--success);
            color: #fff;
        }
        .step-label {
            font-size: .72rem;
            margin-top: .4rem;
            color: var(--text-muted);
            text-align: center;
            white-space: nowrap;
        }
        .step-item.active .step-label { color: var(--accent); font-weight: 600; }
        .step-item.completed .step-label { color: var(--success); }

        /* Sections */
        .install-body { padding: 2rem 2.5rem; }
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: .3rem;
        }
        .section-subtitle {
            color: var(--text-muted);
            font-size: .9rem;
            margin-bottom: 1.5rem;
        }

        /* Check list */
        .check-list { list-style: none; padding: 0; margin: 0; }
        .check-list li {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem .9rem;
            border-radius: 10px;
            margin-bottom: .4rem;
            background: var(--bg-card2);
            border: 1px solid var(--border);
            font-size: .9rem;
        }
        .check-list li .badge-status {
            margin-left: auto;
            font-size: .75rem;
            padding: .25rem .6rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .check-list li.ok .badge-status { background: rgba(16,185,129,.15); color: var(--success); }
        .check-list li.fail .badge-status { background: rgba(239,68,68,.15); color: var(--danger); }
        .check-list li .check-icon { font-size: 1rem; flex-shrink: 0; }
        .check-list li.ok .check-icon { color: var(--success); }
        .check-list li.fail .check-icon { color: var(--danger); }

        /* Group headers */
        .check-group-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
            margin: 1rem 0 .5rem;
            padding-left: .2rem;
        }

        /* Form */
        .form-label { font-size: .85rem; color: var(--text-muted); margin-bottom: .3rem; }
        .form-control, .form-select {
            background: var(--bg-card2) !important;
            border: 1px solid var(--border) !important;
            color: var(--text) !important;
            border-radius: 10px;
            padding: .6rem 1rem;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px var(--accent-glow) !important;
            outline: none;
        }
        .form-control::placeholder { color: #4a5568; }
        .form-select option { background: var(--bg-card2); }

        /* Boutons */
        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: .7rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: .95rem;
            transition: background .2s, transform .1s, box-shadow .2s;
        }
        .btn-accent:hover {
            background: var(--accent-h);
            color: #fff;
            box-shadow: 0 4px 20px var(--accent-glow);
            transform: translateY(-1px);
        }
        .btn-accent:active { transform: translateY(0); }
        .btn-accent:disabled { opacity: .6; cursor: not-allowed; }

        .btn-outline-secondary {
            border: 1px solid var(--border) !important;
            color: var(--text-muted) !important;
            background: transparent !important;
            border-radius: 10px;
            padding: .7rem 1.5rem;
        }
        .btn-outline-secondary:hover {
            background: var(--bg-card2) !important;
            color: var(--text) !important;
        }

        /* Alert */
        .alert-danger-custom {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 12px;
            color: #fca5a5;
            padding: 1rem 1.2rem;
        }

        /* Log box */
        .log-box {
            background: #0d0f1a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.2rem;
            font-family: 'Consolas', 'Fira Code', monospace;
            font-size: .8rem;
            max-height: 300px;
            overflow-y: auto;
            color: #a0aec0;
        }
        .log-box .log-ok { color: var(--success); }
        .log-box .log-fail { color: var(--danger); }
        .log-box .log-detail {
            color: #4a5568;
            padding-left: 1rem;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Success */
        .success-icon {
            width: 80px; height: 80px;
            background: rgba(16,185,129,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
            color: var(--success);
            margin: 0 auto 1.5rem;
        }

        /* DB toggle */
        #mysql-fields { display: none; }

        /* Divider */
        hr.divider { border-color: var(--border); margin: 1.5rem 0; }

        /* Footer */
        .install-footer {
            text-align: center;
            padding: 1.2rem 2rem;
            border-top: 1px solid var(--border);
            font-size: .8rem;
            color: var(--text-muted);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .install-body { padding: 1.5rem 1.2rem; }
            .stepper { padding: 1.5rem 1rem 0; }
            .step-label { display: none; }
        }
    </style>
</head>
<body>

<div class="text-center mb-4">
    <div class="mnemo-logo"><span></span>Mnémo</div>
    <div class="text-muted mt-1" style="font-size:.85rem;">Assistant d'installation</div>
</div>

<div class="install-card">

    <!-- Stepper -->
    <div class="stepper">
        <?php
        $steps = [
            1 => 'Vérification',
            2 => 'Configuration',
            3 => 'Installation',
            4 => 'Terminé',
        ];
        foreach ($steps as $n => $label) {
            $cls = '';
            if ($n < $step)  $cls = 'completed';
            if ($n === $step) $cls = 'active';
            $icon = $n < $step ? '<i class="bi bi-check-lg"></i>' : $n;
            echo "<div class=\"step-item {$cls}\">";
            echo "<div class=\"step-circle\">{$icon}</div>";
            echo "<div class=\"step-label\">{$label}</div>";
            echo "</div>";
        }
        ?>
    </div>

    <div class="install-body">

        <?php if (!empty($errors)): ?>
        <div class="alert-danger-custom mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- STEP 1 : Bienvenue + Vérification                         -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <?php if ($step === 1): ?>

        <div class="section-title">Bienvenue dans l'installateur</div>
        <div class="section-subtitle">Vérification des prérequis système avant de continuer.</div>

        <!-- PHP Version -->
        <div class="check-group-title"><i class="bi bi-cpu me-1"></i>Version PHP</div>
        <ul class="check-list">
            <?php
            $r = $requirements['php'];
            $cls = $r['ok'] ? 'ok' : 'fail';
            $ico = $r['ok'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            ?>
            <li class="<?= $cls ?>">
                <i class="bi <?= $ico ?> check-icon"></i>
                <?= htmlspecialchars($r['label']) ?>
                <span class="badge-status"><?= htmlspecialchars($r['current']) ?></span>
            </li>
        </ul>

        <!-- Extensions -->
        <div class="check-group-title"><i class="bi bi-puzzle me-1"></i>Extensions PHP</div>
        <ul class="check-list">
            <?php foreach ($requirements as $key => $r):
                if (!str_starts_with($key, 'ext_')) continue;
                $cls = $r['ok'] ? 'ok' : 'fail';
                $ico = $r['ok'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            ?>
            <li class="<?= $cls ?>">
                <i class="bi <?= $ico ?> check-icon"></i>
                <?= htmlspecialchars($r['label']) ?>
                <span class="badge-status"><?= htmlspecialchars($r['current']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Permissions -->
        <div class="check-group-title"><i class="bi bi-folder2-open me-1"></i>Permissions d'écriture</div>
        <ul class="check-list">
            <?php foreach ($requirements as $key => $r):
                if (!str_starts_with($key, 'perm_')) continue;
                $cls = $r['ok'] ? 'ok' : 'fail';
                $ico = $r['ok'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            ?>
            <li class="<?= $cls ?>">
                <i class="bi <?= $ico ?> check-icon"></i>
                <?= htmlspecialchars($r['label']) ?>
                <span class="badge-status"><?= htmlspecialchars($r['current']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <hr class="divider">

        <div class="d-flex justify-content-end">
            <?php if ($allOk): ?>
            <a href="install.php?step=2" class="btn btn-accent">
                Continuer <i class="bi bi-arrow-right ms-2"></i>
            </a>
            <?php else: ?>
            <button class="btn btn-accent" disabled>
                Résolvez les erreurs pour continuer
            </button>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- STEP 2 : Configuration                                    -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <?php elseif ($step === 2): ?>

        <div class="section-title">Configuration de l'application</div>
        <div class="section-subtitle">Renseignez les informations de votre installation.</div>

        <form method="POST" action="install.php" id="configForm">
            <input type="hidden" name="action" value="configure">

            <!-- Application -->
            <div class="check-group-title"><i class="bi bi-app me-1"></i>Application</div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label">Nom de l'application</label>
                    <input type="text" class="form-control" name="app_name"
                           value="<?= htmlspecialchars($savedCfg['app_name'] ?? 'Mnémo') ?>"
                           placeholder="Mnémo" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">URL de l'application</label>
                    <input type="url" class="form-control" name="app_url"
                           value="<?= htmlspecialchars($savedCfg['app_url'] ?? 'http://localhost:8000') ?>"
                           placeholder="http://localhost:8000" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mode debug</label>
                <select class="form-select" name="debug">
                    <option value="false" <?= ($savedCfg['debug'] ?? 'false') === 'false' ? 'selected' : '' ?>>
                        Non (recommandé en production)
                    </option>
                    <option value="true" <?= ($savedCfg['debug'] ?? '') === 'true' ? 'selected' : '' ?>>
                        Oui (développement uniquement)
                    </option>
                </select>
            </div>

            <!-- Base de données -->
            <div class="check-group-title mt-4"><i class="bi bi-database me-1"></i>Base de données</div>

            <div class="mb-3">
                <label class="form-label">Type de base de données</label>
                <select class="form-select" name="db_connection" id="dbConnection">
                    <option value="sqlite" <?= ($savedCfg['db_connection'] ?? 'sqlite') === 'sqlite' ? 'selected' : '' ?>>
                        SQLite (simple, recommandé)
                    </option>
                    <option value="mysql" <?= ($savedCfg['db_connection'] ?? '') === 'mysql' ? 'selected' : '' ?>>
                        MySQL / MariaDB
                    </option>
                </select>
            </div>

            <div id="sqlite-info" class="mb-3">
                <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;padding:.75rem 1rem;font-size:.85rem;color:var(--text-muted);">
                    <i class="bi bi-info-circle me-2 text-accent" style="color:var(--accent)"></i>
                    SQLite créera automatiquement le fichier <code style="color:var(--accent)">database/database.sqlite</code> dans le répertoire racine.
                </div>
            </div>

            <div id="mysql-fields">
                <div class="row g-3 mb-3">
                    <div class="col-sm-8">
                        <label class="form-label">Hôte</label>
                        <input type="text" class="form-control" name="db_host"
                               value="<?= htmlspecialchars($savedCfg['db_host'] ?? '127.0.0.1') ?>"
                               placeholder="127.0.0.1">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" name="db_port"
                               value="<?= htmlspecialchars($savedCfg['db_port'] ?? '3306') ?>"
                               placeholder="3306">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom de la base</label>
                    <input type="text" class="form-control" name="db_database"
                           value="<?= htmlspecialchars($savedCfg['db_database'] ?? 'mnemo') ?>"
                           placeholder="mnemo">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Utilisateur</label>
                        <input type="text" class="form-control" name="db_username"
                               value="<?= htmlspecialchars($savedCfg['db_username'] ?? 'root') ?>"
                               placeholder="root">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="db_password"
                               value="<?= htmlspecialchars($savedCfg['db_password'] ?? '') ?>"
                               placeholder="••••••••">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="d-flex justify-content-between">
                <a href="install.php?step=1" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </a>
                <button type="submit" class="btn btn-accent">
                    Continuer <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- STEP 3 : Installation                                     -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <?php elseif ($step === 3): ?>

        <div class="section-title">Installation</div>
        <div class="section-subtitle">Cliquez sur le bouton ci-dessous pour lancer l'installation.</div>

        <?php if (!empty($logs)): ?>
        <div class="mb-4">
            <div class="check-group-title"><i class="bi bi-terminal me-1"></i>Résultat de l'installation</div>
            <div class="log-box">
                <?php foreach ($logs as $log): ?>
                <div class="<?= $log['ok'] ? 'log-ok' : 'log-fail' ?>">
                    <?= $log['ok'] ? '✔' : '✘' ?> <?= htmlspecialchars($log['msg']) ?>
                </div>
                <?php if (!empty($log['detail'])): ?>
                <div class="log-detail"><?= htmlspecialchars($log['detail']) ?></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $cfg = $_SESSION['install_cfg'] ?? [];
        ?>

        <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.5rem;margin-bottom:1.5rem;">
            <div class="check-group-title mb-2" style="margin-top:0"><i class="bi bi-list-check me-1"></i>Récapitulatif</div>
            <div class="row g-2" style="font-size:.875rem;">
                <div class="col-sm-6">
                    <span style="color:var(--text-muted)">Nom :</span>
                    <strong><?= htmlspecialchars($cfg['app_name'] ?? '-') ?></strong>
                </div>
                <div class="col-sm-6">
                    <span style="color:var(--text-muted)">URL :</span>
                    <strong><?= htmlspecialchars($cfg['app_url'] ?? '-') ?></strong>
                </div>
                <div class="col-sm-6">
                    <span style="color:var(--text-muted)">Base de données :</span>
                    <strong><?= htmlspecialchars(strtoupper($cfg['db_connection'] ?? 'sqlite')) ?></strong>
                </div>
                <div class="col-sm-6">
                    <span style="color:var(--text-muted)">Mode debug :</span>
                    <strong><?= ($cfg['debug'] ?? 'false') === 'true' ? 'Activé' : 'Désactivé' ?></strong>
                </div>
            </div>
        </div>

        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:12px;padding:1rem 1.2rem;margin-bottom:1.5rem;font-size:.85rem;color:#fde68a;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            L'installation va écrire le fichier <code>.env</code>, exécuter les migrations et créer un lien de stockage. Cette opération peut prendre quelques secondes.
        </div>

        <form method="POST" action="install.php" id="installForm">
            <input type="hidden" name="action" value="install">

            <hr class="divider">

            <div class="d-flex justify-content-between align-items-center">
                <a href="install.php?step=2" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </a>
                <button type="submit" class="btn btn-accent" id="installBtn">
                    <i class="bi bi-play-fill me-2"></i>Lancer l'installation
                </button>
            </div>
        </form>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- STEP 4 : Terminé                                          -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <?php elseif ($step === 4): ?>

        <div class="text-center py-3">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <div class="section-title">Installation terminée !</div>
            <p class="section-subtitle">Mnémo a été installé avec succès sur votre serveur.</p>

            <?php if (!empty($logs)): ?>
            <div class="text-start mb-4">
                <div class="check-group-title"><i class="bi bi-terminal me-1"></i>Journal d'installation</div>
                <div class="log-box">
                    <?php foreach ($logs as $log): ?>
                    <div class="<?= $log['ok'] ? 'log-ok' : 'log-fail' ?>">
                        <?= $log['ok'] ? '✔' : '✘' ?> <?= htmlspecialchars($log['msg']) ?>
                    </div>
                    <?php if (!empty($log['detail'])): ?>
                    <div class="log-detail"><?= htmlspecialchars($log['detail']) ?></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.25);border-radius:12px;padding:1rem 1.2rem;margin-bottom:1.5rem;font-size:.85rem;color:#c7d2fe;text-align:left;">
                <i class="bi bi-shield-check me-2" style="color:var(--accent)"></i>
                Pour votre sécurité, le fichier <code>install.php</code> ne peut plus être utilisé. Vous pouvez le supprimer de votre serveur.
            </div>

            <a href="/" class="btn btn-accent px-4">
                <i class="bi bi-house-fill me-2"></i>Accéder à Mnémo
            </a>
        </div>

        <?php endif; ?>

    </div><!-- /.install-body -->

    <div class="install-footer">
        Mnémo &mdash; Installateur &bull; PHP <?= PHP_VERSION ?>
    </div>

</div><!-- /.install-card -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Toggle MySQL / SQLite fields
    var dbSelect = document.getElementById('dbConnection');
    var mysqlFields = document.getElementById('mysql-fields');
    var sqliteInfo  = document.getElementById('sqlite-info');

    function toggleDB() {
        if (!dbSelect) return;
        var isMysql = dbSelect.value === 'mysql';
        if (mysqlFields) mysqlFields.style.display = isMysql ? 'block' : 'none';
        if (sqliteInfo)  sqliteInfo.style.display  = isMysql ? 'none'  : 'block';
    }

    if (dbSelect) {
        dbSelect.addEventListener('change', toggleDB);
        toggleDB();
    }

    // Bouton install : désactiver pendant l'envoi
    var installForm = document.getElementById('installForm');
    var installBtn  = document.getElementById('installBtn');
    if (installForm && installBtn) {
        installForm.addEventListener('submit', function () {
            installBtn.disabled = true;
            installBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Installation en cours…';
        });
    }
})();
</script>
</body>
</html>
