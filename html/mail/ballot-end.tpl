Bonjour,
<br>
<br>
{$ballot.asker_name} vous a invité à participer à la consultation suivante via l'application Civicpower :
<br>
<br>
{$ballot.ballot_title}
<br>
<br>
Ce scrutin est maintenant terminé, vous pouvez consulter les résultats sur votre compte Civicpower.<br>
<br>
<br>

{capture assign=ballot_url}{$smarty.env.APP_HOST}/{cp_mail_shortcode($to,"/r/{$ballot.ballot_shortcode}",$ballot.ballot_id)}{/capture}
{include file="mail/btn.tpl" btn_url=$ballot_url btn_lib="Voir les résultats"}
