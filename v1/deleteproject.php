<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$vote_dir = 'votes';
$desc_dir = 'descriptions';
$upload_dir = 'uploads';

if (!isset($_POST['fichier'])) {
    die("Aucun fichier spécifié.");
}

$fichier = basename($_POST['fichier']);

$description_file = $desc_dir . '/' . $fichier . '.txt';
$upload_file = $upload_dir . '/' . $fichier;
$vote_file = $vote_dir . '/' . $fichier . '.txt';

if (!file_exists($description_file)) {
    die("<script>alert('Fichier description introuvable !'); window.location.href='index.php';</script>");
}

$lines = file($description_file);
$author = '';

foreach ($lines as $line) {
    if (preg_match('/^Auteur\s*:\s*(.+)$/i', trim($line), $matches)) {
        $author = trim($matches[1]);
        break;
    }
}

if (strtolower($_SESSION['username']) !== strtolower($author)) {
    die("<script>alert('Vous n\'êtes pas autorisé à supprimer ce projet.'); window.location.href='index.php';</script>");
}

if (file_exists($upload_file)) {
    unlink($upload_file);
}

if (file_exists($description_file)) {
    unlink($description_file);
}

if (file_exists($vote_file)) {
    unlink($vote_file);
}

echo "<script>
alert('Projet supprimé avec succès !');
window.location.href='index.php';
</script>";
exit();
?>
