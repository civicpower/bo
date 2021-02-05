{seo_title}{if $asker.asker_name|@strlen gt 0}Asker : {$asker.asker_name}{else}{t}Admin. asker{/t}{/if}{/seo_title}
{seo_description}{$seo_title}{/seo_description}


<div class="row">
    <div class="col-md-6">
        <div class="card card-gray-dark card-outline">
            <div class="card-header">
                <div class="card-title">Infos</div>
            </div>
            <div class="card-body row">
                <input type="hidden" id="asker_id" value="{$asker.asker_id}" />
                <div class="form-group col-md-12">
                    <label for="asker_name">Libellé de l'organisateur</label>
                    <input type="text" class="form-control" id="asker_name" value="{$asker.asker_name|for_input}"/>
                </div>
                <div class="form-group col-md-6">
                    <label for="user_firstname">Prénom</label>
                    <input type="text" class="form-control" id="user_firstname" value="{$asker.user.user_firstname|for_input}"/>
                </div>
                <div class="form-group col-md-6">
                    <label for="user_lastname">Nom</label>
                    <input type="text" class="form-control" id="user_lastname" value="{$asker.user.user_lastname|for_input}"/>
                </div>
                <div class="form-group col-md-6">
                    <label for="user_phone_international">Téléphone</label>
                    <input type="text" class="form-control" id="user_phone_international" value="{$asker.user.user_phone_international|for_input}"/>
                </div>
                <div class="form-group col-md-6">
                    <label for="user_email">Email</label>
                    <input type="text" class="form-control" id="user_email" value="{$asker.user.user_email|for_input}"/>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-gray-dark card-outline">
            <div class="card-header">
                <div class="card-title">Asker Type</div>
            </div>
            <div class="card-body">
                <div class="form-group col-md-6">
                    <label for="user_lastname">Type de asker</label>
                    <select class="form-control" id="select-ask_type">
                        {foreach from=$ask_type item=item key=key}
                            <option {if $item.astyp_id eq $asker.asker_astyp_id}selected="selected"{/if} value="{$item.astyp_id|for_input}">{$item.astyp_lib|for_input}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-gray-dark card-outline">
            <div class="card-header">
                <div class="card-title">Consultations</div>
            </div>
            <div class="card-body">
                {foreach from=$asker.ballot_list item=item2 key=key2}
                    <div>
                        <div class="row mt-1">
                            <span class="col-md-8"><a class="" href="/ballot.php?ballot_id={$item2.ballot_id}">{$item2.ballot_title}</a></span>
                        </div>

                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>

