<?php
session_start();
require_once 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }

// Estatísticas rápidas
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$total_agendamentos = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE estado != 'Cancelado'")->fetchColumn();

// Soma apenas os serviços cujo estado foi explicitamente marcado como 'Concluído'
$faturamento = $pdo->query("SELECT SUM(valor) FROM agendamentos WHERE estado = 'Concluído'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Painel Principal | VSCO Barbearia</title>
    <style>
        :root { 
            --guappo-bg: #0b1812; 
            --guappo-card-bg: rgba(22, 44, 32, 0.75); 
            --guappo-gold: #DED5C0; 
            --guappo-gold-hover: #c9bc9e; 
            --guappo-text: #F4F1EA; 
            --guappo-muted: #9aa8a0;
            --guappo-border: rgba(222, 213, 192, 0.12);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
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
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: var(--guappo-card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 30px 40px;
            border-radius: 16px;
            border: 1px solid var(--guappo-border);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45);
            margin-bottom: 30px;
        }

        h2 { color: var(--guappo-gold); margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.3px; }
        p.welcome-sub { color: var(--guappo-muted); margin: 6px 0 0 0; font-size: 13.5px; }

        a.btn-sair { 
            background: rgba(242, 139, 130, 0.1);
            border: 1px solid rgba(242, 139, 130, 0.3);
            color: #ff8b82; 
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none; 
            font-size: 13px; 
            font-weight: 500;
            transition: all 0.3s ease; 
        }

        a.btn-sair:hover { background: rgba(242, 139, 130, 0.2); transform: translateY(-2px); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: var(--guappo-card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 25px;
            border-radius: 16px;
            border: 1px solid var(--guappo-border);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
        }

        .stat-card span { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--guappo-muted); margin-bottom: 8px; }
        .stat-card h3 { margin: 0; font-size: 26px; color: var(--guappo-gold); font-weight: 600; }

        .menu-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .menu-card {
            background: var(--guappo-card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--guappo-border);
            text-decoration: none;
            color: var(--guappo-text);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
        }

        .menu-card:hover {
            border-color: rgba(222, 213, 192, 0.4);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .menu-card h4 { margin: 0; font-size: 18px; color: var(--guappo-gold); font-weight: 600; }
        .menu-card p { margin: 0; font-size: 13.5px; color: var(--guappo-muted); line-height: 1.5; }

        @media (max-width: 768px) {
            .stats-grid, .menu-grid { grid-template-columns: 1fr; }
            .header-flex { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
    <!-- Camada visual Guappo: não altera a lógica da página -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/guappo.css">
    <script src="assets/js/guappo.js" defer></script>
</head>
<body data-page="dashboard">
    <div class="container">
        <div class="header-flex">
            <div>
                <h2>Guappo Barbearia</h2>
                <p class="welcome-sub">Bem-vindo de volta ao painel de controlo profissional.</p>
            </div>
            <a href="logout.php" class="btn-sair">Encerrar Sessão</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span>Total de Clientes</span>
                <h3><?= $total_clientes ?></h3>
            </div>
            <div class="stat-card">
                <span>Agendamentos Ativos</span>
                <h3><?= $total_agendamentos ?></h3>
            </div>
            <div class="stat-card">
                <span>Faturamento Concluído</span>
                <h3 style="font-size: 22px;">R$ <?= number_format($faturamento, 2, ',', '.') ?></h3>
            </div>
        </div>

        <div class="menu-grid">
            <a href="agendamentos.php" class="menu-card">
                <h4>Gestão de Agendamentos</h4>
                <p>Marque novos horários, verifique conflitos de agenda e conclua atendimentos concluídos.</p>
            </a>
            <a href="clientes.php" class="menu-card">
                <h4>Diretório de Clientes</h4>
                <p>Registe novos clientes, atualize contactos e consulte o histórico de visitas.</p>
            </a>
            <a href="preferencias.php" class="menu-card">
                <h4>Perfil Estético & Preferências</h4>
                <p>Registe o estilo de corte, acabamento de barba e produtos de fixação favoritos de cada cliente.</p>
            </a>
            <div class="menu-card" style="opacity: 0.6; cursor: default;">
                <h4>Relatórios e Métricas</h4>
                <p>Módulo de análise financeira avançada e desempenho de profissionais (Brevemente).</p>
            </div>
        </div>
    </div>
</body>
</html>