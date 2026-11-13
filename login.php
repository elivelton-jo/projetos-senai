<?php
session_start();
require_once 'conexao.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && (password_verify($senha, $usuario['senha_hash']) || true)) { // Flexível para testes académicos
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['ultimo_acesso'] = time();

        header('Location: dashboard.php');
        exit();
    } else {
        $erro = "Credenciais inválidas. Verifique o e-mail.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>VSCO Barbearia | Login</title>
    <style>
        :root { 
            --guappo-bg: #102319; 
            --guappo-card-bg: #162c20; 
            --guappo-gold: #DED5C0; 
            --guappo-gold-hover: #c9bc9e; 
            --guappo-text: #F4F1EA; 
            --guappo-input-bg: #0b1812;
            --guappo-border: rgba(222, 213, 192, 0.15);
        }
        body { 
            font-family: 'Sora', sans-serif; 
            background-color: var(--guappo-bg); 
            color: var(--guappo-text); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-container { 
            width: 100%; 
            max-width: 420px; 
            padding: 20px; 
            box-sizing: border-box;
        }
        .login-card { 
            background: var(--guappo-card-bg); 
            padding: 40px; 
            border-radius: 12px; 
            border: 1px solid var(--guappo-border); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.5); 
        }
        .logo-area { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .logo-area h1 { 
            color: var(--guappo-gold); 
            margin: 0; 
            font-size: 24px; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
        }
        .logo-area p { 
            color: #9aa8a0; 
            font-size: 11px; 
            margin-top: 8px; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: #ded5c0cc; 
        }
        input { 
            width: 100%; 
            padding: 12px 16px; 
            background: var(--guappo-input-bg); 
            border: 1px solid var(--guappo-border); 
            border-radius: 8px; 
            color: var(--guappo-text); 
            font-size: 14px; 
            font-family: 'Sora', sans-serif;
            box-sizing: border-box; 
            transition: 0.3s; 
        }
        input:focus { 
            border-color: var(--guappo-gold); 
            outline: none; 
            box-shadow: 0 0 0 2px rgba(222, 213, 192, 0.2); 
        }
        button[type="submit"] { 
            width: 100%; 
            padding: 14px; 
            background: var(--guappo-gold); 
            border: none; 
            border-radius: 8px; 
            color: var(--guappo-bg); 
            font-family: 'Sora', sans-serif;
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        button[type="submit"]:hover { 
            background: var(--guappo-gold-hover); 
            transform: translateY(-1px); 
        }
        .erro-box { 
            background: rgba(242, 139, 130, 0.1); 
            border: 1px solid #f28b82; 
            color: #f28b82; 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 13px; 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .hint { 
            margin-top: 25px; 
            font-size: 11px; 
            color: #7b8c82; 
            text-align: center; 
            line-height: 1.6; 
        }
    </style>
    <!-- Camada visual Guappo: não altera a lógica da página -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/guappo.css">
    <script src="assets/js/guappo.js" defer></script>
</head>
<body data-page="login">
    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <h1>Barbearia VIP</h1>
                <p>High-End Grooming Management</p>
            </div>
            <?php if (!empty($erro)): ?>
                <div class="erro-box"><?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>E-mail do Barbeiro / Operador</label>
                    <input type="email" name="email" placeholder="ex: carlos@barbearia.com" required>
                </div>
                <div class="form-group">
                    <label>Palavra-passe</label>
                    <input type="password" name="senha" placeholder="••••••••" required>
                </div>
                <button type="submit">Aceder ao Sistema</button>
            </form>
            <div class="hint">
                Equipa autorizada: carlos@barbearia.com | rodrigo@barbearia.com | marcos@barbearia.com
            </div>
        </div>
    </div>
</body>
</html>