{assign var="ballot_mode" value="creation"}
{if $ballot|is_array && isset($ballot.ballot_id)}
    {assign var="ballot_mode" value="update"}
{/if}
{seo_title}{if $ballot_mode eq "update"}{$ballot.ballot_title}{else}{t}Nouvelle consultation{/t}{/if}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

{assign var="can_edit" value=true}
{if isset($ballot.ballot_id) && isset($ballot.ballot_bstatus_id)}
    {assign var="can_edit" value=false}
    {if in_array($ballot.ballot_bstatus_id,cp_editable_status_list())}
        {assign var="can_edit" value=true}
    {/if}
{/if}
{if $user.user_is_admin}
    {assign var="can_edit" value=true}
{/if}

{if $can_edit}
    {assign var="input_disabled" value=""}
{else}
    {assign var="input_disabled" value="disabled=\"disabled\""}
{/if}
{if $user.user_is_admin}
    {assign var="input_disabled" value=""}
{/if}


<input type="hidden" id="zad" value="{$user.user_is_admin}"/>
{if false}
    {if $user.user_is_admin}
        <select class="form-control">
            <option value="">...</option>
            {foreach from=$bal_status item=item key=key}
                <option {if $item.bstatus_id eq $ballot.ballot_bstatus_id}selected="selected"{/if} value="{$item.bstatus_id}">{$item.bstatus_lib}</option>
            {/foreach}
        </select>
    {/if}
{/if}
{if $ballot.ballot_bstatus_id eq $smarty.env.STATUS_BALLOT_EN_COURS_DE_CREATION}
    {if $user.user_is_admin}
        <div class="alert alert-default-info">
            <span class="badge badge-warning"><i class="fas fa-crown"></i> ADMIN</span>
            Publication en cours de création
        </div>
    {/if}
    {if $user.user_id eq $ballot.asker_user_id}
        <div class="alert alert-default-info">
            Votre consultation est en cours de création.
            Pour la publier, cliquez ici : &nbsp;
            <button type="button" class="btn btn-danger btn-sm bg-cp-red" id="btn-open-publish">Publier ma consultation</button>
        </div>
    {/if}
{/if}
{if $ballot.ballot_bstatus_id eq $smarty.env.STATUS_BALLOT_EN_ATTENTE_DE_VALIDATION}
    {if $user.user_is_admin}
        <div class="alert alert-default-warning">
            <span class="badge badge-warning"><i class="fas fa-crown"></i> ADMIN</span>
            Publication en attente de validation
            <button class="btn btn-success btn-sm" id="btn-admin-validate">Accepter la consultation</button>
            <button class="btn btn-danger btn-sm" id="btn-admin-reject">Refuser la consultation ...</button>
        </div>
    {/if}
    {if $user.user_id eq $ballot.asker_user_id}
        <div class="alert alert-default-warning">
            Votre consultation est en attente de validation.
            <button class="btn btn-primary btn-sm" id="btn-edit">Modifier la consultation</button>
        </div>
    {/if}
{/if}
{if $ballot.ballot_bstatus_id >= $smarty.env.STATUS_BALLOT_VALIDE_EN_ATTENTE}
    {if $user.user_is_admin}
        <div class="alert alert-default-success">
            <span class="badge badge-warning"><i class="fas fa-crown"></i> ADMIN</span>
            Publication validée
            &nbsp;
            {*<button class="btn btn-danger btn-sm" id="btn-admin-reject">Refuser la consultation ...</button>*}
        </div>
    {/if}
    {if $user.user_id eq $ballot.asker_user_id}
        <div class="alert alert-default-success">
            Votre consultation est validée. Il n'est désormais plus possible de la modifier.
        </div>
    {/if}
    {if $ballot.ballot_acceptation_reason|@strlen gt 0}
        <div>
            <div class="alert alert-default-info">
                <label>Commentaire du modérateur</label> :
                {$ballot.ballot_acceptation_reason|for_html|nl2br}
            </div>
        </div>
    {/if}
{/if}
{if $ballot.ballot_bstatus_id eq $smarty.env.STATUS_BALLOT_REFUSE}
    {if $user.user_is_admin}
        <div class="alert alert-default-warning">
            <span class="badge badge-warning"><i class="fas fa-crown"></i> ADMIN</span>
            Publication refusée.
            &nbsp;
            <button class="btn btn-success btn-sm" id="btn-admin-validate">Accepter la consultation</button>
        </div>
    {/if}
    {if $user.user_id eq $ballot.asker_user_id}
        <div class="alert alert-default-warning">
            Votre consultation est refusée.
            &nbsp;
            <button class="btn btn-primary btn-sm" id="btn-edit">Modifier la consultation</button>
        </div>
    {/if}
    {if $ballot.ballot_rejection_reason|@strlen gt 0}
        <div>
            <label>Motif du refus</label>
            <div class="alert alert-default-danger">{$ballot.ballot_rejection_reason|for_html|nl2br}</div>
        </div>
    {/if}
{/if}


<div class="row">
    <div class="col-md-4 col-12">
        <div class="sticky-top">
            <div class="card card-secondary">
                <div class="card-header  bg-cp-darkgrey">
                    <h3 class="card-title">{t}Infos de la consultation{/t}</h3>
                    {if isset($ballot.ballot_id) && $can_edit}
                        <button type="submit" class="btn-remove-ballot btn btn-xs btn-dark float-right bg-cp-black"><i class="fas fa-trash"></i> Supprimer cette consultation</button>
                    {/if}
                </div>
                <div class="card-body">
                    {if $can_edit}
                    <form id="form_ballot" action="/ballot" method="post">
                        {/if}
                        <input type="hidden" name="action" value="save_ballot"/>
                        <input type="hidden" id="ballot_id" name="ballot_id" value="{if isset($ballot.ballot_id)}{$ballot.ballot_id}{/if}"/>
                        <div class="form-group">
                            <label for="ballot_asker_id">Compte organisateur</label>
                            {if $asker_list|@count eq 0}
                                <a href="/asker" class="btn btn-success btn-block">Créer un profil organisateur</a>
                            {else}
                                <select {$input_disabled} class="form-control form-control-lg" name="ballot_asker_id" id="ballot_asker_id">
                                    <option>Sélectionnez un compte organisateur</option>
                                    {foreach from=$asker_list item=item key=key}
                                        <option {if $item.asker_id eq $ballot.ballot_asker_id}selected="selected"{/if} value="{$item.asker_id}">{$item.asker_name}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                        {if $asker_list|@count gt 0}
                            <div class="form-group">
                                <label for="ballot_title">Titre de la consultation</label>
                                <input {$input_disabled} id="ballot_title" name="ballot_title" value="{$ballot.ballot_title|check_string|for_input}" class="form-control form-control-lg" type="text" placeholder="{t}Titre de la consultation{/t}"/>
                            </div>
                            <div class="form-group">
                                <label for="ballot_description">Contexte (facultatif)</label>
                                <textarea {$input_disabled} name="ballot_description" rows="2" id="ballot_description" class="form-control form-control-lg" type="text" placeholder="{t}Description{/t}">{$ballot.ballot_description|for_input}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="ballot_engagement">Votre engagement</label>
                                <textarea {$input_disabled} name="ballot_engagement" rows="5" id="ballot_engagement" class="form-control form-control-lg" type="text" placeholder="{t}A l'issue de cette consultation je m'engage à ...{/t}">{$ballot.ballot_engagement|for_input}</textarea>
                            </div>
                            {************************************************************************************}
                            {*                            {printr2($ballot)}*}
                            <div class="form-group clearfix" id="ballot_date_div">
                                <label>Début de la consultation</label>
                                <div>
                                    <div>
                                        <input {$input_disabled} {if $ballot.ballot_asap eq 1}checked="checked"{/if} value="1" class="cb_asap" type="radio" id="asap_1" name="ballot_asap"/>
                                        <label for="asap_1">Dès que possible</label>
                                    </div>
                                    <div>
                                        <input {$input_disabled} {if $ballot.ballot_asap neq 1}checked="checked"{/if} value="0" class="cb_asap" type="radio" id="asap_0" name="ballot_asap"/>
                                        <label for="asap_0">Choisir une date de début</label>
                                    </div>
                                </div>
                                <div class="row input-group" id="div_ballot_start" style="{if $ballot.ballot_asap eq 1}display:none;{/if}">
                                    <input class="col-7" min="{$ballot.dt_min}" value="{$ballot.dt_start|civicpower_datetime_to_date}" type="date" class="form-control" name="ballot_start_date" id="ballot_start_date" {$input_disabled}/>
                                    <input class="col-5" value="{$ballot.dt_start|civicpower_datetime_to_hour}" type="time" class="form-control" name="ballot_start_time" id="ballot_start_time" {$input_disabled}/>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <label>Fin de la consultation</label>
                                <div class="row input-group">
                                    <input class="col-7" min="{$ballot.dt_min}" value="{$ballot.dt_end|civicpower_datetime_to_date}" type="date" class="form-control" name="ballot_end_date" id="ballot_end_date" {$input_disabled}/>
                                    <input class="col-5" value="{$ballot.dt_end|civicpower_datetime_to_hour}" type="time" class="form-control" name="ballot_end_time" id="ballot_end_time" {$input_disabled}/>
                                </div>
                            </div>
                            {************************************************************************************}
                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input {$input_disabled} type="checkbox" id="ballot_can_change_vote" name="ballot_can_change_vote" {if $ballot.ballot_can_change_vote eq 1}checked="checked"{/if} />
                                    <label for="ballot_can_change_vote">Autoriser la modification d'un vote</label>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input {$input_disabled} type="checkbox" id="ballot_see_results_live" name="ballot_see_results_live" {if $ballot.ballot_see_results_live eq 1}checked="checked"{/if} />
                                    <label for="ballot_see_results_live">Permettre de voir les tendances avant la fin de la consultation</label>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input {$input_disabled} type="checkbox" id="ballot_open" name="ballot_open" {if $ballot.open}checked="checked"{/if} />
                                    <label for="ballot_open">Tout le monde peut participer à cette consultation</label>
                                </div>
                            </div>
                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input {$input_disabled} type="checkbox" id="ballot_share" name="ballot_share" {if $ballot.ballot_share}checked="checked"{/if} />
                                    <label for="ballot_share">Permettre le partage sur les réseaux sociaux</label>
                                </div>
                            </div>
                            {if $can_edit}
                                <div>
                                    <button type="submit" class="btn btn-dark bg-cp-blue"><i class="fas fa-check"></i> {if !isset($ballot.ballot_id)}Continuer ...{else}Enregistrer{/if}</button>
                                </div>
                            {/if}
                        {/if}
                        {if $can_edit}</form>{/if}


                </div>
            </div>
        </div>
    </div>
    {if isset($ballot) && isset($ballot.question) && is_array($ballot.question)}
        {assign var="mode" value="edit"}
        {if $ballot.ballot_bstatus_id >= $smarty.env.STATUS_BALLOT_VALIDE_EN_ATTENTE}
            {assign var="mode" value="result"}
        {/if}
        <div class="col-md-8 col-12">
            {*            {printr($ballot)}*}
            <div class="card card-primary card-tabs">
                <div class="card-header p-1 pt-1  bg-cp-blue">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a
                                    class="nav-link active "
                                    id="custom-tabs-one-questions-tab"
                                    data-toggle="pill"
                                    href="#custom-tabs-one-questions"
                                    role="tab"
                                    aria-controls="custom-tabs-one-questions"
                                    aria-selected="false"
                            >{if $mode eq "edit"}Questions{else}Résultats{/if}</a>
                        </li>
                        <li class="nav-item">
                            <a
                                    class="nav-link"
                                    id="custom-tabs-one-voters-tab"
                                    data-toggle="pill"
                                    href="#custom-tabs-one-voters"
                                    role="tab"
                                    aria-controls="custom-tabs-one-voters"
                                    aria-selected="false"
                            >Liste de votants</a>
                        </li>
                        {if $ballot.astyp_lib|simplize=="mairie"}
                            <li class="nav-item">
                                <a
                                        class="nav-link"
                                        id="custom-tabs-one-cities-tab"
                                        data-toggle="pill"
                                        href="#custom-tabs-one-cities"
                                        role="tab"
                                        aria-controls="custom-tabs-one-cities"
                                        aria-selected="false"
                                >Communes</a>
                            </li>
                        {/if}
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        <div class="tab-pane fade active show" id="custom-tabs-one-questions" role="tabpanel" aria-labelledby="custom-tabs-one-questions-tab">
                            {if $mode eq "edit"}
                                <div id="question_list">
                                    {foreach from=$ballot.question item=item key=key}
                                        {include file="block/block-modal-new-question.tpl" question=$item}
                                    {/foreach}
                                </div>
                                {if $can_edit}
                                    <button id="btn-add-question" class="btn btn-sm btn-block btn-danger bg-cp-red"><i class="fa fa-plus"></i> &nbsp; Ajouter une question</button>
                                {/if}
                            {else}
                                <div class="div_participation">
                                    Participation : <span class="span_participation">{$ballot.ballot_participation}</span>
                                </div>
                                <div id="result_list">
                                    {foreach from=$ballot.question item=item key=key}
                                        <div class="div-question_title">{$item.question_title}</div>
                                        <div class="div-question_description">{$item.question_description}</div>
                                        <div class="div-option_list">
                                            {foreach from=$item.option item=item2 key=key2}
                                                <div class="row div_row">
                                                    <div class="col-6 div-option_title">{$item2.option_title}</div>
                                                    <div class="col-2 div-nb_vote">{$item2.nb_vote}</div>
                                                    <div style="background-size:{"%.2f"|sprintf:$item2.prc_vote}%" class="col-4 div-prc_vote">{"%.2f"|sprintf:$item2.prc_vote} %</div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-voters" role="tabpanel" aria-labelledby="custom-tabs-one-voters-tab">
                            {include file="block/block-voters-tab.tpl"}
                        </div>
                        {if $ballot.astyp_lib|simplize=="mairie"}
                            <div class="tab-pane fade" id="custom-tabs-one-cities" role="tabpanel" aria-labelledby="custom-tabs-one-cities-tab">
                                {include file="block/block-cities-tab.tpl"}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {else}
        {if false}
            <div class="col-md-8 col-12">
                <div class="sticky-top">
                    <div class="card card-secondary">
                        <div class="card-header  bg-cp-darkgrey">
                            <h3 class="card-title">{t}Quelques règles pour la création des consultations{/t}</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                                Success alert preview. This alert is dismissable.
                            </div>

                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                                Danger alert preview. This alert is dismissable. A wonderful serenity has taken possession of my entire
                                soul, like these sweet mornings of spring which I enjoy with my whole heart.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    {/if}

    <div class="invisible question-item-stock">
        {include file="block/block-modal-new-question.tpl"}
    </div>
    <div class="invisible option-item-stock">
        {include file="block/block-modal-new-option.tpl"}
    </div>


    <div class="modal fade" id="modal_ballot_success" tabindex="-1" aria-labelledby="modal_label_1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_label_1">{t}Enregistrement de la consultation{/t}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Votre consultation a été enregistrée avec succès</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{t}Fermer{/t}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_ballot_error" tabindex="-1" aria-labelledby="modal_label_1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_label_1">{t}Une erreur est survenue !{/t}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body sbg-danger">
                    <ul id="modal_ballot_error_list">

                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{t}Fermer{/t}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-publish" aria-hidden="true" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Publier une consultation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div id="modal-body-publish-loading">
                    <div class="modal-body">
                        <p>Chargement en cours ...</p>
                    </div>
                </div>
                <div id="modal-body-publish-ok" style="display:none;">
                    <div class="modal-body">
                        <p>Vous vous apprêtez à publier votre consultation.</p>
                        <p>Elle va désormais être en attente de modération.</p>
                        <p class="alert alert-danger">En publiant votre consultation, il ne sera plus possible de la modifier</p>
                        <p>Voulez-vous poursuivre la publication ?</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="btn-publish">Publier</button>
                    </div>
                </div>
                <div id="modal-body-publish-quota" style="display:none;">
                    <div class="modal-body">
                        <p>Vous avez atteint votre quota de consultations actives.</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {if $user.user_is_admin}
        <div class="modal fade" id="modal-ballot-reject" aria-hidden="true" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Refuser une consultation</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body-publish-loading">
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <label class="label">Indiquez le motif de refus</label>
                                    <textarea id="rejection_reason" rows="6" class="form-control" placeholder="Motif de refus ..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="btn-admin-reject-confirm">Refuser cette consultation</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-ballot-accept" aria-hidden="true" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Accepter une consultation</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body-publish-loading">
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <label class="label">Commentaire (facultatif)</label>
                                    <textarea id="acceptation_reason" rows="6" class="form-control" placeholder="Commentaire (facultatif)"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="btn-admin-accept-confirm">Accepter cette consultation</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>

