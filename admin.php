<?php
session_start();
require_once __DIR__ . '/db_config.php';

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Vérifier si un mot de passe a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $login_error = 'Mot de passe incorrect.';
        }
    }

    // Afficher le formulaire de connexion
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Jim Tech - Connexion Admin</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="login-container">
            <div class="login-panel">
                <h1>Connexion Admin</h1>
                <p>Accès réservé à l'administration Jim Tech</p>
                <?php if (isset($login_error)): ?>
                    <div class="login-error"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="POST" class="login-form">
                    <input type="password" name="password" placeholder="Mot de passe" required autofocus>
                    <button type="submit" class="btn">Se connecter</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Le reste du code admin seulement si authentifié
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    $errorMessage = 'Erreur de connexion à la base de données : ' . $mysqli->connect_error;
    $requests = [];
} else {
    $mysqli->set_charset(DB_CHARSET);

    $sql = "SELECT id, full_name, email, phone, project_type, budget, source, message, created_at
            FROM quote_requests
            ORDER BY created_at DESC
            LIMIT 200";

    $result = $mysqli->query($sql);
    if (!$result) {
        $errorMessage = 'Erreur lors de la récupération des demandes : ' . $mysqli->error;
        $requests = [];
    } else {
        $requests = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $errorMessage = null;
    }

    $mysqli->close();
}

$totalRequests = count($requests);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jim Tech - Administration des demandes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="admin-header">
        <div class="logo">
            <span>JIM TECH Admin</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.html">Retour au site</a></li>
                <li><a href="admin.php" class="active">Demandes reçues</a></li>
                <li><a href="admin.php?logout=1">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-page">
        <section class="admin-intro">
            <h1>Demandes de devis reçues</h1>
            <p>Consultez ici les demandes envoyées depuis le formulaire de contact et le modal projet.</p>
            <div class="admin-summary">
                <span><strong><?php echo $totalRequests; ?></strong> demandes affichées</span>
                <span>Les plus récentes sont en haut.</span>
            </div>
        </section>

        <?php if (!empty($errorMessage)): ?>
            <div class="admin-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (empty($requests) && empty($errorMessage)): ?>
            <div class="admin-empty">Aucune demande enregistrée pour le moment.</div>
        <?php elseif (!empty($requests)): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Budget</th>
                            <th>Origine</th>
                            <th>Date</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($request['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8'); ?></a></td>
                                <td><?php echo htmlspecialchars($request['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($request['project_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($request['budget'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($request['source'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><textarea readonly><?php echo htmlspecialchars($request['message'], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
