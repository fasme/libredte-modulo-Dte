<div class="page-header"><h1>Configuración de respaldos automáticos en Dropbox</h1></div>
<?php if (!$Emisor->config_respaldos_dropbox) : ?>
<p>Aquí podrá conectar LibreDTE con Dropbox para que se realicen respalos automáticos del contribuyente <?=$Emisor->razon_social?>.</p>
<div class="row">
    <div class="col-md-6">
        <a class="btn btn-primary btn-lg btn-block" href="https://db.tt/328o5XBy" role="button" target="_blank">
            Crear cuenta en Dropbox
        </a>
    </div>
    <div class="col-md-6">
        <a class="btn btn-success btn-lg btn-block" href="<?=$authUrl?>" role="button">
            Conectar LibreDTE con Dropbox
        </a>
    </div>
</div>
<?php else: ?>
<p>Usted tiene conectado el contribuyente <?=$Emisor->razon_social?> de LibreDTE con su cuenta de Dropbox, esto significa que se realizarán respaldos automáticos de los datos de su empresa.</p>
<div class="row">
    <div class="col-md-3 text-center">
        <span class="fa fa-user" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$account->getDisplayName()?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-envelope" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$account->getEmail()?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-globe" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=$account->getCountry()?> / <?=$account->getLocale()?></span>
    </div>
    <div class="col-md-3 text-center">
        <span class="fa fa-database" style="font-size:128px"></span>
        <br/>
        <span class="lead"><?=num($accountSpace['used']/1024/1024/1024,1)?> / <?=num($accountSpace['allocation']['allocated']/1024/1024/1024,1)?> GB</span>
    </div>
</div>
<br/>
<?php $uso = round(($accountSpace['used']/$accountSpace['allocation']['allocated'])*100);?>
<div class="progress">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$uso?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$uso?>%;">
        <?=$uso?>%
    </div>
</div>
<br/>
<a class="btn btn-danger btn-lg btn-block" href="dropbox/desconectar" role="button">
    Desconectar LibreDTE de Dropbox
</a>
<?php endif; ?>
