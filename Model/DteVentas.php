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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_venta de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_venta
 * @author SowerPHP Code Generator
 * @version 2015-09-25 20:05:11
 */
class Model_DteVentas extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_venta'; ///< Tabla del modelo

    /**
     * Método que entrega el total mensual del libro de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-25
     */
    public function getTotalesMensuales($anio)
    {
        $periodo_actual = date('Ym');
        $periodo = $anio.'01';
        $totales_mensuales = [];
        for ($i=0; $i<12; $i++) {
            if ($periodo>$periodo_actual) {
                break;
            }
            $totales_mensuales[$periodo] = array_merge(
                ['periodo'=>$periodo],
                (new Model_DteVenta($this->getContribuyente()->rut, $periodo, $this->getContribuyente()->config_ambiente_en_certificacion))->getTotales()
            );
            $periodo = \sowerphp\general\Utility_Date::nextPeriod($periodo);
        }
        return $totales_mensuales;
    }

    /**
     * Método que entrega el resumen anual de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-25
     */
    public function getResumenAnual($anio)
    {
        $libros = [];
        foreach (range(1,12) as $mes) {
            $mes = $mes < 10 ? '0'.$mes : $mes;
            $DteVenta = new Model_DteVenta($this->getContribuyente()->rut, (int)($anio.$mes), (int)$this->getContribuyente()->config_ambiente_en_certificacion);
            $resumen = $DteVenta->getResumen();
            if ($resumen) {
                $libros[$anio][$mes] = $resumen;
            }
        }
        // ir sumando en el resumen anual
        $resumen = [];
        foreach($libros[$anio] as $mes => $resumen_mensual) {
            foreach ($resumen_mensual as $r) {
                $cols = array_keys($r);
                unset($cols[array_search('TpoDoc',$cols)]);
                if (!isset($resumen[$r['TpoDoc']])) {
                    $resumen[$r['TpoDoc']] = ['TpoDoc' => $r['TpoDoc']];
                    foreach ($cols as $col) {
                        $resumen[$r['TpoDoc']][$col] = 0;
                    }
                }
                foreach ($cols as $col) {
                    $resumen[$r['TpoDoc']][$col] += $r[$col];
                }
            }
        }
        ksort($resumen);
        return $resumen;
    }

}
