<?php
require_once '../config/config.php';
require_once '../views/layouts/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - NGAARY SHOP</title>

    <!-- BOOTSTRAP 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- HERO -->
<section class="contact-hero">
    <div class="container">
        <div class="section-sub">On est là pour vous</div>
        <h1 class="section-title">Contactez-nous</h1>
        <p class="mt-3 text-white-50" style="max-width: 500px;">
            Une question sur votre commande, un produit, ou juste envie de nous dire bonjour ? On vous répond sous 24h.
        </p>
    </div>
</section>

<!-- CARTES INFO -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <div class="info-card">
                    <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <h6 class="fw-bold mb-1">Notre adresse</h6>
                    <p class="text-muted small mb-0">Dakar, Sénégal<br>Keur Massar</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card">
                    <div class="info-icon"><i class="bi bi-whatsapp"></i></div>
                    <h6 class="fw-bold mb-1">WhatsApp / Téléphone</h6>
                    <p class="text-muted small mb-0">+221 77 000 00 00<br>Lun – Sam, 8h à 20h</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card">
                    <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                    <h6 class="fw-bold mb-1">Email</h6>
                    <p class="text-muted small mb-0">ngarrryyoot@ngaaryshop.sn<br>Réponse sous 24h</p>
                </div>
            </div>

        </div>

        <!-- FORMULAIRE + HORAIRES -->
        <div class="row g-5">

            <!-- FORMULAIRE -->
            <div class="col-lg-7">
                <div class="contact-card">
                    <h4 class="font-serif fw-bold mb-1">Envoyez-nous un message</h4>
                    <p class="text-muted small mb-4">Tous les champs sont obligatoires.</p>

                    <form id="contactForm" action="<?= BASE_URL ?>/send_contact.php" method="POST" novalidate>
                        <?php
                            // Protection CSRF
                            if (empty($_SESSION['csrf_token'])) {
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            }
                        ?>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control" placeholder="Fatou" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" placeholder="Diallo" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="fatou@gmail.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sujet</label>
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
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Votre message…" required></textarea>
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-send me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">

                <!-- HORAIRES -->
                <div class="info-card mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock text-success me-2"></i>Nos horaires</h6>
                    <div class="horaire-item">
                        <span>Lundi – Vendredi</span>
                        <span class="fw-semibold text-success">8h – 20h</span>
                    </div>
                    <div class="horaire-item">
                        <span>Samedi</span>
                        <span class="fw-semibold text-success">9h – 18h</span>
                    </div>
                    <div class="horaire-item">
                        <span>Dimanche</span>
                        <span class="fw-semibold text-muted">Fermé</span>
                    </div>
                </div>

                <!-- FAQ RAPIDE -->
                <div class="info-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-question-circle text-success me-2"></i>Questions fréquentes</h6>

                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Quel est le délai de livraison ?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    Livraison sous 24 à 48h à Dakar, et 3 à 5 jours pour les autres régions du Sénégal.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Comment retourner un produit ?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    Contactez-nous dans les 7 jours suivant la réception. Le produit doit être intact et dans son emballage d'origine.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Quels sont les modes de paiement ?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-0 pt-0 small text-muted">
                                    Nous acceptons Wave, Orange Money, Free Money et le paiement à la livraison en FCFA.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function showToast(message) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-ngaary';
        toast.innerHTML = `<i class="bi bi-check-circle-fill text-success fs-5"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    // Soumission du formulaire avec toast
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showToast('Message envoyé ! On vous répond sous 24h 🌿');
        this.reset();
    });
</script>

<?php require_once '../views/layouts/footer.php'; ?>
</body>
</html>