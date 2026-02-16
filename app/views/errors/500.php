<?php
/** @var \Throwable|null $exception */
$message = isset($exception) ? $exception->getMessage() : 'Erreur interne du serveur.';
?>

<h1>500 - Erreur serveur</h1>
<p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<p><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Retour a l'accueil</a></p>
