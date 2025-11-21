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

 # para o campo Status, coluna que deverá ser criada no frontend baseado nas condições, teremos as seguintes regras:
	- Em Processamento (badge roxo): Quando possui fscs, type == "TCMS";
	- Pendente (badge laranja): Quando possui fscs, type == "CRED" e não possuí data de concessão definida;
	- Negada (badge cinza): Quando o fscs for == "00000";
	- Ativa (badge verde):  Quando possui fscs, type == "CRED" e possuí data de concessão definida;
	- Vencida (badge vermelha): Quando data vencimento < hoje();