{*
<div style="width:100%;max-width:70%;margin:25px auto;">
    Votre consultation suivante a été acceptée :<br/> {$ballot.ballot_title}<br/>
    Pour la voir, cliquez ici :
    {capture assign=url}{$smarty.env.APP_HOST}/{$ballot.ballot_shortcode}{/capture}
    <a style="font-weight:bold;text-decoration:none;color:#3b53b8;" href="{$url}" target="_blank">{$url}</a>
</div>
*}


Bonjour,<br>
<br>
Vous nous avez soumis la consultation suivante :<br>
<br>
<strong>{$ballot.ballot_title}</strong><br>
<br>
Nous vous confirmons qu'elle a été validée par notre équipe de modération :
si vous avez renseigné la liste des votants, une invitation leur sera envoyée à l'ouverture du scrutin ; si votre consultation est publique, l'url à transmettre à votre communauté de votants est disponible sur votre compte.
<br>
<br>
L'équipe Civicpower
<br>
<br>


{capture assign=url}{$smarty.env.APP_HOST}/{$ballot.ballot_shortcode}{/capture}
{include file="mail/btn.tpl" btn_url=$url btn_lib="Accédez à votre compte"}
