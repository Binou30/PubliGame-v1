<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$vote_dir = 'votes';
$desc_dir = 'descriptions/';
$upload_dir = 'uploads/';

if (!isset($_POST['fichier'])) {
    die("Aucun fichier spécifié.");
}

$fichier = basename($_POST['fichier']);

$fichier = basename($_GET['fichier']);
$description_file = $desc_dir . $fichier . '.txt';
$upload_file = $upload_dir . $fichier;
$vote_file = $vote_dir . '/' . $fichier . '.txt';

// Lire l'auteur depuis le fichier description
if (!file_exists($description_file)) {
    die("<script>alert('Fichier description introuvable !'); window.location.href='index.php';</script>");
}

$lines = file($description_file);
$author = '';
foreach ($lines as $line) {
    if (stripos($line, 'Auteur :') === 0) {
        $author = trim(substr($line, 7));
        break;
    }
}

// Comparaison insensible à la casse
if (strtolower($_SESSION['username']) !== strtolower($author)) {
    die("<script>alert('Vous n\'êtes pas autorisé à supprimer ce projet.'); window.location.href='index.php';</script>");
}

// Supprimer le fichier uploadé
if (file_exists($upload_file)) {
    unlink($upload_file);
}

// Supprimer le fichier description
if (file_exists($description_file)) {
    unlink($description_file);
}

// Supprimer le fichier votes
if (file_exists($vote_file)) {
    unlink($vote_file);
}

// Fenêtre popup et retour à l'index
echo "<script>
alert('Projet supprimé avec succès !');
window.location.href='index.php';
</script>";
exit();
?>
