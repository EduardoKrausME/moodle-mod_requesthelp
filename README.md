# mod_requesthelp - Preciso de ajuda

Atividade Moodle para organizar dúvidas rápidas em uma fila centralizada, especialmente durante aulas presenciais, síncronas ou laboratórios.

## Fluxo

1. O professor cria a atividade e define os assuntos disponíveis.
2. O aluno escolhe um assunto e descreve a dúvida.
3. A dúvida entra na fila do professor como **Aberta**.
4. O professor pode assumir o atendimento, escrever uma resposta ou clicar em **Respondido em sala**.
5. A atividade registra o tempo até a primeira resposta.
6. Professor ou aluno, conforme a configuração, pode marcar a dúvida como **Resolvida** ou reabri-la.

## Recursos

- fila centralizada por atividade;
- assuntos configuráveis, um por linha;
- status Aberto, Respondido e Resolvido;
- atualização automática da fila em 5, 10, 15 ou 30 segundos;
- ação “Respondido em sala”, sem obrigar o professor a digitar uma resposta;
- histórico de mensagens quando a resposta precisa ficar registrada no Moodle;
- professor pode assumir um atendimento;
- tempo até a primeira resposta;
- filtros por status e assunto;
- visão individual do aluno com suas próprias dúvidas;
- eventos Moodle para criação, resposta e resolução;
- Privacy API;
- backup e restore Moodle 2;
- interface responsiva e sem renderer customizado.

## Compatibilidade

- Moodle 4.5 ou superior.
- PHP compatível com a versão do Moodle instalada.

## Instalação

Copie a pasta `requesthelp` para `mod/requesthelp` e execute a atualização do Moodle em Administração do site > Notificações.
