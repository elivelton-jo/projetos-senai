<?php
session_start();
require_once 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }

$mensagem = "";
$erro = "";

// Ação para concluir agendamento rapidamente
if (isset($_GET['concluir_id'])) {
    $concluir_id = $_GET['concluir_id'];
    $stmt_up = $pdo->prepare("UPDATE agendamentos SET estado = 'Concluído' WHERE id = ?");
    $stmt_up->execute([$concluir_id]);
    $mensagem = "Atendimento marcado como concluído e faturamento atualizado!";
}

// Processar Agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $servico_id = $_POST['servico_id'] ?? '';
    $barbeiro_id = $_POST['barbeiro_id'] ?? '';
    $data_agendamento = $_POST['data_agendamento'] ?? '';
    $hora_agendamento = $_POST['hora_agendamento'] ?? '';
    
    if (!empty($cliente_id) && !empty($servico_id) && !empty($barbeiro_id) && !empty($data_agendamento) && !empty($hora_agendamento)) {
        $data_hora = $data_agendamento . ' ' . $hora_agendamento . ':00';
        
        $conflito = $pdo->prepare("SELECT id FROM agendamentos WHERE barbeiro_id = ? AND data_hora = ? AND estado != 'Cancelado'");
        $conflito->execute([$barbeiro_id, $data_hora]);
        
        if ($conflito->rowCount() > 0) {
            $erro = "Este profissional já possui um agendamento marcado para este dia e horário!";
        } else {
            $s_stmt = $pdo->prepare("SELECT preco FROM servicos WHERE id = ?");
            $s_stmt->execute([$servico_id]);
            $servico_info = $s_stmt->fetch(PDO::FETCH_ASSOC);
            $valor = $servico_info['preco'] ?? 0;

            $stmt = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, barbeiro_id, data_hora, valor, estado) VALUES (?, ?, ?, ?, ?, 'Agendado')");
            $stmt->execute([$cliente_id, $servico_id, $barbeiro_id, $data_hora, $valor]);
            $mensagem = "Agendamento criado com sucesso!";
        }
    } else {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    }
}

$clientes = $pdo->query("SELECT id, nome_completo FROM clientes ORDER BY nome_completo ASC")->fetchAll(PDO::FETCH_ASSOC);
$servicos = $pdo->query("SELECT id, nome, preco FROM servicos ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$barbeiros = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'barbeiro' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

$agendamentos = $pdo->query("
    SELECT a.*, c.nome_completo as cliente_nome, s.nome as servico_nome, u.nome as barbeiro_nome 
    FROM agendamentos a
    INNER JOIN clientes c ON a.cliente_id = c.id
    INNER JOIN servicos s ON a.servico_id = s.id
    INNER JOIN usuarios u ON a.barbeiro_id = u.id
    ORDER BY a.data_hora DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Gestão de Agendamentos | Guappo Barbearia</title>
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

        h3 { font-size: 17px; margin: 45px 0 20px 0; }

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

        a.voltar:hover { color: var(--guappo-gold); transform: translateX(-3px); }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .form-group { position: relative; }

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

        .btn-submit:hover { background: var(--guappo-gold-hover); transform: translateY(-2px); }
        
        .table-container { width: 100%; overflow-x: auto; border-radius: 10px; border: 1px solid var(--guappo-border); margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; white-space: nowrap; }
        th, td { padding: 16px 18px; border-bottom: 1px solid var(--guappo-border); text-align: left; }
        th { background: rgba(222, 213, 192, 0.04); color: var(--guappo-gold); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 1.2px; }
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

        .badge-estado {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            background: rgba(222, 213, 192, 0.08);
            color: var(--guappo-gold);
            border: 1px solid rgba(222, 213, 192, 0.15);
        }

        .badge-concluido {
            background: rgba(76, 209, 55, 0.12);
            color: #61f249;
            border-color: rgba(76, 209, 55, 0.3);
        }

        .btn-concluir {
            background: rgba(76, 209, 55, 0.15);
            color: #61f249;
            border: 1px solid rgba(76, 209, 55, 0.35);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .btn-concluir:hover {
            background: rgba(76, 209, 55, 0.25);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            body { padding: 15px; }
            .container { padding: 20px; }
            .form-grid { grid-template-columns: 1fr; gap: 16px; }
            .btn-submit { grid-column: span 1; }
        }
    </style>
    <script>
        function atualizarValor() {
            const select = document.getElementById('servico_id');
            const selectedOption = select.options[select.selectedIndex];
            const preco = selectedOption.getAttribute('data-preco') || '0.00';
            document.getElementById('valor').value = parseFloat(preco).toFixed(2);
        }
    </script>
    <!-- Camada visual Guappo: não altera a lógica da página -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/guappo.css">
    <script src="assets/js/guappo.js" defer></script>
</head>
<body data-page="agendamentos">
    <div class="container">
        <div class="header-flex">
            <h2>Novo Agendamento</h2>
            <a href="dashboard.php" class="voltar">&larr; Voltar ao Painel</a>
        </div>
        
        <?php if (!empty($mensagem)): ?><div class="msg"><?= $mensagem ?></div><?php endif; ?>
        <?php if (!empty($erro)): ?><div class="erro"><?= $erro ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">-- Selecione o Cliente --</option>
                        <?php foreach ($clientes as $cli): ?>
                            <option value="<?= $cli['id'] ?>"><?= htmlspecialchars($cli['nome_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Serviço</label>
                    <select name="servico_id" id="servico_id" onchange="atualizarValor()" required>
                        <option value="">-- Escolha o Serviço --</option>
                        <?php foreach ($servicos as $serv): ?>
                            <option value="<?= $serv['id'] ?>" data-preco="<?= $serv['preco'] ?>">
                                <?= htmlspecialchars($serv['nome']) ?> (R$ <?= number_format($serv['preco'], 2, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Profissional / Barbeiro</label>
                    <select name="barbeiro_id" required>
                        <option value="">-- Selecione o Profissional --</option>
                        <?php foreach ($barbeiros as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="text" id="valor" value="0.00" readonly style="color: var(--guappo-gold); font-weight: 600;">
                </div>

                <div class="form-group">
                    <label>Data do Atendimento</label>
                    <input type="date" name="data_agendamento" required min="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Horário (Das 08:00 às 18:00)</label>
                    <select name="hora_agendamento" required>
                        <option value="">-- Selecione o Horário --</option>
                        <?php 
                        for ($h = 8; $h <= 18; $h++) {
                            foreach (['00', '30'] as $m) {
                                if ($h == 18 && $m == '30') continue;
                                $horarioStr = sprintf('%02d:%s', $h, $m);
                                echo "<option value=\"$horarioStr\">$horarioStr</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">Confirmar Agendamento</button>
        </form>

        <h3>Histórico de Agendamentos & Faturamento</h3>
        <div class="table-container">
            <table>
                <tr>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Profissional</th>
                    <th>Data e Hora</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($agendamentos as $ag): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ag['cliente_nome']) ?></strong></td>
                    <td><?= htmlspecialchars($ag['servico_nome']) ?></td>
                    <td><?= htmlspecialchars($ag['barbeiro_nome']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?></td>
                    <td style="color: var(--guappo-gold); font-weight: 500;">R$ <?= number_format($ag['valor'], 2, ',', '.') ?></td>
                    <td>
                        <?php if ($ag['estado'] == 'Concluído'): ?>
                            <span class="badge-estado badge-concluido">Concluído</span>
                        <?php else: ?>
                            <span class="badge-estado"><?= htmlspecialchars($ag['estado']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($ag['estado'] != 'Concluído'): ?>
                            <a href="agendamentos.php?concluir_id=<?= $ag['id'] ?>" class="btn-concluir">Marcar Concluído</a>
                        <?php else: ?>
                            <span style="color: var(--guappo-muted); font-size: 12px;">Finalizado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>