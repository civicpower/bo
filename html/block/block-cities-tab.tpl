<div class="row">
    <div class="well col-lg-12 mb-3">
        Vous pouvez limiter l'accès à une consultation aux habitants d'une ou plusieurs communes.
    </div>
    <div class="col-lg-4">
        <input {$input_disabled} type="number" class="form-control form-control-lg" placeholder="Code Postal" id="input_zipcode" min="0" max="99999" maxlength="5" />
    </div>
    <div class="col-lg-4">
        <select {$input_disabled} id="input_commune" class="form-control form-control-lg">
            <option class="original" value="">Choix de la commune</option>
        </select>
    </div>
    <div class="col-lg-4">
        <button  {$input_disabled} class="btn btn-lg btn-primary btn-block" id="btn-city-add">
            <i class="fa fa-city"></i> &nbsp;
            Ajouter la commune
        </button>
    </div>
    <div class="mt-4 card col-12">
        <div class="card-body row" id="cities-list">

        </div>
    </div>
</div>



