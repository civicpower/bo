{seo_title}{t}Mes profils organisateur{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

<div class="row">
    <div class="col-6">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h2 class="h1 card-title">{$seo_title}</h2>

                <div class="card-tools">
                    <a href="/asker" class="btn btn-sm btn-success">
                        <i class="fa fa-plus-square"></i>
                        Nouveau profil organisateur
                    </a>
                </div>
            </div>
            <div class="table-responsive card-body p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Nom de l'organisateur</th>
                        <th>Consultation</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$asker_list item=item key=key}
                        <tr>
                            <td><a href="/asker?asker_id={$item.asker_id|intval}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a></td>
                            <td><a href="/asker?asker_id={$item.asker_id|intval}">{$item.asker_name}</a></td>
                            <td>{$item.nb_ballot}</td>
                            <td>
                                <button data-asker_id="{$item.asker_id|intval}" class="btn-remove btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

