<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | <?= htmlspecialchars(settings('university_name', 'SGAU-OPENLU'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= base_url('/assets/css/stryle.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <form method="POST" action="<?= base_url('/login') ?>">
        <?= csrf_field() ?>
        <div class="auth-header">
            <img src="<?= base_url('/assets/images/' . settings('university_logo', 'openlu v1.jpg')) ?>" alt="Logo" class="auth-logo" style="height: 60px; width: auto; display: block; margin: 0 auto 15px auto;">
            <h1 class="university-name"><?= htmlspecialchars(settings('university_name', 'SGAU-OPENLU'), ENT_QUOTES, 'UTF-8') ?></h1>
            <h2>Connexion</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" placeholder="nom@exemple.com" required autocomplete="email">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
