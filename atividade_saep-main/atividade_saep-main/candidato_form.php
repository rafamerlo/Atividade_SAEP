<?php
require_once __DIR__ . '/config.php';

// Tabela: candidato1 (id_candidato1, nome, numero_candidato, cargo, partido_ficticio)
$id     = (int) ($_GET['id'] ?? 0);
$dados  = ['nome' => '', 'numero_candidato' => '', 'cargo' => '', 'partido_ficticio' => ''];
$cargos = CARGOS;
$erros  = [];

// Editar: carrega o registro existente
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM candidato1 WHERE id_candidato1 = :id');
    $stmt->execute([':id' => $id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        flash('erro', 'Candidato não encontrado.');
        redirect('candidatos.php');
    }
    $dados = [
        'nome'             => (string) $registro['nome'],
        'numero_candidato' => (string) $registro['numero_candidato'],
        'cargo'            => (string) $registro['cargo'],
        'partido_ficticio' => (string) ($registro['partido_ficticio'] ?? ''),
    ];
    // Se o cargo salvo não estiver na lista padrão, mantém como opção válida
    if ($dados['cargo'] !== '' && !in_array($dados['cargo'], $cargos, true)) {
        $cargos[] = $dados['cargo'];
    }
}

// Salvar (INSERT ou UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $dados['nome']             = trim((string) ($_POST['nome'] ?? ''));
    $dados['numero_candidato'] = preg_replace('/\D/', '', (string) ($_POST['numero_candidato'] ?? ''));
    $dados['cargo']            = (string) ($_POST['cargo'] ?? '');
    $dados['partido_ficticio'] = trim((string) ($_POST['partido_ficticio'] ?? ''));

    if (mb_strlen($dados['nome']) < 3 || mb_strlen($dados['nome']) > 100) {
        $erros['nome'] = 'Informe o nome, com 3 a 100 caracteres.';
    }
    if (!preg_match('/^[1-9]\d{1,4}$/', $dados['numero_candidato'])) {
        $erros['numero_candidato'] = 'O número deve ter de 2 a 5 dígitos e não pode começar com zero.';
    } elseif (ja_existe('candidato1', 'id_candidato1', 'numero_candidato', (int) $dados['numero_candidato'], $id)) {
        $erros['numero_candidato'] = 'Já existe um candidato com este número.';
    }
    if (!in_array($dados['cargo'], $cargos, true)) {
        $erros['cargo'] = 'Escolha um cargo da lista.';
    }
    if (mb_strlen($dados['partido_ficticio']) > 50) {
        $erros['partido_ficticio'] = 'O partido pode ter no máximo 50 caracteres.';
    }

    if (!$erros) {
        $numero  = (int) $dados['numero_candidato'];                       // coluna INT
        $partido = $dados['partido_ficticio'] === '' ? null : $dados['partido_ficticio'];   // opcional

        try {
            if ($id > 0) {
                $sql = 'UPDATE candidato1
                           SET nome = :nome, numero_candidato = :numero, cargo = :cargo, partido_ficticio = :partido
                         WHERE id_candidato1 = :id';
                $params = [':nome' => $dados['nome'], ':numero' => $numero, ':cargo' => $dados['cargo'],
                           ':partido' => $partido, ':id' => $id];
                $msg = 'Alterações salvas.';
            } else {
                $sql = 'INSERT INTO candidato1 (nome, numero_candidato, cargo, partido_ficticio)
                        VALUES (:nome, :numero, :cargo, :partido)';
                $params = [':nome' => $dados['nome'], ':numero' => $numero, ':cargo' => $dados['cargo'],
                           ':partido' => $partido];
                $msg = 'Candidato cadastrado.';
            }
            db()->prepare($sql)->execute($params);

            flash('ok', $msg);
            redirect('candidatos.php');
        } catch (PDOException $ex) {
            if (is_duplicado($ex)) {
                $erros['numero_candidato'] = 'Já existe um candidato com este número.';
            } else {
                throw $ex;
            }
        }
    }
}

$titulo = $id > 0 ? 'Editar candidato' : 'Novo candidato';
$ativo  = 'candidatos';
require __DIR__ . '/includes/header.php';
?>
<div class="cabeca">
  <div>
    <h1><?= e($titulo) ?></h1>
    <p class="sub">Nome, número e cargo são obrigatórios. O partido é opcional.</p>
  </div>
</div>

<form class="painel form" method="post" novalidate>
  <?= csrf_field() ?>

  <div class="campo <?= isset($erros['nome']) ? 'invalido' : '' ?>">
    <label for="nome">Nome do candidato</label>
    <input type="text" id="nome" name="nome" maxlength="100" required autofocus
           value="<?= e($dados['nome']) ?>" <?= isset($erros['nome']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['nome'])): ?><p class="erro"><?= e($erros['nome']) ?></p><?php endif; ?>
  </div>

  <div class="campo <?= isset($erros['numero_candidato']) ? 'invalido' : '' ?>">
    <label for="numero_candidato">Número na urna</label>
    <input type="text" id="numero_candidato" name="numero_candidato" maxlength="5" inputmode="numeric"
           data-digitos required value="<?= e($dados['numero_candidato']) ?>"
           <?= isset($erros['numero_candidato']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['numero_candidato'])): ?>
      <p class="erro"><?= e($erros['numero_candidato']) ?></p>
    <?php else: ?>
      <p class="dica">De 2 a 5 dígitos, só números.</p>
    <?php endif; ?>
    <div class="urna" aria-hidden="true">
      <span class="urna-rotulo">Como aparece na urna</span>
      <span class="digitos grande" id="previa-numero"></span>
    </div>
  </div>

  <div class="campo <?= isset($erros['cargo']) ? 'invalido' : '' ?>">
    <label for="cargo">Cargo</label>
    <select id="cargo" name="cargo" required <?= isset($erros['cargo']) ? 'aria-invalid="true"' : '' ?>>
      <option value="">Selecione um cargo</option>
      <?php foreach ($cargos as $c): ?>
        <option value="<?= e($c) ?>" <?= $dados['cargo'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($erros['cargo'])): ?><p class="erro"><?= e($erros['cargo']) ?></p><?php endif; ?>
  </div>

  <div class="campo <?= isset($erros['partido_ficticio']) ? 'invalido' : '' ?>">
    <label for="partido_ficticio">Partido (opcional)</label>
    <input type="text" id="partido_ficticio" name="partido_ficticio" maxlength="50"
           value="<?= e($dados['partido_ficticio']) ?>"
           <?= isset($erros['partido_ficticio']) ? 'aria-invalid="true"' : '' ?>>
    <?php if (isset($erros['partido_ficticio'])): ?>
      <p class="erro"><?= e($erros['partido_ficticio']) ?></p>
    <?php else: ?>
      <p class="dica">Use um nome de partido fictício.</p>
    <?php endif; ?>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-confirma"><?= $id > 0 ? 'Salvar alterações' : 'Cadastrar candidato' ?></button>
    <a class="btn btn-neutro" href="candidatos.php">Cancelar</a>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
