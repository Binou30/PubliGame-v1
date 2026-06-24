<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$user_file = 'users.txt';
$found = false;

// Supprimer le compte de users.txt
if (file_exists($user_file)) {
    $lines = file($user_file);
    $new_lines = array();

    foreach ($lines as $line) {
        $line = trim($line);
        $parts = explode(':', $line);
        if (count($parts) >= 2) {
            if (strcasecmp($parts[0], $username) !== 0) {
                $new_lines[] = $line;
            } else {
                $found = true;
            }
        }
    }

    $fp = fopen($user_file, 'w');
    if ($fp) {
        foreach ($new_lines as $newline) {
            fwrite($fp, $newline . "\n");
        }
        fclose($fp);
    }
}

// Supprimer les projets publiés par cet utilisateur
$uploads_dir = 'uploads';
$desc_dir = 'descriptions';
$votes_dir = 'votes';
$comments_dir = 'comments';

if (is_dir($desc_dir)) {
    $desc_files = scandir($desc_dir);
    foreach ($desc_files as $desc_file) {
        if ($desc_file === '.' || $desc_file === '..') continue;
        
        $desc_path = $desc_dir . '/' . $desc_file;
        if (is_file($desc_path)) {
            $content = file_get_contents($desc_path);
            if (preg_match('/Auteur\s*:\s*(.+)/i', $content, $matches)) {
                $auteur = trim($matches[1]);
                // Comparaison insensible à la casse
                if (strcasecmp($auteur, $username) === 0) {
                    // Récupérer le nom du fichier uploadé
                    $nom_fichier = basename($desc_file, '.txt');
                    
                    // Supprimer le fichier uploadé
                    $upload_path = $uploads_dir . '/' . $nom_fichier;
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                    
                    // Supprimer la description
                    unlink($desc_path);
                    
                    // Supprimer les votes
                    $votes_path = $votes_dir . '/' . $nom_fichier . '.txt';
                    if (file_exists($votes_path)) {
                        unlink($votes_path);
                    }
                    
                    // Supprimer les commentaires
                    $comments_path = $comments_dir . '/' . $nom_fichier . '.txt';
                    if (file_exists($comments_path)) {
                        unlink($comments_path);
                    }
                }
            }
        }
    }
}

session_destroy();

if ($found) {
    header('Location: index.php?msg=compte_supprime');
} else {
    header('Location: index.php?msg=compte_introuvable');
}
exit;
?>
