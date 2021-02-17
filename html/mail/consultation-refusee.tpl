Bonjour
<br>
<br>
Vous nous avez soumis la consultation suivante :
<br>
<br>
{$ballot.ballot_title}
<br>
<br>
Nous vous informons qu'elle n'a pas été validée par notre équipe de modération pour le motif suivant :<br>

<strong>{$ballot.ballot_rejection_reason}</strong><br><br>

Nous vous invitons à vous connecter sur votre compte pour pouvoir modifier votre consultation le cas échéant.

<br>
<br>
L'équipe Civicpower
<br>
<br>

{capture assign=url}https://{$smarty.env.SITE_URL}/ballot?ballot_id={$ballot.ballot_id}{/capture}
{include file="mail/btn.tpl" btn_url=$url btn_lib="Accédez à votre compte"}
