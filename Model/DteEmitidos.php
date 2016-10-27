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
 * Clase para mapear la tabla dte_emitido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_emitido
 * @author SowerPHP Code Generator
 * @version 2015-09-23 11:44:17
 */
class Model_DteEmitidos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    /**
     * Método que entrega el detalle de las ventas en un rango de tiempo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getDetalle($desde, $hasta)
    {
        // datos del xml
        list($razon_social, $nacionalidad, $moneda, $moneda_total, $fecha_hora) = $this->db->xml('e.xml', [
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep',
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad',
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/TpoMoneda',
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/MntTotal',
            '/*/SetDTE/Caratula/TmstFirmaEnv',
        ], 'http://www.sii.cl/SiiDte');
        $razon_social = 'CASE WHEN e.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social.' END AS razon_social';
        // realizar consulta
        $datos = $this->db->getTable('
            SELECT
                t.tipo,
                e.folio,
                e.fecha,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                '.$razon_social.',
                e.exento,
                e.neto,
                e.iva,
                e.total,
                '.$nacionalidad.' AS nacionalidad,
                '.$moneda.' AS moneda,
                '.$moneda_total.' AS moneda_monto,
                e.sucursal_sii AS sucursal,
                i.glosa AS intercambio,
                u.usuario,
                '.$fecha_hora.' AS fecha_hora
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = e.emisor AND i.dte = e.dte AND i.folio = e.folio AND i.certificacion = e.certificacion
                JOIN dte_tipo AS t ON e.dte = t.codigo
                JOIN contribuyente AS r ON e.receptor = r.rut
                JOIN usuario AS u ON e.usuario = u.id
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
        foreach ($datos as &$d) {
            if ($d['nacionalidad']) {
                $d['nacionalidad'] = \sasco\LibreDTE\Sii\Aduana::getNacionalidad($d['nacionalidad']);
            }
            $d['sucursal'] = $this->getContribuyente()->getSucursal($d['sucursal'])->sucursal;
        }
        return $datos;
    }

    /**
     * Método que entrega los totales de documentos emitidos por tipo de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-24
     */
    public function getPorTipo($desde, $hasta)
    {
        return $this->db->getTable('
            SELECT t.tipo, COUNT(*) AS total
            FROM dte_emitido AS e JOIN dte_tipo AS t ON e.dte = t.codigo
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
            GROUP BY t.tipo
            ORDER BY total DESC
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por día
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-24
     */
    public function getPorDia($desde, $hasta)
    {
        return $this->db->getTable('
            SELECT fecha AS dia, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY fecha
            ORDER BY fecha
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por hora
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getPorHora($desde, $hasta)
    {
        $hora = $this->db->xml('xml', '/*/SetDTE/Caratula/TmstFirmaEnv', 'http://www.sii.cl/SiiDte');
        return $this->db->getTable('
            SELECT SUBSTR('.$hora.', 12, 2) AS hora, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY hora
            ORDER BY hora
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por sucursal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-24
     */
    public function getPorSucursal($desde, $hasta)
    {
        $datos = $this->db->getTable('
            SELECT sucursal_sii AS sucursal, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY sucursal
            ORDER BY total DESC
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
        foreach($datos as &$d) {
            $d['sucursal'] = $this->getContribuyente()->getSucursal($d['sucursal'])->sucursal;
        }
        return $datos;
    }

    /**
     * Método que entrega los totales de documentos emitidos por usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-24
     */
    public function getPorUsuario($desde, $hasta)
    {
        return $this->db->getTable('
            SELECT u.usuario, COUNT(*) AS total
            FROM dte_emitido AS e JOIN usuario AS u ON e.usuario = u.id
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
            GROUP BY u.usuario
            ORDER BY total DESC
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por nacionalidad
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getPorNacionalidad($desde, $hasta)
    {
        $nacionalidad = $this->db->xml('xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad', 'http://www.sii.cl/SiiDte');
        $datos = $this->db->getTable('
            SELECT '.$nacionalidad.' AS nacionalidad, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
                AND '.$nacionalidad.' != \'\'
            GROUP BY nacionalidad
            ORDER BY total DESC
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
        foreach ($datos as &$d) {
            $d['nacionalidad'] = \sasco\LibreDTE\Sii\Aduana::getNacionalidad($d['nacionalidad']);
        }
        return $datos;
    }

    /**
     * Método que entrega los totales de documentos emitidos por moneda
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function getPorMoneda($desde, $hasta)
    {
        $moneda = $this->db->xml('xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/TpoMoneda', 'http://www.sii.cl/SiiDte');
        return $this->db->getTable('
            SELECT '.$moneda.' AS moneda, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
                AND '.$moneda.' != \'\'
            GROUP BY moneda
            ORDER BY total DESC
        ', [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por día de todos los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-27
     */
    public function countDiarios($desde, $hasta, $certificacion)
    {
        if (is_numeric($desde)) {
            $desde = date('Y-m-d', strtotime('-'.$desde.' months'));
        }
        if (!$hasta)
            $hasta = date('Y-m-d');
        return $this->db->getTable('
            SELECT fecha AS dia, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
            GROUP BY fecha
            ORDER BY fecha
        ', [':certificacion'=>(int)$certificacion, ':desde'=>$desde, ':hasta'=>$hasta]);
    }

}
