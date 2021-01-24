{seo_title}{$smarty.env.LOGO_NAME} - {t}Mot de passe obligatoire{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

<div class="text-center" id="div-login-logo">
    <img src="/images/logo-square.png" id="login-logo"/>
</div>

<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h1>{$smarty.env.LOGO_NAME}</h1>
        </div>
        <div class="card-body text-center">
            <p class="login-box-msg">Renseignez un mot de passe</p>

            <p>
                Pour la sécurité de vos données, merci de choisir un mot de passe avant d'accéder à l'espace d'administration.
            </p>
            <p>
                <a class="btn btn-primary btn-lg" href="{$smarty.env.APP_HOST}/user-password">Renseigner le mot de passe</a>
            </p>
        </div>
    </div>
</div>

