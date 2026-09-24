<?php
require_once __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = db()->prepare(
        'SELECT * FROM eleitor1
          WHERE nome LIKE :a OR numero_titulo LIKE :b OR cidade LIKE :c
          ORDER BY nome'
    );
    $stmt->execute([':a' => $like, ':b' => $like, ':c' => $like]);
} else {
    $stmt = db()->query('SELECT * FROM eleitor1 ORDER BY nome');
}
$eleitores = $stmt->fetchAll();

$titulo = 'Eleitores';
$ativo  = 'eleitores';
require __DIR__ . '/includes/header.php';
?>
<div class="cabeca">
  <div>
    <h1>Eleitores</h1>
    <p class="sub"><?= count($eleitores) ?> <?= count($eleitores) === 1 ? 'resultado' : 'resultados' ?><?= $q !== '' ? ' para "' . e($q) . '"' : '' ?></p>
  </div>
  <a class="btn btn-confirma" href="eleitor_form.php">Novo eleitor</a>
</div>

<form class="busca" method="get" role="search">
  <label class="sr-only" for="q">Buscar eleitor</label>
  <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Buscar por nome, título ou cidade">
  <button class="btn btn-neutro" type="submit">Buscar</button>
  <?php if ($q !== ''): ?><a class="btn btn-fantasma" href="eleitores.php">Limpar</a><?php endif; ?>
</form>

<div class="painel">
<?php if ($eleitores): ?>
  <div class="tabela-wrap">
    <table>
      <thead>
        <tr><th>Nome</th><th>Título de eleitor</th><th>Cidade</th><th><span class="sr-only">Ações</span></th></tr>
      </thead>
      <tbody>
      <?php foreach ($eleitores as $r): ?>
        <tr>
          <td><?= e($r['nome']) ?></td>
          <td><?= e($r['numero_titulo']) ?></td>
          <td><?= ou_vazio($r['cidade'], 'Não informada') ?></td>
          <td class="acoes">
            <a class="btn btn-sm btn-corrige" href="eleitor_form.php?id=<?= (int) $r['id_eleitor1'] ?>">Editar</a>
            <button type="button" class="btn btn-sm btn-excluir" data-excluir
                    data-id="<?= (int) $r['id_eleitor1'] ?>" data-nome="<?= e($r['nome']) ?>">Excluir</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php elseif ($q !== ''): ?>
  <div class="vazio">
    <p>Nenhum eleitor encontrado para "<?= e($q) ?>".</p>
    <a class="btn btn-neutro" href="eleitores.php">Ver todos os eleitores</a>
  </div>
<?php else: ?>
  <div class="vazio">
    <p>Nenhum eleitor cadastrado ainda.</p>
    <a class="btn btn-confirma" href="eleitor_form.php">Cadastrar o primeiro eleitor</a>
  </div>
<?php endif; ?>
</div>

<dialog id="dlg-excluir" aria-labelledby="dlg-titulo">
  <form method="post" action="eleitor_excluir.php">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="">
    <h2 id="dlg-titulo">Excluir eleitor?</h2>
    <p><strong data-nome></strong> será removido do cadastro. Essa ação não pode ser desfeita.</p>
    <div class="form-acoes">
      <button type="submit" class="btn btn-perigo">Excluir eleitor</button>
      <button type="button" class="btn btn-neutro" data-fechar>Cancelar</button>
    </div>
  </form>
</dialog>
<?php require __DIR__ . '/includes/footer.php'; ?>
