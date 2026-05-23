<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Service d'envoi d'emails avec PHPMailer
 * 
 * Installation : composer require phpmailer/phpmailer
 * 
 * Variables .env requises :
 * MAIL_HOST=smtp.gmail.com
 * MAIL_PORT=587
 * MAIL_USERNAME=votre@email.com
 * MAIL_PASSWORD=votre_mot_de_passe_app
 * MAIL_FROM=noreply@ngaaryshop.sn
 * MAIL_FROM_NAME=NGAARY SHOP
 */
class MailService {

    private static function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST']     ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(
            $_ENV['MAIL_FROM']      ?? 'noreply@ngaaryshop.sn',
            $_ENV['MAIL_FROM_NAME'] ?? 'NGAARY SHOP'
        );

        return $mail;
    }

    // =========================================
    // CONFIRMATION DE COMMANDE
    // =========================================
    public static function sendOrderConfirmation(array $commande, array $lignes, string $emailClient): bool {
        try {
            $mail = self::createMailer();
            $mail->addAddress($emailClient);
            $mail->isHTML(true);
            $mail->Subject = "Commande confirmée - " . $commande['numero_commande'];
            $mail->Body    = self::templateOrderConfirmation($commande, $lignes);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[MailService] Erreur commande : " . $e->getMessage());
            return false;
        }
    }

    // =========================================
    // BIENVENUE APRÈS INSCRIPTION
    // =========================================
    public static function sendWelcome(string $prenom, string $email): bool {
        try {
            $mail = self::createMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Bienvenue sur NGAARY SHOP, " . $prenom . " !";
            $mail->Body    = self::templateWelcome($prenom);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[MailService] Erreur bienvenue : " . $e->getMessage());
            return false;
        }
    }

    // =========================================
    // TEMPLATES HTML
    // =========================================

    private static function templateOrderConfirmation(array $commande, array $lignes): string {
        $lignesHtml = '';
        foreach ($lignes as $l) {
            $lignesHtml .= "
            <tr>
                <td style='padding:10px; border-bottom:1px solid #f0faf3;'>{$l['nom_produit']}</td>
                <td style='padding:10px; border-bottom:1px solid #f0faf3; text-align:center;'>{$l['quantite']}</td>
                <td style='padding:10px; border-bottom:1px solid #f0faf3; text-align:right; color:#16a34a; font-weight:bold;'>"
                . number_format($l['sous_total'], 0, ',', ' ') . " FCFA</td>
            </tr>";
        }

        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: DM Sans, sans-serif; background:#f0faf3; margin:0; padding:20px;'>
            <div style='max-width:600px; margin:0 auto; background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.1);'>

                <!-- HEADER -->
                <div style='background:linear-gradient(135deg,#0d2818,#1a6b35); padding:30px; text-align:center; color:white;'>
                    <h1 style='font-size:1.5rem; margin:0;'>NGAARY SHOP</h1>
                    <p style='opacity:.8; margin:5px 0 0;'>La qualité à petit prix</p>
                </div>

                <!-- CONTENU -->
                <div style='padding:30px;'>
                    <h2 style='color:#0d2818;'>✅ Commande confirmée !</h2>
                    <p style='color:#64748b;'>
                        Merci pour votre commande. Nous avons bien reçu votre demande et nous la traitons dans les plus brefs délais.
                    </p>

                    <div style='background:#f0faf3; border-radius:10px; padding:15px; margin:20px 0;'>
                        <p style='margin:0; font-size:.9rem;'>
                            <strong>Numéro de commande :</strong>
                            <span style='color:#16a34a; font-weight:bold;'>{$commande['numero_commande']}</span>
                        </p>
                    </div>

                    <!-- ARTICLES -->
                    <h3 style='color:#0d2818; font-size:1rem;'>Articles commandés</h3>
                    <table style='width:100%; border-collapse:collapse;'>
                        <thead>
                            <tr style='background:#f8fafc;'>
                                <th style='padding:10px; text-align:left; font-size:.8rem; color:#64748b;'>Produit</th>
                                <th style='padding:10px; text-align:center; font-size:.8rem; color:#64748b;'>Qté</th>
                                <th style='padding:10px; text-align:right; font-size:.8rem; color:#64748b;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>{$lignesHtml}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan='2' style='padding:10px; font-weight:bold; text-align:right;'>Total</td>
                                <td style='padding:10px; font-weight:bold; color:#16a34a; text-align:right;'>"
                                . number_format($commande['montant_total'], 0, ',', ' ') . " FCFA</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style='color:#64748b; font-size:.9rem; margin-top:20px;'>
                        Notre équipe vous contactera pour confirmer la livraison.
                        Pour toute question, contactez-nous sur
                        <a href='mailto:contact@ngaaryshop.sn' style='color:#16a34a;'>contact@ngaaryshop.sn</a>
                    </p>
                </div>

                <!-- FOOTER -->
                <div style='background:#f8fafc; padding:20px; text-align:center; color:#94a3b8; font-size:.8rem;'>
                    <p style='margin:0;'>© " . date('Y') . " NGAARY SHOP — Dakar, Sénégal</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function templateWelcome(string $prenom): string {
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: DM Sans, sans-serif; background:#f0faf3; margin:0; padding:20px;'>
            <div style='max-width:600px; margin:0 auto; background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.1);'>
                <div style='background:linear-gradient(135deg,#0d2818,#1a6b35); padding:30px; text-align:center; color:white;'>
                    <h1 style='font-size:1.5rem; margin:0;'>NGAARY SHOP</h1>
                </div>
                <div style='padding:30px;'>
                    <h2 style='color:#0d2818;'>Bienvenue, {$prenom} ! </h2>
                    <p style='color:#64748b;'>
                        Votre compte a bien été créé. Vous pouvez maintenant profiter de tous nos avantages :
                    </p>
                    <ul style='color:#64748b;'>
                        <li>Livraison gratuite dès 15 000 FCFA</li>
                        <li>Suivi de vos commandes en temps réel</li>
                        <li>Offres exclusives pour les membres</li>
                    </ul>
                    <div style='text-align:center; margin:30px 0;'>
                        <a href='" . url('boutique.php') . "'
                           style='background:#16a34a; color:white; padding:14px 32px; border-radius:50px; text-decoration:none; font-weight:bold;'>
                            Découvrir la boutique
                        </a>
                    </div>
                </div>
                <div style='background:#f8fafc; padding:20px; text-align:center; color:#94a3b8; font-size:.8rem;'>
                    <p style='margin:0;'>© " . date('Y') . " NGAARY SHOP — Dakar, Sénégal</p>
                </div>
            </div>
        </body>
        </html>";
    }
}