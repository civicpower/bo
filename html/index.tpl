{seo_title}{t}Tableau de bord{/t}{/seo_title}
{seo_description}{$seo_title}{/seo_description}

<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="nb_ballot">__</h3>
                <p>consultations</p>
            </div>
            <div class="icon">
                <i class="ion ion-filing"></i>
            </div>
            <a href="/ballot-list" class="small-box-footer">Mes consultations <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="nb_vote">__<sup style="font-size: 20px">%</sup></h3>
                <p>Votes</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            {*<a href="/insights" class="small-box-footer">Statistiques <i class="fas fa-arrow-circle-right"></i></a>*}
        </div>
    </div>
    {if $user["user_is_admin"]==1}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="nb_users">___</h3>
                    <p>Utilisateurs</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person"></i>
                </div>
                <a href="/admin-user-list" class="small-box-footer">Voir les users <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    {/if}
    {if false}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>___</h3>
                    <p>Visites</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="/insights" class="small-box-footer">Statistiques <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    {/if}
</div>

