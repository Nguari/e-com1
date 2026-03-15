<?php
use App\Utils\Session;

require_once dirname(__DIR__) . '/config/config.php';

$pageTitle   = 'Contact - NGAARY SHOP';
$currentPage = 'contact.php';

// Protection CSRF
if (empty($_SESSION['csrf_token'])) {
    Session::set('csrf_token', bin2hex(random_bytes(32)));
}

include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    /* HERO */
    .contact-hero { background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%); padding: 80px 0; }

    /* CARTES INFO */
    .info-card { background: white; border-radius: 16px; border: none !important; box-shadow: 0 4px 20px rgba(0,0,0,0.07) !important; transition: transform 0.3s, box-shadow 0.3s; }
    .info-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(22,163,74,0.12) !important; }
    .info-icon { width: 54px; height: 54px; background: #f0faf3; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #16a34a; margin: 0 auto 12px; transition: transform 0.3s; }
    .info-card:hover .info-icon { transform: scale(1.15); }

    /* FORMULAIRE */
    .contact-card { background: white; border-radius: 20px; border: none !important; box-shadow: 0 8px 40px rgba(0,0,0,0.08) !important; }
    .form-control, .form-select { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; background-color: #f8fffe; transition: border-color 0.2s, box-shadow 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,0.12); background: white; }

    /* HORAIRES */
    .horaire-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 0.9rem; }
    .horaire-row:last-child { border-bottom: none; }

    /* ANIMATIONS */
    .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
</style>

<!-- ====== HERO ====== -->
<section class="contact-hero text-white">
    <div class="container">
        <div class="text-uppercase small fw-bold text-success mb-2" style="letter-spacing: 3px;">On est là pour vous</div>
        <h1 class="display-4 fw-bold font-serif mb-3">Contactez-nous</h1>
        <p class="text-white-50 mb-0" style="max-width: 500px;">
            Une question sur votre commande, un produit, ou juste envie de nous dire bonjour ? On vous répond sous 24h.
        </p>
    </div>
</section>

<!-- ====== CARTES INFO ====== -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5 text-center">
            <div class="col-md-4 reveal reveal-delay-1">
                <div class="info-card p-4 h-100">
                    <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <h6 class="fw-bold mb-1">Notre adresse</h6>
                    <p class="text-muted small mb-0">Dakar, Sénégal<br>Keur Massar</p>
                </div>
            </div>
            <div class="col-md-4 reveal reveal-delay-2">
                <div class="info-card p-4 h-100">
                    <div class="info-icon"><i class="bi bi-whatsapp"></i></div>
                    <h6 class="fw-bold mb-1">WhatsApp / Téléphone</h6>
                    <p class="text-muted small mb-0">+221 77 000 00 00<br>Lun – Sam, 8h à 20h</p>
                </div>
            </div>
            <div class="col-md-4 reveal reveal-delay-3">
                <div class="info-card p-4 h-100">
                    <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                    <h6 class="fw-bold mb-1">Email</h6>
                    <p class="text-muted small mb-0">contact@ngaaryshop.sn<br>Réponse sous 24h</p>
                </div>
            </div>
        </div>

        <!-- ====== FORMULAIRE + SIDEBAR ====== -->
        <div class="row g-5">

            <!-- FORMULAIRE -->
            <div class="col-lg-7 reveal">
                <div class="contact-card p-4 p-md-5">
                    <h4 class="font-serif fw-bold mb-1">Envoyez-nous un message</h4>
                    <p class="text-muted small mb-4">Tous les champs marqués * sont obligatoires.</p>

                    <!-- FLASH MESSAGES -->
                    <?php if (Session::hasFlash('contact_success')) : ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><?= Session::getFlash('contact_success') ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (Session::hasFlash('contact_error')) : ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span><?= Session::getFlash('contact_error') ?></span>
                        </div>
                    <?php endif; ?>

                    <form id="contactForm" action="<?= url('send_contact.php') ?>" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="prenom" class="form-control" placeholder="Fatou" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" placeholder="Diallo" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="fatou@gmail.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small">Sujet <span class="text-danger">*</span></label>
                                <select name="sujet" class="form-select" required>
                                    <option value="" disabled selected>Choisissez un sujet...</option>
                                    <option value="commande">Commande & livraison</option>
                                    <option value="retour">Retour & échange</option>
                                    <option value="produit">Produit</option>
                                    <option value="partenariat">Partenariat</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small">Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Votre message…" required></textarea>
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold rounded-3">
                                    <i class="bi bi-send me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-5 reveal reveal-delay-2">

                <!-- HORAIRES -->
                <div class="info-card p-4 mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock text-success me-2"></i>Nos horaires</h6>
                    <div class="horaire-row">
                        <span>Lundi – Vendredi</span>
                        <span class="fw-semibold text-success">8h – 20h</span>
                    </div>
                    <div class="horaire-row">
                        <span>Samedi</span>
                        <span class="fw-semibold text-success">9h – 18h</span>
                    </div>
                    <div class="horaire-row">
                        <span>Dimanche</span>
                        <span class="fw-semibold text-muted">Fermé</span>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="info-card p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-question-circle text-success me-2"></i>FAQ rapide</h6>
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Délai de livraison ?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    24-48h à Dakar, 3-5 jours pour les autres régions du Sénégal.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Modes de paiement ?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    Wave, Orange Money et paiement à la livraison en FCFA.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Comment retourner un produit ?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    Contactez-nous dans les 7 jours suivant la réception, produit intact dans son emballage d'origine.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ====== MAPS ====== -->
<div class="container mb-5 reveal">
    <div class="rounded-4 overflow-hidden shadow" style="height: 350px;">
        <iframe
            src="https://maps.google.com/maps?q=Keur+Massar,+Dakar,+Sénégal&output=embed&z=14"
            width="100%"
            height="100%"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>

<!-- SCROLL REVEAL -->
<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>