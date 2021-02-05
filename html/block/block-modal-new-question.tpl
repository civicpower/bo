{assign var="q_type" value="qcu"}
{if $question.question_nb_vote_min gt 1 || $question.question_nb_vote_max gt 1}
    {assign var="q_type" value="qcm"}
{/if}
<div data-question_id="{$question.question_id}" class="card-question card card-dark card-outline type-{$q_type}">
    <div class="card-header">
        {if $can_edit}
            <div class="div-btn-question">
                <button type="button" class="btn btn-light btn-question-up"><i class="fa fa-arrow-up"></i> Monter</button>
                <button type="button" class="btn btn-light btn-question-down"><i class="fa fa-arrow-down"></i> Descendre</button>
                <button type="button" class="float-right btn-remove-question btn btn-dark btn-lg bg-cp-black"><i class="fa fa-trash"></i> &nbsp; Supprimer cette question</button>
            </div>
        {/if}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mw60 mx-auto">
                    <label for="question_title">Libellé de la question</label>
                    <input {$input_disabled} value="{$question.question_title}" type="text" data-field="question_title" class="ajaxable form-control" placeholder="Libellé de la question" required="required">
                </div>
                <div class="form-group question-type">
                    <label>Type de question</label>
                    <select class="form-control select-question-type">
                        <option value="qcu" {if $q_type eq "qcu"}selected="selected"{/if}>Question à choix unique</option>
                        <option value="qcm" {if $q_type eq "qcm"}selected="selected"{/if}>Vote à préférences multiples</option>
                    </select>
                    <div class="div-qcm row">
                        <div class="form-group col-md-6">
                            <label>Nb. choix minimum</label>
                            <input
                                    min="1"
                                    max="50"
                                    {$input_disabled}
                                    value="{if $question.question_nb_vote_min|is_numeric && $question.question_nb_vote_min gt 0}{$question.question_nb_vote_min}{else}1{/if}"
                                    type="number"
                                    data-field="question_nb_vote_min"
                                    class="ajaxable form-control form-control-lg input-nb-choice input-nb-choice-min"
                                    placeholder="Min"
                                    required="required"
                            >
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nb. choix maximum</label>
                            <input
                                    min="1"
                                    max="50"
                                    {$input_disabled}
                                    value="{if $question.question_nb_vote_max|is_numeric && $question.question_nb_vote_max gt 0}{$question.question_nb_vote_max}{else}1{/if}"
                                    type="number"
                                    data-field="question_nb_vote_max"
                                    class="ajaxable form-control form-control-lg input-nb-choice input-nb-choice-max"
                                    placeholder="Max"
                                    required="required"
                            >
                        </div>
                    </div>
                </div>
                {if false}
                    <div class="form-label-group mw60 mx-auto">
                        <input {$input_disabled} value="{$question.question_description}" type="text" data-field="question_description" class="ajaxable form-control form-control-sm" placeholder="Description" required="required">
                        <label for="question_description">Description</label>
                    </div>
                {/if}
            </div>
            <div class="col-md-6">
                <div class="card card-gray-dark card-outline card-gray-dark card-option-list">
                    <div class="card-header">
                        <h3 class="card-title">{t}Réponses possibles{/t}</h3>
                    </div>
                    <div class="card-body option-list-div">
                        <ul class="option-list todo-list ui-sortable" data-widget="todo-list">
                            {foreach from=$question.option item=item2 key=key2}
                                {if $item2.option_can_be_disabled neq '1'}
                                    {include file="block/block-modal-new-option.tpl" option=$item2}
                                {/if}
                            {/foreach}
                        </ul>
                        <ul class="option-list-fixe">
                            {foreach from=$question.option item=item2 key=key2}
                                {if $item2.option_can_be_disabled eq '1'}
                                    {include input_disabled="disabled=\"disabled\"" file="block/block-modal-new-option.tpl" option=$item2}
                                {/if}
                            {/foreach}
                        </ul>
                        {if $can_edit}
                            <div class="mt-2">
                                <button class="btn-block btn-add-option btn text-white btn-lg bg-cp-green">
                                    <i class="fa fa-plus"></i> &nbsp; Ajouter une option
                                </button>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            {if $can_edit}
                <button class="btn btn-lg btn-dark bg-cp-purple btn-block btn-save-question"><i class="fa fa-check"></i> Enregistrer la question</button>
            {/if}
        </div>
    </div>
</div>
