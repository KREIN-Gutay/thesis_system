<?php
session_start();

class Auth
{

    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        return true;
    }

    public static function logout()
    {
        session_unset();
        session_destroy();
        return true;
    }

    public static function check()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function user()
    {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    public static function id()
    {
        return self::check() ? $_SESSION['user_id'] : null;
    }

    public static function username()
    {
        return self::check() ? $_SESSION['username'] : null;
    }

    public static function role()
    {
        return self::check() ? $_SESSION['role'] : null;
    }

    public static function isAdmin()
    {
        return self::check() && $_SESSION['role'] === 'admin';
    }

    public static function isStudent()
    {
        return self::check() && $_SESSION['role'] === 'student';
    }

    public static function isAdviser()
    {
        return self::check() && $_SESSION['role'] === 'adviser';
    }

    public static function redirectIfAuthenticated($redirectUrl = '/')
    {
        if (self::check()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    public static function redirectIfNotAuthenticated($redirectUrl = '/login.php')
    {
        if (!self::check()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    public static function redirectIfNotAdmin($redirectUrl = '/')
    {
        if (!self::isAdmin()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    public static function redirectIfNotStudent($redirectUrl = '/')
    {
        if (!self::isStudent()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    public static function redirectIfNotAdviser($redirectUrl = '/')
    {
        if (!self::isAdviser()) {
            header("Location: $redirectUrl");
            exit();
        }
    }
}
