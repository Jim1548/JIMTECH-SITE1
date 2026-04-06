<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée.']);
    exit;
}

$full_name = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS));
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = trim(filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS));
$project_type = trim(filter_input(INPUT_POST, 'type_projet', FILTER_SANITIZE_SPECIAL_CHARS));
$budget = trim(filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_SPECIAL_CHARS));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));
$source = trim(filter_input(INPUT_POST, 'source', FILTER_SANITIZE_SPECIAL_CHARS));

if (!$full_name || !$email || !$message) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Merci de remplir au moins votre nom, votre e-mail et votre message.']);
    exit;
}

$allowedProjectTypes = ['web', 'design', 'video', 'global', 'other'];
$allowedBudgets = ['small', 'medium', 'large', 'unknown'];

if (!in_array($project_type, $allowedProjectTypes, true)) {
    $project_type = 'other';
}
if (!in_array($budget, $allowedBudgets, true)) {
    $budget = 'unknown';
}
if (!$source) {
    $source = 'site-form';
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

$mysqli->set_charset(DB_CHARSET);

$sql = "INSERT INTO quote_requests
    (full_name, email, phone, project_type, budget, message, source)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossible de préparer la requête.']);
    exit;
}

$stmt->bind_param(
    'sssssss',
    $full_name,
    $email,
    $phone,
    $project_type,
    $budget,
    $message,
    $source
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossible d’enregistrer votre demande.']);
    $stmt->close();
    $mysqli->close();
    exit;
}

$stmt->close();
$mysqli->close();

echo json_encode(['status' => 'success', 'message' => 'Merci ! Votre demande a bien été enregistrée.']);
