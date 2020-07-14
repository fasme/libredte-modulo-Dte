<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/pendientes" class="nav-link">
            <i class="fa fa-download"></i> Pendientes
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-search"></i> Filtrar
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:ERRONEO" class="dropdown-item">Rechazados</a>
            <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:REPARO" class="dropdown-item">Con reparo</a>
            <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:CORRECTO" class="dropdown-item">Correctos</a>
            <div class="dropdown-divider"></div>
            <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:null" class="dropdown-item">Sin estado</a>
            <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D" class="dropdown-item">Ver todo</a>
        </div>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boletas" class="nav-link">
            <i class="fa fa-book"></i> Libro de boletas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Reporte consumo de folios boletas</h1></div>
<?php
// preparar títulos de columnas (con link para ordenar por dicho campo)
$titles = [];
$colsWidth = [];
foreach ($columns as $column => $info) {
    $titles[] = $info['name'].' '.
        '<div class="float-right"><a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/A'.$searchUrl.'" title="Ordenar ascendentemente por '.$info['name'].'"><i class="fas fa-sort-alpha-down"></i></a>'.
        ' <a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/D'.$searchUrl.'" title="Ordenar descendentemente por '.$info['name'].'"><i class="fas fa-sort-alpha-up"></i></a></div>'
    ;
    $colsWidth[] = null;
}
$titles[] = 'Acciones';
$colsWidth[] = $actionsColsWidth;

// crear arreglo para la tabla y agregar títulos de columnas
$data = array($titles);

// agregar fila para búsqueda mediante formulario
$row = array();
$form = new \sowerphp\general\View_Helper_Form(false);
$optionsBoolean = array(array('', 'Seleccione una opción'), array('1', 'Si'), array('0', 'No'));
foreach ($columns as $column => &$info) {
    // si es un tipo de dato de fecha o fecha con hora se muestra un input para fecha
    if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
        $row[] = $form->input(array('type'=>'date', 'name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
    // si es cualquier otro tipo de datos
    else {
        $row[] = $form->input(array('name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
}
$row[] = '<button type="submit" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></button>';
$data[] = $row;

// crear filas de la tabla
foreach ($Objs as &$obj) {
    $row = array();
    foreach ($columns as $column => &$info) {
        if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
            $row[] = \sowerphp\general\Utility_Date::format($obj->{$column});
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $obj->{$column};
        }
    }
    $actions = '<div class="btn-group">';
    $actions .= '<a href="'.$_base.$module_url.$controller.'/xml/'.$obj->dia.'" title="Descargar XML" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i> XML</a>';
    $actions .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
    $actions .= '<div class="dropdown-menu dropdown-menu-right">';
    if ($obj->track_id) {
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/actualizar_estado/'.$obj->dia.$listarFilterUrl.'" title="Actualizar estado del envio al SII" class="dropdown-item" onclick="return Form.loading(\'Actualizando estado del RCOF...\')"><i class="fas fa-sync fa-fw mr-2"></i> Actualizar estado</a>';
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/solicitar_revision/'.$obj->dia.$listarFilterUrl.'" title="Solicitar revisión del envio al SII" class="dropdown-item" onclick="return Form.loading(\'Solicitando revisión del envío al SII...\')"><i class="fab fa-rev fa-fw mr-2"></i> Solicitar revisión</a>';
        $actions .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/estado_envio/'.$obj->track_id.'\', 750, 550); return false" title="Ver el estado del envío en la web del SII" class="dropdown-item"><i class="fa fa-eye fa-fw mr-2"></i> Ver estado en SII</a>';
    }
    if ($is_admin) {
        $actions .= '<div class="dropdown-divider"></div>';
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/eliminar/'.$obj->dia.$listarFilterUrl.'" title="Eliminar el RCOF" class="dropdown-item" onclick="return Form.confirm(this, \'¿Desea eliminar el RCOF del día '.\sowerphp\general\Utility_Date::format($obj->dia).'?<br/><br/><strong>Importante</strong>: Esto no lo eliminará del SII si fue aceptado\')"><i class="fas fa-times fa-fw mr-2"></i> Eliminar RCOF</a>';
    }
    $actions .= '</div>';
    $actions .= '</div>';
    $row[] = $actions;
    $data[] = $row;
}

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer ([
    'link' => $_base.$module_url.$controller,
    'linkEnd' => $linkEnd,
    'listarFilterUrl' => $listarFilterUrl
]);
$maintainer->setId($models);
$maintainer->setColsWidth($colsWidth);
echo $maintainer->listar ($data, $pages, $page);
