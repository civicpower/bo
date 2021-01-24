Bonjour,
<br>
<br>
{$ballot.asker_name} vous invite à participer à la consultation suivante via l'application Civicpower :
<br>
<br>
{$ballot.ballot_title}
<br>
<br>
Plus que 24 heures pour participer !
<br>
<br>

{capture assign=ballot_url}{$smarty.env.APP_HOST}/{cp_mail_shortcode($to,"/{$ballot.ballot_shortcode}",$ballot.ballot_id)}{/capture}

{include file="mail/btn.tpl" btn_url=$ballot_url btn_lib="Votez !"}

