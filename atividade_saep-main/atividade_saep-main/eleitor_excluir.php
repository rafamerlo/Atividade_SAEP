<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('eleitores.php');
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM eleitor1 WHERE id_eleitor1 = :id');
    $stmt->execute([':id' => $id]);
    $stmt->rowCount() > 0
        ? flash('ok', 'Eleitor excluído.')
        : flash('erro', 'Eleitor não encontrado.');
} else {
    flash('erro', 'Eleitor não encontrado.');
}

redirect('eleitores.php');
