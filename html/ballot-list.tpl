{seo_title}{t}Mes consultations{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

<div class="row">
    <div class="col-12">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="h1 card-title">{$seo_title}</h2>

                <div class="card-tools d-none d-sm-inline-block">
                    <a href="/ballot" class="btn btn-sm btn-danger bg-cp-red"><i class="fa fa-plus-square"></i> Nouvelle consultation</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Consultation</th>
                        <th>Dates</th>
                        <th>Statut</th>
{*                        <th>Nombre de questions</th>*}
                        <th>Résultats</th>
                        <th>Url de partage</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$ballot_list item=item key=key}
                        <tr>
                            <td><a href="/ballot?ballot_id={$item.ballot_id|intval}" class="btn btn-sm btn-dark bg-cp-blue"><i class="fa fa-edit"></i></a></td>
                            <td>{$item.asker_name}<br /><a href="/ballot?ballot_id={$item.ballot_id|intval}">{$item.ballot_title}</a></td>
                            <td>{$item.ballot_start_fr}<br />{$item.ballot_end_fr}</td>
                            <td><span class="badge badge-dark bg-cp-black">{$item|cp_ballot_status}</span></td>
{*                            <td>{$item.nb_question}</td>*}
                            <td><a class="btn btn-sm btn-dark btn-cp-blue" href="/ballot?ballot_id={$item.ballot_id|intval}">{$item.participation} participants</a></td>
                            <td>
                                <a target="_blank" href="{$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}" class="btn-shortcode-url btn btn-xs btn-light">
                                    {$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}
                                    <span class="alert alert-light shortcode-url-copied">URL copiée</span>
                                </a>
                                <button class="btn-copy-shortcode btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="Copier dans le Presse-papier"><i class="fa fa-copy"></i></button>
                                <input class="input-shortcode" type="text" value="{$smarty.env.APP_HOST}/{$item.ballot_shortcode|for_html}"/>
                            </td>
                            <td>
                                <button data-ballot_id="{$item.ballot_id|intval}" class="btn-remove btn btn-sm btn-dark bg-cp-black"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

