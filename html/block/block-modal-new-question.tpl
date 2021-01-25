<div data-question_id="{$question.question_id}" class="card-question card card-primary card-outline">
    <div class="card-header">
        {if $can_edit}
            <div class="div-btn-question">
                <button type="button" class="btn btn-light btn-question-up"><i class="fa fa-arrow-up"></i> Monter</button>
                <button type="button" class="btn btn-light btn-question-down"><i class="fa fa-arrow-down"></i> Descendre</button>
                <button type="button" class="float-right btn-remove-question btn btn-dark btn-sm bg-cp-black"><i class="fa fa-trash"></i> &nbsp; Supprimer cette question</button>
            </div>
        {/if}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-label-group mw60 mx-auto">
                    <input {$input_disabled} value="{$question.question_title}" type="text" data-field="question_title" class="ajaxable form-control" placeholder="Libellé de la question" required="required">
                    <label for="question_title">Libellé de la question</label>
                </div>
                {if false}
                    <div class="form-label-group mw60 mx-auto">
                        <input {$input_disabled} value="{$question.question_description}" type="text" data-field="question_description" class="ajaxable form-control form-control-sm" placeholder="Description" required="required">
                        <label for="question_description">Description</label>
                    </div>
                {/if}
                {if $can_edit}
                    <button class="btn btn-sm btn-dark bg-cp-blue btn-block btn-save-question"><i class="fa fa-check"></i> Enregistrer</button>
                {/if}
            </div>
            <div class="col-md-6">
                <div class="card card-primary card-outline card-green card-option-list">
                    <div class="card-header">
                        <h3 class="card-title">{t}Réponses possibles{/t}</h3>
                    </div>
                    <div class="card-body option-list-div">
                        <ul class="option-list todo-list ui-sortable" data-widget="todo-list">
                            {foreach from=$question.option item=item2 key=key2}
                                {if $item2.option_can_be_deleted eq '1'}
                                    {include file="block/block-modal-new-option.tpl" option=$item2}
                                {/if}
                            {/foreach}
                        </ul>
                        <ul class="option-list-fixe">
                            {foreach from=$question.option item=item2 key=key2}
                                {if $item2.option_can_be_deleted neq '1'}
                                    {include input_disabled="disabled=\"disabled\"" file="block/block-modal-new-option.tpl" option=$item2}
                                {/if}
                            {/foreach}
                        </ul>
                        {if $can_edit}
                            <div>
                                <button class="btn-block btn-add-option btn btn-sm btn-success bg-cp-green">
                                    <i class="fa fa-plus"></i> &nbsp; Ajouter une option
                                </button>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
