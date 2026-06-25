<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_FILES['mon_fichier']) || $_FILES['mon_fichier']['error'] != 0) {
        $message = "Erreur lors de l'envoi du fichier.";
    } else {
        $filename = $_FILES['mon_fichier']['name'];
        $ext = strtolower(substr(strrchr($filename, '.'), 1));

        $ext_interdites = array('phtml', 'sh', 'pl', 'cgi');
        if (in_array($ext, $ext_interdites)) {
            $message = "Extension interdite pour des raisons de sécurité.";
        } else {
            $filename_clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

            $upload_dir = 'uploads/';
            $desc_dir = 'descriptions/';

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755);
            if (!is_dir($desc_dir)) mkdir($desc_dir, 0755);

            $upload_path = $upload_dir . $filename_clean;

            if (move_uploaded_file($_FILES['mon_fichier']['tmp_name'], $upload_path)) {
                $nom_projet = isset($_POST['nom_projet']) ? strip_tags($_POST['nom_projet']) : '';
                $description = isset($_POST['description']) ? strip_tags($_POST['description']) : '';

                $description_file = $desc_dir . $filename_clean . '.txt';
                $contenu = "Auteur : " . $_SESSION['username'] . "\nNom du projet : " . $nom_projet . "\nDescription : " . $description;

                $fp = fopen($description_file, 'w');
                if ($fp) {
                    fwrite($fp, $contenu);
                    fclose($fp);
                    header("Location: index.php?flash=1");
                    exit();
                } else {
                    $message = "Fichier enregistré, mais erreur lors de l'enregistrement de la description.";
                }
            } else {
                $message = "Erreur lors de la sauvegarde du fichier.";
            }
        }
    }
}
?>
  
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier un projet</title>
    <link rel="icon" href="static/favicon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
  <div class="cadre">
    <div class="body">
      <h1><u>Publiez votre projet ici !</u></h1>
      <h3>Importez votre projet en cliquant sur le bouton ci-dessous et ajoutez un commentaire si nécessaire.</h3>
      <form action="publish.php" method="post" enctype="multipart/form-data" class="publish-form" onsubmit="return confirm('Voulez-vous vraiment publier ce projet ?');">
        <input id="fichier" type="file" name="mon_fichier" style="display: none;" required>
        <button type="button" id="btn-choisir">Choisir un fichier</button>
        <span id="nom-fichier" class="custom"><b>Aucun fichier choisi</b></span>
        <br><br>
        <input type="text" name="nom_projet" placeholder="Nom du projet" required class="form-input"><br><br>
        <input type="text" name="description" placeholder="Description (optionnelle)" class="form-input"><br><br>
        <div class="button-group">
          <button type="button" onclick="window.location.href='index.php'">Retour à l'accueil</button>
          <button type="submit">Valider</button>
        </div>
      </form>

      <?php include_once dirname(__FILE__) . '/footer.php'; ?>
    </div>
  </div>

<script>
  var input = document.getElementById('fichier');
  var nomFichier = document.getElementById('nom-fichier');
  var btn = document.getElementById('btn-choisir');

  btn.onclick = function() {
    input.click();
  };
  input.onchange = function() {
    if (input.files.length > 0) {
      nomFichier.textContent = input.files[0].name;
      nomFichier.className = 'fichier-choisi';
    } else {
      nomFichier.textContent = "Aucun fichier choisi";
      nomFichier.className = 'custom';
    }
  };
</script>

<style>
@import url("static/site.css");
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
    position: relative;
    min-height: 100vh;
    background-color: transparent;
  }
  .body {
    background: rgba(7, 16, 34, 0.72) !important;
    border-radius: 12px !important;
    position: relative;
    z-index: 1;
  }
  body {
    margin: 0;
    min-height: 100vh;
    color: var(--text);
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: transparent;
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
    text-shadow: 0.0625rem 0.0625rem 0.25rem rgba(0, 0, 0, 0.25);
  }

  .form-input {
    width: 100%;
    max-width: 500px;
    padding: clamp(0.5rem, 2vw, 0.625rem);
    font-size: clamp(0.875rem, 2.5vw, 1rem);
    border: 0.125rem solid #727272;
    border-radius: 0.5rem;
    font-family: 'Poppins', inherit;
  }

  .button-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1rem;
  }

  .publish-form {
    max-width: 600px;
  }
  .custom {
    color: #8f3333;
  }
  #nom-fichier.fichier-choisi {
    color: #8f3333;
    font-weight: bold;
  }
  .cadre {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;  
    bottom: 0;
    background-color: transparent;
    border: clamp(0.25rem, 1vw, 0.3125rem) solid #727272;
    box-sizing: border-box;
  }
  .body {
    margin-left: clamp(0.25rem, 1vw, 0.3125rem);
    padding: clamp(0.5rem, 2vw, 1rem);
  }

  @media (max-width: 768px) {
    .button-group {
      flex-direction: column;
    }

    button {
      width: 100%;
      max-width: 300px;
    }

    .form-input {
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
</body>
</html>
