- Sistema de gestão de credenciais de segurança de militares da aeronautica
- Sistema deve ter as seguintes funcionalidades:
    - Cadastrar credenciais
    - Listar credenciais
    - Editar credenciais
    - Deletar credenciais
    - Buscar credenciais

- Sistema deve ter as seguintes tabelas:

    - credentials (
id
user_id
fscs //Número da ficha de solicitação de credencial de segurança
name //Nome do militar
secrecy //Grau de Sigilo (Reservado ou Secreto)
credential //número da credencial de segurança
concession //data de concessão
validity //data de validade
created_at
updated_at
deleted_at
    )

    - users (
        id
        name
        email
        password
        role_id
        created_at
        updated_at
        deleted_at
    )
    - roles (
        id
        name
        guard_name
        created_at
        updated_at
        deleted_at
    )
    - permissions (
        id
        name
        guard_name
        created_at
        updated_at
        deleted_at
    )
    - role_has_permissions (
        role_id
        permission_id
    )
    - user_has_roles (
        user_id
        role_id
    )

