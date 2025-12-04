const scriptGoogle = "https://script.google.com/macros/s/AKfycbwmsJ7-mDDGsR6MRUIo7CgyrFsAqedpFJPpmV6wRveMebBIAAmcPQNAQqWHZdM6kdjm/exec";
const dadosFormulario = document.forms['caixa'];

dadosFormulario.addEventListener('submit', function(e) {
    e.preventDefault();

    // Desabilitar botão enviar
    const botaoEnviar = document.getElementById('enviar');
    botaoEnviar.disabled = true;
    botaoEnviar.textContent = 'Enviando...';

    // Capturar valores
    const atendente = document.getElementById('atendente').value.trim();
    const produto = document.getElementById('produto').value.trim();
    let valor = document.getElementById('valor').value.trim();
    const transacao = document.getElementById('transacao').value.trim();

    // Formatar valor: substituir vírgula por ponto e converter para número
    valor = valor.replace(',', '.');
    const valorNum = parseFloat(valor);

    // Validação
    if (!atendente || !produto || isNaN(valorNum) || !transacao) {
        alert('Por favor, preencha todos os campos corretamente!');
        botaoEnviar.disabled = false;
        botaoEnviar.textContent = 'Enviar';
        return;
    }

    // Formatar valor para exibição (com vírgula)
    const valorFormatado = valorNum.toFixed(2).replace('.', ',');

    // Cria objeto com os dados
    const dadosConvertidos = {
        atendente: atendente,
        produto: produto,
        valor: valorFormatado,
        transacao: transacao
    };

    // Mostrar preview dos dados
    const confirmar = confirm(`Dados que serão enviados:\n\nAtendente: ${atendente}\nProduto: ${produto}\nValor: R$ ${valorFormatado}\nTransação: ${transacao}\n\nConfirmar envio?`);
    
    if (!confirmar) {
        botaoEnviar.disabled = false;
        botaoEnviar.textContent = 'Enviar';
        return;
    }

    // Enviar para o Google Apps Script
    fetch(scriptGoogle, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ data: [dadosConvertidos] })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'sucesso' || data.created === 1) {
            alert('✅ Dados enviados com sucesso!');
            dadosFormulario.reset();
        } else {
            throw new Error(data.message || 'Erro ao salvar dados');
        }
    })
    .catch(error => {
        console.error('Erro ao enviar:', error);
        alert(`❌ Erro ao enviar dados: ${error.message}`);
    })
    .finally(() => {
        botaoEnviar.disabled = false;
        botaoEnviar.textContent = 'Enviar';
    });
});