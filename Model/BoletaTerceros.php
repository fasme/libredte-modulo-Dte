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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla boleta_tercero de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla boleta_tercero
 * @author SowerPHP Code Generator
 * @version 2019-08-09 15:59:48
 */
class Model_BoletaTerceros extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'boleta_tercero'; ///< Tabla del modelo

    /**
     * Método que sincroniza las boletas de terceros recibidas por la empresa
     * en el SII con el registro local de boletas en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-23
     */
    public function sincronizar($meses)
    {
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $boletas = $this->getBoletas($periodo);
            foreach ($boletas as $boleta) {
                $Receptor = new Model_Contribuyente(explode('-', $boleta['receptor_rut'])[0]);
                if (!$Receptor->razon_social) {
                    $Receptor->razon_social = $boleta['receptor_nombre'];
                    $Receptor->save();
                }
                $BoletaTercero = new Model_BoletaTercero($this->getContribuyente()->rut, $boleta['numero']);
                $BoletaTercero->receptor = $Receptor->rut;
                $BoletaTercero->anulada = (int)($boleta['estado'] == 'ANUL');
                $BoletaTercero->set($boleta);
                $BoletaTercero->save();
            }
        }
    }

    /**
     * Método que obtiene las boletas emitidas desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function getBoletas($periodo)
    {
        $r = libredte_consume('/sii/boletas_terceros_emitidas/'.$this->getContribuyente()->getRUT().'/'.$periodo.'?formato=json', [
            'auth'=>[
                'rut' => $this->getContribuyente()->getRUT(),
                'clave' => $this->getContribuyente()->config_sii_pass,
            ],
        ]);
        if ($r['status']['code']!=200) {
            if ($r['status']['code']==404) {
                return [];
            }
            throw new \Exception('Error al obtener boletas de terceros del período '.(int)$periodo.' desde el SII: '.$r['body'], $r['status']['code']);
        }
        return $r['body'];
    }

    /**
     * Método que entrega un resumen por período de las boletas de terceros
     * emitidas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-10
     */
    public function getPeriodos($periodo = null)
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        $where = ['emisor = :emisor', 'anulada = false'];
        $vars = [':emisor'=>$this->getContribuyente()->rut];
        if ($periodo) {
            $where[] = $periodo_col.' = :periodo';
            $vars[':periodo'] = $periodo;
        }
        return $this->db->getTable('
            SELECT
                '.$periodo_col.' AS periodo,
                COUNT(*) AS cantidad,
                MIN(fecha) AS fecha_inicial,
                MAX(fecha) AS fecha_final,
                SUM(total_honorarios) AS honorarios,
                SUM(total_liquido) AS liquido,
                SUM(total_retencion) AS retencion
            FROM boleta_tercero
            WHERE '.implode(' AND ', $where).'
            GROUP BY '.$periodo_col.'
            ORDER BY '.$periodo_col.' DESC
        ', $vars);
    }

    /**
     * Método que entrega el resumen de cierto período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-10
     */
    public function getPeriodo($periodo)
    {
        $datos = $this->getPeriodos($periodo);
        return !empty($datos) ? $datos[0] : [];
    }

    /**
     * Método que entrega las boletas de cierto período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-23
     */
    public function buscar(array $filtros = [], $order = 'ASC')
    {
        $where = ['b.emisor = :emisor'];
        $vars = [':emisor'=>$this->getContribuyente()->rut];
        if (!empty($filtros['periodo'])) {
            $periodo_col = $this->db->date('Ym', 'b.fecha');
            $where[] = $periodo_col.' = :periodo';
            $vars[':periodo'] = $filtros['periodo'];
        }
        if (!empty($filtros['receptor'])) {
            if (strpos($filtros['receptor'], '-')) {
                list($rut, $dv) = explode('-', str_replace('.', '', $filtros['receptor']));
            } else {
                $rut = (int)$filtros['receptor'];
            }
            $where[] = 'b.receptor = :receptor';
            $vars[':receptor'] = $rut;
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'b.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'b.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['honorarios_desde'])) {
            $where[] = 'b.total_honorarios >= :honorarios_desde';
            $vars[':honorarios_desde'] = $filtros['honorarios_desde'];
        }
        if (!empty($filtros['honorarios_hasta'])) {
            $where[] = 'b.total_honorarios <= :honorarios_hasta';
            $vars[':honorarios_hasta'] = $filtros['honorarios_hasta'];
        }
        if (isset($filtros['anulada'])) {
            if ($filtros['anulada']) {
                $where[] = 'b.anulada = true';
            } else {
                $where[] = 'b.anulada = false';
            }
        }
        if (isset($filtros['sucursal_sii']) and is_numeric($filtros['sucursal_sii'])) {
            if ($filtros['sucursal_sii']) {
                $where[] = 'b.sucursal_sii = :sucursal_sii';
                $vars[':sucursal_sii'] = $filtros['sucursal_sii'];
            } else {
                $where[] = 'b.sucursal_sii IS NULL';
            }
        }
        $boletas = $this->db->getTable('
            SELECT
                b.codigo,
                b.receptor AS receptor_rut,
                c.dv AS receptor_dv,
                c.razon_social AS receptor_razon_social,
                b.numero,
                b.fecha,
                b.fecha_emision,
                b.total_honorarios AS honorarios,
                b.total_liquido AS liquido,
                b.total_retencion AS retencion,
                b.anulada,
                b.sucursal_sii
            FROM
                boleta_tercero AS b
                LEFT JOIN contribuyente AS c ON c.rut = b.receptor
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY b.fecha '.$order.', b.numero '.$order.'
        ', $vars);
        foreach ($boletas as &$b) {
            $b['sucursal'] = $this->getContribuyente()->getSucursal($b['sucursal_sii'])->sucursal;
        }
        return $boletas;
    }

    /**
     * Método que emite una BTE en el SII y entrega el objeto local para trabajar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-23
     */
    public function emitir($boleta)
    {
        // consumir servicio web y emitir boleta
        $r = libredte_consume('/sii/boleta_terceros_emitir', [
            'auth'=>[
                'rut' => $this->getContribuyente()->getRUT(),
                'clave' => $this->getContribuyente()->config_sii_pass,
            ],
            'boleta' => $boleta,
        ]);
        if ($r['status']['code']!=200) {
            throw new \Exception('Error al emitir boleta: '.$r['body'], $r['status']['code']);
        }
        $boleta = $r['body'];
        if (empty($boleta['Encabezado']['IdDoc']['CodigoBarras'])) {
            throw new \Exception('No fue posible emitir la boleta o bien no se pudo obtener el código de barras de la boleta emitida');
        }
        // crear registro de la boleta en la base de datos
        $BoletaTercero = new Model_BoletaTercero();
        $BoletaTercero->emisor = $this->getContribuyente()->rut;
        $BoletaTercero->numero = $boleta['Encabezado']['IdDoc']['Folio'];
        $BoletaTercero->codigo = $boleta['Encabezado']['IdDoc']['CodigoBarras'];
        $BoletaTercero->receptor = explode('-', $boleta['Encabezado']['Receptor']['RUTRecep'])[0];
        $BoletaTercero->fecha = $boleta['Encabezado']['IdDoc']['FchEmis'];
        $BoletaTercero->fecha_emision = date('Y-m-d');
        $BoletaTercero->total_honorarios = $boleta['Encabezado']['Totales']['MntBruto'];
        $BoletaTercero->total_retencion = $boleta['Encabezado']['Totales']['MntRetencion'];
        $BoletaTercero->total_liquido = $boleta['Encabezado']['Totales']['MntNeto'];
        $BoletaTercero->anulada = 0;
        if (!empty($boleta['Encabezado']['Emisor']['CdgSIISucur'])) {
            $BoletaTercero->sucursal_sii = $boleta['Encabezado']['Emisor']['CdgSIISucur'];
        }
        $BoletaTercero->save();
        // guardar datos del receptor si es posible
        $Receptor = $BoletaTercero->getReceptor();
        if (!$Receptor->usuario) {
            $Receptor->razon_social = mb_substr($boleta['Encabezado']['Receptor']['RznSocRecep'],0,100);
            $Receptor->direccion = mb_substr($boleta['Encabezado']['Receptor']['DirRecep'],0,70);
            $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($boleta['Encabezado']['Receptor']['CmnaRecep']);
            if ($comuna) {
                $Receptor->comuna = $comuna;
            }
            try {
                $Receptor->save();
            } catch (\Exception $e) {
            }
        }
        // entregar boleta emitida
        return $BoletaTercero;
    }

}
