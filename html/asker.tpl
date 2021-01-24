{seo_title}Profil organisateur{if isset($asker.asker_name)} - {$asker.asker_name}{/if}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

{if $mode_update && !isset($asker.asker_id)}
    <div class="alert alert-danger">
        Vous n'avez pas accès à cette page !
        <a class="  " href="/">Cliquez ici pour revenir à votre tableau de bord</a>
    </div>
{else}
    {assign var="disabled" value=""}
    {if $nb_active_ballots gt 0 && $mode_update}
        <div class="alert alert-danger">
            Vous avez des consultations actives. Veuillez attendre qu'elles se terminent pour pouvoir modifier vos informations
        </div>
        {assign var="disabled" value="disabled=\"disabled\""}
    {/if}
    <div class="row">
        <input type="hidden" id="asker_id" name="asker_id" value="{$asker.asker_id}"/>
        {if isset($asker.asker_id) && is_numeric($asker.asker_id) && $asker.asker_id>0}
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">{$asker.asker_name}</h3>
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="/uploads/pp/{cp_asker_token($asker.asker_id)}.png?rand={rand(0,99999)}_{rand(0,99999)}" alt="{$asker.asker_name}"/>
                        </div>
                        <div class="text-center mt-2 mb-4">
                            <div id="btn-import-file" class="btn btn-file btn-light">
                                Modifier la photo
                                <input {$disabled} type="file" id="pp-file"/>
                            </div>
                        </div>
                        <small class="text-muted">
                            Cette photo est visible par les votants : elle est publique et liée au profil organisateur.
                            Elle ne constitue pas une donnée personnelle de l’utilisateur, dont l’identité n’est pas divulguée.
                        </small>
                    </div>
                </div>
            </div>
        {/if}
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="/asker.php" id="form_profile" class="form-horizontal">
                        <input type="hidden" name="action" value="save_asker"/>
                        <input type="hidden" name="asker_id" value="{$smarty.request.asker_id|intval}"/>
                        <div class="form-group row">
                            <label for="asker_name" class="col-sm-2 col-form-label">Libellé du profil</label>
                            <div class="col-sm-10">
                                <input {$disabled} type="text" value="{$asker.asker_name|for_input}" class="form-control" name="asker_name" id="asker_name" placeholder="Nom public auprès des votants"/>
                            </div>
                            <small class="text-muted col-sm-10 offset-sm-2">
                                Pour une Mairie ou une Association, inclure l’article défini : “La Mairie de XXX”, “L’association YYY”.<br />
                                Ce libellé est public et lié au profil organisateur. Il ne constitue pas une donnée personnelle de l’utilisateur, dont l’identité n’est pas divulguée.
                            </small>
                        </div>
                        <div class="form-group row">
                            <div class="offset-sm-2 col-sm-10">
                                <button {$disabled} type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal_account_error" tabindex="-1" aria-labelledby="modal_label_1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-danger">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal_label_1">Une erreur est survenue !</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul id="modal_account_error_list">

                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{t}Fermer{/t}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

{/if}
