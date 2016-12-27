<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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
 * Clase para el Dashboard del módulo de facturación
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-02
 */
class Controller_Dashboard extends \Controller_App
{

    /**
     * Acción principal que muestra el dashboard
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-27
     */
    public function index()
    {
        $periodo = date('Ym');
        $periodo_anterior = \sowerphp\general\Utility_Date::previousPeriod($periodo);
        $Emisor = $this->getContribuyente();
        // contadores
        $desde = date('Y-m-01');
        $hasta = date('Y-m-d');
        $n_temporales = (new Model_DteTmps())->setWhereStatement(['emisor = :emisor'], [':emisor'=>$Emisor->rut])->count();
        $n_emitidos = (new Model_DteEmitidos())->setWhereStatement(['emisor = :emisor', 'certificacion = :certificacion', 'fecha BETWEEN :desde AND :hasta', 'dte NOT IN (46, 52)'], [':emisor'=>$Emisor->rut, ':certificacion'=>$Emisor->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta])->count();
        $n_recibidos = (new Model_DteRecibidos())->setWhereStatement(['receptor = :receptor', 'certificacion = :certificacion', 'fecha BETWEEN :desde AND :hasta', 'dte != 52'], [':receptor'=>$Emisor->rut, ':certificacion'=>$Emisor->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta])->count();
        $n_recibidos += (new Model_DteEmitidos())->setWhereStatement(['emisor = :emisor', 'certificacion = :certificacion', 'fecha BETWEEN :desde AND :hasta', 'dte = 46'], [':emisor'=>$Emisor->rut, ':certificacion'=>$Emisor->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta])->count();
        $n_intercambios = (new Model_DteIntercambios())->setWhereStatement(['receptor = :receptor', 'certificacion = :certificacion', 'usuario IS NULL'], [':receptor'=>$Emisor->rut, ':certificacion'=>$Emisor->config_ambiente_en_certificacion])->count();
        // valores para cuota
        $cuota = $Emisor->getCuota();
        $n_dtes = $cuota ? $Emisor->getTotalDocumentosUsadosPeriodo() : false;
        // libros pendientes de enviar del período anterior
        $libro_ventas = (new Model_DteVentas())->setWhereStatement(['emisor = :emisor', 'periodo = :periodo', 'certificacion = :certificacion', 'track_id IS NOT NULL'], [':emisor'=>$Emisor->rut, ':periodo'=>$periodo_anterior, ':certificacion'=>$Emisor->config_ambiente_en_certificacion])->count();
        $libro_compras = (new Model_DteCompras())->setWhereStatement(['receptor = :receptor', 'periodo = :periodo', 'certificacion = :certificacion', 'track_id IS NOT NULL'], [':receptor'=>$Emisor->rut, ':periodo'=>$periodo_anterior, ':certificacion'=>$Emisor->config_ambiente_en_certificacion])->count();
        // ventas
        $ventas_periodo_aux = $Emisor->getVentasPorTipo($periodo);
        $ventas_periodo = [];
        foreach ($ventas_periodo_aux as $label => $value) {
            $ventas_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $label),
                'value' => $value,
            ];
        }
        // compras
        $compras_periodo_aux = $Emisor->getComprasPorTipo($periodo);
        $compras_periodo = [];
        foreach ($compras_periodo_aux as $label => $value) {
            $compras_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $label),
                'value' => $value,
            ];
        }
        // folios
        $folios_aux = $Emisor->getFolios();
        $folios = [];
        foreach ($folios_aux as $f) {
            if (!$f['alerta'])
                $f['alerta'] = 1;
            $folios[$f['tipo']] = $f['disponibles'] ? round((1-($f['alerta']/$f['disponibles']))*100) : 0;
        }
        // estados de documentos emitidos del periodo
        $emitidos_estados = $Emisor->getDocumentosEmitidosResumenEstados($desde, $hasta);
        // asignar variables a la vista
        $this->set([
            'nav' => array_slice(\sowerphp\core\Configure::read('nav.module'), 1),
            'Emisor' => $Emisor,
            'Firma' => $Emisor->getFirma($this->Auth->User->id),
            'periodo' => $periodo,
            'periodo_anterior' => $periodo_anterior,
            'desde' => $desde,
            'hasta' => $hasta,
            'n_temporales' => $n_temporales,
            'n_emitidos' => $n_emitidos,
            'n_recibidos' => $n_recibidos,
            'n_intercambios' => $n_intercambios,
            'libro_ventas' => $libro_ventas,
            'libro_compras' => $libro_compras,
            'propuesta_f29' => $libro_ventas and $libro_compras and date('d')<=7,
            'ventas_periodo' => $ventas_periodo,
            'compras_periodo' => $compras_periodo,
            'folios' => $folios,
            'n_dtes' => $n_dtes,
            'cuota' => $cuota,
            'emitidos_estados' => $emitidos_estados,
        ]);
    }

}
