<div style="min-height: 50vh;">
    <div style="text-align: center;">
        <img src="https://{$smarty.env.SITE_URL_MAIL}/uploads/pp/{civicpower_hash_db(false, $ballot.ballot_asker_id, SALT_ASKER)}.png" style="border-radius:50%;border:1px solid #999999;width:100%;max-width:150px;"/>
    </div>
    <div style="width:100%;max-width:70%;margin:25px auto;">
        Votre consultation suivante a été acceptée :<br /> {$ballot.ballot_title}<br/>
        Pour la voir, cliquez ici :
        {capture assign=url}{$smarty.env.APP_HOST}/{$ballot.ballot_shortcode}{/capture}
        <a style="font-weight:bold;text-decoration:none;color:#3b53b8;" href="{$url}" target="_blank">{$url}</a>
    </div>
</div>