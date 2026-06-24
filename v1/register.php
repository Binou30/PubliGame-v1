<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$username = '';
$password = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }
    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
    }

    if ($username == '' || $password == '') {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        $filepath = 'users.txt';
        $found = false;

        if (file_exists($filepath)) {
            $lines = file($filepath);
            foreach ($lines as $line) {
                $parts = explode(':', trim($line));
                if (strtolower($parts[0]) == strtolower($username)) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            $message = 'Nom d’utilisateur déjà pris.';
        } else {
            // On enregistre le mot de passe en clair (à ne pas faire en production)
            $entry = $username . ':' . $password . "\n";
            $fp = fopen($filepath, 'a');
            fwrite($fp, $entry);
            fclose($fp);

            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="icon" href="static/favicon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style type="text/css">
@import url("static/site.css");
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: transparent;
        }
        .cadre {
            position: relative;
            min-height: 100vh;
            background-color: transparent !important;
        }
        .body {
            background: rgba(7, 16, 34, 0.72) !important;
            border-radius: 12px !important;
            position: relative;
            z-index: 1 !important;
        }

        button {
            padding: clamp(0.5rem, 1.2vw, 0.65rem) clamp(0.9rem, 2vw, 1rem);
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #ff8a3c, #ff4f81);
            color: #ffffff;
            font-weight: 600;
            font-family: 'Poppins', inherit;
            font-size: clamp(0.72rem, 2vw, 0.85rem);
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 clamp(0.375rem, 1.5vw, 0.5rem) clamp(0.875rem, 2.5vw, 1.125rem) rgba(0, 0, 0, 0.25);
            transition:
                transform 0.15s ease,
                box-shadow 0.15s ease,
                filter 0.15s ease,
                background-position 0.2s ease;
            background-size: 150% 150%;
            background-position: 0% 50%;
        }

        button:hover {
            transform: translateY(-0.125rem);
            box-shadow: 0 clamp(0.625rem, 2vw, 0.75rem) clamp(1.25rem, 3vw, 1.5rem) rgba(0, 0, 0, 0.35);
            filter: brightness(1.05);
            background-position: 100% 50%;
        }

        button:active {
            transform: translateY(0);
            box-shadow: 0 clamp(0.375rem, 1.5vw, 0.5rem) clamp(0.625rem, 2vw, 0.75rem) rgba(0, 0, 0, 0.3);
            filter: brightness(0.97);
        }

        h1 {
            font-size: clamp(1.6rem, 4vw, 2.3rem);
            font-weight: 800;
            color: #8f3333;
            text-shadow: 0.1875rem 0.1875rem 0.5rem rgba(0, 0, 0, 0.3),
                         0 0 1.25rem rgba(143, 51, 51, 0.2);
            letter-spacing: -0.02em;
            margin-bottom: clamp(1rem, 3vw, 1.25rem);
            transition: text-shadow 0.3s ease;
        }

        h1:hover {
            text-shadow: 0.25rem 0.25rem 0.75rem rgba(0, 0, 0, 0.4),
                         0 0 1.875rem rgba(143, 51, 51, 0.3);
        }

        p {
            font-size: clamp(0.82rem, 2vw, 0.98rem);
            font-weight: 500;
            color: #8f3333;
            line-height: 1.7;
            text-shadow: 0.0625rem 0.0625rem 0.1875rem rgba(0, 0, 0, 0.2);
            margin: clamp(0.75rem, 2vw, 0.9375rem) 0;
        }

        p b, label b {
            font-weight: 600;
            text-shadow: 0.0625rem 0.0625rem 0.25rem rgba(0, 0, 0, 0.25);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            max-width: 400px;
            padding: clamp(0.5rem, 2vw, 0.625rem);
            font-size: clamp(0.875rem, 2.5vw, 1rem);
            border: 0.125rem solid #727272;
            border-radius: 0.5rem;
            font-family: 'Poppins', inherit;
        }

        .cadre {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            border: clamp(0.25rem, 1vw, 0.3125rem) solid #727272;
            box-sizing: border-box;
        }
        .body {
            margin-left: clamp(0.25rem, 1vw, 0.3125rem);
            padding: clamp(0.5rem, 2vw, 1rem);
        }
        .copyright {
            text-align: center;
            width: 100%;
            font-size: clamp(0.7rem, 2vw, 0.8125rem);
            font-weight: 500;
            color: #8f3333;
            text-shadow: 0.0625rem 0.0625rem 0.25rem rgba(0, 0, 0, 0.3);
            letter-spacing: 0.05em;
            opacity: 0.9;
            transition: opacity 0.3s ease;
            margin-top: clamp(2rem, 5vw, 3rem);
            padding-bottom: clamp(1rem, 3vw, 2rem);
        }

        .copyright:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            button {
                width: 100%;
                max-width: 300px;
                margin: 0.5rem 0;
            }

            h1 {
                font-size: clamp(1.5rem, 6vw, 2rem);
            }

            input[type="text"], input[type="password"] {
                width: 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .body {
                margin-left: 0.5rem;
                padding: 0.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            button {
                font-size: 0.8rem;
                padding: 0.5rem 1rem;
            }

            .cadre {
                border-width: 0.25rem;
            }
        }

        @media (min-width: 1200px) {
            .body {
                max-width: 1200px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="cadre">
        <div class="body">
            <h1><u>Créer un compte</u></h1>
            <form method="POST" action="register.php">
                <label><b>Nom d'utilisateur :</b></label><br />
                <input type="text" name="username" required /><br /><br />
                <label><b>Mot de passe :</b></label><br />
                <input type="password" name="password" required /><br /><br />
                <?php if ($message != ''): ?>
                    <p style="color: #8f3333;"><b><?php echo $message; ?></b></p>
                <?php endif; ?>
                <button type="button" onclick="window.location.href='index.php'">Retour à l'accueil</button>
                <button type="submit">Créer le compte</button>
            </form>
            <?php include_once dirname(__FILE__) . '/footer.php'; ?>
        </div>
    </div>
</body>
</html>
