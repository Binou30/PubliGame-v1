<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
$uploads_dir = 'uploads';
$desc_dir = 'descriptions';
$votes_dir = 'votes';

function load_comments($filename) {
    $comments = array();
    if (file_exists($filename)) {
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line == '') continue;
            $parts = explode('|', $line, 3);
            if (count($parts) == 3) {
                $comments[] = array('date' => $parts[0], 'user' => $parts[1], 'texte' => $parts[2]);
            }
        }
    }
    return $comments;
}

$projets = array();

$dh = opendir($uploads_dir);
if ($dh) {
    while (($file = readdir($dh)) !== false) {
        if ($file != "." && $file != "..") {
            $nom_affiche = basename($file);
            $nom_fichier = $file;
            $fichier_path = $uploads_dir . '/' . $file;
            $date_modif = date("d/m/Y H:i", filemtime($fichier_path));

            $desc_path = $desc_dir . '/' . $file . '.txt';
            $description = '';
            $auteur = 'Anonyme';

            if (file_exists($desc_path)) {
                $contenu = file_get_contents($desc_path);
                
                if (preg_match('/Auteur\s*:\s*(.+)/i', $contenu, $matches)) {
                    $auteur = trim($matches[1]);
                }

                $lines = explode("\n", $contenu);
                if (count($lines) > 1) {
                    $description = trim(implode("\n", array_slice($lines, 1)));
                }
            }

            $vote_file = $votes_dir . '/' . $nom_fichier . '.txt';
            $likes = 0;
            $dislikes = 0;
            if (file_exists($vote_file)) {
                $lines = file($vote_file);
                foreach ($lines as $line) {
                    if (strpos($line, 'likes=') === 0) {
                        $likes = intval(substr($line, 6));
                    }
                    if (strpos($line, 'dislikes=') === 0) {
                        $dislikes = intval(substr($line, 9));
                    }
                }
            }

            $projets[] = array(
                'nom_affiche' => $nom_affiche,
                'nom_fichier' => $nom_fichier,
                'date_modif' => $date_modif,
                'description' => $description,
                'auteur' => $auteur,
                'likes' => $likes,
                'dislikes' => $dislikes
            );
        }
    }
    closedir($dh);

    foreach ($projets as $key => $projet) {
        $projets[$key]['commentaires'] = load_comments('comments/' . $projet['nom_fichier'] . '.txt');
    }
}
?>
	
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets publiés</title>
    <link rel="icon" href="static/favicon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isset($_GET['flash']) && $_GET['flash'] == 1): ?>
    <script>
        alert("Projet supprimé avec succès!");
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="cadre">
        <div class="body">
            <h1><u>Projets publiés</u></h1>
            <h2>Vous voici dans les projets publiés par les autres utilisateurs !</h2>
            <?php if (count($projets) > 0): ?>
                <ul>
                <?php foreach ($projets as $p): ?>
                    <li>
                        <span class="nom-fichier"><?php echo htmlspecialchars($p['nom_affiche']); ?></span>
                        <div class="project-meta">
                            <span class="date">(<?php echo $p['date_modif']; ?>)</span>
                            <a href="download.php?f=<?php echo urlencode($p['nom_fichier']); ?>" class="download-link">Télécharger</a>
                            <?php if (isset($_SESSION['username']) && strcasecmp($_SESSION['username'], $p['auteur']) === 0): ?>
                            <form method="POST" action="deleteproject.php" class="delete-project-form" onsubmit="return confirm('Voulez-vous vraiment supprimer ce projet ?');">
                                    <input type="hidden" name="fichier" value="<?php echo htmlspecialchars($p['nom_fichier']); ?>">
                                    <button type="submit" class="delete-project-btn" aria-label="Supprimer ce projet">✖</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="vote.php" style="display:inline;">
                            <input type="hidden" name="fichier" value="<?php echo htmlspecialchars($p['nom_fichier']); ?>">
                            <input type="hidden" name="vote" value="like">
                            <button type="submit" style="background:none; border:none; cursor:pointer;">👍</button>
                            <b><?php echo $p['likes']; ?></b>
                        </form>

                        <form method="POST" action="vote.php" style="display:inline;">
                            <input type="hidden" name="fichier" value="<?php echo htmlspecialchars($p['nom_fichier']); ?>">
                            <input type="hidden" name="vote" value="dislike">
                            <button type="submit" style="background:none; border:none; cursor:pointer;">👎</button>
                            <b><?php echo $p['dislikes']; ?></b>
                        </form>
                        <br><br>
                        <?php if (!empty($p['description'])): ?>
                            <?php
                                $desc = nl2br(htmlspecialchars($p['description']));
                                $desc = preg_replace('/^(Auteur|Nom du projet|Description)\s*:/mi', '<strong>$1 :</strong>', $desc);
                                echo $desc;
                            ?><br>
                        <?php else: ?>
                            <strong><em>Pas de description</em></strong><br>
                        <?php endif; ?>
                        <span style="color: black; font-size: 0.9em;"><strong>Auteur :</strong> <?php echo htmlspecialchars($p['auteur']); ?></span>
                        <?php
                            if (!empty($p['commentaires'])) {
                                echo "<h3>Commentaires :</h3><ul>";
                                foreach ($p['commentaires'] as $index => $c) {
                                    echo "<li><em><b>" . htmlspecialchars($c['date']) . "</b></em> - <strong><u>" . htmlspecialchars($c['user']) . "</u></strong> : " . htmlspecialchars($c['texte']);
                                    if (isset($_SESSION['username']) && strcasecmp($_SESSION['username'], $c['user']) === 0) {
                                        echo '<form method="POST" action="comments.php" style="display:inline;" onsubmit="return confirm(\'Voulez-vous vraiment supprimer ce commentaire ?\');">
                                                <input type="hidden" name="nom_fichier" value="' . htmlspecialchars($p['nom_fichier']) . '">
                                                <input type="hidden" name="action" value="supprimer">
                                                <input type="hidden" name="commentaire_id" value="' . $index . '">
                                                <button type="submit" style="color:#8f3333; background:none; border:none; font-weight:bold; cursor:pointer;">✖</button>
                                            </form>';
                                    }
                                    echo "</li>";
                                }
                                echo "</ul>";
                            } else {
                                echo "<p><em><strong>Aucun commentaire pour ce projet</strong></em></p>";
                            }

                            if (isset($_SESSION['username'])) {
                                echo '<form method="POST" action="comments.php">
                                        <input type="hidden" name="nom_fichier" value="' . htmlspecialchars($p['nom_fichier']) . '">
                                        <input type="hidden" name="action" value="ajouter">
                                        <label for="commentaire-' . htmlspecialchars($p['nom_fichier']) . '"><b>Ajouter un commentaire :</b></label><br>
                                        <textarea id="commentaire-' . htmlspecialchars($p['nom_fichier']) . '" name="commentaire" rows="2" cols="50" required></textarea><br>
                                        <button type="submit">Envoyer</button>
                                    </form>';
                            } else {
                                echo '<p><em>Connectez-vous pour ajouter un commentaire.</em></p>';
                            }
                            ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><b>Aucun projet publié pour le moment.</b></p>
            <?php endif; ?>
            <button onclick="window.location.href='index.php'">Retour à l'accueil</button>
            <?php include_once dirname(__FILE__) . '/footer.php'; ?>
        </div>
    </div>
</body>
<style>
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
        padding-bottom: clamp(8rem, 14vw, 10rem);
    }

    button:not([style*="background:none"]):not([style*="background: none"]) {
        margin-bottom: clamp(3rem, 6vw, 4rem);
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

    button:not([style*="background:none"]):not([style*="background: none"]):hover {
		transform: translateY(-0.125rem);
		box-shadow: 0 clamp(0.625rem, 2vw, 0.75rem) clamp(1.25rem, 3vw, 1.5rem) rgba(0, 0, 0, 0.35);
		filter: brightness(1.05);
		background-position: 100% 50%;
	}

    button:not([style*="background:none"]):not([style*="background: none"]):active {
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

    h2, h3 {
        font-size: clamp(1rem, 3vw, 1.6rem);
        font-weight: 700;
        color: #8f3333;
        text-shadow: 0.125rem 0.125rem 0.375rem rgba(0, 0, 0, 0.25),
                     0 0 0.9375rem rgba(143, 51, 51, 0.15);
        letter-spacing: -0.01em;
        margin-bottom: clamp(0.75rem, 2vw, 0.9375rem);
    }

    h3 {
        font-size: clamp(0.95rem, 2.5vw, 1.25rem);
    }

    p {
        font-size: clamp(0.82rem, 2vw, 0.98rem);
        font-weight: 500;
        color: #8f3333;
        line-height: 1.7;
        text-shadow: 0.0625rem 0.0625rem 0.1875rem rgba(0, 0, 0, 0.2);
        margin: clamp(0.75rem, 2vw, 0.9375rem) 0;
    }

    p b, p strong {
        font-weight: 600;
        text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.25);
    }
    .cadre {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: transparent;
        border: clamp(0.25rem, 1vw, 0.3125rem) solid #727272;
        box-sizing: border-box;
    }
    .body {
        margin-left: clamp(0.25rem, 1vw, 0.3125rem);
        padding: clamp(0.5rem, 2vw, 1rem);
    }
    ul { list-style-type: none; padding-left: 0; }
    li {
        margin-bottom: clamp(1rem, 3vw, 1.5625rem);
        padding-bottom: clamp(0.75rem, 2vw, 0.9375rem);
        border-bottom: clamp(0.125rem, 0.5vw, 0.125rem) solid #000000;
    }
    .nom-fichier {
        font-weight: bold;
        font-size: clamp(1rem, 3vw, 1.2rem);
        text-decoration: underline;
    }
    .date {
        color: black;
        font-size: clamp(0.8rem, 2vw, 0.9rem);
        margin-left: clamp(0.5rem, 2vw, 0.625rem);
    }
	.project-meta { 
		display: inline-flex; 
		flex-wrap: wrap; 
		align-items: center; 
		gap: clamp(0.75rem, 2vw, 1rem); 
	} 
	.delete-project-form { 
		display: inline-block; 
		margin: 0; 
		padding: 0; 
		position: relative; 
		top: 32px; 
		left: -3px; 
	} 
	.delete-project-btn { 
		color: #8f3333; 
		background: none; 
		border: none; 
		font-weight: bold; 
		cursor: pointer; 
		padding: 0; 
		margin: 0; 
		font-size: clamp(1rem, 2vw, 1.1rem); 
		line-height: 1; 
		display: inline-flex; 
		align-items: center; 
		justify-content: center; 
		transition: transform 0.15s ease; 
	}
    a.download-link {
        margin-left: 0;
        text-decoration: none;
        color: #235098;
        font-weight: bold;
        font-style: italic;
    }
    a.download-link:hover {
        text-decoration: underline;
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
        button:not([style*="background:none"]):not([style*="background: none"]) {
            width: 100%;
            max-width: 300px;
            margin: 0.5rem 0;
        }

        h1 {
            font-size: clamp(1.5rem, 6vw, 2rem);
        }

        h2 {
            font-size: clamp(1rem, 4vw, 1.4rem);
        }

        h3 {
            font-size: clamp(0.9rem, 4vw, 1.2rem);
        }

        .body {
            margin-left: 0.5rem;
            padding: 0.5rem;
        }

        li {
            font-size: 0.9rem;
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

        h2 {
            font-size: 1.1rem;
        }

        h3 {
            font-size: 1rem;
        }

        button:not([style*="background:none"]):not([style*="background: none"]) {
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
</html>
