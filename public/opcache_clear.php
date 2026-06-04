<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache vidé.';
} else {
    echo 'OPcache non disponible.';
}
