{seo_title}{if $user.user_firstname|@strlen gt 0}User : {$user.user_firstname} {$user.user_lastname}{else}{t}Admin. utilisateurs{/t}{/if}{/seo_title}
{seo_description}{$seo_title}{/seo_description}


<div class="row">
    <div class="col-md-4">
        <div class="card card-gray-dark card-outline">
            <div class="card-header">
                <div class="card-title">Infos</div>
            </div>
            <div class="card-body">
                <form id="form-user" href="#" method="post">
                    <input type="hidden" name="user_id" value="{$user.user_id}" />
                    <div class="form-group">
                        <label for="user_firstname">Prénom</label>
                        <input disabled="disabled" type="text" class="form-control" id="user_firstname" value="{$user.user_firstname|for_input}"/>
                    </div>
                    <div class="form-group">
                        <label for="user_lastname">Nom</label>
                        <input disabled="disabled" type="text" class="form-control" id="user_lastname" value="{$user.user_lastname|for_input}"/>
                    </div>
                    <div class="form-group">
                        <label for="user_phone_international">Téléphone</label>
                        <input disabled="disabled" type="text" class="form-control" id="user_phone_international" value="{$user.user_phone_international|for_input}"/>
                    </div>
                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input disabled="disabled" type="text" class="form-control" id="user_email" value="{$user.user_email|for_input}"/>
                    </div>
                    <div class="form-group">
                        <label for="user_nb_active_ballot_allowed">Nb consultations actives autorisées</label>
                        <input type="number" class="form-control" name="user_nb_active_ballot_allowed" id="user_nb_active_ballot_allowed" value="{$user.user_nb_active_ballot_allowed|for_input}"/>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-gray-dark card-outline">
            <div class="card-header">
                <div class="card-title">Consultations</div>
            </div>
            <div class="card-body">
                {foreach from=$user.ballot_list item=item2 key=key2}
                    <div>
                        <div class="row">
                            <span class="col-md-8"><a href="/ballot.php?ballot_id={$item2.ballot_id}">{$item2.ballot_title}</a></span>
                            <span class="col-md-4">{$item2.asker_name}</span>
                        </div>

                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>

