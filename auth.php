<?php
require_once 'config.php';
require_once 'functions.php';

initializeLanguage();

if (!isLoggedIn()) {
    redirect('index.php');
}
?>
