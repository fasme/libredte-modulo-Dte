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
    // si es un archivo
    if ($info['type']=='file') {
        $row[] = '';
    }
    // si es de tipo boolean se muestra lista desplegable
    else if ($info['type']=='boolean' || $info['type']=='tinyint') {
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $optionsBoolean, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es llave foránea
    else if ($info['fk']) {
        $class = 'Model_'.\sowerphp\core\Utility_Inflector::camelize(
            $info['fk']['table']
        );
        $classs = $fkNamespace[$class].'\Model_'.\sowerphp\core\Utility_Inflector::camelize(
            \sowerphp\core\Utility_Inflector::pluralize($info['fk']['table'])
        );
        $objs = new $classs();
        $options = $objs->getList();
        array_unshift($options, array('', 'Seleccione una opción'));
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $options, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es un tipo de dato de fecha o fecha con hora se muestra un input para fecha
    else if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
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
        // si es un archivo
        if ($info['type']=='file') {
            if ($obj->{$column.'_size'})
                $row[] = '<a href="'.$_base.$module_url.$controller.'/d/'.$column.'/'.urlencode($obj->id).'"><i class="fa fa-download"></i></a>';
            else
                $row[] = '';
        }
        // si es boolean se usa Si o No según corresponda
        else if ($info['type']=='boolean' || $info['type']=='tinyint') {
            $row[] = $obj->{$column}=='t' || $obj->{$column}=='1' ? 'Si' : 'No';
        }
        // si es llave foránea
        else if ($info['fk']['table']) {
            // si no es vacía la columna
            if (!empty($obj->{$column})) {
                $method = 'get'.\sowerphp\core\Utility_Inflector::camelize($info['fk']['table']);
                $row[] = $obj->$method($obj->$column)->{$info['fk']['table']};
            } else {
                $row[] = '';
            }
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $obj->{$column};
        }
    }
    $actions = '<a href="'.$_base.$module_url.$controller.'/xml/'.$obj->dia.'" title="Descargar XML" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i></a>';
    $actions .= ' <a href="'.$_base.$module_url.$controller.'/actualizar_estado/'.$obj->dia.$listarFilterUrl.'" title="Actualizar estado del envio al SII" class="btn btn-primary"><i class="fas fa-sync fa-fw"></i></a>';
    $actions .= ' <a href="'.$_base.$module_url.$controller.'/solicitar_revision/'.$obj->dia.$listarFilterUrl.'" title="Solicitar revisión del envio al SII" class="btn btn-primary"><i class="fa fa-eye fa-fw"></i></a>';
    $actions .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/estado_envio/'.$obj->track_id.'\', 750, 550); return false" title="Ver el estado del envío en la web del SII" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    //$actions .= ' <a href="'.$_base.$module_url.$controller.'/enviar_sii/'.$obj->dia.$listarFilterUrl.'" title="Reenviar al SII" onclick="return Form.checkSend(\'¿Confirmar el reenvío del reporte de consumo de folios al SII?\')"><i class="far fa-paper-plane fa-fw"></i></a>';
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
