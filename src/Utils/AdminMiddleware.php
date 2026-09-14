<?php

namespace App\Utils;

class AdminMiddleware {
    public static function check(): void {
        if (!Auth::check()) {
            if (!headers_sent()) {
                header('Location: ' . url('login.php'));
            }
            exit();
        }
        if (!Auth::isAdmin()) {
            if (!headers_sent()) {
                header('Location: ' . url('index.php'));
            }
            exit();
        }
    }
}