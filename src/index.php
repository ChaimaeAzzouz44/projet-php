<?php

function saluer(string $nom): string
{
    return "Bonjour " . htmlspecialchars($nom) . " !";
}

$nom = $_GET['nom'] ?? 'DevOps';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon application PHP</title>
</head>
<body>
    <h1><?= saluer($nom) ?></h1>
    <p>Cette application est déployée automatiquement par un pipeline CI/CD.</p>
</body>
</html>