La consultation suivante de {$ballot.asker_name} doit être validée :
<br />
<br />
{$ballot.ballot_title}
<br/>
<br/>
{capture assign=url}https://{$smarty.env.SITE_URL}/ballot?ballot_id={$ballot.ballot_id}{/capture}
Pour la voir, <a style="font-weight:bold;text-decoration:none;color:#3b53b8;" href="{$url}" target="_blank">cliquez ici</a>
