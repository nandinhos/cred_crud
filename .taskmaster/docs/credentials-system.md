## REGRA DE NEGÓCIO

- todos os campos são opcionais, pois serão feitos critérios de status baseado no preenchimento dos campos;
- as credenciais devem ser vinculadas a um único usuário que exista no sistema;
- possuimos super admin, admin e consulta;
- o super admin (militar desenvolvedor)tem acesso total e irrestrito ao sistema, mas também tem acesso a tela de consulta de sua própria credencial, que será a página inicial do sistema;
- o admin (militares do setor de inteligência)podem visualizar, editar e excluir credenciais, não terá acesso e não verá a tela de usuários (nem visualizar a sidebar);
- o consulta (os demais militares de todos os escritórios)pode visualizar somente sua tela para fins de consulta de número de sua própria credencial (Página para mostrar crendencial por id de usuário) e terá acesso a uma página para anexar documentos quando solicitada a renovação, que só estará disponível, faltando 30 dias para o vencimento (não terá acesso a tela de credenciais nem a tela de usuários e nem visualizar a sidebar);
- possuímos a coluna chamada "type" para que ela mostre o tipo de documento que será criado no final do processo... ou CRED (Credencial de Segurança) ou TCMS (Termo de Compromisso e Manutenção de Sigilo);
- possuímos a coluna "rank", que mostra o posto/gradução do militar pertencente a unidade militar;
- a Data de Concessão e a Data de Validade são campos opcionais;
- a Data de Validade pra CRED (Cre de Segurança) é um campo calculado automaticamente, que são de 2 anos a partir da data de concessão;
- a Data de Validade pra TCMS (Termo de Compromisso e Manutenção de Sigilo) é um campo calculado automaticamente, que será até o final do corrente ano da data de concessão, ou seja, se for 2025, a validade será 31/12/2025.;
- o campo Sigilo deve ser obrigatório e somente R (Reservado) ou S (Secreto)

- o campo user_id é obrigatório e deve ser vinculado a um único usuário que exista no sistema;
- o campo type é obrigatório e deve ser CRED ou TCMS;
- o Sigilo pode ser somente R (Reservado) ou S (Secreto);
- o Status pode ser somente Em Processamento (badge roxo), Pendente (badge laranja), Negada (badge cinza), Ativa (badge verde) ou Vencida (badge vermelha);

 # para o campo Status, coluna que deverá ser criada no frontend baseado nas condições, teremos as seguintes regras:
	- Em Processamento (badge roxo): Quando possui fscs, type == "TCMS";
	- Pendente (badge laranja): Quando possui fscs, type == "CRED" e não possuí data de concessão definida;
	- Negada (badge cinza): Quando o fscs for == "00000";
	- Ativa (badge verde):  Quando possui fscs, type == "CRED" e possuí data de concessão definida;
	- Vencida (badge vermelha): Quando data vencimento < hoje();