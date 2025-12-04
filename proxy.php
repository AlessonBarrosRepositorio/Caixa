<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// URL do seu Google Apps Script
$googleScriptUrl = 'https://script.google.com/macros/s/AKfycbwmsJ7-mDDGsR6MRUIo7CgyrFsAqedpFJPpmV6wRveMebBIAAmcPQNAQqWHZdM6kdjm/exec';

// Permitir preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log de depuração (opcional)
$logFile = 'proxy_log.txt';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recebe os dados do frontend
        $postData = file_get_contents('php://input');
        
        // Log dos dados recebidos (ocultando dados sensíveis se necessário)
        file_put_contents($logFile, "Dados recebidos: " . substr($postData, 0, 500) . "\n", FILE_APPEND);
        
        // Configura a requisição para o Google Apps Script
        $ch = curl_init($googleScriptUrl);
        
        // Configurações do CURL
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // Para desenvolvimento
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log da resposta
        file_put_contents($logFile, "Resposta HTTP: $httpCode\n", FILE_APPEND);
        file_put_contents($logFile, "Resposta: " . substr($response, 0, 500) . "\n", FILE_APPEND);
        
        if ($error) {
            throw new Exception("Erro no CURL: " . $error);
        }
        
        // Verifica se a resposta é JSON válido
        if ($response === false || trim($response) === '') {
            throw new Exception("Resposta vazia do servidor");
        }
        
        // Tenta decodificar para verificar se é JSON válido
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse === null && json_last_error() !== JSON_ERROR_NONE) {
            // Se não for JSON, pode ser um redirecionamento HTML
            // Tenta extrair JSON se estiver dentro de uma tag <script>
            if (preg_match('/<script[^>]*>.*?({.*?}).*?<\/script>/is', $response, $matches)) {
                $response = $matches[1];
                $jsonResponse = json_decode($response, true);
            }
            
            // Se ainda não for JSON válido, cria uma resposta de erro
            if ($jsonResponse === null) {
                throw new Exception("Resposta inválida do servidor: " . substr($response, 0, 200));
            }
        }
        
        // Retorna a resposta original (ou processada)
        echo $response;
        
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Teste de conexão simples
        echo json_encode([
            'status' => 'online',
            'message' => 'Proxy funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_method' => $_SERVER['REQUEST_METHOD']
        ]);
    } else {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Método não permitido'
        ]);
    }
    
} catch (Exception $e) {
    // Log do erro
    file_put_contents($logFile, "ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Retorna erro em formato JSON
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>