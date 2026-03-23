<?php

namespace App\Utils;

class AdminMiddleware {
    public static function check(): void {
        if (!Auth::check()) {
            header('Location: ' . url('login.php'));
            exit();
        }
        if (!Auth::isAdmin()) {
            header('Location: ' . url('index.php'));
            exit();
        }
    }
}