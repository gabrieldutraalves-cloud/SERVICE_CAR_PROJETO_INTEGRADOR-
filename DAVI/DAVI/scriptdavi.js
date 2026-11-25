/* ============================================================

   SERVICE CAR — scriptdavi.js

   1. Página de pagamento

   2. Página de histórico de serviços

   3. Página de status do serviço

   4. Inicialização (cada página só roda o seu trecho)

   ============================================================ */

// ===== 1. PÁGINA DE PAGAMENTO =====

function iniciarPagamento() {

  // Verifica se estamos na página correta

  if (!document.body.classList.contains('pagamento')) {

    return;

  }

  const escolhido = obterServico();
  if (escolhido) {
    document.getElementById('pag-nome').textContent = escolhido.nome;
    document.getElementById('pag-data').textContent = escolhido.data + ' às ' + escolhido.horario;
    const moeda = valor => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    document.getElementById('pag-preco').textContent = moeda(escolhido.preco);
    const parcelas = document.querySelector('[name="parcelas"]');
    const centavos = Math.round(escolhido.preco * 100);
    for (let n = 1; n <= parcelas.options.length; n++) {
      const base = Math.floor(centavos / n);
      const resto = centavos % n;
      parcelas.options[n - 1].textContent = resto
        ? `${resto}x de ${moeda((base + 1) / 100)} + ${n - resto}x de ${moeda(base / 100)}`
        : `${n}x de ${moeda(base / 100)}`;
    }
  }

  const apenasDigitos = valor => valor.replace(/\D/g, '');

  // Máscara do número do cartão: 0000 0000 0000 0000

  document.querySelectorAll('[data-card]').forEach(campo => {

    campo.addEventListener('input', () => {

      campo.value = apenasDigitos(campo.value)

        .slice(0, 16)

        .replace(/(\d{4})(?=\d)/g, '$1 ');

    });

  });

  // CVC: só números, até 3 dígitos

  document.querySelectorAll('[data-cvc]').forEach(campo => {

    campo.addEventListener('input', () => {

      campo.value = apenasDigitos(campo.value).slice(0, 3);

    });

  });

  // Máscara do CPF: 000.000.000-00

  document.querySelectorAll('[data-cpf]').forEach(campo => {

    campo.addEventListener('input', () => {

      campo.value = apenasDigitos(campo.value)

        .slice(0, 11)

        .replace(/(\d{3})(\d)/, '$1.$2')

        .replace(/(\d{3})(\d)/, '$1.$2')

        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    });

  });

  // Validação: devolve a mensagem de erro ou '' quando está tudo certo

  function validar(form) {

    const campo = nome => form.elements[nome];

    if (form.dataset.m === 'Pix') {

      if (apenasDigitos(campo('cpf').value).length !== 11) {

        return 'Informe um CPF com 11 números.';

      }

      if (!campo('email').value.trim() || !campo('email').checkValidity()) {

        return 'Informe um e-mail válido.';

      }

      const telefone = apenasDigitos(campo('telefone').value).length;

      if (telefone < 10 || telefone > 11) {

        return 'Informe um telefone com DDD.';

      }

      return '';

    }

    if (apenasDigitos(campo('cartao').value).length !== 16) {

      return 'O cartão deve ter 16 números.';

    }

    if (apenasDigitos(campo('cvc').value).length !== 3) {

      return 'O CVC deve ter 3 números.';

    }

    if (campo('titular').value.trim().length < 3) {

      return 'Informe o nome do titular do cartão.';

    }

    return '';

  }

  document.querySelectorAll('form.forma-pagamento').forEach(form => {

    const erro = form.querySelector('.erro');

    // limpa a mensagem quando a pessoa volta a digitar

    form.addEventListener('input', () => {

      erro.textContent = '';

    });

    form.addEventListener('submit', evento => {

      evento.preventDefault();

      const mensagem = validar(form);

      erro.textContent = mensagem;

      if (mensagem) {

        return;

      }

      // guarda a forma escolhida para a página de status mostrar

      try {

        sessionStorage.setItem(chavePagamento(escolhido), form.dataset.m);

      } catch (e) { /* sem armazenamento: a página de status usa o padrão */ }

      window.location.href = escolhido ? `statusServico.html?id=${escolhido.id}` : 'statusServico.html';

    });

    // botão "Alterar cartão": limpa os dados e volta para o campo do cartão

    const alterar = form.querySelector('.alterar');

    if (alterar) {

      alterar.addEventListener('click', () => {

        form.elements.cartao.value = '';

        form.elements.cvc.value = '';

        erro.textContent = '';

        form.elements.cartao.focus();

      });

    }

  });

}

// ===== 2. PÁGINA DE HISTÓRICO DE SERVIÇOS =====

// ===== DADOS DE EXEMPLO DOS SERVIÇOS =====

// Troque este array pelos dados do seu banco de dados quando integrar ao PHP.

const servicos = [

  {

    id: 1,

    nome: 'Polimento Automotivo',

    data: '17/01/2020',

    preco: 50.00,
    horario: '15:30',
    termino: '17:30',

    status: 'Em andamento'

  },

  {

    id: 2,

    nome: 'Higienização Interna',

    data: '09/09/2016',

    preco: 44.99,
    horario: '09:00',
    termino: '10:30',

    status: 'Finalizado'

  },

  {

    id: 3,

    nome: 'Proteção da Pintura',

    data: '26/10/2021',

    preco: 65.00,
    horario: '14:00',
    termino: '16:00',

    status: 'Cancelado'

  }

];

function obterServico() {
  const id = new URLSearchParams(window.location.search).get('id');
  return servicos.find(item => String(item.id) === id);
}

function chavePagamento(servico) {
  return servico ? 'formaPagamento-' + servico.id : 'formaPagamento';
}

function iniciarHistorico() {

  // Verifica se estamos na página correta

  if (!document.body.classList.contains('historico')) {

    return;

  }

  // ===== SELEÇÃO DOS ELEMENTOS =====

  const lista = document.getElementById('lista-servicos');

  const pesquisa = document.getElementById('pesquisa');

  const botoesFiltro = document.querySelectorAll('.filtro');

  let filtroAtual = 'Todos';

  const classesStatus = {

    'Em andamento': 'andamento',

    'Finalizado': 'finalizado',

    'Cancelado': 'cancelado'

  };

  // ===== FORMATAR PREÇOS =====

  function formatarPreco(valor) {

    return valor.toLocaleString('pt-BR', {

      style: 'currency',

      currency: 'BRL'

    });

  }

  // ===== ATUALIZAR INDICADORES =====

  function atualizarIndicadores() {

    document.getElementById('total').textContent = servicos.length;

    document.getElementById('andamento').textContent = servicos.filter(s => s.status === 'Em andamento').length;

    document.getElementById('finalizados').textContent = servicos.filter(s => s.status === 'Finalizado').length;

  }

  // ===== CRIAR E MOSTRAR SERVIÇOS =====

  function mostrarServicos() {

    const termo = pesquisa.value.trim().toLocaleLowerCase('pt-BR');

    const filtrados = servicos.filter(servico => {

      const combinaPesquisa = servico.nome.toLocaleLowerCase('pt-BR').includes(termo);

      const combinaFiltro = filtroAtual === 'Todos' || servico.status === filtroAtual;

      return combinaPesquisa && combinaFiltro;

    });

    lista.replaceChildren();

    if (!filtrados.length) {

      const vazio = document.createElement('p');

      vazio.className = 'vazio';

      vazio.textContent = 'Nenhum serviço encontrado.';

      lista.appendChild(vazio);

      return;

    }

    filtrados.forEach(servico => {

      const artigo = document.createElement('article');

      artigo.className = 'servico';

      const info = document.createElement('div');

      info.className = 'servico-info';

      const icone = document.createElement('span');

      icone.className = 'icone-carro';

      icone.setAttribute('aria-hidden', 'true');

      icone.textContent = '🚘';

      const texto = document.createElement('div');

      texto.className = 'servico-texto';

      const titulo = document.createElement('h3');

      titulo.textContent = servico.nome;

      const data = document.createElement('p');

      data.textContent = servico.data;

      const preco = document.createElement('p');

      preco.textContent = formatarPreco(servico.preco);

      texto.append(titulo, data, preco);

      info.append(icone, texto);

      const botao = document.createElement('button');

      botao.type = 'button';

      botao.className = `status ${classesStatus[servico.status]}`;

      botao.setAttribute('aria-label', `Ver detalhes de ${servico.nome}, ${servico.status}`);

      botao.textContent = servico.status;

      const seta = document.createElement('span');

      seta.setAttribute('aria-hidden', 'true');

      seta.textContent = '➜';

      botao.appendChild(seta);

      botao.addEventListener('click', () => {
        window.location.href = `statusServico.html?id=${servico.id}`;
      });

      artigo.append(info, botao);

      lista.appendChild(artigo);

    });

  }

  // ===== EVENTOS DE PESQUISA E FILTROS =====

  pesquisa.addEventListener('input', mostrarServicos);

  botoesFiltro.forEach(botao => {

    botao.addEventListener('click', () => {

      filtroAtual = botao.dataset.filtro;

      botoesFiltro.forEach(item => {

        const ativo = item === botao;

        item.classList.toggle('ativo', ativo);

        item.setAttribute('aria-pressed', String(ativo));

      });

      mostrarServicos();

    });

  });

  // ===== INICIALIZAR PÁGINA =====

  atualizarIndicadores();

  mostrarServicos();

}

// ===== 3. PÁGINA STATUS DO SERVIÇO =====

function iniciarStatusServico() {

  // Verifica se estamos na página correta

  if (!document.body.classList.contains('status-servico')) {

    return;

  }

  // forma de pagamento escolhida na página de pagamento (padrão: Pix)

  const escolhido = obterServico();
  let formaPagamento = 'Pix';

  try {

    formaPagamento = sessionStorage.getItem(chavePagamento(escolhido)) || 'Pix';

  } catch (e) { /* mantém o padrão */ }

  const servico = {

    nome: escolhido?.nome ?? 'Polimento estético',

    empresa: 'Service Car',

    horario: escolhido?.horario ?? '15:30',
    previsao: escolhido ? escolhido.horario + ' - ' + escolhido.termino : '15:30 - 17:30',

    local: 'Rua do Marquês - Copo Pistão',

    formaPagamento: formaPagamento,

    statusPagamento: 'Simulação — sem cobrança',

    etapaAtual: 2

  };

  // Atualiza as informações

  document.getElementById('nome-servico').textContent = servico.nome;

  document.getElementById('empresa').textContent = servico.empresa;

  document.getElementById('horario').textContent = servico.horario;

  document.getElementById('previsao-horario').textContent = servico.previsao;

  document.getElementById('local').textContent = servico.local;

  document.getElementById('forma-pagamento').textContent = servico.formaPagamento;

  document.getElementById('status-pagamento').textContent = servico.statusPagamento;

  // Atualiza a barra de progresso

  const etapas = document.querySelectorAll('.status-servico .etapa');

  if (escolhido?.status === 'Cancelado') {
    document.querySelector('.progresso').textContent = 'Serviço cancelado';
    return;
  }
  if (escolhido?.status === 'Finalizado') {
    servico.etapaAtual = etapas.length - 1;
  }

  etapas.forEach((etapa, indice) => {

    etapa.classList.remove('concluida', 'atual');

    etapa.removeAttribute('aria-current');

    if (indice < servico.etapaAtual) {

      etapa.classList.add('concluida');

    }

    if (indice === servico.etapaAtual) {

      etapa.classList.add('atual');

      etapa.setAttribute('aria-current', 'step');

    }

  });

  // Preenche a linha de progresso (valor de 0 a 1 usado pelo CSS)

  const linha = document.querySelector('.status-servico .linha-progresso');

  if (linha && etapas.length > 1) {

    linha.style.setProperty('--progresso', servico.etapaAtual / (etapas.length - 1));

  }

}

// ===== 4. INICIALIZAÇÃO =====

function iniciarPagina() {

  iniciarPagamento();

  iniciarHistorico();

  iniciarStatusServico();

}

if (document.readyState === 'loading') {

  document.addEventListener('DOMContentLoaded', iniciarPagina);

} else {

  iniciarPagina();

}
