<?php
use App\Utils\Session;
use App\Config\Database;

require_once dirname(__DIR__) . '/config/config.php';

// =========================================
// 1. MÉTHODE HTTP
// =========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('contact.php'));
    exit();
}

// =========================================
// 2. VÉRIFICATION CSRF
// =========================================
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    error_log('[send_contact] Token CSRF invalide.');
    Session::flash('contact_error', 'Erreur de sécurité. Veuillez réessayer.');
    header('Location: ' . url('contact.php'));
    exit();
}

// Régénérer le token après usage
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// =========================================
// 3. RÉCUPÉRATION DES DONNÉES
// =========================================
$prenom  = trim($_POST['prenom']  ?? '');
$nom     = trim($_POST['nom']     ?? '');
$email   = trim($_POST['email']   ?? '');
$sujet   = trim($_POST['sujet']   ?? '');
$message = trim($_POST['message'] ?? '');

// =========================================
// 4. VALIDATION
// =========================================
$erreurs = [];

if (empty($prenom)) {
    $erreurs[] = 'Le prénom est obligatoire.';
}

if (empty($nom)) {
    $erreurs[] = 'Le nom est obligatoire.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'L\'adresse email est invalide.';
}

if (empty($sujet)) {
    $erreurs[] = 'Le sujet est obligatoire.';
}

if (empty($message)) {
    $erreurs[] = 'Le message est obligatoire.';
}

if (!empty($erreurs)) {
    Session::flash('contact_error', implode(' ', $erreurs));
    header('Location: ' . url('contact.php'));
    exit();
}

// =========================================
// 5. NETTOYAGE (après validation)
// =========================================
$prenom  = htmlspecialchars($prenom,  ENT_QUOTES, 'UTF-8');
$nom     = htmlspecialchars($nom,     ENT_QUOTES, 'UTF-8');
$email   = filter_var($email,         FILTER_SANITIZE_EMAIL);
$sujet   = htmlspecialchars($sujet,   ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// =========================================
// 6. INSERTION EN BASE DE DONNÉES
// =========================================
try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        INSERT INTO contacts (prenom, nom, email, sujet, message, created_at)
        VALUES (:prenom, :nom, :email, :sujet, :message, NOW())
    ");

    $stmt->execute([
        ':prenom'  => $prenom,
        ':nom'     => $nom,
        ':email'   => $email,
        ':sujet'   => $sujet,
        ':message' => $message,
    ]);

    // =========================================
    // 7. SUCCÈS
    // =========================================
    Session::flash('contact_success', 'Votre message a bien été envoyé ! On vous répond sous 24h 🌿');
    header('Location: ' . url('contact.php'));
    exit();

} catch (\Exception $e) {
    error_log('[send_contact] Erreur BDD : ' . $e->getMessage());
    Session::flash('contact_error', 'Une erreur est survenue. Veuillez réessayer.');
    header('Location: ' . url('contact.php'));
    exit();
}