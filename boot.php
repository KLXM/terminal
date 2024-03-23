<?php // Boot code
if (rex::isBackend() && rex::getUser() && rex_backend_login::hasSession() ) {
rex_view::addJsFile(rex_addon::get('terminal')->getAssetsUrl('terminal.js'));
}
