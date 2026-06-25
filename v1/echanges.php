<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$echanges_dir = 'echanges';
$echanges_file = $echanges_dir . '/echanges.txt';

if (!file_exists($echanges_dir)) {
    mkdir($echanges_dir, 0777);
}

function lire_messages($fichier) {
    $messages = array();
    if (file_exists($fichier)) {
        $lignes = file($fichier);
        foreach ($lignes as $ligne) {
            $ligne = trim($ligne);
            if ($ligne != '') {
                $parts = explode('|||', $ligne);
                if (count($parts) == 3) {
                    $messages[] = array(
                        'auteur' => $parts[0],
                        'date' => $parts[1],
                        'texte' => $parts[2]
                    );
                }
            }
        }
    }
    return $messages;
}

function ecrire_messages($fichier, $messages) {
    $fp = fopen($fichier, 'w');
    if ($fp) {
        foreach ($messages as $msg) {
            $texte_sans_conflict = str_replace('|||', ' ', $msg['texte']);
            fwrite($fp, $msg['auteur'] . '|||' . $msg['date'] . '|||' . $texte_sans_conflict . "\n");
        }
        fclose($fp);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $texte = trim($_POST['message']);
    if ($texte != '') {
        $messages = lire_messages($echanges_file);
        $nouveau = array(
            'auteur' => $_SESSION['username'],
            'date' => date('d/m/Y H:i'),
            'texte' => $texte
        );
        $messages[] = $nouveau;
        ecrire_messages($echanges_file, $messages);
        header('Location: echanges.php');
        exit;
    }
}

$messages = array_reverse(lire_messages($echanges_file));
?>

<?php if (isset($_GET['deleted'])): ?>
	<script>
	alert("Message supprimé avec succès !");
	</script>
	<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Échanges</title>
    <link rel="icon" href="static/favicon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
@import url("static/site.css");
        textarea {
            width: 100%;
            max-width: 600px;
            height: clamp(4rem, 10vw, 4.375rem);
            resize: vertical;
            padding: clamp(0.5rem, 2vw, 0.5rem);
            font-size: clamp(0.8rem, 2vw, 0.85rem);
            border: 0.125rem solid #727272;
            border-radius: 0.5rem;
            font-family: 'Poppins', inherit;
        }
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

        button:not(.delete-btn) {
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

        button:not(.delete-btn):hover {
            transform: translateY(-0.125rem);
            box-shadow: 0 clamp(0.625rem, 2vw, 0.75rem) clamp(1.25rem, 3vw, 1.5rem) rgba(0, 0, 0, 0.35);
            filter: brightness(1.05);
            background-position: 100% 50%;
        }

        button:not(.delete-btn):active {
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

        h3 {
            font-size: clamp(0.95rem, 2.5vw, 1.25rem);
            font-weight: 700;
            color: #8f3333;
            text-shadow: 0.125rem 0.125rem 0.375rem rgba(0, 0, 0, 0.25),
                         0 0 0.9375rem rgba(143, 51, 51, 0.15);
            letter-spacing: -0.01em;
            margin-bottom: clamp(0.75rem, 2vw, 0.9375rem);
        }

        p {
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            font-weight: 500;
            color: #8f3333;
            line-height: 1.7;
            text-shadow: 0.0625rem 0.0625rem 0.1875rem rgba(0, 0, 0, 0.2);
            margin: clamp(0.75rem, 2vw, 0.9375rem) 0;
        }

        p b {
            font-weight: 600;
            text-shadow: 0.0625rem 0.0625rem 0.25rem rgba(0, 0, 0, 0.25);
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
        .cadre {
            background-color: transparent;
            border: clamp(0.25rem, 1vw, 0.3125rem) solid #727272;
            box-sizing: border-box;
        }
        .body {
            margin-left: clamp(0.25rem, 1vw, 0.3125rem);
            padding: clamp(0.5rem, 2vw, 0.9rem);
            padding-bottom: clamp(3rem, 7vw, 4rem); /* espace pour footer fixe */
        }
        .message {
            background: rgba(255,255,255,0.7);
            padding: clamp(0.5rem, 2vw, 0.625rem);
            border-radius: clamp(0.25rem, 1vw, 0.3125rem);
            width: 100%;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            button:not(.delete-btn) {
                width: 100%;
                max-width: 300px;
                margin: 0.5rem 0;
            }

            textarea {
                width: 100%;
                max-width: 100%;
            }

            h1 {
                font-size: clamp(1.5rem, 6vw, 2rem);
            }

            h3 {
                font-size: clamp(0.9rem, 4vw, 1.2rem);
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

            h3 {
                font-size: 1rem;
            }

            button:not(.delete-btn) {
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
        form.inline { display: inline; }
        button.delete-btn {
            color: #8f3333;
            background: none;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="cadre">
    <div class="body">
        <h1><u>Échanges</u></h1>
        <h3>Ici, vous pourrez discuter avec les autres membres de PubliGame!</h3>
        <form action="echanges.php" method="post">
            <textarea name="message" placeholder="Écrivez votre message ici..." required rows="4" class="textarea"></textarea><br>
            <button type="button" onclick="window.location.href='index.php'">Retour à l'accueil</button>
            <button type="submit">Publier</button>
        </form>
        <hr style="border: none; height: 2px; background-color: black; margin: 20px 0;">
        <div>
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $index => $msg): ?>
                    <div class="message">
                        <strong><u><?php echo htmlspecialchars($msg['auteur']); ?></u></strong> 
                        <em><b>le <?php echo htmlspecialchars($msg['date']); ?></b></em>
                        <?php if ($_SESSION['username'] == $msg['auteur']): ?>
                            <form class="inline" method="post" action="supprimer_message.php" onsubmit="return confirm('Voulez-vous vraiment supprimer ce message ?');">
                                <input type="hidden" name="index" value="<?php echo count($messages) - $index - 1; ?>">
                                <button type="submit" class="delete-btn">✖</button>
                            </form>
                        <?php endif; ?>
                        <br>
                        <i><?php echo nl2br(htmlspecialchars($msg['texte'])); ?></i>
                    </div><br>
                <?php endforeach; ?>
            <?php else: ?>
                <p><b>Aucun message publié pour le moment.</b></p>
            <?php endif; ?>
        </div>
        <?php include_once dirname(__FILE__) . '/footer.php'; ?>
        <script>
            (function(){
                var footer = document.querySelector('.site-footer');
                if(!footer) return;
                footer.classList.add('hide-until-bottom');
                function checkFooter(){
                    var atBottom = (window.innerHeight + window.pageYOffset) >= (document.documentElement.scrollHeight - 10);
                    if(atBottom){ footer.classList.add('visible'); } else { footer.classList.remove('visible'); }
                }
                window.addEventListener('scroll', checkFooter);
                window.addEventListener('resize', checkFooter);
                document.addEventListener('DOMContentLoaded', checkFooter);
                checkFooter();
            })();
        </script>
    </div>
</div>
</body>
</html>
