<?php
declare(strict_types=1);

/* ------------------------------------------------------------------
 * Configuração e funções auxiliares usadas por todas as páginas.
 * Ajuste as constantes abaixo conforme o seu MySQL.
 * ------------------------------------------------------------------ */

const DB_HOST = 'localhost';
const DB_NAME = 'eleitor';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP/WAMP: geralmente vazio

const CARGOS = [
    'Presidente',
    'Governador',
    'Senador',
    'Deputado Federal',
    'Deputado Estadual',
    'Prefeito',
    'Vereador',
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Conexão PDO única (reaproveitada em toda a requisição). */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Não foi possível conectar ao banco de dados. '
               . 'Confira o MySQL e as constantes em config.php.');
        }
    }

    return $pdo;
}

/** Escapa texto para exibir em HTML (evita XSS). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Mensagens de retorno (aparecem uma vez) ---------- */

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensagem];
}

function get_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Proteção CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(400);
        exit('Requisição inválida. Volte e tente novamente.');
    }
}

/* ---------- Formatação ---------- */

/** Mostra o valor escapado ou um texto discreto quando o campo (opcional) está vazio. */
function ou_vazio($valor, string $texto): string
{
    $valor = trim((string) $valor);
    return $valor !== '' ? e($valor) : '<span class="suave">' . e($texto) . '</span>';
}

/** Mostra o número do candidato em caixinhas, como na urna. */
function digitos(string $numero): string
{
    $html = '<span class="digitos"><span class="sr-only">Número ' . e($numero) . '</span>';
    foreach (str_split($numero) as $d) {
        $html .= '<span class="d" aria-hidden="true">' . e($d) . '</span>';
    }
    return $html . '</span>';
}

/** Código 1062 do MySQL = valor duplicado em chave UNIQUE. */
function is_duplicado(PDOException $e): bool
{
    return (int) ($e->errorInfo[1] ?? 0) === 1062;
}

/**
 * Já existe OUTRO registro com o mesmo valor?
 * Ao editar, ignora o próprio registro ($idAtual). Ao cadastrar, use 0.
 * Use apenas nomes fixos de tabela/coluna (nunca dados vindos do usuário).
 */
function ja_existe(string $tabela, string $colunaId, string $coluna, $valor, int $idAtual): bool
{
    $stmt = db()->prepare("SELECT COUNT(*) FROM `$tabela` WHERE `$coluna` = :valor AND `$colunaId` <> :id");
    $stmt->execute([':valor' => $valor, ':id' => $idAtual]);
    return (int) $stmt->fetchColumn() > 0;
}
