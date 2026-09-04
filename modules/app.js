
    // Inicialização do Banco de Dados Relacional WMS no LocalStorage
    let estoque = JSON.parse(localStorage.getItem('e_estoque_wms')) || [
        {id: 101, nome: "Furadeira de Impacto Bosch", barcode: "789100000101", categoria: "Ferramenta", risco: "Geral", rotatividade: "Alta", embalagem: "Caixa Padrão A", qtd: 5, min: 2, local: "A-01-A-1", segmento: "Obra Centro - Torre A"},
        {id: 102, nome: "Cabo Flexível Sil 10mm", barcode: "789100000102", categoria: "Consumível", risco: "Geral", rotatividade: "Alta", embalagem: "Caixa Padrão B", qtd: 150, min: 20, local: "A-01-A-2", segmento: "Obra Centro - Torre A"},
        {id: 103, nome: "Multímetro Digital Fluke", barcode: "789100000103", categoria: "Equipamento", risco: "Geral", rotatividade: "Baixa", embalagem: "Caixa Padrão A", qtd: 1, min: 2, local: "C-03-A-1", segmento: "Geral Engenharia"},
        {id: 104, nome: "Disjuntor DIN 50A", barcode: "789100000104", categoria: "Consumível", risco: "Geral", rotatividade: "Media", embalagem: "Caixa Padrão B", qtd: 30, min: 40, local: "B-02-A-1", segmento: "Obra Centro - Torre A"}
    ];

    let auditoria = JSON.parse(localStorage.getItem('e_auditoria_wms')) || [];
    let ordensPicking = JSON.parse(localStorage.getItem('e_picking_wms')) || [];
    let perfilAtual = "Almoxarife";
    let segmentoSelecionado = "construcao";
    let qrcodeInstancia = null;

    // Relógio do Sistema
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleString('pt-BR');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Controle de Abas / Módulos
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
        if(tabId === 'mod-etiquetas') gerarEtiquetaPreview();
    }

    // Controle de Níveis de Acesso (RBAC)
    function switchProfile(perfil) {
        perfilAtual = perfil;
        document.getElementById('current-profile').innerText = `${perfil} ${perfil === 'Gestor / ADM' ? '(Acesso Total + Fiscal)' : '(Acesso Operacional)'}`;
        logAuditoria(`Alterou visualização para perfil: ${perfil}`);
        alert(`Perfil alterado para ${perfil}. As permissões operacionais foram sincronizadas.`);
    }

    // Regras de Segmento Dinâmicas
    function setSegment(tipo, btn) {
        segmentoSelecionado = tipo;
        document.querySelectorAll('.segment-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const label = document.getElementById('dynamic-label');
        const input = document.getElementById('cad-dinamico');

        if(tipo === 'construcao') {
            label.innerText = "Vínculo de Centro de Custo / Obra Padrão *";
            input.placeholder = "Ex: Obra Residencial Torre A";
        } else if(tipo === 'varejo') {
            label.innerText = "SKU Comercial / Marca Depositante *";
            input.placeholder = "Ex: SKU-99281 - Marca Varejo";
        } else if(tipo === 'transporte') {
            label.innerText = "Placa do Caminhão / Frota Vinculada *";
            input.placeholder = "Ex: BRA2E19 - Scania R450";
        }
    }

    // Regra de Endereçamento Automático baseada em Segurança, Risco e Rotatividade
    function calcularEnderecamentoAuto() {
        const risco = document.getElementById('cad-risco').value;
        const rotatividade = document.getElementById('cad-rotatividade').value;
        let rua = "B";
        let nivel = "02";

        if(risco === "Químico") rua = "Q-SPO"; // Zona Especial de Químicos
        else if(risco === "Alimentar") rua = "A-CLI"; // Zona Climatizada
        else {
            if(rotatividade === "Alta") rua = "A"; // Próximo à Docas/Picking
            else if(rotatividade === "Media") rua = "B";
            else rua = "C"; // Pulmão de Estoque
        }

        const predio = "0" + Math.floor(Math.random() * 5 + 1);
        const andar = "A-" + Math.floor(Math.random() * 3 + 1);
        
        document.getElementById('cad-local').value = `${rua}-${predio}-${andar}`;
    }

    // Renderização Geral
    function render() {
        const tbody = document.querySelector('#tabela-estoque tbody');
        const selectMov = document.getElementById('mov-ativo');
        const selectPick = document.getElementById('pick-item');
        const selectFul = document.getElementById('ful-item');
        const selectInv = document.getElementById('inv-item');
        const selectEtiq = document.getElementById('etiq-item');

        tbody.innerHTML = '';
        selectMov.innerHTML = '';
        selectPick.innerHTML = '';
        selectFul.innerHTML = '';
        selectInv.innerHTML = '';
        selectEtiq.innerHTML = '';

        let temAlerta = false;

        estoque.forEach(item => {
            const sobAviso = item.qtd <= item.min;
            if(sobAviso) temAlerta = true;

            const tr = document.createElement('tr');
            if(sobAviso) tr.style.backgroundColor = '#fff5f5';

            tr.innerHTML = `
                <td><strong>${item.nome}</strong></td>
                <td><code style="background:#e2e8f0; padding:2px 4px; border-radius:4px;">${item.barcode}</code></td>
                <td>${item.embalagem}</td>
                <td>${item.qtd} un</td>
                <td>${item.min} un</td>
                <td>📍 <strong>${item.local}</strong></td>
                <td>${item.segmento}</td>
                <td>${sobAviso ? '🔴 Crítico / Repor' : '🟢 Normal'}</td>
            `;
            tbody.appendChild(tr);

            // Popula os Selects
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.innerText = `${item.nome} (Disp: ${item.qtd} | End: ${item.local})`;
            
            selectMov.appendChild(opt.cloneNode(true));
            selectPick.appendChild(opt.cloneNode(true));
            selectFul.appendChild(opt.cloneNode(true));
            selectInv.appendChild(opt.cloneNode(true));
            selectEtiq.appendChild(opt.cloneNode(true));
        });

        document.getElementById('min-stock-alert').style.display = temAlerta ? 'block' : 'none';
        
        // Render Auditoria
        const tbodyAuditoria = document.querySelector('#tabela-auditoria tbody');
        tbodyAuditoria.innerHTML = '';
        auditoria.slice(-5).reverse().forEach(log => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${log.time}</td>
                <td>👤 ${log.user}</td>
                <td>${log.acao}</td>
                <td style="font-family: monospace; color: #64748b;">${log.hash}</td>
            `;
            tbodyAuditoria.appendChild(tr);
        });

        renderPickingTable();
        salvarDados();
    }

    function salvarDados() {
        localStorage.setItem('e_estoque_wms', JSON.stringify(estoque));
        localStorage.setItem('e_auditoria_wms', JSON.stringify(auditoria));
        localStorage.setItem('e_picking_wms', JSON.stringify(ordensPicking));
    }

    // Cadastro de Ativos
    function cadastrarItem(e) {
        e.preventDefault();
        
        const nome = document.getElementById('cad-nome').value;
        const barcode = document.getElementById('cad-barcode').value;
        const risco = document.getElementById('cad-risco').value;
        const rotatividade = document.getElementById('cad-rotatividade').value;
        const embalagem = document.getElementById('cad-embalagem').value;
        const qtd = parseInt(document.getElementById('cad-qtd').value);
        const min = parseInt(document.getElementById('cad-min').value);
        const local = document.getElementById('cad-local').value;
        const dinamico = document.getElementById('cad-dinamico').value;

        const novoItem = {
            id: Date.now(),
            nome,
            barcode,
            categoria: risco,
            risco,
            rotatividade,
            embalagem,
            qtd,
            min,
            local,
            segmento: dinamico || "Geral"
        };

        estoque.push(novoItem);
        logAuditoria(`Cadastrou ativo com endereçamento auto WMS: ${nome} no endereço ${local}`);
        document.getElementById('form-cadastro').reset();
        calcularEnderecamentoAuto();
        render();
        alert("Ativo validado e estocado via Endereçamento Inteligente!");
    }

    // Movimentação Simples
    function registrarMovimentacao(e) {
        e.preventDefault();
        const idAtivo = parseInt(document.getElementById('mov-ativo').value);
        const tipo = document.getElementById('mov-tipo').value;
        const qtdMov = parseInt(document.getElementById('mov-qtd').value);
        const resp = document.getElementById('mov-resp').value;
        const condicao = document.getElementById('mov-condicao').value;

        const item = estoque.find(i => i.id === idAtivo);
        if(!item) return;

        if((tipo === 'Retirada' || tipo === 'Consumo') && item.qtd < qtdMov) {
            alert("⚠️ Erro WMS: Saldo insuficiente no endereço para realizar a retirada!");
            return;
        }

        if(tipo === 'Retirada' || tipo === 'Consumo') {
            item.qtd -= qtdMov;
        } else {
            item.qtd += qtdMov;
        }

        logAuditoria(`Movimentação (${tipo}): ${qtdMov} un de ${item.nome}. Resp: ${resp}. Condição: ${condicao}`);
        document.getElementById('form-movimentacao').reset();
        render();
        alert("Movimentação registrada com sucesso!");
    }

    // CONTROLE DE PICKING
    function gerarOrdemPicking(e) {
        e.preventDefault();
        const estrategia = document.getElementById('pick-estratégia').value;
        const idItem = parseInt(document.getElementById('pick-item').value);
        const qtd = parseInt(document.getElementById('pick-qtd').value);
        const pedido = document.getElementById('pick-pedido').value;

        const item = estoque.find(i => i.id === idItem);
        if(!item || item.qtd < qtd) {
            alert("⚠️ Quantidade insuficiente em estoque para gerar esta ordem de picking!");
            return;
        }

        const novaOrdem = {
            id: 'OP-' + Math.floor(Math.random() * 8999 + 1000),
            estrategia,
            itemId: item.id,
            itemNome: item.nome,
            barcode: item.barcode,
            local: item.local,
            qtd,
            pedido,
            status: 'Pendente Conferência'
        };

        ordensPicking.push(novaOrdem);
        logAuditoria(`Gerou Ordem de Picking ${novaOrdem.id} [Modo: ${estrategia}] para o pedido ${pedido}`);
        render();
        alert("Ordem de Picking gerada! Encaminhada para a Mesa de Conferência.");
    }

    function renderPickingTable() {
        const tbody = document.querySelector('#tabela-picking tbody');
        const selectConf = document.getElementById('conf-ordem');
        tbody.innerHTML = '';
        selectConf.innerHTML = '<option value="">Selecione uma ordem...</option>';

        ordensPicking.forEach(op => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${op.id}</strong></td>
                <td>${op.estrategia}</td>
                <td>${op.itemNome}</td>
                <td>📍 ${op.local}</td>
                <td>${op.qtd} un</td>
                <td>${op.pedido}</td>
                <td><span class="badge" style="background:${op.status === 'Concluído' ? 'var(--success)' : 'var(--warning)'}">${op.status}</span></td>
                <td>${op.status !== 'Concluído' ? `<button class="btn-sm" style="background:var(--accent); color:white;" onclick="enviarParaMesaConf('${op.id}')">Ir p/ Conferência</button>` : 'Finalizado'}</td>
            `;
            tbody.appendChild(tr);

            if(op.status !== 'Concluído') {
                const opt = document.createElement('option');
                opt.value = op.id;
                opt.innerText = `${op.id} - ${op.itemNome} (${op.qtd} un) - Pedido: ${op.pedido}`;
                selectConf.appendChild(opt);
            }
        });
    }

    function enviarParaMesaConf(idOp) {
        switchTab('mod-conferencia', document.querySelectorAll('.tab-btn')[2]);
        document.getElementById('conf-ordem').value = idOp;
        carregarOrdemConferencia();
    }

    // MESA DE CONFERÊNCIA
    let ordemAtualConf = null;
    let qtdBipada = 0;

    function carregarOrdemConferencia() {
        const opId = document.getElementById('conf-ordem').value;
        ordemAtualConf = ordensPicking.find(o => o.id === opId);
        qtdBipada = 0;

        if(ordemAtualConf) {
            document.getElementById('conf-detalhes').style.display = 'block';
            document.getElementById('conf-item-nome').innerText = ordemAtualConf.itemNome;
            document.getElementById('conf-item-qtd').innerText = ordemAtualConf.qtd;
            document.getElementById('conf-item-bipada').innerText = qtdBipada;
            document.getElementById('conf-item-code').innerText = ordemAtualConf.barcode;
            document.getElementById('btn-finalizar-conf').disabled = true;
        } else {
            document.getElementById('conf-detalhes').style.display = 'none';
        }
    }

    function biparConferencia(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcodeIn = document.getElementById('conf-barcode').value.trim();
            if(!ordemAtualConf) {
                alert("Selecione uma ordem de picking primeiro!");
                return;
            }

            if(barcodeIn === ordemAtualConf.barcode) {
                qtdBipada++;
                document.getElementById('conf-item-bipada').innerText = qtdBipada;
                document.getElementById('conf-barcode').value = '';
                
                if(qtdBipada >= ordemAtualConf.qtd) {
                    document.getElementById('btn-finalizar-conf').disabled = false;
                    alert("✅ Todos os itens foram bipados e conferidos com sucesso!");
                }
            } else {
                alert("❌ Código de Barras incorreto! Item não pertence a este pedido.");
            }
        }
    }

    function finalizarConferencia() {
        if(!ordemAtualConf) return;
        
        // Dar baixa no estoque real
        const item = estoque.find(i => i.id === ordemAtualConf.itemId);
        if(item) {
            item.qtd -= ordemAtualConf.qtd;
        }

        ordemAtualConf.status = 'Concluído';
        logAuditoria(`Mesa de Conferência: Ordem ${ordemAtualConf.id} conferida, bipada e despachada.`);
        alert(`📦 Conferência Concluída! Etiqueta de Volume e Expedição emitida para o pedido ${ordemAtualConf.pedido}.`);
        
        ordemAtualConf = null;
        carregarOrdemConferencia();
        render();
    }

    // OPERAÇÃO FULFILLMENT
    function processarFulfillment(e) {
        e.preventDefault();
        const cliente = document.getElementById('ful-cliente').value;
        const cnpj = document.getElementById('ful-cnpj').value;
        const idItem = parseInt(document.getElementById('ful-item').value);
        const qtd = parseInt(document.getElementById('ful-qtd').value);
        const dest = document.getElementById('ful-destinatario').value;

        const item = estoque.find(i => i.id === idItem);
        if(!item || item.qtd < qtd) {
            alert("⚠️ Erro Fulfillment: Saldo em estoque insuficiente!");
            return;
        }

        item.qtd -= qtd;
        const nfNum = Math.floor(Math.random() * 899999 + 100000);
        logAuditoria(`Operação Fulfillment: Emissão de NF de Terceiros nº ${nfNum} para ${cliente} (${cnpj}). Despacho de ${qtd} un de ${item.nome}`);
        
        render();
        alert(`🚚 Operação Fulfillment Concluída!\n\nNota Fiscal de Envio por Conta e Ordem de Terceiros Emitida nº ${nfNum}.\nDestino: ${dest}`);
    }

    // INVENTÁRIO FÍSICO COM CONVERSÃO
    function ajustarInventario(e) {
        e.preventDefault();
        const idItem = parseInt(document.getElementById('inv-item').value);
        const fator = parseInt(document.getElementById('inv-fator').value);
        const qtdContada = parseInt(document.getElementById('inv-qtd-contada').value);
        const motivo = document.getElementById('inv-motivo').value;

        const item = estoque.find(i => i.id === idItem);
        if(!item) return;

        const qtdTotalConvertida = qtdContada * fator;
        const qtdAnterior = item.qtd;
        item.qtd = qtdTotalConvertida;

        logAuditoria(`Ajuste de Inventário: ${item.nome}. Anterior: ${qtdAnterior} un | Ajustado: ${qtdTotalConvertida} un (Fator: ${fator}). Motivo: ${motivo}`);
        render();
        alert("Ajuste de conciliação de inventário aplicado ao estoque digital!");
    }

    // GESTÃO DE ETIQUETAS
    function gerarEtiquetaPreview() {
        const idItem = parseInt(document.getElementById('etiq-item').value);
        const tipo = document.getElementById('etiq-tipo').value;
        const item = estoque.find(i => i.id === idItem);

        if(!item) return;

        document.getElementById('lbl-nome').innerText = item.nome;
        document.getElementById('lbl-detalhes').innerText = `Endereço: ${item.local} | EAN: ${item.barcode} | Tipo: ${tipo}`;

        // Limpa QR Code anterior
        document.getElementById('qrcode').innerHTML = '';
        qrcodeInstancia = new QRCode(document.getElementById("qrcode"), {
            text: `WMS-ITEM:${item.id}|EAN:${item.barcode}|LOC:${item.local}`,
            width: 100,
            height: 100
        });
    }

    // Auditoria Log
    function logAuditoria(acao) {
        const novoLog = {
            time: new Date().toLocaleTimeString('pt-BR'),
            user: perfilAtual,
            acao: acao,
            hash: '0x' + Math.floor(Math.random()*16777215).toString(16).toUpperCase()
        };
        auditoria.push(novoLog);
    }

    // Exportação CSV
    function exportarExcel() {
        if(perfilAtual !== 'Gestor / ADM') {
            alert("⛔ Acesso Negado: Apenas gestores podem exportar dados estratégicos e fiscais.");
            return;
        }
        let csvContent = "data:text/csv;charset=utf-8,ID;Item;EAN;Unidade_Carga;Quantidade_Sistema;Estoque_Minimo;Endereco_WMS;Segmento\n";
        estoque.forEach(i => {
            csvContent += `${i.id};${i.nome};${i.barcode};${i.embalagem};${i.qtd};${i.min};${i.local};${i.segmento}\n`;
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "banco_wms_estoque.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        logAuditoria("Exportou o banco de dados completo em CSV");
    }

    function simularEnvioEmail() {
        alert("📧 Relatório WMS gerado e disparado com sucesso para os gestores!");
        logAuditoria("Disparou e-mails de alerta consolidados do sistema");
    }

    function toggleTermoRequire() {
        const tipo = document.getElementById('mov-tipo').value;
        document.getElementById('termo-box').style.display = (tipo === 'Retirada') ? 'block' : 'none';
    }

    // Inicialização
    calcularEnderecamentoAuto();
    render();



    /* Camada de persistência: todos os registros operacionais são enviados à API PHP/MySQL. */
    const API_URL = 'api/index.php';
    salvarDados = () => {}; // remove a persistência operacional do LocalStorage
    logAuditoria = () => {};
    let usuarioLogado = null;

    async function requisicaoAuth(action, payload = {}) {
        const response = await fetch('api/auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, ...payload }) });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.error || 'Falha de autenticação.');
        return data;
    }
    function mostrarAutenticacao(firstAccount = false) {
        document.getElementById('auth-overlay').classList.remove('hidden');
        document.getElementById('auth-toggle').textContent = firstAccount ? 'Criar a primeira conta de administrador' : 'Cadastrar usuário';
        const token = new URLSearchParams(location.search).get('reset');
        if (token) { document.getElementById('login-form').style.display = 'none'; document.getElementById('register-form').style.display = 'none'; document.getElementById('recovery-form').style.display = 'none'; document.getElementById('reset-form').style.display = 'block'; document.getElementById('auth-title').textContent = 'Redefinir senha'; document.getElementById('auth-subtitle').textContent = 'Escolha uma nova senha com ao menos 8 caracteres.'; }
    }
    async function verificarSessao() {
        const response = await fetch('api/auth.php'); const data = await response.json();
        if (!data.user) { mostrarAutenticacao(!data.hasUsers); return false; }
        usuarioLogado = data.user; perfilAtual = data.user.role;
        document.getElementById('current-profile').textContent = `${data.user.name} — ${data.user.role}`;
        document.getElementById('business-segment').value = data.user.segment || 'CONSTRUCAO_CIVIL'; configurarOpcoesDoSegmento();
        document.getElementById('auth-overlay').classList.add('hidden'); if (localStorage.getItem('wms_skip_tutorial') !== 'true') setTimeout(() => abrirTutorial(), 150); return true;
    }
    function alternarAutenticacao() {
        const registration = document.getElementById('register-form'); const isRegister = registration.style.display !== 'none';
        registration.style.display = isRegister ? 'none' : 'block'; document.getElementById('login-form').style.display = isRegister ? 'block' : 'none';
        document.getElementById('auth-title').textContent = isRegister ? 'Acessar o E-Gestão WMS' : 'Cadastro de usuário';
    }
    function mostrarRecuperacao() { document.getElementById('login-form').style.display = 'none'; document.getElementById('register-form').style.display = 'none'; document.getElementById('reset-form').style.display = 'none'; document.getElementById('recovery-form').style.display = 'block'; document.getElementById('auth-title').textContent = 'Recuperar acesso'; document.getElementById('auth-subtitle').textContent = 'Informe seu e-mail para receber o link de redefinição.'; }
    async function solicitarRedefinicao(e) { e.preventDefault(); try { const result = await requisicaoAuth('request_reset', { email: document.getElementById('recovery-email').value }); alert(result.message); document.getElementById('recovery-form').style.display = 'none'; document.getElementById('login-form').style.display = 'block'; } catch (error) { alert(error.message); } }
    async function redefinirSenha(e) { e.preventDefault(); const password = document.getElementById('reset-password').value; if (password !== document.getElementById('reset-password-confirm').value) return alert('As senhas não coincidem.'); try { const result = await requisicaoAuth('reset_password', { token: new URLSearchParams(location.search).get('reset'), password }); alert(result.message); history.replaceState({}, '', location.pathname); document.getElementById('reset-form').style.display = 'none'; document.getElementById('login-form').style.display = 'block'; } catch (error) { alert(error.message); } }
    function bloquearTela() { if (!usuarioLogado) return; document.getElementById('auth-title').textContent = 'Tela bloqueada'; document.getElementById('auth-subtitle').textContent = 'A sessão de ' + usuarioLogado.name + ' continua ativa. Confirme sua senha para voltar à operação.'; ['login-form','register-form','recovery-form','reset-form'].forEach(id => document.getElementById(id).style.display = 'none'); document.getElementById('auth-toggle').style.display = 'none'; document.getElementById('forgot-password').style.display = 'none'; document.getElementById('lock-user').textContent = usuarioLogado.email; document.getElementById('unlock-password').value = ''; document.getElementById('lock-form').style.display = 'block'; document.getElementById('auth-overlay').classList.remove('hidden'); setTimeout(() => document.getElementById('unlock-password').focus(), 50); }
    async function desbloquearTela(e) { e.preventDefault(); try { await requisicaoAuth('unlock', { password: document.getElementById('unlock-password').value }); document.getElementById('lock-form').style.display = 'none'; document.getElementById('auth-overlay').classList.add('hidden'); document.getElementById('auth-toggle').style.display = ''; document.getElementById('forgot-password').style.display = ''; } catch (error) { alert(error.message); } }
    async function encerrarSessao() { if (!confirm('Deseja encerrar a sessão? Será necessário informar e-mail e senha para acessar novamente.')) return; try { await requisicaoAuth('logout'); usuarioLogado = null; estoque = []; ordensPicking = []; auditoria = []; render(); document.getElementById('auth-title').textContent = 'Acessar o E-Gestão WMS'; document.getElementById('auth-subtitle').textContent = 'Entre com suas credenciais para operar o estoque.'; ['register-form','recovery-form','reset-form','lock-form'].forEach(id => document.getElementById(id).style.display = 'none'); document.getElementById('login-form').style.display = 'block'; document.getElementById('auth-toggle').style.display = ''; document.getElementById('forgot-password').style.display = ''; document.getElementById('auth-overlay').classList.remove('hidden'); } catch (error) { alert(error.message); } }
    function abrirTutorial(forced = false) { if (!forced && localStorage.getItem('wms_skip_tutorial') === 'true') return; document.getElementById('tutorial-no-show').checked = localStorage.getItem('wms_skip_tutorial') === 'true'; document.getElementById('tutorial-overlay').classList.remove('hidden'); }
    function fecharTutorial() { if (document.getElementById('tutorial-no-show').checked) localStorage.setItem('wms_skip_tutorial', 'true'); else localStorage.removeItem('wms_skip_tutorial'); document.getElementById('tutorial-overlay').classList.add('hidden'); }
    async function fazerLogin(e) { e.preventDefault(); try { await requisicaoAuth('login', { email: document.getElementById('login-email').value, password: document.getElementById('login-password').value }); if (await verificarSessao()) carregarDoBanco(); } catch (error) { alert(error.message); } }
    async function cadastrarUsuario(e) { e.preventDefault(); try { const result = await requisicaoAuth('register', { name: document.getElementById('reg-name').value, email: document.getElementById('reg-email').value, password: document.getElementById('reg-password').value, role: document.getElementById('reg-role').value, segment: document.getElementById('business-segment').value }); alert(result.message); if (result.user && await verificarSessao()) carregarDoBanco(); else alternarAutenticacao(); } catch (error) { alert(error.message); } }
    function atualizarSegmentoNegocio() { segmentoSelecionado = document.getElementById('business-segment').value; const labels = { CONSTRUCAO_CIVIL:['Centro de custo / obra','Ex: Obra Residencial Torre A'], ENGENHARIA:['Projeto / departamento técnico','Ex: Projeto Elétrico — Bloco B'], MERCADO:['SKU / setor de loja','Ex: Mercearia — SKU-9082'], PETSHOP:['SKU / linha pet','Ex: Rações — SKU-440'], ROUPAS:['Coleção / grade','Ex: Inverno 2026 — M'], TINTAS:['Cor / lote do fabricante','Ex: Branco Fosco — Lote 028'], MATERIAL_CONSTRUCAO:['Categoria / fornecedor','Ex: Hidráulica — Fornecedor X'], CALCADOS:['Coleção / numeração','Ex: Casual — nº 41'] }; const rule = labels[segmentoSelecionado] || labels.CONSTRUCAO_CIVIL; document.getElementById('dynamic-label').textContent = rule[0] + ' *'; document.getElementById('cad-dinamico').placeholder = rule[1]; }
    function configurarOpcoesDoSegmento() {
        const segment = document.getElementById('business-segment').value;
        const options = {
            CONSTRUCAO_CIVIL: { categories:['Ferramentas','EPIs','Elétrica','Hidráulica','Estrutural'], packing:['Palete PBR','Caixa Padrão B','Gaiola Aramada'], picking:['Discreto por obra','Por zona de canteiro','Por lote de frente de trabalho','Por onda de entrega','Prioridade por cronograma'] },
            ENGENHARIA: { categories:['Instrumentos','Componentes técnicos','EPIs','Materiais de projeto','Manutenção'], packing:['Caixa Padrão A','Caixa Padrão B','Maleta técnica'], picking:['Discreto por projeto','Por laboratório','Por lote de manutenção','Por janela de serviço','Por criticidade técnica'] },
            MERCADO: { categories:['Mercearia','Perecíveis','Bebidas','Higiene','Limpeza'], packing:['Caixa plástica','Palete PBR','Gaiola Aramada'], picking:['FEFO por validade','Por onda de reposição','Por corredor','Por loja','Por lote promocional'] },
            PETSHOP: { categories:['Rações','Higiene pet','Acessórios','Medicamentos','Aquarismo'], packing:['Saco','Caixa Padrão B','Palete PBR'], picking:['FEFO por validade','Por espécie','Por pedido e-commerce','Por onda de reposição','Por lote'] },
            ROUPAS: { categories:['Vestuário','Acessórios','Cama e banho','Coleções','Devoluções'], packing:['Caixa Padrão A','Caixa Padrão B','Cabideiro'], picking:['Por coleção','Por grade/tamanho','Por pedido e-commerce','Por onda de expedição','Por loja'] },
            TINTAS: { categories:['Tintas','Complementos','Ferramentas','Químicos','Impermeabilizantes'], packing:['Lata','Balde','Palete PBR'], picking:['Por cor e lote','FEFO','Por pedido balcão','Por rota de entrega','Por onda'] },
            MATERIAL_CONSTRUCAO: { categories:['Cimento','Elétrica','Hidráulica','Ferragens','Revestimentos'], packing:['Palete PBR','Gaiola Aramada','Caixa Padrão C'], picking:['Por endereço pesado','Por rota de entrega','Por lote de obra','Por onda','Discreto'] },
            CALCADOS: { categories:['Calçados','Acessórios','Coleções','Devoluções','Embalagens'], packing:['Caixa de calçado','Caixa Padrão B','Palete PBR'], picking:['Por numeração','Por coleção','Por pedido e-commerce','Por loja','Por onda'] }
        };
        const config = options[segment] || options.CONSTRUCAO_CIVIL;
        const category = document.getElementById('cad-categoria'); const packaging = document.getElementById('cad-embalagem'); const picking = document.getElementById('pick-estratégia');
        category.innerHTML = config.categories.map(v => `<option value="${v}">${v}</option>`).join('');
        packaging.innerHTML = config.packing.map(v => `<option value="${v}">${v}</option>`).join('');
        picking.innerHTML = config.picking.map(v => `<option value="${v}">${v}</option>`).join('');
        atualizarSegmentoNegocio();
    }
    const atualizarSegmentoAnterior = atualizarSegmentoNegocio;
    atualizarSegmentoNegocio = function () { atualizarSegmentoAnterior(); configurarOpcoesDoSegmentoSemRecursao(); };
    function configurarOpcoesDoSegmentoSemRecursao() { const segment = document.getElementById('business-segment').value; const labels = { CONSTRUCAO_CIVIL:'Construção civil', ENGENHARIA:'Engenharia', MERCADO:'Mercado', PETSHOP:'Pet shop', ROUPAS:'Vestuário', TINTAS:'Tintas', MATERIAL_CONSTRUCAO:'Material de construção', CALCADOS:'Calçados' }; document.querySelector('#form-cadastro h2')?.setAttribute('data-segment', labels[segment] || ''); }

    const renderOriginal = render;
    render = function () {
        renderOriginal();
        const rows = document.querySelectorAll('#tabela-estoque tbody tr');
        rows.forEach((row, index) => { const item = estoque[index]; const cell = document.createElement('td'); cell.innerHTML = `<button class="btn-sm" style="background:#fff1f2;color:#be123c" onclick="solicitarExclusao(${item.id}, '${String(item.nome).replace(/'/g, "\\'")}')">Solicitar exclusão</button>`; row.appendChild(cell); });
        const requests = window.ultimasSolicitacoes || [];
        const panel = document.getElementById('security-panel'); panel.style.display = ['ADMIN','GESTOR'].includes(usuarioLogado?.role) ? 'block' : 'none';
        document.getElementById('deletion-requests').innerHTML = requests.map(r => `<p style="margin:8px 0"><strong>${r.itemName}</strong> — solicitado por ${r.requestedBy}. <button class="btn-sm" onclick="aprovarExclusao(${r.id})">Aprovar</button></p>`).join('') || '<small>Nenhuma solicitação pendente.</small>';
    };

    async function requisicaoApi(action, payload = {}) {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, operator: perfilAtual, ...payload })
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.error || 'Não foi possível concluir a operação no servidor.');
        return result;
    }

    function aplicarSnapshot(data) {
        estoque = data.items || [];
        ordensPicking = data.orders || [];
        auditoria = data.audits || [];
        window.ultimasSolicitacoes = data.deletionRequests || [];
        render();
    }

    async function carregarDoBanco() {
        try {
            if (!usuarioLogado && !(await verificarSessao())) return;
            const response = await fetch(API_URL);
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Falha ao carregar dados.');
            aplicarSnapshot(data);
        } catch (error) {
            console.error(error);
            alert('A aplicação precisa ser acessada pelo servidor Apache do XAMPP e o MySQL deve estar configurado. Consulte o arquivo database.sql.');
        }
    }

    async function solicitarExclusao(itemId, itemName) { const reason = prompt(`Informe o motivo para solicitar a exclusão de “${itemName}”.`); if (!reason) return; try { const result = await requisicaoApi('request_deletion', { itemId, reason }); aplicarSnapshot(result.data); alert('Solicitação enviada. Um segundo usuário autorizado deve aprová-la.'); } catch (error) { alert(error.message); } }
    async function aprovarExclusao(requestId) { const password = prompt('Confirme sua senha para aprovar esta exclusão.'); if (!password) return; try { const result = await requisicaoApi('approve_deletion', { requestId, password }); aplicarSnapshot(result.data); alert('Exclusão aprovada e item desativado.'); } catch (error) { alert(error.message); } }
    function baixarRelatorioPdf(module = 'estoque', period = null) { period ||= document.getElementById('report-period')?.value || 'daily'; window.open(`api/report.php?period=${encodeURIComponent(period)}&module=${encodeURIComponent(module)}`, '_blank'); }
    function simularEnvioEmail(module = 'estoque') { const period = document.getElementById('report-period')?.value || 'daily'; const url = `${location.origin}${location.pathname.replace(/[^/]+$/, '')}api/report.php?period=${encodeURIComponent(period)}&module=${encodeURIComponent(module)}`; window.location.href = `mailto:?subject=${encodeURIComponent('Relatório WMS — ' + module)}&body=${encodeURIComponent('Olá, segue o link para gerar o relatório WMS: ' + url)}`; }
    function exportarModuloExcel(module) { const rows = module === 'picking' ? ordensPicking.map(o => [o.id,o.estrategia,o.itemNome,o.qtd,o.pedido,o.status]) : module === 'estoque' ? estoque.map(i => [i.id,i.nome,i.barcode,i.qtd,i.min,i.local]) : auditoria.map(a => [a.time,a.user,a.acao,a.hash]); const csv = '\uFEFF' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g,'""')}"`).join(';')).join('\n'); const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'})); link.download = `relatorio-${module}.csv`; link.click(); URL.revokeObjectURL(link.href); }
    function instalarAcoesDeModulo() { const modules = [['mod-picking','picking','Picking & Separação'],['mod-conferencia','conferencia','Mesa de Conferência'],['mod-fulfillment','fulfillment','Fulfillment 3PL'],['mod-etiquetas','etiquetas','Etiquetas & QR Code']]; modules.forEach(([id,key,name]) => { const target = document.getElementById(id); if (!target || target.querySelector('.module-report-actions')) return; const box = document.createElement('div'); box.className='card module-report-actions'; box.innerHTML = `<h2>Relatórios — ${name}</h2><div class="form-row"><select id="period-${key}"><option value="daily">Diário</option><option value="weekly">Semanal</option><option value="monthly">Mensal</option><option value="annual">Anual</option></select><button class="btn btn-primary" type="button" onclick="baixarRelatorioPdf('${key}',document.getElementById('period-${key}').value)">Baixar PDF</button></div><div class="form-row" style="margin-top:10px"><button class="btn btn-export" type="button" onclick="exportarModuloExcel('${key}')">Baixar Excel/CSV</button><button class="btn btn-pdf" type="button" onclick="simularEnvioEmail('${key}')">Enviar por e-mail</button></div>`; target.appendChild(box); }); }

    async function salvarDocumento(itemId) {
        const file = document.getElementById('cad-documento').files[0];
        if (!file) return;
        const form = new FormData();
        form.append('itemId', itemId);
        form.append('documento', file);
        const response = await fetch('api/document.php', { method: 'POST', body: form });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.error || 'O ativo foi salvo, mas o documento não pôde ser armazenado.');
    }

    async function cadastrarItem(e) {
        e.preventDefault();
        try {
            const result = await requisicaoApi('create_item', {
                nome: document.getElementById('cad-nome').value,
                barcode: document.getElementById('cad-barcode').value,
                risco: document.getElementById('cad-risco').value,
                rotatividade: document.getElementById('cad-rotatividade').value,
                embalagem: document.getElementById('cad-embalagem').value,
                categoria: document.getElementById('cad-categoria').value,
                qtd: Number(document.getElementById('cad-qtd').value),
                min: Number(document.getElementById('cad-min').value),
                local: document.getElementById('cad-local').value,
                segmento: document.getElementById('cad-dinamico').value,
                invoiceNumber: document.getElementById('rec-nf').value,
                invoiceTotal: Number(document.getElementById('rec-nf-total').value),
                receivedAt: document.getElementById('rec-data').value,
                supplier: document.getElementById('rec-empresa').value,
                vehicle: document.getElementById('rec-veiculo').value,
                driver: document.getElementById('rec-motorista').value,
                plate: document.getElementById('rec-placa').value
            });
            await salvarDocumento(result.itemId);
            document.getElementById('form-cadastro').reset();
            calcularEnderecamentoAuto();
            aplicarSnapshot(result.data);
            alert('Ativo cadastrado e salvo no banco de dados.');
        } catch (error) { alert(error.message); }
    }

    async function registrarMovimentacao(e) {
        e.preventDefault();
        try {
            const result = await requisicaoApi('movement', {
                itemId: Number(document.getElementById('mov-ativo').value),
                type: document.getElementById('mov-tipo').value,
                quantity: Number(document.getElementById('mov-qtd').value),
                responsible: document.getElementById('mov-resp').value,
                condition: document.getElementById('mov-condicao').value,
                reason: document.getElementById('mov-motivo').value
                ,emailRequested: document.getElementById('mov-enviar-email').value
            });
            document.getElementById('form-movimentacao').reset();
            aplicarSnapshot(result.data);
            alert('Movimentação registrada no banco de dados.');
        } catch (error) { alert(error.message); }
    }

    async function gerarOrdemPicking(e) {
        e.preventDefault();
        try {
            const result = await requisicaoApi('create_picking', {
                strategy: document.getElementById('pick-estratégia').value,
                itemId: Number(document.getElementById('pick-item').value),
                quantity: Number(document.getElementById('pick-qtd').value),
                customerOrder: document.getElementById('pick-pedido').value
            });
            aplicarSnapshot(result.data);
            document.getElementById('form-picking').reset();
            alert('Ordem de picking salva e encaminhada para conferência.');
        } catch (error) { alert(error.message); }
    }

    async function finalizarConferencia() {
        if (!ordemAtualConf) return;
        try {
            const result = await requisicaoApi('complete_picking', { orderCode: ordemAtualConf.id });
            ordemAtualConf = null;
            document.getElementById('conf-detalhes').style.display = 'none';
            aplicarSnapshot(result.data);
            alert('Conferência concluída e baixa de estoque registrada.');
        } catch (error) { alert(error.message); }
    }

    async function processarFulfillment(e) {
        e.preventDefault();
        try {
            const result = await requisicaoApi('fulfillment', {
                client: document.getElementById('ful-cliente').value,
                cnpj: document.getElementById('ful-cnpj').value,
                itemId: Number(document.getElementById('ful-item').value),
                quantity: Number(document.getElementById('ful-qtd').value),
                recipient: document.getElementById('ful-destinatario').value
            });
            document.getElementById('form-fulfillment').reset();
            aplicarSnapshot(result.data);
            alert(`Operação fulfillment concluída. NF de referência: ${result.invoice}.`);
        } catch (error) { alert(error.message); }
    }

    async function ajustarInventario(e) {
        e.preventDefault();
        try {
            const result = await requisicaoApi('inventory_adjustment', {
                itemId: Number(document.getElementById('inv-item').value),
                factor: Number(document.getElementById('inv-fator').value),
                countedQuantity: Number(document.getElementById('inv-qtd-contada').value),
                reason: document.getElementById('inv-motivo').value
            });
            document.getElementById('form-inventario').reset();
            aplicarSnapshot(result.data);
            alert('Ajuste de inventário armazenado no banco de dados.');
        } catch (error) { alert(error.message); }
    }

    instalarAcoesDeModulo();
    carregarDoBanco();
