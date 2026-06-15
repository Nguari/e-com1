<?php
/**
 * Layout principal de l'application
 * 
 * @var string $content Contenu de la page
 * @var string $pageTitle Titre de la page
 * @var string $currentPage Page courante
 */

// Valeur par défaut pour éviter les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);
$content = $content ?? '';
$pageTitle = $pageTitle ?? 'NGAARY SHOP';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <!-- BOOTSTRAP 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ngaary-green: #16a34a;
            --ngaary-bg:    #f0faf3;
            --ngaary-dark:  #0d2818;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--ngaary-bg);
            color: var(--ngaary-dark);
        }

        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>