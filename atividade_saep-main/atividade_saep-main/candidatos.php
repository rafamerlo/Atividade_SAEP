<?php
require_once __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = db()->prepare(
        'SELECT * FROM candidato1
          WHERE nome LIKE :a OR numero_candidato LIKE :b OR cargo LIKE :c OR partido_ficticio LIKE :d
          ORDER BY cargo, numero_candidato'
    );
    $stmt->execute([':a' => $like, ':b' => $like, ':c' => $like, ':d' => $like]);
} else {
    $stmt = db()->query('SELECT * FROM candidato1 ORDER BY cargo, numero_candidato');
}
$candidatos = $stmt->fetchAll();

$titulo = 'Candidatos';
$ativo  = 'candidatos';
require __DIR__ . '/includes/header.php';
?>
<div class="cabeca">
  <div>
    <h1>Candidatos</h1>
    <p class="sub"><?= count($candidatos) ?> <?= count($candidatos) === 1 ? 'resultado' : 'resultados' ?><?= $q !== '' ? ' para "' . e($q) . '"' : '' ?></p>
  </div>
  <a class="btn btn-confirma" href="candidato_form.php">Novo candidato</a>
</div>

<form class="busca" method="get" role="search">
  <label class="sr-only" for="q">Buscar candidato</label>
  <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Buscar por nome, número, cargo ou partido">
  <button class="btn btn-neutro" type="submit">Buscar</button>
  <?php if ($q !== ''): ?><a class="btn btn-fantasma" href="candidatos.php">Limpar</a><?php endif; ?>
</form>

<div class="painel">
<?php if ($candidatos): ?>
  <div class="tabela-wrap">
    <table>
      <thead>
        <tr><th>Número</th><th>Nome</th><th>Cargo</th><th>Partido</th><th><span class="sr-only">Ações</span></th></tr>
      </thead>
      <tbody>
      <?php foreach ($candidatos as $r): ?>
        <tr>
          <td><?= digitos((string) $r['numero_candidato']) ?></td>
          <td><?= e($r['nome']) ?></td>
          <td><?= e($r['cargo']) ?></td>
          <td><?= ou_vazio($r['partido_ficticio'], 'Sem partido') ?></td>
          <td class="acoes">
            <a class="btn btn-sm btn-corrige" href="candidato_form.php?id=<?= (int) $r['id_candidato1'] ?>">Editar</a>
            <button type="button" class="btn btn-sm btn-excluir" data-excluir
                    data-id="<?= (int) $r['id_candidato1'] ?>" data-nome="<?= e($r['nome']) ?>">Excluir</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php elseif ($q !== ''): ?>
  <div class="vazio">
    <p>Nenhum candidato encontrado para "<?= e($q) ?>".</p>
    <a class="btn btn-neutro" href="candidatos.php">Ver todos os candidatos</a>
  </div>
<?php else: ?>
  <div class="vazio">
    <p>Nenhum candidato cadastrado ainda.</p>
    <a class="btn btn-confirma" href="candidato_form.php">Cadastrar o primeiro candidato</a>
  </div>
<?php endif; ?>
</div>

<dialog id="dlg-excluir" aria-labelledby="dlg-titulo">
  <form method="post" action="candidato_excluir.php">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="">
    <h2 id="dlg-titulo">Excluir candidato?</h2>
    <p><strong data-nome></strong> será removido do cadastro. Essa ação não pode ser desfeita.</p>
    <div class="form-acoes">
      <button type="submit" class="btn btn-perigo">Excluir candidato</button>
      <button type="button" class="btn btn-neutro" data-fechar>Cancelar</button>
    </div>
  </form>
</dialog>
<?php require __DIR__ . '/includes/footer.php'; ?>
