<?php
require_once __DIR__ . '/config.php';

// Tabela: eleitor1 (id_eleitor1, nome, numero_titulo, cidade)
$id    = (int) ($_GET['id'] ?? 0);
$dados = ['nome' => '', 'numero_titulo' => '', 'cidade' => ''];
$erros = [];

// Editar: carrega o registro existente
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM eleitor1 WHERE id_eleitor1 = :id');
    $stmt->execute([':id' => $id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        flash('erro', 'Eleitor não encontrado.');
        redirect('eleitores.php');
    }
    $dados = [
        'nome'          => (string) $registro['nome'],
        'numero_titulo' => (string) $registro['numero_titulo'],
        'cidade'        => (string) ($registro['cidade'] ?? ''),
    ];
}

// Salvar (INSERT ou UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $dados['nome']          = trim((string) ($_POST['nome'] ?? ''));
    // Formato do título: TIT + 3 dígitos (ex.: TIT001). Aceita minúsculas e espaços, e normaliza.
    $dados['numero_titulo'] = strtoupper(preg_replace('/\s+/', '', (string) ($_POST['numero_titulo'] ?? '')));
    $dados['cidade']        = trim((string) ($_POST['cidade'] ?? ''));

    if (mb_strlen($dados['nome']) < 3 || mb_strlen($dados['nome']) > 100) {
        $erros['nome'] = 'Informe o nome completo, com 3 a 100 caracteres.';
    }
    if (!preg_match('/^TIT\d{3}$/', $dados['numero_titulo'])) {
        $erros['numero_titulo'] = 'O título deve começar com TIT seguido de 3 números, como TIT001.';
    } elseif (ja_existe('eleitor1', 'id_eleitor1', 'numero_titulo', $dados['numero_titulo'], $id)) {
        $erros['numero_titulo'] = 'Já existe um eleitor com este título.';
    }
    if (mb_strlen($dados['cidade']) > 80) {
        $erros['cidade'] = 'A cidade pode ter no máximo 80 caracteres.';
    }

    if (!$erros) {
        $cidade = $dados['cidade'] === '' ? null : $dados['cidade'];   // campo opcional

        try {
            if ($id > 0) {
                $sql = 'UPDATE eleitor1 SET nome = :nome, numero_titulo = :titulo, cidade = :cidade
                         WHERE id_eleitor1 = :id';
                $params = [':nome' => $dados['nome'], ':titulo' => $dados['numero_titulo'],
                           ':cidade' => $cidade, ':id' => $id];
                $msg = 'Alterações salvas.';
            } else {
                $sql = 'INSERT INTO eleitor1 (nome, numero_titulo, cidade) VALUES (:nome, :titulo, :cidade)';
                $params = [':nome' => $dados['nome'], ':titulo' => $dados['numero_titulo'],
                           ':cidade' => $cidade];
                $msg = 'Eleitor cadastrado.';
            }
            db()->prepare($sql)->execute($params);

            flash('ok', $msg);
            redirect('eleitores.php');
        } catch (PDOException $ex) {
            if (is_duplicado($ex)) {
                $erros['numero_titulo'] = 'Já existe um eleitor com este título.';
            } else {
                throw $ex;
            }
        }
    }
}

$titulo = $id > 0 ? 'Editar eleitor' : 'Novo eleitor';
$ativo  = 'eleitores';
require __DIR__ . '/includes/header.php';
?>
<div class="cabeca">
  <div>
    <h1><?= e($titulo) ?></h1>
    <p class="sub">Nome e título são obrigatórios. A cidade é opcional.</p>
  </div>
</div>

<form class="painel form" method="post" novalidate>
  <?= csrf_field() ?>

  <div class="campo <?= isset($erros['nome']) ? 'invalido' : '' ?>">
    <label for="nome">Nome completo</label>
    <input type="text" id="nome" name="nome" maxlength="100" required autofocus
           value="<?= e($dados['nome']) ?>" <?= isset($erros['nome']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['nome'])): ?><p class="erro"><?= e($erros['nome']) ?></p><?php endif; ?>
  </div>

  <div class="campo <?= isset($erros['numero_titulo']) ? 'invalido' : '' ?>">
    <label for="numero_titulo">Número do título de eleitor</label>
    <input type="text" id="numero_titulo" name="numero_titulo" maxlength="6" placeholder="TIT001"
           autocapitalize="characters" autocomplete="off"
           data-titulo required value="<?= e($dados['numero_titulo']) ?>"
           <?= isset($erros['numero_titulo']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['numero_titulo'])): ?>
      <p class="erro"><?= e($erros['numero_titulo']) ?></p>
    <?php else: ?>
      <p class="dica">TIT seguido de 3 números, como TIT001 ou TIT108.</p>
    <?php endif; ?>
  </div>

  <div class="campo <?= isset($erros['cidade']) ? 'invalido' : '' ?>">
    <label for="cidade">Cidade (opcional)</label>
    <input type="text" id="cidade" name="cidade" maxlength="80"
           value="<?= e($dados['cidade']) ?>" <?= isset($erros['cidade']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['cidade'])): ?><p class="erro"><?= e($erros['cidade']) ?></p><?php endif; ?>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-confirma"><?= $id > 0 ? 'Salvar alterações' : 'Cadastrar eleitor' ?></button>
    <a class="btn btn-neutro" href="eleitores.php">Cancelar</a>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
