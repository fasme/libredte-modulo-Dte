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
     * @version 2019-10-05
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        // contadores
        $periodo_actual = date('Ym');
        $periodo = !empty($_GET['periodo']) ? (int)$_GET['periodo'] : $periodo_actual;
        $periodo_anterior = \sowerphp\general\Utility_Date::previousPeriod($periodo);
        $periodo_siguiente = \sowerphp\general\Utility_Date::nextPeriod($periodo);
        $desde = \sowerphp\general\Utility_Date::normalize($periodo.'01');
        $hasta = \sowerphp\general\Utility_Date::lastDayPeriod($periodo);
        $n_temporales = (new Model_DteTmps())->setContribuyente($Emisor)->getTotal();
        $n_emitidos = $Emisor->countVentas($periodo);
        $n_recibidos = $Emisor->countCompras($periodo);
        $n_intercambios = (new Model_DteIntercambios())->setContribuyente($Emisor)->getTotalPendientes();
        $documentos_rechazados = (new Model_DteEmitidos())->setContribuyente($Emisor)->getTotalRechazados();
        $rcof_rechazados = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getTotalRechazados();
        // valores para cuota
        $cuota = $Emisor->getCuota();
        $n_dtes = $Emisor->getTotalDocumentosUsadosPeriodo($periodo);
        // libros pendientes de enviar del período anterior
        $libro_ventas_existe = (new Model_DteVentas())->setContribuyente($Emisor)->libroGenerado($periodo_anterior);
        $libro_compras_existe = (new Model_DteCompras())->setContribuyente($Emisor)->libroGenerado($periodo_anterior);
        // ventas
        $ventas_periodo_aux = $Emisor->getVentasPorTipo($periodo);
        $ventas_periodo = [];
        foreach ($ventas_periodo_aux as $vt) {
            $ventas_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $vt['tipo']),
                'value' => $vt['documentos'],
            ];
        }
        // compras
        $compras_periodo_aux = $Emisor->getComprasPorTipo($periodo);
        $compras_periodo = [];
        foreach ($compras_periodo_aux as $vc) {
            $compras_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $vc['tipo']),
                'value' => $vc['documentos'],
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
        $emitidos_eventos = $Emisor->getDocumentosEmitidosResumenEventos($desde, $hasta);
        $n_emitidos_reclamados = 0;
        foreach ($emitidos_eventos as $evento) {
            if ($evento['evento']=='R') {
                $n_emitidos_reclamados = $evento['total'];
            }
        }
        $rcof_estados = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getResumenEstados($desde, $hasta);
        // pendientes de procesar en registro de compra
        $RegistroCompras = (new Model_RegistroCompras())->setContribuyente($Emisor);
        $n_registro_compra_pendientes = 0;
        $registro_compra_pendientes = $RegistroCompras->getResumenPendientes();
        foreach ($registro_compra_pendientes as $p) {
            $n_registro_compra_pendientes += $p['cantidad'];
        }
        $registro_compra_pendientes_dias = $RegistroCompras->getByDias();
        $registro_compra_pendientes_dias_grafico = [];
        foreach ($registro_compra_pendientes_dias as $p) {
            // fecha recepción sii
            if (empty($registro_compra_pendientes_dias_grafico[$p['fecha_recepcion_sii']])) {
                $registro_compra_pendientes_dias_grafico[$p['fecha_recepcion_sii']] = [
                    'dia' => $p['fecha_recepcion_sii'],
                    'recepcion_sii' => null,
                    'aceptacion_automatica' => null,
                ];
            }
            // fecha aceptación automática
            if (empty($registro_compra_pendientes_dias_grafico[$p['fecha_aceptacion_automatica']])) {
                $registro_compra_pendientes_dias_grafico[$p['fecha_aceptacion_automatica']] = [
                    'dia' => $p['fecha_aceptacion_automatica'],
                    'recepcion_sii' => null,
                    'aceptacion_automatica' => null,
                ];
            }
            // agregar registros
            $registro_compra_pendientes_dias_grafico[$p['fecha_recepcion_sii']]['recepcion_sii'] += $p['cantidad'];
            $registro_compra_pendientes_dias_grafico[$p['fecha_aceptacion_automatica']]['aceptacion_automatica'] += $p['cantidad'];
        }
        ksort($registro_compra_pendientes_dias_grafico);
        $registro_compra_pendientes_dias_grafico = array_values($registro_compra_pendientes_dias_grafico);
        // boletas honorarios
        $BoletaHonorarios = new Model_BoletaHonorarios();
        $boletas_honorarios_resumen = $BoletaHonorarios->getPeriodo($periodo);
        // boletas terceros
        $BoletaTerceros = new Model_BoletaTerceros();
        $boletas_terceros_resumen = $BoletaTerceros->getPeriodo($periodo);
        // asignar variables a la vista
        $this->set([
            'nav' => array_slice(\sowerphp\core\Configure::read('nav.module'), 1),
            'Emisor' => $Emisor,
            'Firma' => $Emisor->getFirma($this->Auth->User->id),
            'periodo_actual' => $periodo_actual,
            'periodo' => $periodo,
            'periodo_anterior' => $periodo_anterior,
            'periodo_siguiente' => $periodo_siguiente,
            'desde' => $desde,
            'hasta' => $hasta,
            'n_temporales' => $n_temporales,
            'n_emitidos' => $n_emitidos,
            'n_recibidos' => $n_recibidos,
            'n_intercambios' => $n_intercambios,
            'libro_ventas_existe' => $libro_ventas_existe,
            'libro_compras_existe' => $libro_compras_existe,
            'propuesta_f29' => ($libro_ventas_existe and $libro_compras_existe and (date('d')<=20 or ($periodo < $periodo_actual))),
            'ventas_periodo' => $ventas_periodo,
            'compras_periodo' => $compras_periodo,
            'folios' => $folios,
            'n_dtes' => $n_dtes,
            'cuota' => $cuota,
            'emitidos_estados' => $emitidos_estados,
            'emitidos_eventos' => $emitidos_eventos,
            'n_emitidos_reclamados' => $n_emitidos_reclamados,
            'documentos_rechazados' => $documentos_rechazados,
            'rcof_rechazados' => $rcof_rechazados,
            'rcof_estados' => $rcof_estados,
            'registro_compra_pendientes' => $registro_compra_pendientes,
            'registro_compra_pendientes_dias' => $registro_compra_pendientes_dias,
            'registro_compra_pendientes_dias_grafico' => $registro_compra_pendientes_dias_grafico,
            'registro_compra_pendientes_rango_montos' => $RegistroCompras->getByRangoMontos(),
            'n_registro_compra_pendientes' => $n_registro_compra_pendientes,
            'boletas_honorarios_resumen' => $boletas_honorarios_resumen,
            'boletas_terceros_resumen' => $boletas_terceros_resumen,
        ]);
    }

}
