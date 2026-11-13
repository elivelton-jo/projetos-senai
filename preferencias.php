<?php
session_start();
require_once 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }

$mensagem = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $estilo_corte = $_POST['estilo_corte'] ?? '';
    $estilo_barba = $_POST['estilo_barba'] ?? '';
    $detalhes_estilo = $_POST['detalhes_estilo'] ?? '';
    $acabamento_barba = $_POST['acabamento_barba'] ?? '';
    $tratamento_facial = $_POST['tratamento_facial'] ?? '';
    $produto_fixacao = $_POST['produto_fixacao'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    if (!empty($cliente_id)) {
        $checar = $pdo->prepare("SELECT id FROM preferencias_clientes WHERE cliente_id = ?");
        $checar->execute([$cliente_id]);

        if ($checar->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE preferencias_clientes SET estilo_corte = ?, estilo_barba = ?, detalhes_estilo = ?, acabamento_barba = ?, tratamento_facial = ?, produto_fixacao = ?, observacoes = ? WHERE cliente_id = ?");
            $stmt->execute([$estilo_corte, $estilo_barba, $detalhes_estilo, $acabamento_barba, $tratamento_facial, $produto_fixacao, $observacoes, $cliente_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO preferencias_clientes (cliente_id, estilo_corte, estilo_barba, detalhes_estilo, acabamento_barba, tratamento_facial, produto_fixacao, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cliente_id, $estilo_corte, $estilo_barba, $detalhes_estilo, $acabamento_barba, $tratamento_facial, $produto_fixacao, $observacoes]);
        }
        $mensagem = "Perfil estético e preferências guardados com sucesso!";
    } else {
        $erro = "Por favor, selecione um cliente.";
    }
}

$clientes = $pdo->query("SELECT id, nome_completo FROM clientes ORDER BY nome_completo ASC")->fetchAll(PDO::FETCH_ASSOC);
$perfis = $pdo->query("
    SELECT p.*, c.nome_completo as cliente_nome 
    FROM preferencias_clientes p 
    INNER JOIN clientes c ON p.cliente_id = c.id 
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Perfil Estético | Guappo Barbearia</title>
    <style>
        :root { 
            --guappo-bg: #0b1812; 
            --guappo-card-bg: rgba(22, 44, 32, 0.75); 
            --guappo-gold: #DED5C0; 
            --guappo-gold-hover: #c9bc9e; 
            --guappo-text: #F4F1EA; 
            --guappo-muted: #9aa8a0;
            --guappo-input-bg: rgba(11, 24, 18, 0.85);
            --guappo-border: rgba(222, 213, 192, 0.12);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body { 
            font-family: 'Sora', sans-serif; 
            background: linear-gradient(135deg, #07100b 0%, var(--guappo-bg) 100%);
            color: var(--guappo-text); 
            margin: 0; 
            padding: 40px 20px; 
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container { 
            max-width: 1100px; 
            margin: auto; 
            background: var(--guappo-card-bg); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 45px; 
            border-radius: 16px; 
            border: 1px solid var(--guappo-border); 
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45); 
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid var(--guappo-border); 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }

        h2, h3 { 
            color: var(--guappo-gold); 
            margin: 0; 
            font-size: 22px; 
            font-weight: 600; 
            letter-spacing: -0.3px;
        }

        h3 { 
            font-size: 17px; 
            margin: 45px 0 20px 0; 
        }

        a.voltar { 
            color: var(--guappo-muted); 
            text-decoration: none; 
            font-size: 13px; 
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        a.voltar:hover { 
            color: var(--guappo-gold); 
            transform: translateX(-3px);
        }
        
        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 24px; 
        }

        .full-width { grid-column: span 2; }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1.2px; 
            color: rgba(222, 213, 192, 0.7); 
            font-weight: 500;
        }

        select, input { 
            width: 100%; 
            padding: 14px 18px; 
            background: var(--guappo-input-bg); 
            border: 1px solid var(--guappo-border); 
            border-radius: 10px; 
            color: var(--guappo-text); 
            font-size: 13.5px; 
            font-family: 'Sora', sans-serif;
            box-sizing: border-box; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23DED5C0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 45px;
            cursor: pointer;
        }

        select:hover, input:hover { border-color: rgba(222, 213, 192, 0.25); }

        select:focus, input:focus { 
            border-color: var(--guappo-gold); 
            outline: none; 
            background: rgba(11, 24, 18, 0.95);
            box-shadow: 0 0 0 3px rgba(222, 213, 192, 0.12), 0 4px 12px rgba(0,0,0,0.2); 
        }
        
        .btn-submit { 
            background: var(--guappo-gold); 
            color: var(--guappo-bg); 
            font-weight: 600; 
            font-family: 'Sora', sans-serif;
            text-transform: uppercase; 
            letter-spacing: 1.2px; 
            cursor: pointer; 
            border: none; 
            padding: 16px; 
            border-radius: 10px; 
            width: 100%; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            margin-top: 30px; 
            grid-column: span 2; 
            box-shadow: 0 4px 15px rgba(222, 213, 192, 0.15);
        }

        .btn-submit:hover { 
            background: var(--guappo-gold-hover); 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(222, 213, 192, 0.25);
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--guappo-border);
            margin-top: 15px;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 13.5px; 
            white-space: nowrap;
        }

        th, td { 
            padding: 16px 18px; 
            border-bottom: 1px solid var(--guappo-border); 
            text-align: left; 
        }

        th { 
            background: rgba(222, 213, 192, 0.04); 
            color: var(--guappo-gold); 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 1.2px; 
        }

        tr { transition: background-color 0.2s ease; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(222, 213, 192, 0.03); }
        
        .msg, .erro { 
            padding: 14px 18px; 
            border-radius: 10px; 
            margin-bottom: 25px; 
            font-size: 13.5px; 
            animation: slideInDown 0.4s ease forwards;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .msg { background: rgba(76, 209, 55, 0.12); border: 1px solid rgba(76, 209, 55, 0.3); color: #61f249; }
        .erro { background: rgba(242, 139, 130, 0.12); border: 1px solid rgba(242, 139, 130, 0.3); color: #ff8b82; }

        @media (max-width: 768px) {
            body { padding: 15px; }
            .container { padding: 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width, .btn-submit { grid-column: span 1; }
        }
    </style>
    <!-- Camada visual Guappo: não altera a lógica da página -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/guappo.css">
    <script src="assets/js/guappo.js" defer></script>
</head>
<body data-page="preferencias">
    <div class="container">
        <div class="header-flex">
            <h2>Perfil Estético e Preferências</h2>
            <a href="dashboard.php" class="voltar">&larr; Voltar ao Painel</a>
        </div>
        
        <?php if (!empty($mensagem)): ?><div class="msg"><?= $mensagem ?></div><?php endif; ?>
        <?php if (!empty($erro)): ?><div class="erro"><?= $erro ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="full-width">
                    <label>Selecionar Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">-- Selecione o Cliente Registado --</option>
                        <?php foreach ($clientes as $cli): ?>
                            <option value="<?= $cli['id'] ?>"><?= htmlspecialchars($cli['nome_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Estilo de Corte Preferido</label>
                    <input type="text" name="estilo_corte" placeholder="Ex: Low Fade Executivo" required>
                </div>

                <div>
                    <label>Acabamento de Barba</label>
                    <input type="text" name="acabamento_barba" placeholder="Ex: Desenhada a Navalha" required>
                </div>

                <div>
                    <label>Detalhes do Estilo</label>
                    <input type="text" name="detalhes_estilo" placeholder="Ex: Degradê nas costeletas, risco navalhado">
                </div>

                <div>
                    <label>Tratamento Facial / Rosto</label>
                    <input type="text" name="tratamento_facial" placeholder="Ex: Toalha Quente com Óleos Essenciais">
                </div>

                <div>
                    <label>Produto de Fixação Habitual</label>
                    <input type="text" name="produto_fixacao" placeholder="Ex: Pomada Efeito Mate (Seca)">
                </div>

                <div>
                    <label>Observações ou Alergias</label>
                    <input type="text" name="observacoes" placeholder="Ex: Pele sensível a lâminas">
                </div>

                <input type="hidden" name="estilo_barba" value="Padrão">
            </div>

            <button type="submit" class="btn-submit">Guardar Perfil de Estilo</button>
        </form>

        <h3>Perfis Ativos no Salão</h3>
        <div class="table-container">
            <table>
                <tr>
                    <th>Cliente</th>
                    <th>Corte</th>
                    <th>Barba</th>
                    <th>Tratamento Facial</th>
                    <th>Produto</th>
                </tr>
                <?php foreach ($perfis as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['cliente_nome']) ?></strong></td>
                    <td><?= htmlspecialchars($p['estilo_corte']) ?></td>
                    <td><?= htmlspecialchars($p['acabamento_barba']) ?></td>
                    <td><?= htmlspecialchars($p['tratamento_facial'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($p['produto_fixacao'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>