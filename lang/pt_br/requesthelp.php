<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * requesthelp.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['actions'] = 'Ações';
$string['addinformation'] = 'Adicionar informação';
$string['additionalinformation'] = 'Informação adicional';
$string['allstatuses'] = 'Todos os status';
$string['allsubjects'] = 'Todos os assuntos';
$string['askforhelp'] = 'Preciso de ajuda';
$string['assignedto'] = 'Atendido por';
$string['autorefresh'] = 'A fila é atualizada automaticamente';
$string['claim'] = 'Assumir atendimento';
$string['conversation'] = 'Conversa';
$string['created'] = 'Criado em';
$string['defaultsubjects'] = 'Conteúdo da aula
Exercício
Atividade
Dúvida técnica
Outro';
$string['description'] = 'Descreva sua dúvida ou problema';
$string['descriptionrequired'] = 'Descreva a dúvida ou o problema.';
$string['eventrequestanswered'] = 'Pedido de ajuda respondido';
$string['eventrequestcreated'] = 'Pedido de ajuda criado';
$string['eventrequestresolved'] = 'Pedido de ajuda resolvido';
$string['filter'] = 'Filtrar';
$string['invalidsubject'] = 'O assunto selecionado não está disponível nesta atividade.';
$string['markanswered'] = 'Respondido em sala';
$string['messagerequired'] = 'Escreva uma mensagem antes de enviar.';
$string['modulename'] = 'Preciso de ajuda';
$string['modulename_help'] = 'Os alunos enviam dúvidas para uma fila centralizada que o professor pode responder no Moodle ou diretamente em sala de aula.';
$string['modulenameplural'] = 'Atividades Preciso de ajuda';
$string['myrequests'] = 'Minhas dúvidas';
$string['nonewmodules'] = 'Não há atividades Preciso de ajuda neste curso.';
$string['norequests'] = 'Não há dúvidas nesta fila.';
$string['noyourrequests'] = 'Você ainda não pediu ajuda nesta atividade.';
$string['openrequests'] = 'Dúvidas abertas';
$string['pluginadministration'] = 'Administração de Preciso de ajuda';
$string['pluginname'] = 'Preciso de ajuda';
$string['privacy:metadata:requesthelp_messages'] = 'Armazena as mensagens adicionadas aos pedidos de ajuda.';
$string['privacy:metadata:requesthelp_messages:message'] = 'O conteúdo da mensagem.';
$string['privacy:metadata:requesthelp_messages:timecreated'] = 'Quando a mensagem foi criada.';
$string['privacy:metadata:requesthelp_messages:userid'] = 'O autor da mensagem.';
$string['privacy:metadata:requesthelp_requests'] = 'Armazena os pedidos de ajuda enviados pelos alunos.';
$string['privacy:metadata:requesthelp_requests:assignedto'] = 'O professor responsável pelo atendimento, quando houver.';
$string['privacy:metadata:requesthelp_requests:description'] = 'A dúvida ou o problema descrito pelo aluno.';
$string['privacy:metadata:requesthelp_requests:subject'] = 'O assunto selecionado.';
$string['privacy:metadata:requesthelp_requests:timecreated'] = 'Quando o pedido foi enviado.';
$string['privacy:metadata:requesthelp_requests:userid'] = 'O usuário que enviou o pedido de ajuda.';
$string['queue'] = 'Fila de dúvidas';
$string['refreshinterval'] = 'Atualização automática da fila';
$string['refreshinterval_help'] = 'Define de quanto em quanto tempo a fila do professor será atualizada enquanto a página estiver aberta. Escolha Nunca para desativar.';
$string['reopen'] = 'Reabrir';
$string['requestcreated'] = 'Sua dúvida foi adicionada à fila.';
$string['requesthelp:addinstance'] = 'Adicionar uma nova atividade Preciso de ajuda';
$string['requesthelp:manage'] = 'Gerenciar e responder pedidos de ajuda';
$string['requesthelp:submit'] = 'Enviar pedidos de ajuda';
$string['requesthelp:view'] = 'Visualizar atividades Preciso de ajuda';
$string['requesthelpname'] = 'Nome da atividade';
$string['requestsettings'] = 'Configurações da fila de ajuda';
$string['requestsummary'] = 'Dúvida #{$a}';
$string['resolve'] = 'Resolver';
$string['respond'] = 'Responder';
$string['response'] = 'Resposta';
$string['responsetime'] = 'Tempo até a primeira resposta';
$string['seconds'] = '{$a} segundos';
$string['send'] = 'Enviar';
$string['sendrequest'] = 'Enviar pedido de ajuda';
$string['status'] = 'Status';
$string['statusanswered'] = 'Respondido';
$string['statusopen'] = 'Aberto';
$string['statusresolved'] = 'Resolvido';
$string['student'] = 'Aluno';
$string['studentcanreopen'] = 'Permitir que o aluno reabra a própria dúvida resolvida';
$string['studentcanresolve'] = 'Permitir que o aluno marque a própria dúvida como resolvida';
$string['studentreply'] = 'Aluno';
$string['subject'] = 'Assunto';
$string['subjects'] = 'Assuntos';
$string['subjects_help'] = 'Informe um assunto por linha. O aluno deverá escolher um deles ao pedir ajuda.';
$string['teacherreply'] = 'Professor';
$string['totalanswered'] = 'Respondidas';
$string['totalopen'] = 'Abertas';
$string['totalresolved'] = 'Resolvidas';
$string['viewrequest'] = 'Ver dúvida';
$string['waiting'] = 'Aguardando';
$string['waitingtime'] = 'Tempo de resposta';
