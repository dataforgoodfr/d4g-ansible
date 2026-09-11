<?php
require_once __DIR__ . '/lib.php';

admin_session_start();
admin_logout();
admin_redirect('index.php');
