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
 * Clase para mapear la tabla dte_recibido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_recibido
 * @author SowerPHP Code Generator
 * @version 2015-09-27 19:27:12
 */
class Model_DteRecibidos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_recibido'; ///< Tabla del modelo

    /**
     * Método que entrega el listado de documentos que tienen compras de
     * activos fijos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-03
     */
    public function getActivosFijos($filtros)
    {
        if (empty($filtros['desde']) or empty($filtros['hasta'])) {
            return false;
        }
        $where = ['r.fecha BETWEEN :desde AND :hasta'];
        $vars = [
                ':receptor' => $this->getContribuyente()->rut,
                ':certificacion' => (int)$this->getContribuyente()->config_ambiente_en_certificacion,
                ':desde' => $filtros['desde'],
                ':hasta' => $filtros['hasta'],
        ];
        if (isset($filtros['sucursal'])) {
            if ($filtros['sucursal']) {
                $where[] = 'r.sucursal_sii_receptor IS NULL';
                $vars[':sucursal'] = $filtros['sucursal'];
            } else {
                $where[] = 'r.sucursal_sii_receptor IS NULL';
            }
        }
        list($items, $precios) = $this->db->xml('i.archivo_xml', [
            '/*/SetDTE/DTE/*/Detalle/NmbItem',
            '/*/SetDTE/DTE/*/Detalle/PrcItem',
        ], 'http://www.sii.cl/SiiDte');
        $recibidos = $this->db->getTable('
            SELECT
                r.fecha,
                r.sucursal_sii_receptor AS sucursal,
                e.razon_social,
                r.emisor,
                r.intercambio,
                r.dte,
                t.tipo AS documento,
                r.folio,
                r.neto,
                r.monto_activo_fijo,
                CASE WHEN r.neto = r.monto_activo_fijo THEN \'Total\' ELSE \'Parcial\' END AS montos,
                CASE WHEN r.intercambio IS NOT NULL THEN '.$items.' ELSE NULL END AS items,
                CASE WHEN r.intercambio IS NOT NULL THEN '.$precios.' ELSE NULL END AS precios
            FROM
                dte_recibido AS r
                JOIN contribuyente AS e ON r.emisor = e.rut
                JOIN dte_tipo AS t ON t.codigo = r.dte
                LEFT JOIN dte_intercambio AS i ON i.receptor = r.receptor AND i.codigo = r.intercambio AND i.certificacion = r.certificacion
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND '.implode(' AND ', $where).'
                AND r.monto_activo_fijo IS NOT NULL
            ORDER BY r.fecha, r.sucursal_sii_receptor, r.emisor, r.folio
        ', $vars);
        foreach ($recibidos as &$f) {
            $f['sucursal'] = $this->getContribuyente()->getSucursal($f['sucursal'])->sucursal;
            if ($f['items']) {
                $f['items'] = explode('","', utf8_decode($f['items']));
                $f['precios'] = explode('","', utf8_decode($f['precios']));
            } else {
                $f['items'] = $f['precios'] = [];
            }
        }
        return $recibidos;
    }

    /**
     * Método que busca en los documentos recibidos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-11
     */
    public function buscar($filtros)
    {
        // determinar receptor, fecha desde y hasta para la busqueda
        if (!empty($filtros['fecha'])) {
            $fecha_desde = $fecha_hasta = $filtros['fecha'];
        } else if (!empty($filtros['fecha_desde']) and !empty($filtros['fecha_hasta'])) {
            $fecha_desde = $filtros['fecha_desde'];
            $fecha_hasta = $filtros['fecha_hasta'];
        }
        if (empty($fecha_desde) or empty($fecha_hasta)) {
            throw new \Exception('Debe indicar una fecha o un rango para la búsqueda');
        }
        $where = ['d.receptor = :receptor', 'd.fecha BETWEEN :fecha_desde AND :fecha_hasta'];
        $vars = [':receptor'=>$this->getContribuyente()->rut, ':fecha_desde'=>$fecha_desde, ':fecha_hasta'=>$fecha_hasta];
        // filtro emisor
        if (!empty($filtros['emisor'])) {
            $where[] = 'd.emisor = :emisor';
            $vars[':emisor'] = $filtros['emisor'];
        }
        // filtro dte
        if (!empty($filtros['dte'])) {
            $where[] = 'd.dte = :dte';
            $vars[':dte'] = $filtros['dte'];
        }
        // filtro total
        if (!empty($filtros['total'])) {
            $where[] = 'd.total = :total';
            $vars[':total'] = $filtros['total'];
        } else if (!empty($filtros['total_desde']) and !empty($filtros['total_hasta'])) {
            $where[] = 'd.total BETWEEN :total_desde AND :total_hasta';
            $vars[':total_desde'] = $filtros['total_desde'];
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        // realizar consultar
        return $this->db->getTable('
            SELECT
                d.fecha,
                d.emisor,
                e.razon_social,
                d.dte,
                d.folio,
                d.sucursal_sii_receptor AS sucursal,
                d.exento,
                d.neto,
                d.total
            FROM
                dte_recibido AS d
                JOIN contribuyente AS e ON d.emisor = e.rut
            WHERE '.implode(' AND ', $where).'
            ORDER BY d.fecha, d.emisor, d.dte, d.folio
        ', $vars);
    }

}
