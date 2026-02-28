
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? 'NGAARY SHOP' ?></title>
    <!-- Bootstrap, fonts, icons... -->
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <?= $content ?>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>