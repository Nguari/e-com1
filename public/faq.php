<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'FAQ - NGAARY SHOP';
$currentPage = 'faq.php';

include __DIR__ . '/../views/layouts/header.php';
?>

<style>
    .font-serif { font-family: 'Playfair Display', serif; }

    .faq-hero {
        background: linear-gradient(135deg, #0d2818 0%, #1a6b35 100%);
        padding: 80px 0;
    }

    .faq-card {
        background: white;
        border-radius: 18px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
    }

    .faq-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 30px rgba(22,163,74,0.12);
    }

    .faq-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: #f0faf3;
        color: #16a34a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 16px;
    }

    .faq-item {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(22,163,74,0.08);
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }

    .faq-accordion .accordion-button {
        background: white;
        color: #0d2818;
        font-weight: 600;
        padding: 1.1rem 1.25rem;
        border: none;
    }

    .faq-accordion .accordion-button:not(.collapsed) {
        background: #f0faf3;
        color: #0d2818;
        box-shadow: none;
    }

    .faq-accordion .accordion-button::after {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2316a34a' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
    }

    .faq-accordion .accordion-body {
        background: #fff;
        color: #475569;
        font-size: 0.96rem;
        line-height: 1.7;
        padding: 0 1.25rem 1.2rem 1.25rem;
    }

    .faq-cta {
        background: linear-gradient(135deg, #1a4731 0%, #16a34a 100%);
        border-radius: 24px;
        padding: 40px 30px;
        color: white;
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
</style>

<section class="faq-hero text-white">
    <div class="container">
        <div class="text-uppercase small fw-bold text-success mb-2" style="letter-spacing: 3px;">Aide & support</div>
        <h1 class="display-4 fw-bold font-serif mb-3">Foire aux questions</h1>
        <p class="text-white-50 mb-0" style="max-width: 650px;">
            Tout ce qu’il faut savoir sur les commandes, les livraisons, les paiements et les retours chez NGAARY SHOP.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-3 reveal reveal-delay-1">
                <div class="faq-card p-4 text-center">
                    <div class="faq-icon mx-auto"><i class="bi bi-truck"></i></div>
                    <h6 class="fw-bold mb-2">Livraison</h6>
                    <p class="text-muted small mb-0">Suivi rapide et livraison partout au Sénégal.</p>
                </div>
            </div>
            <div class="col-md-3 reveal reveal-delay-2">
                <div class="faq-card p-4 text-center">
                    <div class="faq-icon mx-auto"><i class="bi bi-credit-card-2-front"></i></div>
                    <h6 class="fw-bold mb-2">Paiement</h6>
                    <p class="text-muted small mb-0">Wave, Orange Money et paiement à la livraison.</p>
                </div>
            </div>
            <div class="col-md-3 reveal reveal-delay-3">
                <div class="faq-card p-4 text-center">
                    <div class="faq-icon mx-auto"><i class="bi bi-arrow-repeat"></i></div>
                    <h6 class="fw-bold mb-2">Retours</h6>
                    <p class="text-muted small mb-0">Politique claire pour les produits non conformes.</p>
                </div>
            </div>
            <div class="col-md-3 reveal reveal-delay-1">
                <div class="faq-card p-4 text-center">
                    <div class="faq-icon mx-auto"><i class="bi bi-shield-check"></i></div>
                    <h6 class="fw-bold mb-2">Sécurité</h6>
                    <p class="text-muted small mb-0">Transactions sécurisées et commandes suivies.</p>
                </div>
            </div>
        </div>

        <div class="faq-item reveal">
            <div class="accordion faq-accordion" id="faqAccordion">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Quels sont les délais de livraison ?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Les commandes sont préparées sous 24 à 48 heures. La livraison à Dakar est généralement réalisée en 24 à 72 heures, tandis que les autres régions du Sénégal prennent entre 3 et 5 jours selon la zone de livraison.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Quelles sont les méthodes de paiement acceptées ?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Nous acceptons les paiements via Wave, Orange Money, paiement à la livraison et paiement en ligne selon les options affichées au moment du checkout. Nous vous recommandons de vérifier les moyens de paiement disponibles selon votre localisation.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Puis-je modifier ou annuler ma commande ?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Oui, pour autant que la commande n’ait pas encore été traitée ou expédiée. Contactez-nous au plus vite via WhatsApp ou le formulaire de contact pour demander une modification ou une annulation. Une fois la commande envoyée, la modification n’est plus possible.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Comment retourner un produit défectueux ou incorrect ?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Si le produit reçu est endommagé, incorrect ou défectueux, merci de nous contacter dans les 48 heures suivant la livraison avec une photo claire du produit et de l’emballage. Nous vous guiderons pour un échange ou un remboursement selon le cas.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Proposez-vous des promotions ou réductions ?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Oui. Nous publions régulièrement des offres promotionnelles et des remises sur certains produits. Vous pouvez suivre les nouveautés sur la page d’accueil ou dans la section promotions de notre boutique.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            Comment créer un compte ou me connecter ?
                        </button>
                    </h2>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Vous pouvez créer un compte depuis la page d’inscription. Une fois enregistré, vous pourrez suivre vos commandes, gérer vos favoris et accéder plus facilement à votre panier et à vos informations.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 border-top">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                            Les offres sont-elles réservées aux clients du Sénégal ?
                        </button>
                    </h2>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Nos offres sont surtout destinées à la clientèle sénégalaise, mais certaines commandes peuvent être prises en charge selon la zone de livraison et la disponibilité des produits. Pour un cas particulier, nous vous invitons à nous contacter.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 reveal reveal-delay-1">
            <div class="faq-cta d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-uppercase small fw-bold text-white-50 mb-2" style="letter-spacing: 2px;">Besoin d’aide ?</div>
                    <h4 class="font-serif mb-0">Notre équipe répond rapidement.</h4>
                </div>
                <a href="<?= url('contact.php') ?>" class="btn btn-light px-4 py-2 fw-semibold rounded-pill">
                    <i class="bi bi-headset me-2"></i>Nous contacter
                </a>
            </div>
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
