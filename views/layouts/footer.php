<footer class="bg-dark text-white-50 py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Section À propos -->
            <div class="col-md-4 mb-4 text-center text-md-start">
                <h5 class="text-white font-serif mb-3">NGAARY SHOP</h5>
                <p class="small">Votre destination shopping préférée au Sénégal. Qualité, style et satisfaction garantis.</p>
                <div class="mt-3">
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-tiktok"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            <!-- Section Liens rapides -->
            <div class="col-md-4 mb-4 text-center">
                <h5 class="text-white font-serif mb-3">Liens rapides</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('boutique.php') ?>" class="text-white-50 text-decoration-none hover-text-white">Boutique</a></li>
                    <li class="mb-2"><a href="<?= url('about.php') ?>" class="text-white-50 text-decoration-none hover-text-white">À propos</a></li>
                    <li class="mb-2"><a href="<?= url('contact.php') ?>" class="text-white-50 text-decoration-none hover-text-white">Contact</a></li>
                    <li class="mb-2"><a href="<?= url('faq.php') ?>" class="text-white-50 text-decoration-none hover-text-white">FAQ</a></li>
                </ul>
            </div>

            <!-- Section Contact -->
            <div class="col-md-4 mb-4 text-center text-md-end">
                <h5 class="text-white font-serif mb-3">Contactez-nous</h5>
                <p class="small mb-1"><i class="bi bi-telephone-fill me-2"></i> +221 77 000 00 00</p>
                <p class="small mb-1"><i class="bi bi-envelope-fill me-2"></i> contact@ngaary.sn</p>
                <p class="small"><i class="bi bi-geo-alt-fill me-2"></i> Dakar, Sénégal</p>
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="mt-3 mb-4" style="border-color: rgba(255,255,255,0.1);">

        <!-- Copyright et informations légales -->
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="small mb-0">&copy; <?php echo date('Y'); ?> NGAARY SHOP – Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <ul class="list-inline small mb-0">
                    <li class="list-inline-item"><a href="<?= url('mentions-legales.php') ?>" class="text-white-50 text-decoration-none">Mentions légales</a></li>
                    <li class="list-inline-item mx-2">•</li>
                    <li class="list-inline-item"><a href="<?= url('cgv.php') ?>" class="text-white-50 text-decoration-none">CGV</a></li>
                    <li class="list-inline-item mx-2">•</li>
                    <li class="list-inline-item"><a href="<?= url('livraison.php') ?>" class="text-white-50 text-decoration-none">Livraison</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Badge de paiement sécurisé -->
        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="bi bi-shield-lock-fill me-1"></i> Paiement 100% sécurisé
            </small>
        </div>
    </div>
</footer>

<!-- Styles additionnels pour améliorer l'interaction -->
<style>
    .hover-text-white:hover {
        color: white !important;
        transition: color 0.3s ease;
    }
    
    footer a {
        transition: all 0.3s ease;
    }
    
    footer a:hover {
        color: white !important;
        transform: translateY(-2px);
        display: inline-block;
    }
    
    footer .bi {
        transition: transform 0.3s ease;
    }
    
    footer a:hover .bi {
        transform: scale(1.1);
    }
    
    @media (max-width: 768px) {
        footer .col-md-4 {
            margin-bottom: 2rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- FONCTION TOGGLE SIDEBAR POUR ADMIN -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
    if (overlay) {
        overlay.classList.toggle('show');
    }
}

// Fermer le sidebar au clic sur un lien (mobile)
if (document.querySelectorAll('.sidebar-nav .nav-link')) {
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 991.98) {
                toggleSidebar();
            }
        });
    });
}

// Initialiser les dropdowns Bootstrap
if (typeof bootstrap !== 'undefined') {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
}
</script>

<!-- SIDE CART OVERLAY -->
<div id="sideCartOverlay" class="side-cart-overlay" onclick="closeSideCart()"></div>

<!-- SIDE CART -->
<div id="sideCart" class="side-cart">
    <div class="side-cart-header">
        <h5><i class="bi bi-bag me-2"></i>Mon panier</h5>
        <button class="side-cart-close" onclick="closeSideCart()">&times;</button>
    </div>
    <div id="sideCartBody" class="side-cart-body">
        <div class="side-cart-empty">
            <i class="bi bi-cart-x"></i>
            <p>Votre panier est vide</p>
            <button onclick="closeSideCart()" class="btn btn-outline-success mt-2">Continuer les achats</button>
        </div>
    </div>
    <div id="sideCartFooter" class="side-cart-footer" style="display: none;">
        <div class="side-cart-total">
            <span>Total</span>
            <span id="sideCartTotal">0 FCFA</span>
        </div>
        <button class="side-cart-btn btn-cart-continue" onclick="closeSideCart()">Continuer les achats</button>
        <a href="<?= url('cart.php') ?>" class="side-cart-btn btn-cart-checkout text-center text-white text-decoration-none d-block">Voir le panier</a>
    </div>
</div>

<!-- QUICK VIEW MODAL -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-6 bg-light rounded-start-4 d-flex align-items-center justify-content-center" id="quickViewImage" style="min-height: 300px;">
                        <div class="text-center">
                            <div class="spinner-border text-success" role="status"></div>
                        </div>
                    </div>
                    <div class="col-md-6 p-4" id="quickViewContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-success" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white rounded-circle p-2" data-bs-dismiss="modal" style="z-index: 10;"></button>
        </div>
    </div>
</div>

<script>
// ====== SIDE CART ======
function openSideCart() {
    document.getElementById('sideCart').classList.add('open');
    document.getElementById('sideCartOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
    updateSideCart();
}

function closeSideCart() {
    document.getElementById('sideCart').classList.remove('open');
    document.getElementById('sideCartOverlay').classList.remove('show');
    document.body.style.overflow = '';
}

function updateSideCart() {
    fetch('<?= url('cart_ajax.php') ?>')
        .then(response => response.json())
        .then(data => {
            const sideCartBody = document.getElementById('sideCartBody');
            const sideCartFooter = document.getElementById('sideCartFooter');
            const cartCountSpan = document.getElementById('cartCount');
            
            if (data.items.length === 0) {
                sideCartBody.innerHTML = `
                    <div class="side-cart-empty">
                        <i class="bi bi-cart-x"></i>
                        <p>Votre panier est vide</p>
                        <button onclick="closeSideCart()" class="btn btn-outline-success mt-2">Continuer les achats</button>
                    </div>
                `;
                sideCartFooter.style.display = 'none';
                if (cartCountSpan) {
                    cartCountSpan.style.display = 'none';
                    cartCountSpan.textContent = '0';
                }
                return;
            }
            
            let html = '';
            data.items.forEach(item => {
                html += `
                    <div class="side-cart-item" id="cart-item-${item.id}">
                        <div class="side-cart-item-img">
                            <img src="${item.image}" alt="${item.name}" onerror="this.src='<?= url('assets/img/produits/default.jpg') ?>'">
                        </div>
                        <div class="side-cart-item-info">
                            <div class="side-cart-item-title">${escapeHtml(item.name)}</div>
                            <div class="side-cart-item-price">${item.price}</div>
                            <div class="side-cart-item-quantity">
                                <button onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                <span>${item.quantity}</span>
                                <button onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                            </div>
                            <button class="side-cart-item-remove" onclick="removeCartItem(${item.id})">Supprimer</button>
                        </div>
                        <div class="side-cart-item-subtotal">${item.subtotal}</div>
                    </div>
                `;
            });
            
            sideCartBody.innerHTML = html;
            sideCartFooter.style.display = 'block';
            document.getElementById('sideCartTotal').innerHTML = data.total;
            if (cartCountSpan) {
                cartCountSpan.style.display = 'inline-block';
                cartCountSpan.textContent = data.total_items;
            }
            
            // Animation du badge
            const cartIcon = document.querySelector('.cart-icon-badge');
            if (cartIcon) {
                cartIcon.classList.add('cart-bump');
                setTimeout(() => cartIcon.classList.remove('cart-bump'), 300);
            }
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function updateCartQuantity(productId, quantity) {
    if (quantity < 1) {
        removeCartItem(productId);
        return;
    }
    
    fetch('<?= url('cart_update_ajax.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_produit=' + productId + '&quantite=' + quantity + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSideCart();
            updateCartPageQuantity(productId, quantity);
        }
    });
}

function removeCartItem(productId) {
    fetch('<?= url('cart_remove_ajax.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_produit=' + productId + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSideCart();
            location.reload();
        }
    });
}

function updateCartPageQuantity(productId, quantity) {
    const qtyInput = document.querySelector(`.qty-input[data-id="${productId}"]`);
    if (qtyInput) {
        qtyInput.value = quantity;
        qtyInput.form.submit();
    }
}

// ====== QUICK VIEW ======
function quickView(productId) {
    fetch('<?= url('product_quick_view.php') ?>?id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('quickViewImage').innerHTML = `<img src="${data.image}" alt="${data.name}" class="img-fluid rounded-start-4 w-100" style="height: 100%; object-fit: cover;">`;
                document.getElementById('quickViewContent').innerHTML = `
                    <h3 class="fw-bold mb-2">${escapeHtml(data.name)}</h3>
                    <div class="mb-3">
                        <span class="text-success fw-bold fs-4">${data.price}</span>
                        ${data.old_price ? `<span class="text-muted text-decoration-line-through ms-2">${data.old_price}</span>` : ''}
                    </div>
                    <p class="text-muted small mb-3">${escapeHtml(data.description)}</p>
                    <div class="mb-3">
                        <strong>Stock:</strong> ${data.stock > 0 ? '<span class="text-success">En stock</span>' : '<span class="text-danger">Rupture</span>'}
                    </div>
                    ${data.stock > 0 ? `
                    <form action="<?= url('cart_add.php') ?>" method="POST" class="mt-3" onsubmit="closeSideCart()">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id_produit" value="${data.id}">
                        <div class="d-flex gap-2">
                            <input type="number" name="quantite" value="1" min="1" max="${data.stock}" class="form-control form-control-lg text-center" style="width: 80px;">
                            <button type="submit" class="btn btn-success flex-grow-1 fw-semibold">
                                <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                            </button>
                        </div>
                    </form>
                    ` : '<button class="btn btn-secondary w-100" disabled>Indisponible</button>'}
                    <div class="mt-3 text-center">
                        <a href="<?= url('produit.php?id=') ?>${data.id}" class="text-success text-decoration-none small">Voir les détails →</a>
                    </div>
                `;
                const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                modal.show();
            }
        });
}

// Ajouter le bouton quick view sur tous les produits
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-card').forEach(card => {
        const productLink = card.querySelector('a[href*="produit.php"]');
        if (productLink) {
            const urlParams = new URLSearchParams(productLink.href.split('?')[1]);
            const productId = urlParams.get('id');
            if (productId) {
                const quickViewBtn = document.createElement('button');
                quickViewBtn.className = 'quick-view-btn';
                quickViewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Aperçu rapide';
                quickViewBtn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    quickView(productId);
                };
                const productImg = card.querySelector('.product-img');
                if (productImg) {
                    productImg.style.position = 'relative';
                    productImg.appendChild(quickViewBtn);
                }
            }
        }
    });
});
</script>