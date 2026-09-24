<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('candidatos.php');
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM candidato1 WHERE id_candidato1 = :id');
    $stmt->execute([':id' => $id]);
    $stmt->rowCount() > 0
        ? flash('ok', 'Candidato excluído.')
        : flash('erro', 'Candidato não encontrado.');
} else {
    flash('erro', 'Candidato não encontrado.');
}

redirect('candidatos.php');
