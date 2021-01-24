{seo_title}{t}Admin. utilisateurs{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}


<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                <form method="get" action="/admin-user-list">
                    <div class="row">
                        <div class="col-sm-4">
                            <!-- text input -->
                            <div class="form-group">
                                <label for="input_search">Recherche globale</label>
                                <input type="text" value="{$smarty.request.search|for_input}" class="form-control" placeholder="Recherche ..." name="search" id="input_search">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for=""></label>
                            <button type="submit" class="btn btn-lg btn-primary">RECHERCHER &nbsp; <i class="fas fa-search"></i></button>
                        </div>

                    </div>
                </form>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                    <tr>
                        <th></th>
                        <th>User</th>
                        <th>Organisateurs</th>
                        <th>Consultations</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$user_list item=item key=key}
                        <tr class="">
                            <td><a {snippet_iframe_modal()} href="/admin-user.php?user_id={$item.user_id}&iframe=ok" class="btn btn-sm btn-dark bg-cp-blue"><i class="fa fa-eye"></i></a></td>
                            <td>
                                {if $item.user_is_admin}<span class="badge badge-warning"><i class="fas fa-crown"></i> ADMINISTRATEUR</span><br/>{/if}
                                {if $item.user_firstname|@strlen gt 0 ||  $item.user_lastname|@strlen gt 0}{$item.user_firstname} {$item.user_lastname}<br/>{/if}
                                {if $item.user_email|@strlen gt 0}{$item.user_email}<br/>{/if}
                                {if $item.user_phone_international|@strlen gt 0}{$item.user_phone_international}<br/>{/if}
                                {if $item.nom_commune|@strlen gt 0}{$item.code_postal} {$item.nom_commune}<br/>{/if}
                                <span class="text-sm">Créé le : {$item.user_creation|project_date_fr}</span><br/>
                            </td>
                            <td>
                                {foreach from=$item.asker_list item=item2 key=key2}
                                    <a {snippet_iframe_modal()} href="/admin-asker.php?asker_id={$item2.asker_id}&iframe=ok">{$item2.asker_name}</a>
                                    <br />
                                {/foreach}
                            </td>
                            <td>
                                {foreach from=$item.ballot_list item=item2 key=key2}
                                    <a {snippet_iframe_modal()} href="/ballot.php?ballot_id={$item2.ballot_id}&iframe=ok">{$item2.ballot_title}</a>
                                    <span class="text-sm text-dark">{$item2.asker_name}</span><br />
                                {/foreach}
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

