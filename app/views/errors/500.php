<?php
/** @var \Throwable|null $exception */
$message = isset($exception) ? $exception->getMessage() : 'Erreur interne du serveur.';
?>

<h1>500 - Erreur serveur</h1>
<p><?= vue_echapper($message) ?></p>
<p><a href="<?= vue_echapper(BASE_URL) ?>">Retour a l'accueil</a></p>
