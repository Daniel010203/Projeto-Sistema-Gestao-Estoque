# 📦 Sistema de Gestão de Estoque

> Sistema web desenvolvido para gerenciamento e controle de estoque, utilizando **PHP como principal tecnologia do Back-End**.

## 📋 Sobre o Projeto

O **Sistema de Gestão de Estoque** é uma aplicação web desenvolvida com o objetivo de facilitar o gerenciamento de produtos e operações relacionadas ao controle de estoque.

O projeto foi desenvolvido com foco na aplicação prática do **PHP no desenvolvimento Back-End**, explorando conceitos fundamentais da programação web, integração com banco de dados e construção de funcionalidades voltadas para regras de negócio.

Mais do que uma interface de cadastro, este projeto representa a aplicação do PHP como tecnologia responsável pelo processamento das regras da aplicação, comunicação com o banco de dados e gerenciamento das operações realizadas pelo usuário.

---

# 🚀 Tecnologias Utilizadas

O projeto utiliza tecnologias fundamentais para o desenvolvimento de aplicações web.

### Back-End

* **PHP**
* Processamento de regras de negócio
* Manipulação de requisições HTTP
* Integração com banco de dados
* Processamento de formulários
* Implementação da lógica da aplicação

### Banco de Dados

* **MySQL**

### Front-End

* HTML5
* CSS3
* JavaScript

---

# 🐘 PHP como Tecnologia Principal

O **PHP é o núcleo do Back-End da aplicação**.

Sua utilização permite separar a lógica de processamento da camada visual da aplicação, possibilitando a construção de funcionalidades dinâmicas e a integração com o banco de dados.

Dentro do sistema, o PHP pode ser responsável por processos como:

* Processamento de formulários;
* Cadastro e gerenciamento de produtos;
* Comunicação com o banco de dados;
* Execução de consultas SQL;
* Atualização de informações;
* Exclusão de registros;
* Validação de dados;
* Controle das regras de negócio;
* Gerenciamento das operações do sistema.

A escolha do PHP demonstra sua relevância no desenvolvimento de aplicações web tradicionais e sistemas corporativos, especialmente em cenários que demandam integração com bancos de dados relacionais.

---

# ⚙️ Funcionalidades do Sistema

O sistema foi projetado para centralizar operações relacionadas ao gerenciamento de estoque.

Entre as principais funcionalidades esperadas em uma aplicação desta natureza estão:

* 📦 Cadastro de produtos;
* 📝 Consulta de produtos cadastrados;
* ✏️ Atualização de informações;
* 🗑️ Exclusão de registros;
* 📊 Controle das informações de estoque;
* 🔎 Consulta e visualização de dados;
* 🗄️ Persistência das informações em banco de dados.

---

# 🏗️ Arquitetura da Aplicação

A aplicação segue uma estrutura baseada na separação entre diferentes responsabilidades do sistema.

```text
Projeto-Sistema-Gestao-Estoque
│
├── 📁 assets
│   ├── css
│   ├── js
│   └── imagens
│
├── 📁 config
│   └── configuração de conexão
│
├── 📁 database
│   └── scripts SQL
│
├── 📁 pages
│   └── páginas do sistema
│
├── 📁 includes
│   └── componentes reutilizáveis
│
├── index.php
│
└── README.md
```

> A estrutura pode variar de acordo com a organização atual do projeto.

---

# 🔄 Fluxo da Aplicação

O funcionamento da aplicação pode ser representado da seguinte forma:

```text
Usuário
   │
   ▼
Interface Web
HTML + CSS + JavaScript
   │
   ▼
Back-End
PHP
   │
   ▼
Regras de Negócio
   │
   ▼
Banco de Dados
MySQL
   │
   ▼
Retorno das Informações
   │
   ▼
Interface do Usuário
```

O PHP atua como intermediário entre a interface e a camada de persistência de dados.

---

# 🗄️ Integração com Banco de Dados

O sistema utiliza um banco de dados relacional para armazenamento das informações.

O PHP é responsável pela comunicação entre a aplicação e o banco de dados, permitindo operações como:

```text
CREATE → Cadastro de registros

READ → Consulta de informações

UPDATE → Atualização de dados

DELETE → Exclusão de registros
```

Esse conjunto de operações representa o padrão **CRUD (Create, Read, Update e Delete)**, fundamental no desenvolvimento de sistemas corporativos.

---

# 💻 Como Executar o Projeto

## Pré-requisitos

Antes de executar o projeto, é necessário possuir um ambiente com suporte ao PHP.

Recomenda-se:

* PHP 8+
* MySQL
* Apache
* XAMPP, WAMP ou Laragon
* Navegador Web

---

## 1️⃣ Clone o Repositório

```bash
git clone https://github.com/Daniel010203/Projeto-Sistema-Gestao-Estoque.git
```

---

## 2️⃣ Acesse a Pasta do Projeto

```bash
cd Projeto-Sistema-Gestao-Estoque
```

---

## 3️⃣ Configure o Servidor Local

Caso utilize o **XAMPP**, mova o projeto para:

```text
C:\xampp\htdocs\
```

Exemplo:

```text
C:\xampp\htdocs\Projeto-Sistema-Gestao-Estoque
```

---

## 4️⃣ Inicie os Serviços

No painel do XAMPP, inicie:

```text
Apache
MySQL
```

---

## 5️⃣ Configure o Banco de Dados

Crie o banco de dados utilizando o **phpMyAdmin** ou outro gerenciador MySQL.

Acesse:

```text
http://localhost/phpmyadmin
```

Crie o banco correspondente ao projeto e importe o arquivo SQL, caso esteja disponível no repositório.

---

## 6️⃣ Execute a Aplicação

Acesse pelo navegador:

```text
http://localhost/Projeto-Sistema-Gestao-Estoque
```

---

# 📚 Conceitos Praticados

Este projeto permitiu aplicar conhecimentos importantes relacionados ao desenvolvimento Back-End com PHP.

### Desenvolvimento Web

* Estruturação de aplicações web;
* Comunicação cliente-servidor;
* Processamento de requisições;
* Manipulação de formulários.

### PHP

* Variáveis e estruturas condicionais;
* Funções;
* Processamento de dados;
* Integração com banco de dados;
* Organização da lógica Back-End.

### Banco de Dados

* Modelagem de dados;
* SQL;
* Operações CRUD;
* Persistência de informações;
* Consultas relacionais.

### Engenharia de Software

* Separação de responsabilidades;
* Organização de código;
* Estruturação de funcionalidades;
* Aplicação de regras de negócio.

---

# 🎯 Objetivo Profissional do Projeto

Este projeto foi desenvolvido também como parte da construção de um portfólio técnico voltado para o mercado de tecnologia.

O objetivo é demonstrar conhecimentos práticos em:

* Desenvolvimento Back-End;
* PHP;
* Integração com banco de dados;
* Desenvolvimento de sistemas corporativos;
* Implementação de operações CRUD;
* Organização de aplicações web;
* Aplicação de regras de negócio.

O projeto representa uma aplicação prática de conceitos utilizados no desenvolvimento de sistemas de gestão empresarial.

---

# 🔮 Possíveis Evoluções

Algumas melhorias que podem ser implementadas nas próximas versões:

* [ ] Sistema de autenticação;
* [ ] Controle de usuários e permissões;
* [ ] Dashboard gerencial;
* [ ] Indicadores de estoque;
* [ ] Controle de entrada e saída;
* [ ] Histórico de movimentações;
* [ ] Alertas de estoque mínimo;
* [ ] Relatórios gerenciais;
* [ ] Exportação para Excel e PDF;
* [ ] API REST;
* [ ] Implementação de arquitetura MVC;
* [ ] Dockerização da aplicação;
* [ ] Testes automatizados;
* [ ] Pipeline CI/CD.

---

# 🛠️ Roadmap Técnico

Uma possível evolução arquitetural do projeto seria:

```text
Sistema Atual
      │
      ▼
PHP Estruturado
      │
      ▼
Organização em Camadas
      │
      ▼
Arquitetura MVC
      │
      ▼
API REST
      │
      ▼
Autenticação JWT
      │
      ▼
Docker
      │
      ▼
CI/CD
      │
      ▼
Cloud Deployment
```

Esse roadmap permite transformar progressivamente um projeto acadêmico/prático em uma aplicação com características mais próximas de ambientes corporativos.

---

# 👨‍💻 Autor

**Daniel Vieira**

Profissional em transição para a área de Tecnologia, com foco em:

* Desenvolvimento Back-End;
* Análise de Sistemas;
* Engenharia de Software;
* Banco de Dados;
* Desenvolvimento de APIs;
* Análise de Dados.

### 🔗 GitHub

[Daniel Vieira — GitHub](https://github.com/Daniel010203?utm_source=chatgpt.com)

### 🔗 LinkedIn

[Daniel Vieira — LinkedIn](https://www.linkedin.com/in/danielvieira-dados-analise?utm_source=chatgpt.com)

---

# 📄 Licença

Este projeto foi desenvolvido para fins educacionais e de portfólio profissional.

Sinta-se à vontade para estudar a estrutura do projeto, analisar o código e utilizar os conceitos apresentados como referência para aprendizado.

---

<div align="center">

### 🚀 Desenvolvido com PHP, MySQL, HTML, CSS e JavaScript

**Projeto desenvolvido para fins de aprendizado, prática e evolução profissional no desenvolvimento de sistemas.**

</div>

