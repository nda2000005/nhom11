<?php

if (isset($_REQUEST['php_session_id'])) {
    session_id($_REQUEST['php_session_id']);
}

if (isset($_COOKIE[session_name()])) {
    if (!preg_match('/^[a-zA-Z0-9-,]+$/', $_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
        unset($_COOKIE[session_name()]);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/config/db.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    $altConfigPath = $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
    if (file_exists($altConfigPath)) {
        require_once $altConfigPath;
    }
}

if (!defined('BASE_URL')) {
    $requestUri = $_SERVER['REQUEST_URI'];
    preg_match('/\/(.*?)\//', $requestUri, $matches);
    $projectName = !empty($matches[1]) ? $matches[1] : 'mnopl';
    
    define('BASE_URL', '/' . $projectName . '/public/');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Công nghệ chính hãng</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8fafc; }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f0f9ff;
            padding: 0 5%;
            height: 90px;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .header-logo img { height: 60px; display: block; }

        .header-search {
            flex: 1;
            max-width: 600px;
            margin: 0 30px;
        }

        .search-form {
            display: flex;
            width: 100%;
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            transition: 0.3s;
        }

        .search-form:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 8px;
            font-size: 15px;
            color: #333;
        }

        .search-btn {
            background: #3b82f6;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .search-btn:hover { background: #2563eb; }

        .header-cart a { position: relative; text-decoration: none; display: flex; align-items: center; }
        .header-cart img { width: 32px; height: 32px; }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            border: 2px solid white;
        }

        .debug-session {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: #0f0;
            padding: 5px 10px;
            font-size: 10px;
            border-radius: 5px;
            z-index: 9999;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-logo">
        <a href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>src/img/tsch.png" alt="Trang Chủ">
        </a>
    </div>

    <div class="header-search">
        <form action="<?= BASE_URL ?>index.php" method="GET" class="search-form">
            <input type="text" name="keyword" class="search-input" placeholder="Bạn tìm gì hôm nay?">
            <button type="submit" class="search-btn">
                <i class="fa fa-search"></i>
            </button>
        </form>
    </div>

    <div class="header-cart">
        <a href="<?= BASE_URL ?>cart.php">
            <img src="<?= BASE_URL ?>src/img/add-to-cart.png" alt="Giỏ hàng">

            <?php
            $countBadge = 0;
            if (isset($pdo)) {
                try {
                    $stmtBadge = $pdo->query("SELECT SUM(qty) as total FROM user_cart");
                    $rowBadge = $stmtBadge->fetch(PDO::FETCH_ASSOC);
                    $countBadge = (int)($rowBadge['total'] ?? 0);
                } catch (Exception $e) {
                    $countBadge = 0;
                }
            }
            ?>
            <span class="cart-badge"><?= $countBadge ?></span>
        </a>
    </div>
</header>