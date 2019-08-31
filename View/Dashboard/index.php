<div class="float-right">
    <script>
        function periodo_seleccionar(periodo) {
            if (Form.check('periodo_form')) {
                window.location = _url+'/dte/dashboard?periodo='+encodeURI(periodo);
            }
        }
    </script>
    <form name="periodo_form" id="periodo_form" onsubmit="periodo_seleccionar(this.periodo.value); return false">
        <div class="form-group">
            <label class="control-label sr-only" for="periodoField">Período del dashboard</label>
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <a href="<?=$_base?>/dte/dashboard?periodo=<?=$periodo_anterior?>" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
                    <a href="<?=$_base?>/dte/dashboard?periodo=<?=$periodo_siguiente?>" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i></a>
                </div>
                <input type="text" name="periodo" value="<?=$periodo?>" class="form-control check integer text-center" id="periodoField" placeholder="<?=$periodo_actual?>" size="7" onclick="this.select()" />
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="periodo_seleccionar(document.periodo_form.periodo.value); return false">
                        <span class="fa fa-search"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="page-header"><h1>Facturación</h1></div>

<?php
echo View_Helper_Dashboard::cards([
    [
        'icon' => 'far fa-file',
        'quantity' => $n_temporales,
        'title' => 'Temporales',
        'link' => 'dte_tmps',
        'link_title' => 'Explorar documentos',
    ],
    [
        'icon' => 'fas fa-sign-out-alt',
        'quantity' => $n_emitidos,
        'title' => 'Ventas',
        'link' => 'dte_ventas/ver/'.$periodo,
        'link_title' => 'Ver detalle de ventas',
    ],
    [
        'icon' => 'fas fa-sign-in-alt',
        'quantity' => $n_recibidos,
        'title' => 'Compras',
        'link' => 'dte_compras/ver/'.$periodo,
        'link_title' => 'Ver detalle de compras',
    ],
    [
        'icon' => 'fas fa-exchange-alt',
        'quantity' => $n_intercambios,
        'title' => 'Pendientes',
        'link' => 'dte_intercambios/listar',
        'link_title' => 'Bandeja de intercambio',
    ],
]);
?>

<div class="row">
    <!-- PANEL IZQUIERDA -->
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg btn-block" href="documentos/emitir" role="button">
            Emitir documento
        </a>
        <br />
        <!-- menú módulo -->
        <div class="list-group mb-4">
<?php foreach ($nav as $link=>&$info): ?>
            <a href="<?=$_base.'/dte'.$link?>" title="<?=$info['desc']?>" class="list-group-item">
                <i class="<?=$info['icon']?> fa-fw"></i> <?=$info['name']?>
            </a>
<?php endforeach; ?>
        </div>
        <!-- fin menú módulo -->
        <!-- alertas envío libro o propuesta f29 -->
<?php if (!$libro_ventas_existe) : ?>
            <a class="btn btn-info btn-lg btn-block" href="dte_ventas" role="button" title="Ir al libro de ventas">
                <i class="fa fa-exclamation-circle"></i>
                Generar IV <?=$periodo_anterior?>
            </a>
            <br />
<?php endif; ?>
<?php if (!$libro_compras_existe) : ?>
            <a class="btn btn-info btn-lg btn-block" href="dte_compras" role="button" title="Ir al libro de compras">
                <i class="fa fa-exclamation-circle"></i>
                Generar IC <?=$periodo_anterior?>
            </a>
            <br />
<?php endif; ?>
<?php if ($propuesta_f29) : ?>
            <a class="btn btn-info btn-lg btn-block" href="informes/impuestos/propuesta_f29/<?=$periodo_anterior?>" role="button" title="Descargar archivo con la propuesta del formulario 29">
                <i class="fa fa-download"></i>
                Propuesta F29 <?=$periodo_anterior?>
            </a>
            <br />
<?php endif; ?>
<?php if (!$Emisor->config_sii_pass) : ?>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <p class="lead">¿Sabía que si asigna la contraseña del SII de la empresa podría desbloquear funcionalidades adicionales?</p>
                    <p class="small">Por ejemplo la sincronización con el registro de compras y ventas del SII</p>
                </div>
            </div>
<?php endif; ?>
        <!-- fin alertas envío libro o propuesta f29 -->
<?php if ($cuota) : ?>
        <!-- dtes usados (totales de emitidos y recibidos) -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-calculator fa-fw"></i>
                Documentos usados
            </div>
            <div class="panel-body text-center p-4">
                <span class="lead text-info"><?=num($n_dtes)?></span> <small class="text-muted"> de <?=num($cuota)?></small><br/>
                <span class="small"><a href="<?=$_base?>/dte/informes/documentos_usados">ver detalle de uso</a></span>
            </div>
        </div>
        <!-- fin dtes usados (totales de emitidos y recibidos) -->
<?php endif; ?>
    </div>
    <!-- FIN PANEL IZQUIERDA -->
    <!-- PANEL CENTRO -->
    <div class="col-md-6">
<?php if ($documentos_rechazados) : ?>
        <!-- alertas documentos rechazados  -->
        <div class="row">
            <div class="col-sm-12">
                <a class="btn btn-danger btn-lg btn-block" href="informes/dte_emitidos/estados/<?=$documentos_rechazados['desde']?>/<?=$documentos_rechazados['hasta']?>" role="button" title="Ir al informe de estados de envíos de DTE">
                    <?=num($documentos_rechazados['total'])?> documento(s) rechazado(s) desde el <?=\sowerphp\general\Utility_Date::format($documentos_rechazados['desde'])?>
                </a>
                <br />
            </div>
        </div>
        <!-- fin alertas documentos rechazados -->
<?php endif; ?>
<?php if ($rcof_rechazados) : ?>
        <!-- alertas rcof rechazados  -->
        <div class="row">
            <div class="col-sm-12">
                <a class="btn btn-danger btn-lg btn-block" href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:ERRONEO" role="button" title="Ir al informe de estados de envíos de DTE">
                    <?=num($rcof_rechazados['total'])?> RCOF(s) rechazado(s) desde el <?=\sowerphp\general\Utility_Date::format($rcof_rechazados['desde'])?>
                </a>
                <br />
            </div>
        </div>
        <!-- fin alertas rcof rechazados -->
<?php endif; ?>
<?php if ($Firma and $Firma->getExpirationDays()<=20) : ?>
        <!-- alerta vencimiento firma electrónica -->
        <div class="row">
            <div class="col-sm-12">
                <a class="btn btn-warning btn-lg btn-block" href="<?=$_base?>/dte/admin/firma_electronicas" role="button" title="Ir al mantenedor de firmas electrónicas">
                    La firma electrónica vence en <?=num($Firma->getExpirationDays())?> día(s)
                </a>
                <br />
            </div>
        </div>
        <!-- fin alerta vencimiento firma electrónica -->
<?php endif; ?>
<?php if ($ventas_periodo or $compras_periodo) : ?>
        <!-- graficos ventas y compras -->
        <div class="card-deck">
<?php if ($ventas_periodo) : ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="far fa-chart-bar fa-fw"></i> Ventas
                </div>
                <div class="card-body">
                    <div id="grafico-ventas"></div>
                    <a href="dte_ventas/ver/<?=$periodo?>" class="btn btn-primary btn-block">Ver libro del período</a>
                </div>
            </div>
<?php endif; ?>
<?php if ($compras_periodo) : ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="far fa-chart-bar fa-fw"></i> Compras
                </div>
                <div class="card-body">
                    <div id="grafico-compras"></div>
                    <a href="dte_compras/ver/<?=$periodo?>" class="btn btn-primary btn-block">Ver libro del período</a>
                </div>
            </div>
<?php endif; ?>
        </div>
        <!-- fin graficos ventas y compras -->
<?php endif; ?>
<?php if (!empty($registro_compra_pendientes)) : ?>
<?php if ($registro_compra_pendientes_dias) : ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Documentos recibidos pendientes y su aceptación automática en SII
            </div>
            <div class="card-body">
                <div id="grafico-registro_compra_pendientes_dias"></div>
            </div>
        </div>
<?php endif; ?>
        <!-- documentos recibidos en SII pendientes -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="far fa-chart-bar fa-fw"></i> Documentos recibidos en SII pendientes de procesar (<?=num($n_registro_compra_pendientes)?>)
                    </div>
                    <div class="card-body">
<?php
foreach ($registro_compra_pendientes as &$p) {
    $p['dte_glosa'] = '<a href="'.$_base.'/dte/registro_compras/pendientes?dte='.$p['dte'].'">'.$p['dte_glosa'].'</a>';
    $p['fecha_recepcion_sii_inicial'] = \sowerphp\general\Utility_Date::format($p['fecha_recepcion_sii_inicial'], 'd/m/Y H:i');
    $p['fecha_recepcion_sii_final'] = \sowerphp\general\Utility_Date::format($p['fecha_recepcion_sii_final'], 'd/m/Y H:i');
    $p['total'] = num($p['total']);
    unset($p['dte']);
}
array_unshift($registro_compra_pendientes, ['Documento', 'Pendientes', 'Primero', 'Último', 'Total']);
new \sowerphp\general\View_Helper_Table($registro_compra_pendientes);
?>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="registro_compras/pendientes_resumen_csv" class="btn btn-primary btn-block">Bajar resumen en CSV</a>
                            </div>
                            <div class="col-md-6">
                                <a href="registro_compras/pendientes" class="btn btn-primary btn-block">Ver detalle documentos</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer small">
                        Datos actualizados una vez al día, si hay cambios en el SII los verá reflejados al día siguiente
                    </div>
                </div>
            </div>
        </div>
        <!-- fin documentos recibidos en SII pendientes -->
<?php endif; ?>
<?php if ($emitidos_estados) : ?>
        <!-- estado de documentos emitidos SII -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="far fa-chart-bar fa-fw"></i> Estado envíos al SII de documentos emitidos
                    </div>
                    <div class="card-body">
                        <div id="grafico-dte_emitidos_estados"></div>
                        <a href="informes/dte_emitidos/estados/<?=$desde?>/<?=$hasta?>" class="btn btn-primary btn-block">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin estado de documentos emitidos SII -->
<?php endif; ?>
<?php if ($emitidos_eventos) : ?>
        <!-- estado de documentos emitidos receptores -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="far fa-chart-bar fa-fw"></i> Eventos asignados por receptores de documentos emitidos
                    </div>
                    <div class="card-body">
                        <div id="grafico-dte_emitidos_eventos"></div>
                        <p class="small">
<?php foreach (\sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos as $codigo => $evento) : ?>
                            <strong><?=$codigo?></strong>: <?=$evento?>
<?php endforeach; ?>
                        </p>
                        <a href="informes/dte_emitidos/eventos/<?=$desde?>/<?=$hasta?>" class="btn btn-primary btn-block">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin estado de documentos emitidos receptores -->
<?php endif; ?>
<?php if ($rcof_estados) : ?>
        <!-- estado de rcof enviados al SII -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="far fa-chart-bar fa-fw"></i> Estado envíos al SII de reportes de consumos de folios (RCOF)
                    </div>
                    <div class="card-body">
                        <div id="grafico-rcof_estados"></div>
                        <a href="dte_boleta_consumos/listar/1/dia/D" class="btn btn-primary btn-block">Ver listado de RCOFs</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin estado de rcof enviados al SII -->
<?php endif; ?>
    </div>
    <!-- FIN PANEL CENTRO -->
    <!-- PANEL DERECHA -->
    <div class="col-md-3">
        <!-- buscador documentos -->
        <script>
            function buscar(q) {
                window.location = _url+'/dte/documentos/buscar?q='+encodeURI(q);
            }
            $(function(){$('#qField').focus()});
        </script>
        <form name="buscador" onsubmit="buscar(this.q.value); return false">
            <div class="form-group">
                <label class="control-label sr-only" for="qField">Buscar por código documento</label>
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control" id="qField" placeholder="Buscar DTE..." />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="buscar(document.buscador.q.value); return false">
                            <span class="fa fa-search"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <!-- fin buscador documentos -->
<?php if (!empty($n_emitidos_reclamados)) : ?>
        <!-- documentos emitidos con reclamo de receptor -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-exclamation-circle fa-fw"></i> Emitidos reclamados</div>
                    <div class="card-body text-center">
                        <span class="lead">
<?php if ($n_emitidos_reclamados>1) : ?>
                            <?=num($n_emitidos_reclamados)?> documentos<br/>
<?php else : ?>
                            Un documento<br/>
<?php endif; ?>
                        </span>
                        <span class="small"><a href="<?=$_base?>/dte/informes/dte_emitidos/eventos_detalle/<?=$desde?>/<?=$hasta?>/R">ver detalle</a></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin documentos emitidos con reclamo de receptor -->
<?php endif; ?>
<?php if (!empty($n_registro_compra_pendientes)) : ?>
        <!-- documentos recibidos en SII pendientes -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-paperclip fa-fw"></i> Recibidos pendientes en SII</div>
                    <div class="card-body text-center">
                        <span class="lead">
<?php if ($n_registro_compra_pendientes>1) : ?>
                            <?=num($n_registro_compra_pendientes)?> documentos<br/>
<?php else : ?>
                            Un documento<br/>
<?php endif; ?>
                        </span>
                        <span class="small"><a href="<?=$_base?>/dte/registro_compras/pendientes">ver detalle</a></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin documentos recibidos en SII pendientes -->
        <!-- documentos recibidos pendientes por días faltantes -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-calendar-alt fa-fw"></i>
                Pendientes por días faltantes
            </div>
            <div class="card-body">
                <div class="list-group">
<?php foreach ($registro_compra_pendientes_dias as $p) : ?>
<?php
if ($p['dias_aceptacion_automatica']<=1) {
    $color = 'danger';
} else if ($p['dias_aceptacion_automatica']<=4) {
    $color = 'warning';
} else {
    $color = 'success';
}
?>
                    <a href="<?=$_base?>/dte/registro_compras/pendientes?fecha_recepcion_sii_desde=<?=$p['fecha_recepcion_sii']?>&amp;fecha_recepcion_sii_hasta=<?=$p['fecha_recepcion_sii']?>" class="list-group-item">
                        <span class="badge badge-pill badge-<?=$color?>"><?=\sowerphp\general\Utility_Date::format($p['fecha_aceptacion_automatica'])?> (en <?=$p['dias_aceptacion_automatica']?> días)</span>
                        <span class="badge badge-pill border float-right"><?=num($p['cantidad'])?></span>
                    </a>
<?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- fin documentos recibidos pendientes por días faltantes -->
        <!-- documentos recibidos pendientes por rango montos -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-search-dollar fa-fw"></i>
                Pendientes por rango
            </div>
            <div class="card-body">
                <div class="list-group">
<?php foreach ($registro_compra_pendientes_rango_montos as $p) : ?>
                    <a href="<?=$_base?>/dte/registro_compras/pendientes?total_desde=<?=$p['desde']?>&amp;total_hasta=<?=$p['hasta']?>" class="list-group-item">
                        <span class="badge badge-pill badge-info"><?=num($p['desde'])?> - <?=num($p['hasta'])?></span>
                        <span class="badge badge-pill border float-right"><?=num($p['cantidad'])?></span>
                    </a>
<?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- fin documentos recibidos pendientes por rango montos -->
<?php endif; ?>
<?php if (!empty($boletas_honorarios_resumen)) : ?>
        <!-- boletas de honorarios -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-user-tie fa-fw"></i> Boletas de honorarios</div>
                    <div class="card-body text-center">
                        <span class="lead">
<?php if ($boletas_honorarios_resumen['cantidad']==1) : ?>
                            Una <small>boleta de honorarios por</small> $<?=num($boletas_honorarios_resumen['honorarios'])?>.-<br/>
<?php else : ?>
                            <?=num($boletas_honorarios_resumen['cantidad'])?> <small>boletas de honorarios por</small> $<?=num($boletas_honorarios_resumen['honorarios'])?>.-<br/>
<?php endif; ?>
                        </span>
                        <span class="small"><a href="<?=$_base?>/dte/boleta_honorarios/ver/<?=$periodo?>">ver detalle</a></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin boletas de honorarios -->
<?php endif; ?>
<?php if (!empty($boletas_terceros_resumen)) : ?>
        <!-- boletas de terceros -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-user-secret fa-fw"></i> Boletas de terceros</div>
                    <div class="card-body text-center">
                        <span class="lead">
<?php if ($boletas_terceros_resumen['cantidad']==1) : ?>
                            Una <small>boleta de terceros por</small> $<?=num($boletas_terceros_resumen['honorarios'])?>.-<br/>
<?php else : ?>
                            <?=num($boletas_terceros_resumen['cantidad'])?> <small>boletas de terceros por</small> $<?=num($boletas_terceros_resumen['honorarios'])?>.-<br/>
<?php endif; ?>
                        </span>
                        <span class="small"><a href="<?=$_base?>/dte/boleta_terceros/ver/<?=$periodo?>">ver detalle</a></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin boletas de terceros  -->
<?php endif; ?>
        <!-- folios disponibles -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-file-code fa-fw"></i>
                Folios disponibles
                <a href="admin/dte_folios" class="float-right" title="Ir al mantenedor de folios">
                    <i class="fa fa-cogs fa-fw"></i>
                </a>
            </div>
            <div class="card-body">
<?php if ($folios) : ?>
<?php foreach ($folios as $label => $value) : ?>
                <span><?=$label?></span>
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$value?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$value?>%;">
                        <?=$value?>%
                    </div>
                </div>
<?php endforeach; ?>
<?php else : ?>
                <a href="<?=$_base?>/dte/admin/dte_folios/agregar" class="btn btn-primary btn-block btn-sm">Crear mantenedor de folio</a>
<?php endif; ?>
            </div>
        </div>
        <!-- fin folios disponibles -->
        <!-- firma electrónica -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-certificate fa-fw"></i>
                Firma electrónica
                <a href="admin/firma_electronicas" class="float-right" title="Ir al mantenedor de firmas electrónicas">
                    <i class="fa fa-cogs fa-fw"></i>
                </a>
            </div>
            <div class="card-body">
<?php if ($Firma) : ?>
                <p><?=$Firma->getName()?></p>
                <span class="float-right text-muted small"><em><?=$Firma->getID()?></em></span>
<?php else: ?>
                <p>No hay firma asociada al usuario ni a la empresa</p>
<?php endif; ?>
            </div>
        </div>
        <!-- firma electrónica -->
        <a class="btn btn-success btn-lg btn-block" href="admin/respaldos/exportar/all" role="button">
            <span class="fa fa-download"> Generar respaldo
        </a>
    </div>
    <!-- FIN PANEL DERECHA -->
</div>

<script>
<?php if ($ventas_periodo) : ?>
Morris.Donut({
    element: 'grafico-ventas',
    data: <?=json_encode($ventas_periodo)?>,
    resize: true
});
<?php endif; ?>
<?php if ($compras_periodo) : ?>
Morris.Donut({
    element: 'grafico-compras',
    data: <?=json_encode($compras_periodo)?>,
    resize: true
});
<?php endif; ?>
<?php if ($registro_compra_pendientes_dias_grafico) : ?>
Morris.Line({
    element: 'grafico-registro_compra_pendientes_dias',
    data: <?=json_encode($registro_compra_pendientes_dias_grafico)?>,
    xkey: 'dia',
    ykeys: ['recepcion_sii', 'aceptacion_automatica'],
    labels: ['Recepción SII', 'Aceptación automática'],
    xLabels: 'day',
    events: ['<?=date('Y-m-d')?>'],
    eventLineColors: ['#f00'],
    resize: true,
    xLabelAngle: 45,
    lineColors: ['#16496f', '#e63e52']
});
<?php endif; ?>
<?php if ($emitidos_estados) : ?>
Morris.Bar({
    element: 'grafico-dte_emitidos_estados',
    data: <?=json_encode($emitidos_estados)?>,
    xkey: 'estado',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
<?php endif; ?>
<?php if ($emitidos_eventos) : ?>
Morris.Bar({
    element: 'grafico-dte_emitidos_eventos',
    data: <?=json_encode($emitidos_eventos)?>,
    xkey: 'evento',
    ykeys: ['total'],
    labels: ['DTEs'],
    resize: true
});
<?php endif; ?>
<?php if ($rcof_estados) : ?>
Morris.Bar({
    element: 'grafico-rcof_estados',
    data: <?=json_encode($rcof_estados)?>,
    xkey: 'estado',
    ykeys: ['total'],
    labels: ['RCOFs'],
    resize: true
});
<?php endif; ?>
</script>
