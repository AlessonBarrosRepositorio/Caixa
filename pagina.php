<?php
// PROTEÇÃO DA PÁGINA - Adicione isto no TOPO do arquivo pagina.php
session_start();

// Tempo de sessão em segundos (1 hora = 3600 segundos)
$tempo_sessao = 3600;

// Verifica se está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit();
}

// Verifica se a sessão expirou
if (isset($_SESSION['ultimo_acesso']) && 
    (time() - $_SESSION['ultimo_acesso'] > $tempo_sessao)) {
    // Sessão expirou, destrói e redireciona para login
    session_unset();
    session_destroy();
    header("Location: login.php?expirou=1");
    exit();
}

// Atualiza o tempo do último acesso
$_SESSION['ultimo_acesso'] = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa Gráfica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 10px;
            width: 100%;
            max-width: 500px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        label i {
            margin-right: 8px;
            color: #667eea;
        }
        
        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        input.error {
            border-color: #e74c3c;
            background-color: #fff5f5;
        }
        
        .input-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
            margin-left: 24px;
        }
        
        datalist {
            max-height: 200px;
            overflow-y: auto;
        }
        
        button {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        button:active:not(:disabled) {
            transform: translateY(0);
        }
        
        button:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            animation: fadeIn 0.3s;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert.show {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .footer {
            text-align: center;
            margin-top: 5px;
            padding-top: 2px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .status {
            font-size: 12px;
            padding: 2px 5px;
            border-radius: 5px;
            margin-left: 10px;
        }
        
        .status.online {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.offline {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .valor-container {
            position: relative;
        }
        
        .currency-symbol {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            color: #2c3e50;
        }
        
        .valor-container input {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-cash-register"></i> Caixa Gráfica</h1>
        
        <div id="alert" class="alert"></div>
        
        <form id="caixaForm" novalidate>
            <div class="form-group">
                <label for="atendente"><i class="fas fa-user"></i> Atendente</label>
                <input list="atendentes" id="atendente" name="atendente" required 
                       placeholder="Selecione ou digite o nome">
                <datalist id="atendentes">
                    <option value="Alesson">
                    <option value="Vinicius">
                    <option value="Gabriel">
                </datalist>
                <div class="input-hint">Selecione um atendente da lista ou digite um novo</div>
            </div>
            
            <div class="form-group">
                <label for="produto"><i class="fas fa-box"></i> Produto/Serviço</label>
                <input type="text" id="produto" name="produto" required 
                       placeholder="Ex: Cartão de Visita, Banner, etc.">
                <div class="input-hint">Digite o nome do produto ou serviço vendido</div>
            </div>
            
            <div class="form-group">
                <label for="valor"><i class="fas fa-money-bill-wave"></i> Valor (R$)</label>
                <div class="valor-container">
                    <span class="currency-symbol">R$</span>
                    <input type="text" id="valor" name="valor" required 
                           placeholder="0,00" pattern="^\d{1,3}(\.\d{3})*,\d{2}$">
                </div>
                <div class="input-hint">Use vírgula para centavos (ex: 150,50 para R$ 150,50)</div>
            </div>
            
            <div class="form-group">
                <label for="transacao"><i class="fas fa-credit-card"></i> Forma de Pagamento</label>
                <input list="transacoes" id="transacao" name="transacao" required 
                       placeholder="Selecione a forma de pagamento">
                <datalist id="transacoes">
                    <option value="Dinheiro">
                    <option value="Pix">
                    <option value="Cartão Débito">
                    <option value="Cartão Crédito">
                    <option value="Link de Pagamento">
                </datalist>
            </div>
            
            <button type="submit" id="enviar">
                <span id="enviar-texto"><i class="fas fa-paper-plane"></i> Registrar Venda</span>
            </button>
        </form>
        
        <div class="footer">
            <!--<p>Sistema de Caixa | Dados salvos no Google Sheets</p>
            <p id="connection-status">Status da conexão: <span class="status" id="status-indicator">Verificando...</span></p>-->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos DOM
            const form = document.getElementById('caixaForm');
            const enviarBtn = document.getElementById('enviar');
            const enviarTexto = document.getElementById('enviar-texto');
            const alertDiv = document.getElementById('alert');
            const statusIndicator = document.getElementById('status-indicator');
            const valorInput = document.getElementById('valor');
            
            // URL do proxy (usando o mesmo arquivo PHP)
            const proxyUrl = 'proxy.php';
            
            // Verificar conexão inicial
            checkConnection();
            
            // Formatar valor em tempo real
            valorInput.addEventListener('input', formatCurrency);
            valorInput.addEventListener('blur', finalizeCurrencyFormat);
            
            // Substitua a parte do fetch no seu evento submit por este código:

            // Envio do formulário
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validar formulário
                if (!validateForm()) {
                    return;
                }
                
                // Coletar dados
                const dados = {
                    atendente: document.getElementById('atendente').value.trim(),
                    produto: document.getElementById('produto').value.trim(),
                    valor: formatValueForAPI(valorInput.value),
                    transacao: document.getElementById('transacao').value.trim()
                };
                
                // Mostrar confirmação
                const confirmar = confirm(`Confirma o envio?\n\nAtendente: ${dados.atendente}\nProduto: ${dados.produto}\nValor: R$ ${formatToBRL(valorInput.value)}\nPagamento: ${dados.transacao}`);
                
                if (!confirmar) {
                    return;
                }
                
                // Desabilitar botão
                enviarBtn.disabled = true;
                enviarTexto.innerHTML = '<span class="loading"></span> Enviando...';
                
                try {
                    // Enviar via proxy
                    const response = await fetch(proxyUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ data: [dados] })
                    });
                    
                    // Verificar se a resposta está OK
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    
                    // Obter texto da resposta primeiro
                    const responseText = await response.text();
                    console.log('Resposta bruta:', responseText);
                    
                    // Tentar parsear como JSON
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (jsonError) {
                        console.error('Erro ao parsear JSON:', jsonError);
                        throw new Error('Resposta inválida do servidor. Não é JSON válido.');
                    }
                    
                    console.log('Resultado parseado:', result);
                    
                    if (result.status === 'sucesso' || result.created === 1) {
                        showAlert('✅ Venda registrada com sucesso!', 'success');
                        form.reset();
                        html.reset();
                        
                        // Feedback visual
                        enviarBtn.innerHTML = '<i class="fas fa-check"></i> Registrado!';
                        setTimeout(() => {
                            enviarBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Registrar Venda';
                        }, 2000);
                    } else if (result.status === 'error') {
                        throw new Error(result.message || 'Erro ao salvar dados');
                    } else {
                        // Se não tiver status conhecido, mas parece bem-sucedido
                        showAlert('✅ Dados enviados!', 'success');
                        form.reset();
                        html.reset();
                    }
                } catch (error) {/*
                    console.error('Erro completo:', error);
                    showAlert(`❌ Erro: ${error.message}`, 'error');*/
                } finally {
                    // Reabilitar botão após 2 segundos
                    setTimeout(() => {
                        enviarBtn.disabled = false;
                        enviarTexto.innerHTML = '<i class="fas fa-paper-plane"></i> Registrar Venda';
                    }, 2000);
                }
            });
            
            // Funções auxiliares
            function formatCurrency(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Adiciona zeros à esquerda se necessário
                if (value.length < 3) {
                    value = value.padStart(3, '0');
                }
                
                // Formata como moeda brasileira
                const integerPart = value.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                const decimalPart = value.slice(-2);
                
                e.target.value = `${integerPart},${decimalPart}`;
            }
            
            function finalizeCurrencyFormat(e) {
                let value = e.target.value;
                
                // Se estiver vazio, coloca 0,00
                if (!value || value === ',') {
                    e.target.value = '0,00';
                    return;
                }
                
                // Garante que tem duas casas decimais
                if (!value.includes(',')) {
                    e.target.value = value + ',00';
                } else {
                    const parts = value.split(',');
                    if (parts[1].length === 0) {
                        e.target.value = parts[0] + ',00';
                    } else if (parts[1].length === 1) {
                        e.target.value = parts[0] + ',' + parts[1] + '0';
                    }
                }
            }
            
            function formatValueForAPI(value) {
                // Converte "1.584,13" para "1584.13"
                return value.replace(/\./g, '').replace(',', '.');
            }
            
            function formatToBRL(value) {
                // Formata para exibição
                const num = parseFloat(formatValueForAPI(value));
                return num.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            function validateForm() {
                let isValid = true;
                
                // Resetar erros
                document.querySelectorAll('input').forEach(input => {
                    input.classList.remove('error');
                });
                
                // Validar atendente
                const atendente = document.getElementById('atendente').value.trim();
                if (!atendente) {
                    showInputError('atendente', 'Selecione ou digite um atendente');
                    isValid = false;
                }
                
                // Validar produto
                const produto = document.getElementById('produto').value.trim();
                if (!produto) {
                    showInputError('produto', 'Digite o produto ou serviço');
                    isValid = false;
                }
                
                // Validar valor
                const valor = valorInput.value.trim();
                if (!valor || !/^\d{1,3}(\.\d{3})*,\d{2}$/.test(valor)) {
                    showInputError('valor', 'Digite um valor válido (ex: 150,50)');
                    isValid = false;
                } else if (parseFloat(formatValueForAPI(valor)) <= 0) {
                    showInputError('valor', 'O valor deve ser maior que zero');
                    isValid = false;
                }
                
                // Validar transação
                const transacao = document.getElementById('transacao').value.trim();
                if (!transacao) {
                    showInputError('transacao', 'Selecione a forma de pagamento');
                    isValid = false;
                }
                
                return isValid;
            }
            
            function showInputError(inputId, message) {
                const input = document.getElementById(inputId);
                input.classList.add('error');
                showAlert(`⚠️ ${message}`, 'error');
                input.focus();
            }
            
            function showAlert(message, type) {
                alertDiv.textContent = message;
                alertDiv.className = `alert ${type} show`;
                
                // Auto-esconder após 5 segundos
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                }, 5000);
            }
            
            async function checkConnection() {
                try {
                    const response = await fetch(proxyUrl);
                    const result = await response.json();
                    
                    if (result.status === 'online') {
                        statusIndicator.textContent = 'Conectado';
                        statusIndicator.className = 'status online';
                    } else {
                        throw new Error('Servidor offline');
                    }
                } catch (error) {
                    statusIndicator.textContent = 'Desconectado';
                    statusIndicator.className = 'status offline';
                    showAlert('⚠️ Sem conexão com o servidor. Verifique sua internet.', 'error');
                }
            }
            
            // Adicionar máscara de valor
            function mascaraMoeda(event) {
                const onlyDigits = event.target.value
                    .split("")
                    .filter(s => /\d/.test(s))
                    .join("")
                    .padStart(3, "0");
                const digitsFloat = onlyDigits.slice(0, -2) + "." + onlyDigits.slice(-2);
                event.target.value = maskCurrency(digitsFloat);
            }
            
            function maskCurrency(valor, locale = 'pt-BR', currency = 'BRL') {
                return new Intl.NumberFormat(locale, {
                    style: 'currency',
                    currency
                }).format(valor).replace('R$', '').trim();
            }
            
            // Inicializar máscara
            valorInput.addEventListener('keyup', mascaraMoeda);
        });
    </script>
</body>
</html>