# Requisitos do E-Gestão WMS SaaS

## Objetivo
Centralizar rastreabilidade, saldo, movimentação e governança de materiais para construção civil, engenharia e operações de varejo de alta movimentação (mercados, pet shops, vestuário, tintas, materiais de construção e calçados).

## Requisitos funcionais

1. O sistema deve autenticar usuários por e-mail e senha, com perfis ADMIN, GESTOR, OPERADOR e AUDITOR.
2. O primeiro usuário cadastrado deve ser ADMIN; cadastros posteriores devem ser autorizados por um ADMIN.
3. O cliente deve selecionar seu segmento de atuação em lista suspensa; o cadastro deve adequar rótulos e regras ao segmento.
4. O sistema deve cadastrar itens com EAN, embalagem, risco, curva ABC, endereço, estoque mínimo, vínculo operacional e documento de suporte.
4.1. Todo recebimento deve registrar NF, valor total, data, empresa entregadora, veículo, motorista e placa.
5. O sistema deve registrar entradas, devoluções, retiradas e consumo, identificando responsável, condição e motivo.
6. O sistema deve controlar picking, conferência por código de barras, fulfillment e inventário com conversão de unidade.
6.1. As estratégias de picking devem variar conforme o segmento e oferecer no mínimo cinco alternativas operacionais.
7. O sistema deve manter trilha de auditoria das operações relevantes.
8. O sistema deve gerar relatórios PDF diário, semanal, mensal e anual com indicadores de estoque e movimentações.
9. O sistema deve permitir compartilhar, por cliente de e-mail, o link de geração de relatório.
9.1. Cada movimentação deve registrar se o aviso a gestor/administrador foi solicitado e se o e-mail foi efetivamente enviado.
10. O sistema deve exigir dois usuários distintos para exclusão: um solicita com justificativa e outro GESTOR/ADMIN aprova com a própria senha. A exclusão é lógica para preservar histórico.
11. O sistema deve alertar itens com saldo igual ou inferior ao estoque mínimo.

## Requisitos não funcionais

1. Segurança: senhas devem ser armazenadas exclusivamente com `password_hash`; endpoints operacionais exigem sessão autenticada.
2. Integridade: atualizações de saldo devem usar transações, bloqueio de linha e validação de saldo antes da baixa.
3. Auditoria: operações de estoque e aprovações devem registrar usuário, data, descrição e identificador de integridade.
4. Privacidade: anexos aceitos devem ser limitados a PDF/JPG/PNG e a 5 MB; produção deve adotar antivírus e política de retenção.
5. Disponibilidade: produção deve ter backup automatizado do MySQL, monitoramento de Apache/PHP/MySQL e procedimento de restauração testado.
6. Desempenho: tabelas de itens, movimentos e pedidos devem receber índices por empresa, item, data e status ao adotar multitenancy.
7. Escalabilidade SaaS: evoluir para entidades `organizations`, `warehouses` e `company_id` obrigatório em todas as tabelas operacionais, com isolamento de consultas por tenant.
8. Observabilidade: logs técnicos não devem expor senhas, tokens ou documentos; erros devem ter identificador rastreável.
9. Usabilidade: interface responsiva, acessível por teclado, com mensagens de erro acionáveis e confirmação para ações irreversíveis.
10. Conformidade: definir perfis de acesso, política LGPD, retenção de dados, exportação e eliminação de dados por cliente.

## Próximas evoluções prioritárias para SaaS

1. Multitenancy real por organização, depósito e filial.
2. Recuperação de senha por e-mail transacional com token expirável.
3. Integrações com ERP, NF-e, leitores coletores e marketplaces.
4. Dashboard de indicadores: giro, acuracidade, ruptura, ocupação e produtividade de picking.
5. Fila de notificações para alertas de estoque, pedidos e aprovações.
6. Controle de lote, validade, serial number e FEFO para perecíveis.
