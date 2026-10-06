<?php
require __DIR__ . '/header.php';
require __DIR__ . '/sidebar.php';
?>

<div class="main-content">

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="content p-4">
        <?php require $view; ?>
    </div>

    <?php require __DIR__ . '/footer.php'; ?>

</div>
