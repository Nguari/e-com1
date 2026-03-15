<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle   = 'Accueil - NGAARY SHOP';
$currentPage = 'index.php';

include __DIR__ . '/../views/layouts/header.php';

// Produits statiques en attendant la BDD
$produits = [
    ['id_produit' => 1, 'nom' => 'Chaise en Chêne',       'prix' => 30000, 'prix_ancien' => 37500, 'badge' => '-20%',    'badge_color' => 'danger',  'categorie' => 'Mobilier',    'note' => 4],
    ['id_produit' => 2, 'nom' => 'Table Basse Bambou',     'prix' => 45000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Mobilier',    'note' => 5],
    ['id_produit' => 3, 'nom' => 'Lampe en Rotin',         'prix' => 18500, 'prix_ancien' => 20500, 'badge' => '-10%',    'badge_color' => 'danger',  'categorie' => 'Décoration',  'note' => 4],
    ['id_produit' => 4, 'nom' => 'Panier Tressé',          'prix' => 12000, 'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Accessoires', 'note' => 5],
    ['id_produit' => 5, 'nom' => 'Miroir Bois Flotté',     'prix' => 28000, 'prix_ancien' => null,  'badge' => 'Nouveau', 'badge_color' => 'success', 'categorie' => 'Décoration',  'note' => 4],
    ['id_produit' => 6, 'nom' => 'Coussin Wax Coloré',     'prix' => 8500,  'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Textile',     'note' => 5],
    ['id_produit' => 7, 'nom' => 'Vase Terracotta',        'prix' => 15000, 'prix_ancien' => 18000, 'badge' => '-17%',    'badge_color' => 'danger',  'categorie' => 'Décoration',  'note' => 4],
    ['id_produit' => 8, 'nom' => 'Plateau Osier Naturel',  'prix' => 9500,  'prix_ancien' => null,  'badge' => null,      'badge_color' => null,      'categorie' => 'Accessoires', 'note' => 3],
];

$categories = [
    ['nom' => 'Mobilier',    'icone' => 'bi-house-heart', 'nb' => 24],
    ['nom' => 'Décoration',  'icone' => 'bi-stars',       'nb' => 38],
    ['nom' => 'Textile',     'icone' => 'bi-bag-heart',   'nb' => 17],
    ['nom' => 'Accessoires', 'icone' => 'bi-basket',      'nb' => 21],
];

$avis = [
    ['nom' => 'Aminata D.', 'note' => 5, 'texte' => 'Qualité irréprochable, livraison rapide à Dakar. Je recommande !'],
    ['nom' => 'Moussa K.',  'note' => 5, 'texte' => 'Le panier tressé est magnifique, exactement comme sur la photo.'],
    ['nom' => 'Fatou B.',   'note' => 4, 'texte' => 'Très bons produits, le service client est réactif et sympa.'],
];

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes floating {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-15px); }
    }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4); }
        50%       { box-shadow: 0 0 0 12px rgba(22, 163, 74, 0); }
    }

    .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
    .reveal-delay-4 { transition-delay: 0.4s; }

    .hero-section { background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%); padding: 100px 0; }
    .hero-text     { animation: fadeInLeft 0.9s ease both; }
    .hero-image    { animation: fadeInRight 0.9s ease 0.3s both; }
    .hero-badge    { animation: fadeInUp 0.6s ease both; }
    .hero-title    { animation: fadeInUp 0.7s ease 0.1s both; }
    .hero-desc     { animation: fadeInUp 0.7s ease 0.2s both; }
    .hero-btns     { animation: fadeInUp 0.7s ease 0.3s both; }
    .hero-stats    { animation: fadeInUp 0.7s ease 0.4s both; }

    .btn-vivid { background-color: #16a34a; color: white; border: none; border-radius: 50px; font-weight: 600; letter-spacing: 1px; transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s; animation: pulse-green 2.5s infinite; }
    .btn-vivid:hover { background-color: #15803d; color: white; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(22,163,74,0.4); animation: none; }

    .section-label { color: #16a34a; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 600; display: block; margin-bottom: 6px; }
    .section-divider { height: 3px; width: 50px; background: linear-gradient(90deg, #16a34a, #4ade80); margin-top: 6px; border-radius: 2px; }

    .avantage-item { background: white; border-radius: 14px; padding: 20px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.05); transition: transform 0.3s, box-shadow 0.3s; }
    .avantage-item:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(22,163,74,0.15); }
    .avantage-icon { font-size: 1.8rem; color: #16a34a; margin-bottom: 8px; transition: transform 0.3s; }
    .avantage-item:hover .avantage-icon { transform: scale(1.2) rotate(-5deg); }

    .categorie-card { background: white; border-radius: 16px; padding: 28px 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.3s, box-shadow 0.3s, background 0.3s; color: #0d2818; }
    .categorie-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(22,163,74,0.15); background: #f0faf3; }
    .categorie-icon { font-size: 2.2rem; color: #16a34a; margin-bottom: 12px; transition: transform 0.3s; }
    .categorie-card:hover .categorie-icon { transform: scale(1.2); }

    .product-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,0.14); }
    .product-img { background-color: #e8f5ee; padding: 30px; position: relative; min-height: 180px; display: flex; align-items: center; justify-content: center; }
    .product-img i { font-size: 4rem; color: #a7c9b3; transition: transform 0.3s; }
    .product-card:hover .product-img i { transform: scale(1.1); }
    .btn-wishlist { background: white; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: #0d2818; transition: color 0.2s, transform 0.2s; cursor: pointer; }
    .btn-wishlist:hover { color: #e11d48; transform: scale(1.2); }
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; transition: background-color 0.2s, transform 0.2s; cursor: pointer; }
    .btn-cart:hover { background-color: #15803d; color: white; transform: translateY(-1px); }

    .promo-banner { background: linear-gradient(135deg, #1a4731, #16a34a); border-radius: 20px; color: white; padding: 50px 40px; transition: transform 0.3s, box-shadow 0.3s; }
    .promo-banner:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(22,163,74,0.3); }

    .avis-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); height: 100%; transition: transform 0.3s, box-shadow 0.3s; }
    .avis-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .avis-avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #16a34a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }
</style>

<!-- ====== HERO ====== -->
<section class="hero-section text-white">
    <div class="container">
        <div class="row align-items-center">

            <!-- TEXTE GAUCHE -->
            <div class="col-lg-6 text-lg-start text-center hero-text">
                <span class="hero-badge text-success text-uppercase small fw-bold" style="letter-spacing: 3px;">✦ Nouvelle Collection 2025</span>
                <h1 class="hero-title display-3 font-serif my-4 lh-sm">Naturellement<br><span style="color: #4ade80;">élégant</span></h1>
                <p class="hero-desc lead opacity-75 mb-4">Des objets artisanaux durables, fabriqués avec soin pour embellir votre quotidien — à prix juste.</p>
                <div class="hero-btns d-flex gap-3 flex-wrap justify-content-lg-start justify-content-center mb-5">
                    <a href="<?= url('boutique.php') ?>" class="btn btn-vivid px-4 py-2 shadow">
                        <i class="bi bi-bag me-2"></i>VOIR LA BOUTIQUE
                    </a>
                    <a href="<?= url('contact.php') ?>" class="btn btn-outline-light px-4 py-2 rounded-pill">
                        NOTRE HISTOIRE
                    </a>
                </div>
                <div class="hero-stats d-flex gap-4 justify-content-lg-start justify-content-center">
                    <div>
                        <div class="h4 fw-bold mb-0" style="color: #4ade80;">+1 200</div>
                        <div class="small opacity-75">Clients satisfaits</div>
                    </div>
                    <div class="border-start border-secondary ps-4">
                        <div class="h4 fw-bold mb-0" style="color: #4ade80;">100+</div>
                        <div class="small opacity-75">Produits disponibles</div>
                    </div>
                    <div class="border-start border-secondary ps-4">
                        <div class="h4 fw-bold mb-0" style="color: #4ade80;">4.9★</div>
                        <div class="small opacity-75">Note moyenne</div>
                    </div>
                </div>
            </div>

            <!-- IMAGE DROITE -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center hero-image">
                <div style="width: 100%; border-radius: 24px; overflow: hidden;
                            border: 2px dashed rgba(255,255,255,0.1);
                            background: rgba(255,255,255,0.05);
                            animation: floating 3s ease-in-out infinite;">

                    <img src="<?= URL_ROOT ?>/imgs/herologo.png"
                         alt="NGAARY SHOP - La qualité à petit prix"
                         style="width: 100%; max-height: 400px; object-fit: cover; display: block;"
                         onerror="this.style.display='none'; document.getElementById('hero-placeholder').style.display='flex';">

                    <div id="hero-placeholder"
                         style="display: none; width: 100%; padding: 60px;
                                align-items: center; justify-content: center; text-align: center;">
                        <i class="bi bi-image" style="font-size: 8rem; color: rgba(255,255,255,0.15);"></i>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- ====== FLASH MESSAGES ====== -->
<?php if ($flashSuccess || $flashError) : ?>
<div class="container mt-4">
    <?php if ($flashSuccess) : ?>
        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 py-2">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($flashError) : ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ====== AVANTAGES ====== -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3 reveal reveal-delay-1">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-truck"></i></div>
                    <p class="fw-semibold small mb-0">Livraison gratuite</p>
                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Dès <?= formatFCFA(15000) ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-2">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-arrow-return-left"></i></div>
                    <p class="fw-semibold small mb-0">Retour facile</p>
                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Sous 7 jours</p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-3">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-shield-check"></i></div>
                    <p class="fw-semibold small mb-0">Paiement sécurisé</p>
                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Wave, Orange Money</p>
                </div>
            </div>
            <div class="col-6 col-md-3 reveal reveal-delay-4">
                <div class="avantage-item">
                    <div class="avantage-icon"><i class="bi bi-headset"></i></div>
                    <p class="fw-semibold small mb-0">Support client</p>
                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Lun – Sam, 8h–20h</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== CATÉGORIES ====== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <span class="section-label">Explorez</span>
            <h2 class="font-serif">Nos Catégories</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $i => $cat) : ?>
            <div class="col-6 col-md-3 reveal reveal-delay-<?= $i + 1 ?>">
                <a href="<?= url('boutique.php?categorie=' . urlencode($cat['nom'])) ?>" class="text-decoration-none">
                    <div class="categorie-card text-center">
                        <div class="categorie-icon"><i class="bi <?= $cat['icone'] ?>"></i></div>
                        <h6 class="fw-bold mb-1"><?= $cat['nom'] ?></h6>
                        <small class="text-muted"><?= $cat['nb'] ?> articles</small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====== MEILLEURES VENTES ====== -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 reveal">
            <div>
                <span class="section-label">Tendances</span>
                <h2 class="font-serif mb-0">Nos Meilleures Ventes</h2>
                <div class="section-divider"></div>
            </div>
            <a href="<?= url('boutique.php') ?>" class="text-success text-decoration-none small fw-bold">VOIR TOUT →</a>
        </div>

        <div class="row g-4">
            <?php foreach ($produits as $idx => $produit) : ?>
            <div class="col-6 col-lg-3 reveal reveal-delay-<?= ($idx % 4) + 1 ?>">
                <div class="card product-card h-100">
                    <div class="product-img">
                        <?php if ($produit['badge']) : ?>
                            <span class="badge bg-<?= $produit['badge_color'] ?> position-absolute top-0 start-0 m-2">
                                <?= htmlspecialchars($produit['badge']) ?>
                            </span>
                        <?php endif; ?>
                        <button type="button" class="btn-wishlist position-absolute top-0 end-0 m-2">
                            <i class="bi bi-heart"></i>
                        </button>
                        <i class="bi bi-image"></i>
                    </div>
                    <div class="card-body d-flex flex-column px-3 py-3">
                        <small class="text-muted mb-1" style="font-size: 0.75rem;"><?= $produit['categorie'] ?></small>
                        <h5 class="card-title h6 mb-1 fw-semibold"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <div class="mb-2">
                            <?php for ($j = 1; $j <= 5; $j++) : ?>
                                <i class="bi bi-star<?= $j <= $produit['note'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size: 0.7rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="text-success fw-bold"><?= formatFCFA($produit['prix']) ?></span>
                                <?php if (!empty($produit['prix_ancien'])) : ?>
                                    <span class="text-muted text-decoration-line-through small">
                                        <?= formatFCFA($produit['prix_ancien']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <form action="<?= url('cart_add.php') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                <input type="hidden" name="quantite"   value="1">
                                <button type="submit" class="btn-cart">
                                    <i class="bi bi-cart-plus me-1"></i>Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====== BANNIÈRE PROMO ====== -->
<section class="container py-5 reveal">
    <div class="promo-banner">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <span class="text-uppercase small fw-bold opacity-75" style="letter-spacing: 2px;">⏳ Offre limitée</span>
                <h3 class="font-serif mt-1 mb-2 fw-bold">-15% sur votre première commande</h3>
                <p class="opacity-75 mb-0">
                    Utilisez le code <strong class="text-white">NGAARY15</strong> à la caisse.<br>
                    Livraison gratuite dès <?= formatFCFA(15000) ?> partout au Sénégal.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= url('boutique.php') ?>" class="btn btn-light fw-bold px-4 py-3 rounded-pill text-success fs-6">
                    J'en profite →
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ====== AVIS CLIENTS ====== -->
<section class="pb-5">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <span class="section-label">Ils nous font confiance</span>
            <h2 class="font-serif">Avis Clients</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($avis as $i => $a) : ?>
            <div class="col-md-4 reveal reveal-delay-<?= $i + 1 ?>">
                <div class="avis-card">
                    <div class="mb-2">
                        <?php for ($j = 1; $j <= 5; $j++) : ?>
                            <i class="bi bi-star<?= $j <= $a['note'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-3 fst-italic">"<?= htmlspecialchars($a['texte']) ?>"</p>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avis-avatar"><?= strtoupper(substr($a['nom'], 0, 1)) ?></div>
                        <strong class="small"><?= htmlspecialchars($a['nom']) ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>