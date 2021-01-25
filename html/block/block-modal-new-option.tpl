<li class="option-item" data-option_id="{$option.option_id}">
    <div class="row w-100">
        {if $can_edit}
            <div class="col-1 handle ui-sortable-handle ui-handle">
                <i class="fa fa-grip-vertical"></i>
            </div>
        {/if}
        <div class="col-10 text ui-sortable-2nd">
            <input {$input_disabled} type="text" value="{$option.option_title}" class="option-title ajaxable form-control form-control-sm" placeholder="Option" required="required">
        </div>
        {if $can_edit}
            <div class="col-1 col-delete">
                <button class="btn-remove-option btn btn-dark bg-cp-black btn-xs"><i class="fa fa-trash"></i></button>
            </div>
        {/if}
    </div>
</li>
