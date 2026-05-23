<?php
namespace App\Controllers\Admin;

use PDO;
use App\Repositories\SettingRepository;
use App\Utils\Auth;

class ParametresController {
    private SettingRepository $settingRepo;

    public function __construct(PDO $db) {
        $this->settingRepo = new SettingRepository($db);
    }

    public function index(): void {
        if (!Auth::isAdmin()) {
            header('Location: ' . url('login.php'));
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update();
        }

        $settings = $this->settingRepo->getAll();
        include view_path('admin/parametres.php');
    }

    private function update(): void {
        $action = $_POST['action'] ?? '';
        $data = [];

        switch ($action) {
            case 'update_general':
                $data['site_name'] = $_POST['site_name'] ?? 'NGAARY SHOP';
                $data['site_email'] = $_POST['site_email'] ?? '';
                $data['site_phone'] = $_POST['site_phone'] ?? '';
                $data['site_address'] = $_POST['site_address'] ?? '';
                $data['delivery_fee'] = (int)($_POST['delivery_fee'] ?? 2500);
                $data['free_delivery_min'] = (int)($_POST['free_delivery_min'] ?? 15000);
                break;
            case 'update_security':
                $data['min_password'] = (int)($_POST['min_password'] ?? 8);
                $data['max_login_attempts'] = (int)($_POST['max_login_attempts'] ?? 5);
                $data['session_lifetime'] = (int)($_POST['session_lifetime'] ?? 7200);
                break;
            case 'update_notifications':
                $data['order_email'] = isset($_POST['order_email']) ? 1 : 0;
                $data['newsletter_email'] = isset($_POST['newsletter_email']) ? 1 : 0;
                break;
            case 'update_payment':
                $data['enable_wave'] = isset($_POST['enable_wave']) ? 1 : 0;
                $data['enable_om'] = isset($_POST['enable_om']) ? 1 : 0;
                $data['enable_cash'] = isset($_POST['enable_cash']) ? 1 : 0;
                break;
            case 'update_appearance':
                $data['primary_color'] = $_POST['primary_color'] ?? '#16a34a';
                $data['header_bg'] = $_POST['header_bg'] ?? '#ffffff';
                break;
        }

        if (!empty($data) && $this->settingRepo->setMultiple($data)) {
            $_SESSION['flash_success'] = "Paramètres mis à jour !";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour.";
        }
        header('Location: ' . url('admin/parametres.php'));
        exit();
    }
}