<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla registro_compra de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla registro_compra
 * @author SowerPHP Code Generator
 * @version 2019-08-09 13:54:15
 */
class Controller_RegistroCompras extends \Controller_App
{

    /**
     * Acción para mostrar los documentos recibidos en SII con estado pendientes
     * de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function pendientes()
    {
        $filtros = array_merge($this->getQuery([
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]);
        $Receptor = $this->getContribuyente();
        $pendientes = (new Model_RegistroCompras())->setContribuyente($Receptor)->buscar($filtros);
        if (!$pendientes) {
            \sowerphp\core\Model_Datasource_Session::message('No hay documentos recibidos pendientes en SII');
            $this->redirect('/dte');
        }
        $this->set([
            'filtros' => $filtros,
            'pendientes' => $pendientes,
        ]);
    }

    /**
     * Acción para generar un CSV con los documentos recibidos en SII con
     * estado pendientes de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function pendientes_csv()
    {
        $filtros = array_merge($this->getQuery([
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]);
        $Receptor = $this->getContribuyente();
        $pendientes = (new Model_RegistroCompras())->setContribuyente($Receptor)->getDetalle($filtros);
        if (!$pendientes) {
            \sowerphp\core\Model_Datasource_Session::message('No hay documentos recibidos pendientes en SII');
            $this->redirect('/dte');
        }
        array_unshift($pendientes, array_keys($pendientes[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($pendientes);
        $this->response->sendContent($csv, $Receptor->rut.'-'.$Receptor->dv.'_recibidos_pendientes.csv');
    }

}
