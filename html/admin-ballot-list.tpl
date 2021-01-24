{seo_title}{t}Administrer les consultations{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}


<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="h1 card-title">{$seo_title}</h2>
            </div>
            <div class="card-body">
                <form method="get" action="/admin-ballot-list">
                    <div class="row">
                        <div class="col-sm-4">
                            <!-- text input -->
                            <div class="form-group">
                                <label for="input_search">Recherche globale</label>
                                <input type="text"  value="{$smarty.request.search|for_input}" class="form-control" placeholder="Recherche ..." name="search" id="input_search">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <!-- select -->
                            <div class="form-group">
                                <label for="input_bstatus_id">Statut de modération</label>
                                <select name="bstatus_id" class="form-control">
                                    <option value="">...</option>
                                    {foreach from=$bstatus_list item=item key=key}
                                        <option {if $smarty.request.bstatus_id eq $item.bstatus_id}selected="selected"{/if} value="{$item.bstatus_id}">{$item.bstatus_lib}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 text-left">
                            <div class="form-group text-left">
                                <label for="input_ballot_active">Afficher les consultations supprimées</label>
                                <input type="checkbox" {if request_exists("ballot_active")}checked="checked"{/if}  class="form-control" name="ballot_active" id="input_ballot_active">
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
                        <th>Consultation</th>
                        <th>Dates</th>
                        <th>Statut</th>
                        <th>Résultats</th>
                        <th>Url de partage</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$ballot_list item=item key=key}
                        <tr class="ballot_active_{$item.ballot_active}">
                            <td>
                                <a href="/ballot?ballot_id={$item.ballot_id|intval}" class="btn btn-sm btn-dark bg-cp-blue"><i class="fa fa-eye"></i></a>
                            </td>
                            <td>
                                {$item.asker_name}{if $item.user_firstname|@strlen gt 0 || $item.user_lastname|@strlen gt 0} / <span class="badge badge-secondary">{$item.user_firstname} {$item.user_lastname}</span>{/if}<br/>
                                <a href="/ballot?ballot_id={$item.ballot_id|intval}">{$item.ballot_title}</a>
                            </td>
                            <td>{$item.ballot_start_fr}<br/>{$item.ballot_end_fr}</td>
                            <td><span class="t2 badge badge-dark bg-cp-black">{$item|cp_ballot_status}</span></td>
                            <td><a class="btn btn-sm btn-dark btn-cp-blue" href="/ballot?ballot_id={$item.ballot_id|intval}">{$item.participation} participants</a></td>
                            <td>
                                <a target="_blank" href="{$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}" class="btn-shortcode-url btn btn-xs btn-light">
                                    {$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}
                                    <span class="alert alert-light shortcode-url-copied">URL copiée</span>
                                </a>
                                <button class="btn-copy-shortcode btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="Copier dans le Presse-papier"><i class="fa fa-copy"></i></button>
                                <input class="input-shortcode" type="text" value="{$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}"/>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

