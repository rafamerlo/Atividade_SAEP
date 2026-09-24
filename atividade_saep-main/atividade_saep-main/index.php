<?php
require_once __DIR__ . '/config.php';

$totalEleitores  = (int) db()->query('SELECT COUNT(*) FROM eleitor1')->fetchColumn();
$totalCandidatos = (int) db()->query('SELECT COUNT(*) FROM candidato1')->fetchColumn();

$ultimosEleitores  = db()->query('SELECT nome, cidade FROM eleitor1 ORDER BY id_eleitor1 DESC LIMIT 5')->fetchAll();
$ultimosCandidatos = db()->query('SELECT nome, numero_candidato, cargo FROM candidato1 ORDER BY id_candidato1 DESC LIMIT 5')->fetchAll();

$titulo = 'Início';
$ativo  = 'inicio';
require __DIR__ . '/includes/header.php';
?>
<div class="cabeca">
  <div>
    <h1>Cadastro eleitoral</h1>
    <p class="sub">Cadastre, consulte, atualize e exclua eleitores e candidatos.</p>
  </div>
</div>

<div class="grade">
  <section class="painel bloco">
    <div class="bloco-topo">
      <h2>Eleitores</h2>
      <span class="contagem"><?= $totalEleitores ?> <?= $totalEleitores === 1 ? 'cadastrado' : 'cadastrados' ?></span>
    </div>
    <?php if ($ultimosEleitores): ?>
      <ul class="recentes">
        <?php foreach ($ultimosEleitores as $r): ?>
          <li><span><?= e($r['nome']) ?></span><span class="suave"><?= ou_vazio($r['cidade'], 'Sem cidade') ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="vazio-mini">Nenhum eleitor cadastrado ainda.</p>
    <?php endif; ?>
    <div class="bloco-acoes">
      <a class="btn btn-confirma" href="eleitor_form.php">Novo eleitor</a>
      <a class="btn btn-neutro" href="eleitores.php">Ver todos</a>
    </div>
  </section>

  <section class="painel bloco">
    <div class="bloco-topo">
      <h2>Candidatos</h2>
      <span class="contagem"><?= $totalCandidatos ?> <?= $totalCandidatos === 1 ? 'cadastrado' : 'cadastrados' ?></span>
    </div>
    <?php if ($ultimosCandidatos): ?>
      <ul class="recentes">
        <?php foreach ($ultimosCandidatos as $r): ?>
          <li>
            <span class="linha-cand"><?= digitos((string) $r['numero_candidato']) ?> <?= e($r['nome']) ?></span>
            <span class="suave"><?= e($r['cargo']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="vazio-mini">Nenhum candidato cadastrado ainda.</p>
    <?php endif; ?>
    <div class="bloco-acoes">
      <a class="btn btn-confirma" href="candidato_form.php">Novo candidato</a>
      <a class="btn btn-neutro" href="candidatos.php">Ver todos</a>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
