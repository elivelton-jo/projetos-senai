<?php
session_start();
require_once 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }

$mensagem = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_completo = $_POST['nome_completo'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!empty($nome_completo) && !empty($telefone)) {
        $stmt = $pdo->prepare("INSERT INTO clientes (nome_completo, telefone, email) VALUES (?, ?, ?)");
        $stmt->execute([$nome_completo, $telefone, $email]);
        $mensagem = "Cliente registado com sucesso!";
    } else {
        $erro = "Por favor, preencha os campos obrigatórios (Nome e Telefone).";
    }
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome_completo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Gestão de Clientes | Guappo Barbearia</title>
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
            grid-template-columns: repeat(3, 1fr); 
            gap: 24px; 
        }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1.2px; 
            color: rgba(222, 213, 192, 0.7); 
            font-weight: 500;
        }

        input { 
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

        input:hover {
            border-color: rgba(222, 213, 192, 0.25);
        }

        input:focus { 
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
            grid-column: span 3; 
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
            .form-grid { grid-template-columns: 1fr; gap: 16px; }
            .btn-submit { grid-column: span 1; }
        }
    </style>
    <!-- Camada visual Guappo: não altera a lógica da página -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/guappo.css">
    <script src="assets/js/guappo.js" defer></script>
</head>
<body data-page="clientes">
    <div class="container">
        <div class="header-flex">
            <h2>Gestão de Clientes</h2>
            <a href="dashboard.php" class="voltar">&larr; Voltar ao Painel</a>
        </div>
        
        <?php if (!empty($mensagem)): ?><div class="msg"><?= $mensagem ?></div><?php endif; ?>
        <?php if (!empty($erro)): ?><div class="erro"><?= $erro ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Nome Completo</label>
                    <input type="text" name="nome_completo" placeholder="Ex: Ricardo Alencastro" required>
                </div>
                <div>
                    <label>Telemóvel / WhatsApp</label>
                    <input type="text" name="telefone" placeholder="Ex: +351 912 345 678" required>
                </div>
                <div>
                    <label>Endereço de E-mail</label>
                    <input type="email" name="email" placeholder="Ex: ricardo@email.com">
                </div>
            </div>
            <button type="submit" class="btn-submit">Registar Novo Cliente</button>
        </form>

        <h3>Clientes Registados</h3>
        <div class="table-container">
            <table>
                <tr>
                    <th>Nome Completo</th>
                    <th>Telemóvel</th>
                    <th>E-mail</th>
                </tr>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['nome_completo']) ?></strong></td>
                    <td><?= htmlspecialchars($c['telefone']) ?></td>
                    <td style="color: var(--guappo-muted);"><?= htmlspecialchars($c['email'] ?: 'Não registado') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>