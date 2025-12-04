## REGRA DE NEGÓCIO

### Regras Gerais
- todos os campos são opcionais, pois serão feitos critérios de status baseado no preenchimento dos campos;
- as credenciais devem ser vinculadas a um único usuário que exista no sistema;
- **CADA USUÁRIO PODE TER APENAS UMA CREDENCIAL ATIVA POR VEZ**;
- credenciais antigas ficam no histórico (soft delete) para consulta posterior;
- possuimos super admin, admin e consulta;

### Perfis de Acesso
- o **super admin** (militar desenvolvedor) tem acesso total e irrestrito ao sistema, mas também tem acesso a tela de consulta de sua própria credencial, que será a página inicial do sistema;
- o **admin** (militares do setor de inteligência) podem visualizar, editar e excluir credenciais, não terá acesso e não verá a tela de usuários (nem visualizar a sidebar);
- o **consulta** (os demais militares de todos os escritórios) pode visualizar somente sua tela para fins de consulta de número de sua própria credencial (Página para mostrar credencial por id de usuário) e terá acesso a uma página para anexar documentos quando solicitada a renovação, que só estará disponível, faltando 30 dias para o vencimento (não terá acesso a tela de credenciais nem a tela de usuários e nem visualizar a sidebar);

### Tipos de Documentos
- possuímos a coluna chamada "type" para que ela mostre o tipo de documento que será criado no final do processo:
  - **CRED** (Credencial de Segurança): Para acesso a informações classificadas
  - **TCMS** (Termo de Compromisso e Manutenção de Sigilo): Para compromisso de sigilo

### Níveis de Sigilo
- **CREDENCIAIS DE SEGURANÇA (CRED)** podem ter:
  - **R** (Reservado) - Para informações reservadas
  - **S** (Secreto) - Para informações secretas
- **TERMOS DE COMPROMISSO (TCMS)** devem ter:
  - **AR** (Acesso Restrito) - Para documentos administrativos

### Campos e Validações
- possuímos a coluna "rank", que mostra o posto/graduação do militar pertencente a unidade militar;
- a Data de Concessão e a Data de Validade são campos opcionais;
- a Data de Validade pra **CRED** (Credencial de Segurança) é um campo calculado automaticamente, que são de **2 anos** a partir da data de concessão;
- a Data de Validade pra **TCMS** (Termo de Compromisso e Manutenção de Sigilo) é um campo calculado automaticamente, que será até o **final do corrente ano** da data de concessão, ou seja, se for 2025, a validade será 31/12/2025;
- o campo Sigilo deve ser obrigatório:
  - Para CRED: R (Reservado) ou S (Secreto)
  - Para TCMS: AR (Acesso Restrito)

### Campos Obrigatórios
- o campo user_id é obrigatório e deve ser vinculado a um único usuário que exista no sistema;
- o campo type é obrigatório e deve ser CRED ou TCMS;
- o Sigilo é obrigatório e deve seguir a regra do tipo de documento;
- o Status pode ser somente Em Processamento (badge roxo), Pendente (badge laranja), Negada (badge cinza), Ativa (badge verde) ou Vencida (badge vermelha);

### Histórico de Credenciais
- Quando um usuário recebe uma nova credencial, a anterior deve ser movida para o histórico (soft delete);
- O histórico permite consultar todas as credenciais que um militar já teve, incluindo períodos de vigência;
- Apenas a credencial mais recente (não deletada) fica ativa no sistema;

### Regras de Status (Calculadas Dinamicamente)

O status é calculado automaticamente no Model baseado nas seguintes condições **(ordem de prioridade)**:

#### 1. **NEGADA** (badge cinza - `secondary`)
- **Condição:** `fscs = "00000"`
- **Descrição:** Credencial foi negada pelo Centro de Inteligência
- **Observação:** FSCS "00000" é considerado como "não existe" em todas as outras regras

#### 2. **VENCIDA** (badge vermelho - `danger`)
- **Condição:** `validity < hoje()`
- **Descrição:** Data de validade já passou
- **Aplica-se a:** CRED e TCMS

#### 3. **TCMS VÁLIDA - Documento de Sigilo** (badge verde - `success`)
- **Condição:** 
  - `fscs = null` (sem FSCS)
  - `type = TCMS`
  - `credential` contém "TCMS" (case insensitive)
- **Descrição:** Documento de sigilo que não requer FSCS para ser válido

#### 4. **EM PROCESSAMENTO** (badge azul - `primary`)
- **Condição:**
  - `fscs` existe (não null e diferente de "00000")
  - `type = TCMS`
  - **`concession` existe** (data de concessão do termo)
- **Descrição:** TCMS com FSCS válido e termo já concedido, aguardando aprovação final
- **IMPORTANTE:** TCMS sem concessão é considerado PANE

#### 5. **PENDENTE** (badge amarelo - `warning`)
- **Condição:**
  - `fscs` existe (não null e diferente de "00000")
  - `type = CRED`
  - `concession = null` (sem data de concessão)
- **Descrição:** CRED com FSCS aprovado aguardando data de concessão

#### 6. **VÁLIDA** (badge verde - `success`)
- **Condição:**
  - `fscs` existe (não null e diferente de "00000")
  - `type = CRED`
  - `concession` existe (com data de concessão)
- **Descrição:** Credencial ativa e válida

#### 7. **PANE - VERIFICAR** (badge vermelho - `danger`)
- **Condição:** Qualquer outro caso que não se encaixe nas regras acima
- **Exemplos de casos PANE:**
  - TCMS sem FSCS e sem "TCMS" no número da credencial
  - CRED sem FSCS
  - **TCMS com FSCS mas sem data de concessão** (termo nunca foi assinado)
- **Descrição:** Inconsistência que precisa ser verificada e corrigida

### Ordenação na Tabela

As credenciais são exibidas na seguinte ordem de prioridade:

```
PRIORIDADE 0: PANE - VERIFICAR (sempre primeiro)
    ├─ TCMS sem FSCS e sem "TCMS" no credential
    ├─ CRED sem FSCS
    └─ TCMS com FSCS mas SEM concessão

PRIORIDADE 1: EM PROCESSAMENTO
    └─ TCMS com FSCS e COM concessão (ordenados por data de concessão)

PRIORIDADE 2: DEMAIS CREDENCIAIS
    └─ Ordenadas por data de validade (mais urgentes primeiro)

PRIORIDADE 3: NEGADAS (sempre por último)
    └─ FSCS = "00000"
```

### Cores de Fundo (Gradiente de Vencimento)

Para credenciais válidas, a cor de fundo da linha varia conforme proximidade do vencimento:

- **Vencida:** `bg-red-100` (vermelho claro)
- **1-15 dias:** `bg-orange-200` (laranja forte - CRÍTICO)
- **16-30 dias:** `bg-orange-100` (laranja médio - ATENÇÃO)
- **31-45 dias:** `bg-yellow-200` (amarelo forte - ALERTA)
- **46-60 dias:** `bg-yellow-100` (amarelo médio - Início gradiente)
- **> 60 dias:** Sem cor especial (Normal)
- **PANE:** `bg-red-200` com borda vermelha esquerda
- **Negada:** `bg-gray-200`
- **Pendente:** `bg-indigo-100`

### Constraints de Banco de Dados

- **`credential` (número da credencial):** UNIQUE - Não pode haver números repetidos
- **`fscs`:** NÃO é unique - Múltiplas credenciais podem ter FSCS "00000" (negadas)
- **`user_id`:** NOT NULL - Toda credencial deve estar vinculada a um usuário
- **Regra de negócio:** Um usuário pode ter apenas UMA credencial ativa (não deletada)