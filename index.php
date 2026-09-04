<?php declare(strict_types=1); ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>E-Gestão Integrada de Estoque & WMS</title><link rel="stylesheet" href="assets/css/app.css"></head><body>
<div class="container">
<!-- Header -->
<div class="header">
<h1>📦 E-Gestão Integrada de Estoque &amp; WMS</h1>
<p>Rastreamento Multi-Segmento, Fulfillment, Picking, Conferência e Endereçamento Inteligente</p>
<div class="system-status">
            Fuso: <span id="clock">Carregando...</span><br/>
            Status: <span class="badge">Ativo (Sistemas WMS)</span>
</div>
</div>
<div class="utility-bar">
<div><strong>Segmento atendido</strong><br/><small>Define regras e contexto operacional do cliente</small></div>
<select id="business-segment" onchange="atualizarSegmentoNegocio()">
<option value="CONSTRUCAO_CIVIL">Construção civil</option>
<option value="ENGENHARIA">Engenharia e departamentos técnicos</option>
<option value="MERCADO">Varejo — Mercado</option>
<option value="PETSHOP">Varejo — Pet shop</option>
<option value="ROUPAS">Varejo — Loja de roupas</option>
<option value="TINTAS">Varejo — Loja de tintas</option>
<option value="MATERIAL_CONSTRUCAO">Varejo — Material de construção</option>
<option value="CALCADOS">Varejo — Loja de calçados</option>
</select>
</div>
<!-- Gestão de Login / Níveis de Acesso -->
<div class="auth-bar">
<div>
            Perfil Atual: <strong id="current-profile">Almoxarife (Acesso Operacional)</strong>
</div>
<div>
<button class="btn-sm" onclick="switchProfile('Almoxarife')" style="background: #cbd5e1;">Modo Almoxarife</button>
<button class="btn-sm" onclick="switchProfile('Gestor / ADM')" style="background: var(--bg-primary); color: white;">Modo Gestor / ADM</button>
<button class="btn-sm" onclick="bloquearTela()" style="background: #b45309; color: white;">Sair / Bloquear tela</button>
<button class="btn-sm" onclick="encerrarSessao()" style="background: #b91c1c; color: white;">Encerrar sessão</button>
</div>
</div>
<!-- Navegação por Módulos Logísticos -->
<div class="app-layout">
<aside aria-label="Módulos do sistema" class="module-tabs">
<button class="tab-btn active" onclick="switchTab('mod-operacao', this)">📋 Cadastro &amp; Movimentação</button>
<button class="tab-btn" onclick="switchTab('mod-picking', this)">🛒 Controle de Picking &amp; Separação</button>
<button class="tab-btn" onclick="switchTab('mod-conferencia', this)">🔍 Mesa de Conferência</button>
<button class="tab-btn" onclick="switchTab('mod-fulfillment', this)">🚚 Operação Fulfillment (3PL)</button>
<button class="tab-btn" onclick="switchTab('mod-inventario', this)">📊 Inventário Físico &amp; Conversões</button>
<button class="tab-btn" onclick="switchTab('mod-etiquetas', this)">🏷️ Gestão de Etiquetas &amp; QR Code</button>
<button class="tab-btn" onclick="abrirTutorial(true)" type="button">❔ Tutorial de utilização</button>
</aside>
<main class="workspace">
<!-- Painel de Alertas Rápidos (Estoque Mínimo) -->
<div class="alert-zone" id="min-stock-alert">
        ⚠️ <strong>Aviso de Estoque Crítico:</strong> Existem itens abaixo do estoque mínimo de segurança! Verifique a tabela.
    </div>
<!-- MÓDULO 1: OPERAÇÃO PADRÃO (CADASTRO & MOVIMENTAÇÃO) -->
<div class="tab-content active" id="mod-operacao">
<div class="grid">
<!-- CADASTRO INTELIGENTE COM ENDEREÇAMENTO AUTOMÁTICO E EMBALAGENS PADRÃO -->
<div class="card">
<h2>📝 Cadastro Dinâmico de Ativos com Endereçamento Automático</h2>
<label>Selecione a Regra de Negócio do Segmento:</label>
<div class="segment-selector">
<button class="segment-btn active" onclick="setSegment('construcao', this)">🏗️ Construção</button>
<button class="segment-btn" onclick="setSegment('varejo', this)">🏷️ Varejo</button>
<button class="segment-btn" onclick="setSegment('transportadora', this)">🚛 Transporte</button>
</div>
<form id="form-cadastro" onsubmit="cadastrarItem(event)">
<div class="form-group">
<label>Categoria do item *</label>
<select id="cad-categoria"></select>
</div>
<div class="form-group">
<label>Nome do Ativo / Produto *</label>
<input id="cad-nome" placeholder="Ex: Serra Circular ou Pneu Scania" required="" type="text"/>
</div>
<div class="form-row">
<div class="form-group">
<label>Categoria de Risco / Especialidade *</label>
<select id="cad-risco" onchange="calcularEnderecamentoAuto()">
<option value="Geral">Padrão / Geral</option>
<option value="Químico">Químico / Reagente</option>
<option value="Alimentar">Alimentar / Perecível</option>
</select>
</div>
<div class="form-group">
<label>Curva de Rotatividade (ABC) *</label>
<select id="cad-rotatividade" onchange="calcularEnderecamentoAuto()">
<option value="Alta">Alta Rotatividade (Picking Rápido)</option>
<option value="Media">Média Rotatividade</option>
<option value="Baixa">Baixa Rotatividade (Pulmão)</option>
</select>
</div>
</div>
<h3 style="font-size:14px;color:var(--bg-primary);margin:20px 0 12px;">Dados de recebimento e nota fiscal</h3>
<div class="form-row">
<div class="form-group"><label>Número da NF *</label><input id="rec-nf" placeholder="Ex: 000123" required="" type="text"/></div>
<div class="form-group"><label>Valor total da NF (R$) *</label><input id="rec-nf-total" min="0.01" required="" step="0.01" type="number"/></div>
</div>
<div class="form-row">
<div class="form-group"><label>Data de recebimento *</label><input id="rec-data" required="" type="date"/></div>
<div class="form-group"><label>Empresa entregadora *</label><input id="rec-empresa" required="" type="text"/></div>
</div>
<div class="form-row">
<div class="form-group"><label>Veículo *</label><input id="rec-veiculo" placeholder="Ex: Caminhão baú" required="" type="text"/></div>
<div class="form-group"><label>Motorista *</label><input id="rec-motorista" required="" type="text"/></div>
</div>
<div class="form-group"><label>Placa do veículo *</label><input id="rec-placa" placeholder="ABC1D23" required="" type="text"/></div>
<div class="form-row">
<div class="form-group">
<label>Unidade de Carga Padronizada *</label>
<select id="cad-embalagem">
<option value="Palete PBR">Palete PBR (1200x1000mm)</option>
<option value="Caixa Padrão A">Caixa Padrão A (Small - 30x20x20cm)</option>
<option value="Caixa Padrão B">Caixa Padrão B (Medium - 50x40x40cm)</option>
<option value="Caixa Padrão C">Caixa Padrão C (Large - 80x60x60cm)</option>
<option value="Gaiola Aramada">Gaiola Aramada Padronizada</option>
</select>
</div>
<div class="form-group">
<label>Código de Barras / EAN *</label>
<input id="cad-barcode" placeholder="Ex: 7891234567890" required="" type="text"/>
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>Estoque Inicial *</label>
<input id="cad-qtd" min="1" required="" type="number"/>
</div>
<div class="form-group">
<label>Estoque Mínimo Alerta *</label>
<input id="cad-min" required="" type="number" value="5"/>
</div>
</div>
<div class="form-group">
<label>Endereçamento Sugerido Automaticamente</label>
<input id="cad-local" readonly="" style="background-color: #f1f5f9; font-weight: bold; color: var(--accent);" type="text"/>
</div>
<div class="form-group" id="dynamic-field">
<label id="dynamic-label">Vínculo de Centro de Custo / Obra Padrão *</label>
<input id="cad-dinamico" placeholder="Ex: Obra Residencial Torre A" required="" type="text"/>
</div>
<div class="form-group">
<label>Validação Documental (Anexar XML/Nota) *</label>
<input id="cad-documento" required="" type="file"/>
</div>
<button class="btn btn-primary" type="submit">Cadastrar Ativo com Validação WMS</button>
</form>
</div>
<!-- FLUXO DE MOVIMENTAÇÃO & SEGURANÇA -->
<div class="card">
<h2>🔄 Fluxo de Movimentação &amp; Termo</h2>
<form id="form-movimentacao" onsubmit="registrarMovimentacao(event)">
<div class="form-group">
<label>Selecione o Ativo em Estoque</label>
<select id="mov-ativo" required=""></select>
</div>
<div class="form-row">
<div class="form-group">
<label>Tipo de Movimento</label>
<select id="mov-tipo" onchange="toggleTermoRequire()">
<option value="Retirada">Retirada (Para Uso/Custódia)</option>
<option value="Devolução">Devolução ao Estoque</option>
<option value="Consumo">Consumo Interno (Baixa Definitiva)</option>
</select>
</div>
<div class="form-group">
<label>Quantidade a Movimentar</label>
<input id="mov-qtd" min="1" required="" type="number" value="1"/>
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>Responsável / Custodiante</label>
<input id="mov-resp" placeholder="Nome do Colaborador" required="" type="text"/>
</div>
<div class="form-group">
<label>Condição Física do Material</label>
<select id="mov-condicao">
<option value="Excelente">Excelente / Sem Detalhes</option>
<option value="Bom">Bom / Uso Normal</option>
<option value="Com Avarias">Com Avarias Leves</option>
<option value="Danificado">⚠️ Avariado / Danificado</option>
</select>
</div>
</div>
<div class="form-group">
<label>Motivo / Destino da Movimentação</label>
<textarea id="mov-motivo" placeholder="Descreva a aplicação do material..." required="" rows="2"></textarea>
</div>
<div class="form-group" id="termo-box">
<label style="color: var(--warning)">🔐 Segurança: Exigir Assinatura Digital do Termo de Custódia</label>
<select id="mov-2fa">
<option value="Sim">Sim, Validado via Token/Biometria do Operador</option>
<option value="Não">Não aplicar segurança estendida</option>
</select>
</div>
<div class="form-group">
<label>Notificar gestor e administrador por e-mail?</label>
<select id="mov-enviar-email"><option value="NAO">Não enviar e-mail</option><option value="SIM">Sim, enviar aviso automático</option></select>
</div>
<button class="btn btn-primary" style="background-color: var(--bg-secondary)" type="submit">Registrar Movimentação</button>
</form>
</div>
</div>
<!-- TABELA DE VISUALIZAÇÃO EM TEMPO REAL -->
<div class="card">
<h2>📦 Estoque Atual &amp; Monitoramento WMS</h2>
<div class="table-wrapper">
<table id="tabela-estoque">
<thead>
<tr>
<th>Ativo / Item</th>
<th>Cód. Barras</th>
<th>Unidade Carga</th>
<th>Qtd. Sistema</th>
<th>Mínimo</th>
<th>Endereço WMS</th>
<th>Cliente / Vínculo</th>
<th>Status Alerta</th>
<th>Ações</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>
<button class="btn btn-export" onclick="exportarExcel()">📥 Exportar Banco de Dados Atual (Excel/CSV)</button>
<div class="form-row" style="margin-top:10px;">
<select id="report-period"><option value="daily">Relatório diário</option><option value="weekly">Relatório semanal</option><option value="monthly">Relatório mensal</option><option value="annual">Relatório anual</option></select>
<button class="btn btn-pdf" onclick="baixarRelatorioPdf()" type="button">⬇ Baixar relatório em PDF</button>
</div>
<button class="btn btn-pdf" onclick="simularEnvioEmail()">📧 Enviar link do relatório por e-mail</button>
<div class="card security-panel" id="security-panel">
<h2>Governança — Exclusões pendentes</h2>
<p style="font-size:13px;margin-bottom:12px;">Uma exclusão exige solicitação por um usuário e aprovação de outro gestor ou administrador com confirmação de senha.</p>
<div id="deletion-requests"></div>
</div>
</div>
</div>
<!-- MÓDULO 2: CONTROLE DE PICKING -->
<div class="tab-content" id="mod-picking">
<div class="card">
<h2>🛒 Gestão e Estratégia de Picking (Separação de Pedidos)</h2>
<p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Gere ordens de picking otimizando a rota do operador e escolhendo o melhor modelo de processo.</p>
<form id="form-picking" onsubmit="gerarOrdemPicking(event)">
<div class="form-row">
<div class="form-group">
<label>Estratégia de Picking</label>
<select id="pick-estratégia">
<option value="Discreto">Picking Discreto (1 Pedido por vez - Alta precisão)</option>
<option value="Lote">Picking por Lote (Consolidado múltiplos pedidos)</option>
<option value="Zona">Picking por Zona (Dividido por Setores/Endereço)</option>
<option value="Onda">Picking por Onda (Agendado por Turno/Doca)</option>
</select>
</div>
<div class="form-group">
<label>Selecione o Item para Separação</label>
<select id="pick-item" required=""></select>
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>Quantidade Solicitada</label>
<input id="pick-qtd" min="1" required="" type="number" value="1"/>
</div>
<div class="form-group">
<label>Nº do Pedido / Destino</label>
<input id="pick-pedido" placeholder="Ex: PED-2026-88" required="" type="text"/>
</div>
</div>
<button class="btn btn-primary" type="submit">Gerar Ordem de Picking Otimizada</button>
</form>
<h3 style="margin-top: 25px; font-size: 15px;">Ordens de Picking em Andamento / Transferência para Bancada</h3>
<div class="table-wrapper">
<table id="tabela-picking">
<thead>
<tr>
<th>ID Ordem</th>
<th>Estratégia</th>
<th>Item</th>
<th>Endereço Origem</th>
<th>Qtd</th>
<th>Pedido</th>
<th>Status</th>
<th>Ação</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>
</div>
</div>
<!-- MÓDULO 3: MESA DE CONFERÊNCIA -->
<div class="tab-content" id="mod-conferencia">
<div class="card">
<h2>🔍 Mesa de Conferência &amp; Checkout Bipado</h2>
<p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Validação final de itens vindos do picking. Bipe o código de barras para auditar e gerar a etiqueta de volume.</p>
<div class="form-row">
<div class="form-group">
<label>Simulador de Leitor de Código de Barras (Bipar Cód. Barras)</label>
<input id="conf-barcode" onkeydown="biparConferencia(event)" placeholder="Bipe ou digite o código de barras aqui e pressione Enter" type="text"/>
</div>
<div class="form-group">
<label>Selecione a Ordem de Picking para Conferir</label>
<select id="conf-ordem" onchange="carregarOrdemConferencia()"></select>
</div>
</div>
<div id="conf-detalhes" style="display:none; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px;">
<p><strong>Item:</strong> <span id="conf-item-nome">-</span></p>
<p><strong>Qtd Esperada:</strong> <span id="conf-item-qtd">-</span> | <strong>Qtd Bipada/Validada:</strong> <span id="conf-item-bipada" style="color: var(--accent); font-weight: bold;">0</span></p>
<p><strong>Código Esperado:</strong> <span id="conf-item-code">-</span></p>
<br/>
<button class="btn btn-success" disabled="" id="btn-finalizar-conf" onclick="finalizarConferencia()">Concluir Conferência e Emitir Etiqueta de Volume</button>
</div>
</div>
</div>
<!-- MÓDULO 4: OPERAÇÃO FULFILLMENT -->
<div class="tab-content" id="mod-fulfillment">
<div class="card">
<h2>🚚 Operação Fulfillment (Gestão 3PL &amp; Emissão de NF de Terceiros)</h2>
<p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Gerencie e despache estoque pertencente a terceiros/clientes com emissão direta de nota fiscal de envio.</p>
<form id="form-fulfillment" onsubmit="processarFulfillment(event)">
<div class="form-row">
<div class="form-group">
<label>Nome do Cliente / Depositante (Dono da Mercadoria)</label>
<input id="ful-cliente" placeholder="Ex: Loja Parceira E-Commerce" required="" type="text"/>
</div>
<div class="form-group">
<label>CNPJ / CPF do Cliente</label>
<input id="ful-cnpj" placeholder="00.000.000/0001-00" required="" type="text"/>
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>Selecione o Item do Estoque</label>
<select id="ful-item" required=""></select>
</div>
<div class="form-group">
<label>Quantidade para Despacho</label>
<input id="ful-qtd" min="1" required="" type="number" value="1"/>
</div>
</div>
<div class="form-group">
<label>Dados do Destinatário Final (Endereço de Entrega)</label>
<input id="ful-destinatario" placeholder="Ex: João da Silva - Av. Principal 100, SP" required="" type="text"/>
</div>
<button class="btn btn-primary" type="submit">Processar Fulfillment e Emitir NF de Terceiro</button>
</form>
</div>
</div>
<!-- MÓDULO 5: INVENTÁRIO FÍSICO & CONVERSÕES -->
<div class="tab-content" id="mod-inventario">
<div class="card">
<h2>📊 Inventário Físico com Fator de Conversão de Unidades</h2>
<p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Realize conciliação do estoque lógico com o físico usando múltiplos fatores de conversão (Ex: Caixa com 12 un, Bobina com 50m, Palete com 100un).</p>
<form id="form-inventario" onsubmit="ajustarInventario(event)">
<div class="form-row">
<div class="form-group">
<label>Selecione o Item para Auditar</label>
<select id="inv-item" required=""></select>
</div>
<div class="form-group">
<label>Unidade de Medida / Fator de Contagem</label>
<select id="inv-fator">
<option value="1">Unidades Avulsas (Fator: 1)</option>
<option value="12">Caixa Fechada (Fator: 12 un)</option>
<option value="24">Caixa Máster (Fator: 24 un)</option>
<option value="100">Palete Fechado (Fator: 100 un)</option>
</select>
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>Quantidade Contada no Físico</label>
<input id="inv-qtd-contada" min="0" required="" type="number"/>
</div>
<div class="form-group">
<label>Motivo do Ajuste de Inventário</label>
<input id="inv-motivo" placeholder="Ex: Divergência detectada no inventário cíclico" required="" type="text"/>
</div>
</div>
<button class="btn btn-primary" type="submit">Aplicar Ajuste de Conciliação de Inventário</button>
</form>
</div>
</div>
<!-- MÓDULO 6: GESTÃO DE ETIQUETAS & QR CODE -->
<div class="tab-content" id="mod-etiquetas">
<div class="card">
<h2>🏷️ Gerador de Etiquetas Padrão &amp; QR Code WMS</h2>
<p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Crie etiquetas térmicas de rastreabilidade para produtos, volumes e paletes.</p>
<div class="form-row">
<div class="form-group">
<label>Selecione o Item para Gerar Etiqueta</label>
<select id="etiq-item" onchange="gerarEtiquetaPreview()"></select>
</div>
<div class="form-group">
<label>Tipo de Etiqueta</label>
<select id="etiq-tipo" onchange="gerarEtiquetaPreview()">
<option value="Volume">Etiqueta de Volume / Expedição</option>
<option value="Endereço">Etiqueta de Identificação de Endereço/Gaiola</option>
<option value="EAN">Etiqueta de Produto EAN-13 + QR Code</option>
</select>
</div>
</div>
<div class="label-preview">
<h3>Visualização da Etiqueta de Armazém</h3>
<div id="etiq-conteudo" style="margin-top: 10px;">
<strong id="lbl-nome">Selecione um item</strong><br/>
<span id="lbl-detalhes">---</span>
</div>
<div id="qrcode"></div>
<button class="btn btn-sm" onclick="window.print()" style="background: var(--bg-primary); color: white; width: auto; margin-top: 10px;">🖨️ Imprimir Etiqueta</button>
</div>
</div>
</div>
<!-- LOG DE AUDITORIA (AUDIT TRAIL) -->
<div class="card">
<h2>🔍 Trilha de Auditoria Invisível (Histórico do Sistema)</h2>
<div class="table-wrapper">
<table id="tabela-auditoria" style="font-size: 12px;">
<thead>
<tr>
<th>Timestamp</th>
<th>Operador</th>
<th>Ação Realizada</th>
<th>Registro Hash</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>
</div>
</main>
</div>
<div class="auth-overlay hidden" id="tutorial-overlay">
<div class="tutorial-panel">
<h2>Guia rápido do E-Gestão WMS</h2>
<p>Você pode abrir este guia novamente no menu lateral a qualquer momento.</p>
<div class="tutorial-steps">
<div class="tutorial-step"><strong>1. Defina o segmento</strong><br/><small>Escolha o ramo atendido no topo. As categorias, embalagens e estratégias de picking serão ajustadas.</small></div>
<div class="tutorial-step"><strong>2. Registre o recebimento</strong><br/><small>Em Cadastro &amp; Movimentação, informe item, endereço e dados da nota fiscal e entrega.</small></div>
<div class="tutorial-step"><strong>3. Controle o fluxo</strong><br/><small>Use Picking, Conferência, Fulfillment e Inventário na ordem operacional da sua empresa.</small></div>
<div class="tutorial-step"><strong>4. Acompanhe e proteja</strong><br/><small>Baixe relatórios, acompanhe alertas e use a aprovação dupla para exclusões.</small></div>
</div>
<label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;"><input id="tutorial-no-show" style="width:auto;" type="checkbox"/> Não mostrar automaticamente nos próximos acessos</label>
<button class="btn btn-primary" onclick="fecharTutorial()" type="button">Começar a usar o sistema</button>
</div>
</div>
<div class="auth-overlay" id="auth-overlay">
<div class="auth-panel">
<h2 id="auth-title">Acessar o E-Gestão WMS</h2>
<p id="auth-subtitle">Entre com suas credenciais para operar o estoque.</p>
<form id="login-form" onsubmit="fazerLogin(event)">
<div class="form-group"><label>E-mail</label><input id="login-email" required="" type="email"/></div>
<div class="form-group"><label>Senha</label><input id="login-password" required="" type="password"/></div>
<button class="btn btn-primary" type="submit">Entrar</button>
</form>
<form id="recovery-form" onsubmit="solicitarRedefinicao(event)" style="display:none;">
<div class="form-group"><label>E-mail cadastrado</label><input id="recovery-email" required="" type="email"/></div>
<button class="btn btn-primary" type="submit">Enviar link de recuperação</button>
</form>
<form id="reset-form" onsubmit="redefinirSenha(event)" style="display:none;">
<div class="form-group"><label>Nova senha</label><input id="reset-password" minlength="8" required="" type="password"/></div>
<div class="form-group"><label>Confirme a nova senha</label><input id="reset-password-confirm" minlength="8" required="" type="password"/></div>
<button class="btn btn-primary" type="submit">Redefinir senha</button>
</form>
<form id="lock-form" onsubmit="desbloquearTela(event)" style="display:none;">
<div class="form-group"><label id="lock-user">Sessão bloqueada</label><input autofocus="" id="unlock-password" placeholder="Digite sua senha para continuar" required="" type="password"/></div>
<button class="btn btn-primary" type="submit">Desbloquear</button>
</form>
<form id="register-form" onsubmit="cadastrarUsuario(event)" style="display:none;">
<div class="form-group"><label>Nome completo</label><input id="reg-name" required=""/></div>
<div class="form-group"><label>E-mail</label><input id="reg-email" required="" type="email"/></div>
<div class="form-group"><label>Senha (mínimo 8 caracteres)</label><input id="reg-password" minlength="8" required="" type="password"/></div>
<div class="form-group"><label>Perfil</label><select id="reg-role"><option value="OPERADOR">Operador</option><option value="GESTOR">Gestor</option><option value="AUDITOR">Auditor</option></select></div>
<button class="btn btn-primary" type="submit">Criar usuário</button>
</form>
<button class="link-button" id="auth-toggle" onclick="alternarAutenticacao()" type="button">Criar a primeira conta</button>
<button class="link-button" id="forgot-password" onclick="mostrarRecuperacao()" type="button">Esqueci minha senha</button>

<main class="workspace">
</main>
</div>


</div></div><script src="assets/js/config/app.config.js"></script><script src="assets/js/services/backend.service.js"></script><script src="assets/js/services/auth.service.js"></script><script src="assets/js/services/estoque.service.js"></script><script src="assets/js/services/picking.service.js"></script><script src="assets/js/services/fulfillment.service.js"></script><script src="assets/js/services/inventario.service.js"></script><script src="assets/js/services/document.service.js"></script><script src="assets/js/services/report.service.js"></script><script src="assets/js/modules/app.js"></script></body></html>