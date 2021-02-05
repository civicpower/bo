{seo_title}{$smarty.env.LOGO_NAME} - {t}Index{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

<div class="text-center" id="div-login-logo">
    <img src="/images/logo-square.png" id="login-logo"/>
</div>

<div class="login-box">
    <div class="card card-outline card-gray-dark">
        <div class="card-header text-center">
            <h1>{$smarty.env.LOGO_NAME}</h1>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Connectez-vous</p>

            <form id="form_login" action="/login" method="post">
                <input type="hidden" name="form" value="form"/>
                <div class="input-group mb-3">
                    <input value="{$smarty.post.login|for_input}" name="login" id="input_login" type="text" class="form-control" placeholder="E-Mail ou Mobile">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input value="{$smarty.post.password|for_input}" name="password" id="input_password" type="password" class="form-control" placeholder="Mot de passe">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    {if false}
                        <div class="col-7">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>
                    {/if}
                    <div class="col-12">
                        <button type="submit" class="btn-lg btn bg-cp-purple text-white btn-block">Connexion</button>
                    </div>
                </div>
            </form>
            {if true}
                <div class="text-center">
                    <p class="mb-1 mt-1">
                        <a href="{$smarty.env.APP_HOST}/lost-password">J'ai oublié mon mot de passe</a>
                    </p>
                    <p class="mb-0 mt-1">
                        <a href="{$smarty.env.APP_HOST}/subscribe" class="text-center">Je m'inscris</a>
                    </p>
                </div>
            {/if}
        </div>
    </div>
</div>

<div id="div-error" style="display: none;" class="alert alert-default-danger"></div>

{if $login_errors|@count gt 0}
    <ul id="ul_errors">
        {foreach from=$login_errors item=item}
            <li>{$item}</li>
        {/foreach}
    </ul>
{/if}
<script type="text/javascript">
    document.getElementById('input_login').focus();
</script>