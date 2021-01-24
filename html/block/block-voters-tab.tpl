{capture assign=username_filled}{$user.user_firstname|trim neq "" && $user.user_lastname|trim neq ""}{/capture}
<div id="div-user-name-missing" class="{if $username_filled}d-none{/if}">
    <div class="row">
        <div class="well col-lg-12 mb-3">
            Pour créer une liste d'invitation, merci de renseigner vos nom et prénom.<br />
            Nous indiquerons ainsi à ces votants que vous les avez invités à participer.
        </div>
        <div class="row">
            <div class="form-group-lg col-md-6">
                <label>Votre prénom</label>
                <input type="text" class="form-control form-control-lg" id="user_firstname" value="{$user.user_firstname|for_input}" />
            </div>
            <div class="form-group-lg col-md-6">
                <label>Votre nom</label>
                <input type="text" class="form-control form-control-lg" id="user_lastname" value="{$user.user_lastname|for_input}" />
            </div>
            <div class="form-group-lg col-md-12 mt-2">
                <button class="btn btn-primary btn-lg" id="btn-submit-user-name">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<div id="div-user-name-filled" style="{if !$username_filled}display:none;{/if}">
    <div class="row">
        <div class="well col-lg-12 mb-3">
            Vous pouvez importer des emails et numéro de téléphone à partir de n'importe quel fichier texte.<br/>
            Le format importe peu, nous ne prendrons en compte que les téléphones et email, les utilisateurs décideront de leurs informations personnelles à l'inscription.<br/>
            Vous devez vous assurer au préalable que vous avez récolté leur consentement à être ainsi contacté par vous au sens du RGPD.
        </div>
        <div class="col-lg-6">
            <button {$input_disabled} id="btn-import-file" class="btn btn-file btn-lg btn-primary btn-block">
                <i class="fa fa-paperclip"></i>
                J'importe un fichier de votants
                <input {$input_disabled} type="file" id="voters-file"/>
            </button>
        </div>
        <div class="col-lg-6">
            <button {$input_disabled} class="btn btn-lg btn-primary btn-block" id="btn-voters-write">
                <i class="fa fa-pen"></i>
                J'écris la liste des votants
            </button>
        </div>
        <div id="alert-voters-error" class="alert mt-4 col-12 alert-default-danger text-center">
            Votre liste de votants contient des données incorrectes qui n'ont pas pu être ajoutées.
            <button data-toggle="modal" data-target="#modal-voters-error" role="button" class="btn btn-danger">Cliquez ici pour voir les erreurs</button>
        </div>
        <div class="mt-4 card col-12">
            <div class="card-body row" id="voters-list">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-voters-write" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Inscription des votants</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-lg-6">
                        Vous pouvez saisir les email et numéros de téléphone des utilisateurs que vous souhaitez inviter à participer.
                    </div>
                    <div class="col-lg-6">
                        Vous vous êtes assuré au préalable que vous avez récolté le droit de les contacter au sens du RGPD.
                    </div>
                </div>
                <form>
                    <textarea id="txt-voters-list" rows="10" class="form-control"></textarea>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-voters-write-save"><i class="fa fa-check"></i> Enregistrer</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="modal-voters-error" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Erreurs d'import</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <textarea disabled="disabled" id="txt-voters-error" rows="10" class="form-control"></textarea>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="invisible voter-item-stock">
    {include file="block/block-voters-item.tpl"}
</div>
