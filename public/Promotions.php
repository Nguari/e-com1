<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle   = 'Promotions - NGAARY SHOP';
$currentPage = 'promotions.php';

include __DIR__ . '/../views/layouts/header.php';

// Produits en promotion statiques
$promotions = [
    ['nom' => 'Chaise en Chêne',       'prix' => 30000, 'prix_ancien' => 37500, 'remise' => 20, 'categorie' => 'Mobilier',    'note' => 4, 'expire' => '2025-04-30'],
    ['nom' => 'Lampe en Rotin',        'prix' => 18500, 'prix_ancien' => 20500, 'remise' => 10, 'categorie' => 'Décoration',  'note' => 4, 'expire' => '2025-04-15'],
    ['nom' => 'Vase Terracotta',       'prix' => 15000, 'prix_ancien' => 18000, 'remise' => 17, 'categorie' => 'Décoration',  'note' => 4, 'expire' => '2025-05-01'],
    ['nom' => 'Tapis Wax Géométrique', 'prix' => 22000, 'prix_ancien' => 25000, 'remise' => 12, 'categorie' => 'Textile',     'note' => 4, 'expire' => '2025-04-20'],
    ['nom' => 'Coussin Wax Coloré',    'prix' => 8500,  'prix_ancien' => 10000, 'remise' => 15, 'categorie' => 'Textile',     'note' => 5, 'expire' => '2025-04-25'],
    ['nom' => 'Plateau Osier Naturel', 'prix' => 9500,  'prix_ancien' => 12000, 'remise' => 21, 'categorie' => 'Accessoires', 'note' => 3, 'expire' => '2025-05-05'],
];
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    /* HERO */
    .promo-hero {
        background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%);
        padding: 70px 0;
        position: relative;
        overflow: hidden;
    }
    .promo-hero::before {
        content: '%';
        position: absolute;
        right: -30px;
        top: -40px;
        font-size: 20rem;
        font-weight: 900;
        color: rgba(255,255,255,0.05);
        font-family: 'Playfair Display', serif;
        line-height: 1;
    }

    /* BANNIÈRE CODE PROMO */
    .code-banner {
        background: linear-gradient(135deg, #0d2818, #16a34a);
        border-radius: 20px;
        padding: 40px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .code-banner::after {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .code-promo {
        background: rgba(255,255,255,0.15);
        border: 2px dashed rgba(255,255,255,0.5);
        border-radius: 12px;
        padding: 12px 24px;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 4px;
        display: inline-block;
        cursor: pointer;
        transition: background 0.2s;
    }
    .code-promo:hover { background: rgba(255,255,255,0.25); }

    /* COMPTE À REBOURS */
    .countdown { display: flex; gap: 16px; justify-content: center; }
    .countdown-item {
        background: rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 12px 16px;
        text-align: center;
        min-width: 70px;
    }
    .countdown-number { font-size: 1.8rem; font-weight: 800; line-height: 1; display: block; }
    .countdown-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; }

    /* CARTES PROMO */
    .promo-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
    }
    .promo-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.14); }
    .promo-card-img {
        background: linear-gradient(135deg, #fef2f2, #ffe4e6);
        padding: 30px;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .promo-card-img i { font-size: 4rem; color: #fca5a5; transition: transform 0.3s; }
    .promo-card:hover .promo-card-img i { transform: scale(1.1); }

    /* BADGE REMISE */
    .badge-remise {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #dc2626;
        color: white;
        font-weight: 800;
        font-size: 1rem;
        border-radius: 50%;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(220,38,38,0.4);
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.4); }
        50%       { box-shadow: 0 0 0 10px rgba(220,38,38,0); }
    }

    /* EXPIRE */
    .expire-badge {
        font-size: 0.72rem;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 6px;
        padding: 3px 8px;
        font-weight: 600;
    }

    /* BOUTONS */
    .btn-cart { background-color: #16a34a; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; width: 100%; transition: background-color 0.2s, transform 0.2s; }
    .btn-cart:hover { background-color: #15803d; color: white; transform: translateY(-1px); }
    .btn-wishlist { background: white; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: #0d2818; transition: color 0.2s, transform 0.2s; cursor: pointer; }
    .btn-wishlist:hover { color: #e11d48; transform: scale(1.2); }

    /* SECTION LABEL */
    .section-label { color: #dc2626; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 600; display: block; margin-bottom: 6px; }
    .section-divider { height: 3px; width: 50px; background: linear-gradient(90deg, #dc2626, #f87171); margin-top: 6px; border-radius: 2px; }

    /* ANIMATIONS */
    .reveal { opacity: 0; transform: translateY(25px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.05s; }
    .reveal-delay-2 { transition-delay: 0.1s; }
    .reveal-delay-3 { transition-delay: 0.15s; }
    .reveal-delay-4 { transition-delay: 0.2s; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .hero-anim-1 { animation: fadeInUp 0.6s ease both; }
    .hero-anim-2 { animation: fadeInUp 0.6s ease 0.15s both; }
    .hero-anim-3 { animation: fadeInUp 0.6s ease 0.3s both; }
    .hero-anim-4 { animation: fadeInUp 0.6s ease 0.45s both; }
</style>

<!-- ====== HERO ====== -->
<section class="promo-hero text-white text-center">
    <div class="container">
        <div class="hero-anim-1">
            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-3 d-inline-block" style="font-size: 0.8rem; letter-spacing: 2px;">
                🔥 OFFRES LIMITÉES
            </span>
        </div>
        <h1 class="hero-anim-2 display-4 font-serif fw-bold mb-3">Nos Promotions</h1>
        <p class="hero-anim-3 lead opacity-75 mb-4">Des remises exceptionnelles sur une sélection de produits. Profitez-en avant qu'il ne soit trop tard !</p>

        <!-- COMPTE À REBOURS -->
        <div class="hero-anim-4">
            <p class="small opacity-75 mb-3 text-uppercase fw-bold" style="letter-spacing: 2px;">⏳ Les offres expirent dans</p>
            <div class="countdown justify-content-center">
                <div class="countdown-item">
                    <span class="countdown-number" id="days">00</span>
                    <span class="countdown-label">Jours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="hours">00</span>
                    <span class="countdown-label">Heures</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="minutes">00</span>
                    <span class="countdown-label">Min</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="seconds">00</span>
                    <span class="countdown-label">Sec</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== BANNIÈRE CODE PROMO ====== -->
<section class="container py-5 reveal">
    <div class="code-banner">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <span class="text-uppercase small fw-bold opacity-75" style="letter-spacing: 2px;">🎁 Code exclusif</span>
                <h3 class="font-serif fw-bold mt-1 mb-2">-15% sur votre première commande</h3>
                <p class="opacity-75 mb-0">Copiez le code et collez-le à la caisse. Valable sur tous les articles de la boutique.</p>
            </div>
            <div class="col-md-5 text-center">
                <p class="small opacity-75 mb-2">Cliquez pour copier</p>
                <div class="code-promo" onclick="copierCode(this)" title="Cliquer pour copier">
                    NGAARY15
                </div>
                <p class="small opacity-75 mt-2 mb-0" id="copy-msg" style="display:none;">✅ Code copié !</p>
            </div>
        </div>
    </div>
</section>

<!-- ====== PRODUITS EN PROMO ====== -->
<section class="pb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 reveal">
            <div>
                <span class="section-label">🔥 Économisez maintenant</span>
                <h2 class="font-serif mb-0">Produits en Promotion</h2>
                <div class="section-divider"></div>
            </div>
            <span class="text-muted small"><?= count($promotions) ?> offres disponibles</span>
        </div>

        <div class="row g-4">
            <?php foreach ($promotions as $idx => $produit) : ?>
            <div class="col-6 col-lg-4 reveal reveal-delay-<?= ($idx % 4) + 1 ?>">
                <div class="card promo-card h-100">
                    <div class="promo-card-img">
                        <!-- BADGE REMISE -->
                        <div class="badge-remise">-<?= $produit['remise'] ?>%</div>
                        <!-- WISHLIST -->
                        <button class="btn-wishlist position-absolute top-0 end-0 m-2">
                            <i class="bi bi-heart"></i>
                        </button>
                        <i class="bi bi-image"></i>
                    </div>
                    <div class="card-body d-flex flex-column px-3 py-3">
                        <small class="text-muted mb-1" style="font-size: 0.75rem;"><?= $produit['categorie'] ?></small>
                        <h5 class="card-title h6 mb-1 fw-semibold"><?= htmlspecialchars($produit['nom']) ?></h5>

                        <!-- ÉTOILES -->
                        <div class="mb-2">
                            <?php for ($j = 1; $j <= 5; $j++) : ?>
                                <i class="bi bi-star<?= $j <= $produit['note'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size: 0.7rem;"></i>
                            <?php endfor; ?>
                        </div>

                        <!-- DATE EXPIRATION -->
                        <div class="mb-2">
                            <span class="expire-badge">
                                <i class="bi bi-clock me-1"></i>Expire le <?= date('d/m/Y', strtotime($produit['expire'])) ?>
                            </span>
                        </div>

                        <!-- PRIX -->
                        <div class="mt-auto">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-danger fw-bold fs-5"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="text-muted text-decoration-line-through small"><?= number_format($produit['prix_ancien'], 0, ',', ' ') ?> FCFA</span>
                                <span class="text-success small fw-semibold">
                                    Économie : <?= number_format($produit['prix_ancien'] - $produit['prix'], 0, ',', ' ') ?> FCFA
                                </span>
                            </div>
                            <button class="btn-cart">
                                <i class="bi bi-cart-plus me-1"></i>Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====== BANNIÈRE FINALE ====== -->
<section class="container pb-5 reveal">
    <div class="bg-dark text-white rounded-4 p-5 text-center">
        <h3 class="font-serif fw-bold mb-2">Vous voulez être alerté des prochaines promos ?</h3>
        <p class="opacity-75 mb-4">Inscrivez-vous à notre newsletter et recevez nos offres en exclusivité.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="email" class="form-control bg-white border-0 rounded-start-3" placeholder="votre@email.com">
                    <button class="btn btn-success px-4 rounded-end-3 fw-semibold">
                        <i class="bi bi-bell me-1"></i>M'alerter
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SCROLL REVEAL + COMPTE À REBOURS + COPIER CODE -->
<script>
    // Scroll Reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // Compte à rebours (30 jours)
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 30);

    function updateCountdown() {
        const now  = new Date();
        const diff = endDate - now;

        if (diff <= 0) {
            document.getElementById('days').textContent    = '00';
            document.getElementById('hours').textContent   = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }

        const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('days').textContent    = String(days).padStart(2, '0');
        document.getElementById('hours').textContent   = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    // Copier code promo
    function copierCode(el) {
        navigator.clipboard.writeText('NGAARY15').then(() => {
            const msg = document.getElementById('copy-msg');
            msg.style.display = 'block';
            setTimeout(() => msg.style.display = 'none', 2500);
        });
    }
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>