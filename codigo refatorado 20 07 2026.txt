<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Gestão: WMS & Fluxo de Estoque Multi-Segmento</title>
    <!-- QRCode Lib para geração dinâmica -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --bg-primary: #1e293b;
            --bg-secondary: #0f172a;
            --card-bg: #ffffff;
            --text-main: #334155;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #ea580c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: var(--text-main); padding: 20px; }
        .container { max-width: 1300px; margin: 0 auto; }
        
        /* Header */
        .header { background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary)); color: white; padding: 30px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); position: relative; }
        .header h1 { font-size: 24px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .system-status { position: absolute; top: 30px; right: 30px; text-align: right; font-size: 13px; opacity: 0.9; }
        .badge { background: var(--success); padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; }

        /* Navigation / Module Tabs */
        .module-tabs { display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; }
        .tab-btn { padding: 10px 18px; border: 1px solid #cbd5e1; background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: bold; color: var(--text-main); white-space: nowrap; transition: all 0.2s; }
        .tab-btn.active { background: var(--accent); color: white; border-color: var(--accent); }

        /* Control Panel / Auth */
        .auth-bar { background: #e2e8f0; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
        .btn-sm { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        
        /* Grid Layout */
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        @media(max-width: 900px) { .grid { grid-template-columns: 1fr; } }
        
        .card { background: var(--card-bg); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .card h2 { font-size: 18px; margin-bottom: 18px; color: var(--bg-secondary); border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        
        /* Forms */
        .form-group { margin-bottom: 15px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #475569; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; transition: border 0.2s; }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); }
        
        /* Buttons */
        .btn { width: 100%; padding: 12px; border: none; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; transition: background 0.2s; text-align: center; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-success { background: var(--success); color: white; }
        .btn-export { background: #64748b; color: white; margin-top: 10px; }
        .btn-pdf { background: #94a3b8; color: white; margin-top: 5px; }
        
        /* Segment Selector */
        .segment-selector { display: flex; gap: 10px; margin-bottom: 15px; }
        .segment-btn { flex: 1; padding: 8px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; }
        .segment-btn.active { background: var(--bg-primary); color: white; border-color: var(--bg-primary); }

        /* Tables & Lists */
        .table-wrapper { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #475569; }
        
        .alert-zone { background: #fff5f5; border-left: 4px solid var(--danger); padding: 10px; margin-bottom: 15px; border-radius: 0 4px 4px 0; font-size: 13px; display: none;}
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Label Generator Box */
        .label-preview { border: 2px dashed #cbd5e1; padding: 15px; border-radius: 8px; background: #f8fafc; text-align: center; margin-top: 15px; }
        #qrcode { display: flex; justify-content: center; margin: 10px 0; }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>📦 E-Gestão Integrada de Estoque & WMS</h1>
        <p>Rastreamento Multi-Segmento, Fulfillment, Picking, Conferência e Endereçamento Inteligente</p>
        <div class="system-status">
            Fuso: <span id="clock">Carregando...</span><br>
            Status: <span class="badge">Ativo (Sistemas WMS)</span>
        </div>
    </div>

    <!-- Gestão de Login / Níveis de Acesso -->
    <div class="auth-bar">
        <div>
            Perfil Atual: <strong id="current-profile">Almoxarife (Acesso Operacional)</strong>
        </div>
        <div>
            <button class="btn-sm" style="background: #cbd5e1;" onclick="switchProfile('Almoxarife')">Modo Almoxarife</button>
            <button class="btn-sm" style="background: var(--bg-primary); color: white;" onclick="switchProfile('Gestor / ADM')">Modo Gestor / ADM</button>
        </div>
    </div>

    <!-- Navegação por Módulos Logísticos -->
    <div class="module-tabs">
        <button class="tab-btn active" onclick="switchTab('mod-operacao', this)">📋 Cadastro & Movimentação</button>
        <button class="tab-btn" onclick="switchTab('mod-picking', this)">🛒 Controle de Picking & Separação</button>
        <button class="tab-btn" onclick="switchTab('mod-conferencia', this)">🔍 Mesa de Conferência</button>
        <button class="tab-btn" onclick="switchTab('mod-fulfillment', this)">🚚 Operação Fulfillment (3PL)</button>
        <button class="tab-btn" onclick="switchTab('mod-inventario', this)">📊 Inventário Físico & Conversões</button>
        <button class="tab-btn" onclick="switchTab('mod-etiquetas', this)">🏷️ Gestão de Etiquetas & QR Code</button>
    </div>

    <!-- Painel de Alertas Rápidos (Estoque Mínimo) -->
    <div id="min-stock-alert" class="alert-zone">
        ⚠️ <strong>Aviso de Estoque Crítico:</strong> Existem itens abaixo do estoque mínimo de segurança! Verifique a tabela.
    </div>

    <!-- MÓDULO 1: OPERAÇÃO PADRÃO (CADASTRO & MOVIMENTAÇÃO) -->
    <div id="mod-operacao" class="tab-content active">
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
                        <label>Nome do Ativo / Produto *</label>
                        <input type="text" id="cad-nome" placeholder="Ex: Serra Circular ou Pneu Scania" required>
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
                            <input type="text" id="cad-barcode" placeholder="Ex: 7891234567890" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Estoque Inicial *</label>
                            <input type="number" id="cad-qtd" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Estoque Mínimo Alerta *</label>
                            <input type="number" id="cad-min" value="5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Endereçamento Sugerido Automaticamente</label>
                        <input type="text" id="cad-local" readonly style="background-color: #f1f5f9; font-weight: bold; color: var(--accent);">
                    </div>

                    <div class="form-group" id="dynamic-field">
                        <label id="dynamic-label">Vínculo de Centro de Custo / Obra Padrão *</label>
                        <input type="text" id="cad-dinamico" placeholder="Ex: Obra Residencial Torre A" required>
                    </div>

                    <div class="form-group">
                        <label>Validação Documental (Anexar XML/Nota) *</label>
                        <input type="file" id="cad-documento" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Cadastrar Ativo com Validação WMS</button>
                </form>
            </div>

            <!-- FLUXO DE MOVIMENTAÇÃO & SEGURANÇA -->
            <div class="card">
                <h2>🔄 Fluxo de Movimentação & Termo</h2>
                <form id="form-movimentacao" onsubmit="registrarMovimentacao(event)">
                    <div class="form-group">
                        <label>Selecione o Ativo em Estoque</label>
                        <select id="mov-ativo" required></select>
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
                            <input type="number" id="mov-qtd" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Responsável / Custodiante</label>
                            <input type="text" id="mov-resp" placeholder="Nome do Colaborador" required>
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
                        <textarea id="mov-motivo" rows="2" placeholder="Descreva a aplicação do material..." required></textarea>
                    </div>

                    <div class="form-group" id="termo-box">
                        <label style="color: var(--warning)">🔐 Segurança: Exigir Assinatura Digital do Termo de Custódia</label>
                        <select id="mov-2fa">
                            <option value="Sim">Sim, Validado via Token/Biometria do Operador</option>
                            <option value="Não">Não aplicar segurança estendida</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="background-color: var(--bg-secondary)">Registrar Movimentação</button>
                </form>
            </div>
        </div>

        <!-- TABELA DE VISUALIZAÇÃO EM TEMPO REAL -->
        <div class="card">
            <h2>📦 Estoque Atual & Monitoramento WMS</h2>
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
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <button class="btn btn-export" onclick="exportarExcel()">📥 Exportar Banco de Dados Atual (Excel/CSV)</button>
            <button class="btn btn-pdf" onclick="simularEnvioEmail()">📧 Disparar Relatório Consolidado para Envolvidos (E-mail)</button>
        </div>
    </div>

    <!-- MÓDULO 2: CONTROLE DE PICKING -->
    <div id="mod-picking" class="tab-content">
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
                        <select id="pick-item" required></select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quantidade Solicitada</label>
                        <input type="number" id="pick-qtd" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Nº do Pedido / Destino</label>
                        <input type="text" id="pick-pedido" placeholder="Ex: PED-2026-88" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Gerar Ordem de Picking Otimizada</button>
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
    <div id="mod-conferencia" class="tab-content">
        <div class="card">
            <h2>🔍 Mesa de Conferência & Checkout Bipado</h2>
            <p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Validação final de itens vindos do picking. Bipe o código de barras para auditar e gerar a etiqueta de volume.</p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Simulador de Leitor de Código de Barras (Bipar Cód. Barras)</label>
                    <input type="text" id="conf-barcode" placeholder="Bipe ou digite o código de barras aqui e pressione Enter" onkeydown="biparConferencia(event)">
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
                <br>
                <button class="btn btn-success" id="btn-finalizar-conf" onclick="finalizarConferencia()" disabled>Concluir Conferência e Emitir Etiqueta de Volume</button>
            </div>
        </div>
    </div>

    <!-- MÓDULO 4: OPERAÇÃO FULFILLMENT -->
    <div id="mod-fulfillment" class="tab-content">
        <div class="card">
            <h2>🚚 Operação Fulfillment (Gestão 3PL & Emissão de NF de Terceiros)</h2>
            <p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Gerencie e despache estoque pertencente a terceiros/clientes com emissão direta de nota fiscal de envio.</p>

            <form id="form-fulfillment" onsubmit="processarFulfillment(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome do Cliente / Depositante (Dono da Mercadoria)</label>
                        <input type="text" id="ful-cliente" placeholder="Ex: Loja Parceira E-Commerce" required>
                    </div>
                    <div class="form-group">
                        <label>CNPJ / CPF do Cliente</label>
                        <input type="text" id="ful-cnpj" placeholder="00.000.000/0001-00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Selecione o Item do Estoque</label>
                        <select id="ful-item" required></select>
                    </div>
                    <div class="form-group">
                        <label>Quantidade para Despacho</label>
                        <input type="number" id="ful-qtd" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dados do Destinatário Final (Endereço de Entrega)</label>
                    <input type="text" id="ful-destinatario" placeholder="Ex: João da Silva - Av. Principal 100, SP" required>
                </div>

                <button type="submit" class="btn btn-primary">Processar Fulfillment e Emitir NF de Terceiro</button>
            </form>
        </div>
    </div>

    <!-- MÓDULO 5: INVENTÁRIO FÍSICO & CONVERSÕES -->
    <div id="mod-inventario" class="tab-content">
        <div class="card">
            <h2>📊 Inventário Físico com Fator de Conversão de Unidades</h2>
            <p style="font-size: 13px; margin-bottom: 15px; color: #64748b;">Realize conciliação do estoque lógico com o físico usando múltiplos fatores de conversão (Ex: Caixa com 12 un, Bobina com 50m, Palete com 100un).</p>

            <form id="form-inventario" onsubmit="ajustarInventario(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Selecione o Item para Auditar</label>
                        <select id="inv-item" required></select>
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
                        <input type="number" id="inv-qtd-contada" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo do Ajuste de Inventário</label>
                        <input type="text" id="inv-motivo" placeholder="Ex: Divergência detectada no inventário cíclico" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Aplicar Ajuste de Conciliação de Inventário</button>
            </form>
        </div>
    </div>

    <!-- MÓDULO 6: GESTÃO DE ETIQUETAS & QR CODE -->
    <div id="mod-etiquetas" class="tab-content">
        <div class="card">
            <h2>🏷️ Gerador de Etiquetas Padrão & QR Code WMS</h2>
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
                    <strong id="lbl-nome">Selecione um item</strong><br>
                    <span id="lbl-detalhes">---</span>
                </div>
                <div id="qrcode"></div>
                <button class="btn btn-sm" style="background: var(--bg-primary); color: white; width: auto; margin-top: 10px;" onclick="window.print()">🖨️ Imprimir Etiqueta</button>
            </div>
        </div>
    </div>

    <!-- LOG DE AUDITORIA (AUDIT TRAIL) -->
    <div class="card">
        <h2>🔍 Trilha de Auditoria Invisível (Histórico do Sistema)</h2>
        <div class="table-wrapper">
            <table style="font-size: 12px;" id="tabela-auditoria">
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
</div>

<script>
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
</script>
</body>
</html>