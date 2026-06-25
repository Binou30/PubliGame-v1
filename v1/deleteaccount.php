<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

$user_file = 'users.txt';
$uploads_dir = 'uploads';
$desc_dir = 'descriptions';
$votes_dir = 'votes';
$comments_dir = 'comments';

$found = false;

if (file_exists($user_file) && is_readable($user_file)) {

    $lines = file($user_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_lines = [];

    foreach ($lines as $line) {
        $parts = explode(':', trim($line));

        if (count($parts) >= 2) {
            if (strcasecmp($parts[0], $username) !== 0) {
                $new_lines[] = $line;
            } else {
                $found = true;
            }
        }
    }

    if (is_writable($user_file)) {
        file_put_contents($user_file, implode("\n", $new_lines) . "\n");
    }
}

if (is_dir($desc_dir)) {
    $desc_files = scandir($desc_dir);

    foreach ($desc_files as $desc_file) {
        if ($desc_file === '.' || $desc_file === '..') continue;

        $desc_path = $desc_dir . '/' . $desc_file;

        if (!is_file($desc_path)) continue;

        $content = file_get_contents($desc_path);

        if (preg_match('/Auteur\s*:\s*(.+)/i', $content, $matches)) {
            $auteur = trim($matches[1]);

            if (strcasecmp($auteur, $username) === 0) {

                $nom_fichier = basename($desc_file, '.txt');

                $upload_path = $uploads_dir . '/' . $nom_fichier;
                $votes_path = $votes_dir . '/' . $nom_fichier . '.txt';
                $comments_path = $comments_dir . '/' . $nom_fichier . '.txt';

                if (file_exists($upload_path)) unlink($upload_path);
                if (file_exists($desc_path)) unlink($desc_path);
                if (file_exists($votes_path)) unlink($votes_path);
                if (file_exists($comments_path)) unlink($comments_path);
            }
        }
    }
}

session_unset();
session_destroy();

/* IMPORTANT : éviter page blanche même si bug header */
if ($found) {
	die("<script>alert('Compte supprimé avec succès !'); window.location.href='index.php';</script>");
} else {
    die("<script>alert('Compte introuvable !'); window.location.href='index.php';</script>");
}

exit;
?>
