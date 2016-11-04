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
namespace website\Dte\Admin;

/**
 * Modelo para generar respaldo de datos de un contribuyente
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-11-04
 */
class Model_Respaldo
{

    private $tablas = [
        'cobranza' => [
            'rut' => 'emisor',
        ],
        'contribuyente' => [
            'rut' => 'rut',
        ],
        'contribuyente_config' => [
            'rut' => 'contribuyente',
        ],
        'dte_boleta_consumo' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'dte_caf' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
            'encriptar' => ['xml'],
        ],
        'dte_compra' => [
            'rut' => 'receptor',
            'archivos' => ['xml'],
        ],
        'dte_emitido' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'dte_folio' => [
            'rut' => 'emisor',
        ],
        'dte_guia' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'dte_intercambio' => [
            'rut' => 'receptor',
            'archivos' => ['mensaje'=>'txt', 'mensaje_html'=>'html', 'archivo_xml', 'recepcion_xml', 'recibos_xml', 'resultado_xml'],
        ],
        'dte_intercambio_recepcion' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_recepcion_dte' => [
            'rut' => 'emisor',
        ],
        'dte_intercambio_recibo' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_recibo_dte' => [
            'rut' => 'emisor',
        ],
        'dte_intercambio_resultado' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_resultado_dte' => [
            'rut' => 'emisor',
        ],
        'dte_recibido' => [
            'rut' => 'receptor',
        ],
        'dte_referencia' => [
            'rut' => 'emisor',
        ],
        'dte_venta' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'item' => [
            'rut' => 'contribuyente',
        ],
        'item_clasificacion' => [
            'rut' => 'contribuyente',
        ],
        'lce_asiento' => [
            'rut' => 'contribuyente',
        ],
        'lce_asiento_detalle' => [
            'rut' => 'contribuyente',
        ],
        'lce_cuenta' => [
            'rut' => 'contribuyente',
        ],
        'lce_cuenta_clasificacion' => [
            'rut' => 'contribuyente',
        ],
    ]; ///< Información de las tabla que se exportarán

    /**
     * Constructor del modelo de respaldos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    public function __construct()
    {
        $this->db = &\sowerphp\core\Model_Datasource_Database::get();
    }

    /**
     * Método que entrega el listado de tablas que se podrán respaldar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    public function getTablas()
    {
        $tablas = [];
        foreach ($this->tablas as $tabla => $info) {
            $tablas[] = [$tabla];
        }
        return $tablas;
    }

    /**
     * Método que genera el respaldo
     * @param rut RUT del contribuyente que se desea respaldar
     * @param tablas Arreglo con las tablas a respaldar
     * @return Ruta del directorio donde se dejó el respaldo recién creado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    public function generar($rut, $tablas = [])
    {
        // si no se especificaron tablas se respaldarán todas
        if (!$tablas)
            $tablas = array_keys($this->tablas);
        // crear directorio temporal para respaldos
        $dir = TMP.'/libredte_contribuyente_'.$rut;
        if (file_exists($dir)) {
            \sowerphp\general\Utility_File::rmdir($dir);
        }
        if (file_exists($dir)) {
            throw new \Exception('Directorio de respaldo ya existe');
        }
        mkdir($dir);
        $registros = [];
        // procesar cada tabla
        foreach ($tablas as $tabla) {
            // si la tabla no se puede exportar se omite
            if (!isset($this->tablas[$tabla]))
                continue;
            // obtener datos de la tabla
            $info = $this->tablas[$tabla];
            $datos = $this->db->getTable(
                'SELECT * FROM '.$tabla.' WHERE '.$info['rut'].' = :rut',
                [':rut' => $rut]
            );
            if (empty($datos))
                continue;
            $registros[$tabla] = count($datos);
            // si la tabla es la de configuraciones extras del contribuyente se
            // desencriptan las columnas que corresponden
            if ($tabla=='contribuyente_config') {
                foreach ($datos as &$config) {
                    $key = $config['configuracion'].'_'.$config['variable'];
                    if (in_array($key, \website\Dte\Model_Contribuyente::$encriptar)) {
                        $config['valor'] = \website\Dte\Utility_Data::decrypt($config['valor']);
                    }
                }
            }
            // si hay que desencriptar datos se hace
            if (isset($info['encriptar'])) {
                foreach ($datos as &$row) {
                    foreach ($info['encriptar'] as $col) {
                        $row[$col] = trim(\website\Dte\Utility_Data::decrypt($row[$col]));
                    }
                }
            }
            // transformar booleanos a números
            foreach ($datos as &$row) {
                foreach ($row as &$value) {
                    if (is_bool($value)) {
                        $value = $value===true ? 1 : 0;
                    }
                }
            }
            // procesar archivos
            if (isset($info['archivos'])) {
                $pks = $this->db->getPksFromTable($tabla);
                foreach ($datos as &$row) {
                    foreach ($info['archivos'] as $col => $ext) {
                        if (is_numeric($col)) {
                            $col = $ext;
                            $ext = 'xml';
                        }
                        // recuperar el archivo si está en base64 (o sea no está encriptado)
                        if (!isset($info['encriptar']) or !in_array($col, $info['encriptar'])) {
                            $row[$col] = base64_decode($row[$col]);
                        }
                        if (!empty($row[$col])) {
                            // nombre del archivo
                            $archivo = [];
                            foreach ($pks as $pk) {
                                $archivo[] = $row[$pk];
                            }
                            $archivo = implode('_', $archivo).'-'.$tabla.'-'.$col.'.'.$ext;
                            // guardar archivo
                            if (!file_exists($dir.'/'.$tabla)) {
                                mkdir($dir.'/'.$tabla);
                            }
                            file_put_contents($dir.'/'.$tabla.'/'.$archivo, $row[$col]);
                        }
                        // quitar columna de los datos
                        unset($row[$col]);
                    }
                }
            }
            // guardar datos de la tabla
            array_unshift($datos, array_keys($datos[0]));
            \sowerphp\general\Utility_Spreadsheet_CSV::save($datos, $dir.'/'.$tabla.'.csv');
        }
        // copiar logo si existe
        $logos = \sowerphp\core\Configure::read('dte.logos.dir');
        if (file_exists($logos.'/'.$rut.'.png')) {
            copy($logos.'/'.$rut.'.png', $dir.'/'.$rut.'.png');
        }
        // colocar información del respaldo realizado
        $msg = 'Respaldo de datos de LibreDTE'."\n";
        $msg .= '============================='."\n\n";
        $msg .= '- Contribuyente: '.$rut."\n";
        $msg .= '- Fecha y hora del respaldo: '.date('Y-m-d H:i:s')."\n\n";
        $msg .= 'Registros'."\n";
        $msg .= '---------'."\n\n";
        $total = 0;
        foreach ($registros as $tabla => $cantidad) {
            $total += $cantidad;
            $msg .= '- '.$tabla.': '.num($cantidad)."\n";
        }
        $msg .= "\n".'Total de registros: '.num($total)."\n\n\n\n";
        $msg .= "\n".'LibreDTE ¡facturación electrónica libre para Chile!'."\n";
        file_put_contents($dir.'/README.md', $msg);
        // entregar directorio
        return $dir;
    }

}
