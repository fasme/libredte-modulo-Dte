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

namespace website\Dte;

/**
 * Comando para actualizar los contribuyentes desde el SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-04
 */
class Shell_Command_Contribuyentes_Actualizar extends \Shell_App
{

    /**
     * Método principal del comando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-12
     */
    public function main($opcion = 'all', $ambiente = \sasco\LibreDTE\Sii::PRODUCCION, $dia = null)
    {
        ini_set('memory_limit', '1024M');
        if ($opcion != 'all') {
            if (method_exists($this, $opcion)) {
                $this->$opcion($ambiente, $dia);
            } else {
                $this->out(
                    '<error>Opción '.$opcion.' del comando no fue encontrada.</error>'
                );
                return 1;
            }
        } else {
            if (\sowerphp\core\Configure::read('proveedores.api.libredte')) {
                $this->libredte($ambiente, $dia);
                $this->corregir();
            } else {
                $this->sii($ambiente, $dia);
            }
        }
        $this->showStats();
        return 0;
    }

    /**
     * Método que descarga el listado de contribuyentes desde el SII y luego los pasa
     * el método que los procesa y actualiza en la BD
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-26
     */
    private function sii($ambiente, $dia)
    {
        // obtener firma electrónica
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica();
        } catch (\sowerphp\core\Exception $e) {
            $this->out('<error>No fue posible obtener la firma electrónica: '.$e->getMessage().'</error>');
            return 1;
        }
        // obtener contribuyentes desde el SII
        $contribuyentes = \sasco\LibreDTE\Sii::getContribuyentes($Firma, $ambiente, $dia);
        if (!$contribuyentes) {
            $this->out('<error>No fue posible obtener los contribuyentes desde el SII</error>');
            return 2;
        }
        $this->procesarContribuyentes($contribuyentes);
    }

    /**
     * Método que convierte el string de datos CSV del archivo a un arreglo PHP
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-12
     */
    private function csv2array(&$csv)
    {
        $lines = str_getcsv($csv, "\n");
        $n_lines = count($lines);
        $data = [];
        for ($i=1; $i<$n_lines; $i++) {
            $lines[$i] = utf8_encode($lines[$i]);
            $row = array_map('trim', str_getcsv($lines[$i], ';', ''));
            unset($lines[$i]);
            if (!isset($row[5]))
                continue;
            $row[4] = strtolower($row[4]);
            $row[5] = strtolower($row[5]);
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Método que descarga el listado de contribuyentes desde el servicio web de LibreDTE (versión oficial)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-12
     */
    private function libredte($ambiente, $dia)
    {
        if (!$dia)
            $dia = date('Y-m-d');
        // obtener contribuyentes desde el servicio web de LibreDTE
        $response = libredte_consume('/sii/contribuyentes_autorizados/'.$dia.'?certificacion='.$ambiente.'&formato=csv');
        if ($response['status']['code']!=200 or empty($response['body'])) {
            $this->out('<error>No fue posible obtener los contribuyentes desde el SII</error>');
            return 2;
        }
        $this->procesarContribuyentes($this->csv2array($response['body']));
    }

    /**
     * Método que carga el listado de contribuyentes desde un archivo CSV y luego los pasa
     * al método que los procesa y actualiza en la BD
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-12
     */
    private function csv($archivo)
    {
        // verificar si archivo existe
        if (!is_readable($archivo)) {
            $this->out('<error>No fue posible leer el archivo CSV: '.$archivo.'</error>');
            return 3;
        }
        // obtener datos del archivo
        $datos = file_get_contents($archivo);
        $this->procesarContribuyentes($this->csv2array($datos));
    }

    /**
     * Método que procesa los datos de los contribuyentes y los actualiza en la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-26
     */
    private function procesarContribuyentes($contribuyentes)
    {
        // procesar cada uno de los contribuyentes
        $registros = num(count($contribuyentes));
        $procesados = 0;
        foreach ($contribuyentes as $c) {
            // contabilizar contribuyente procesado
            $procesados++;
            if ($this->verbose) {
                $this->out('Procesando '.num($procesados).'/'.$registros.': contribuyente '.$c[1]);
            }
            // agregar y/o actualizar datos del contribuyente si no tiene usuario administrador
            list($rut, $dv) = explode('-', $c[0]);
            $Contribuyente = new \website\Dte\Model_Contribuyente($rut);
            $Contribuyente->dv = $dv;
            if (!$Contribuyente->usuario) {
                $Contribuyente->razon_social = substr($c[1], 0, 100);
            }
            if (is_numeric($c[2]) and $c[2]) {
                $Contribuyente->config_ambiente_produccion_numero = (int)$c[2];
            }
            if (isset($c[3][9])) {
                $aux = explode('-', $c[3]);
                if (isset($aux[2])) {
                    list($d, $m, $Y) = $aux;
                    if ($Contribuyente->config_ambiente_produccion_numero) {
                        $Contribuyente->config_ambiente_produccion_fecha = $Y.'-'.$m.'-'.$d;
                    } else {
                        $Contribuyente->config_ambiente_certificacion_fecha = $Y.'-'.$m.'-'.$d;
                    }
                }
            }
            if (strpos($c[4], '@')) {
                $Contribuyente->config_email_intercambio_user = $c[4];
            }
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            try {
                $Contribuyente->save();
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                if ($this->verbose) {
                    $this->out('<error>Contribuyente '.$c[1].' no pudo ser guardado en la base de datos</error>');
                }
            }
        }
    }

    /**
     * Método que corrige los datos de los contribuyentes existentes, cargando:
     *  - razon social
     *  - giro
     *  - actividad económica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-06
     */
    private function corregir()
    {
        $db = &\sowerphp\core\Model_Datasource_Database::get();
        $contribuyentes = $db->getCol('
            SELECT rut
            FROM contribuyente
            WHERE
                usuario IS NULL
                AND (
                    giro IS NULL
                    OR actividad_economica IS NULL
                    OR REPLACE(razon_social, \'.\', \'\') = '.$db->concat('rut', '-', 'dv').'
                )
        ');
        $registros = num(count($contribuyentes));
        $procesados = 0;
        $actualizados = 0;
        foreach ($contribuyentes as $rut) {
            $Contribuyente = new \website\Dte\Model_Contribuyente($rut);
            $response = libredte_consume('/sii/contribuyente_situacion_tributaria/'.$Contribuyente->getRUT());
            $this->out($response['body']);
            if ($response['status']['code']==200) {
                $info = $response['body'];
                $procesados++;
                if ($this->verbose) {
                    $this->out('Procesando '.num($procesados).'/'.$registros.': contribuyente '.$Contribuyente->rut.'-'.$Contribuyente->dv);
                }
                $cambios = false;
                if ($Contribuyente->razon_social==\sowerphp\app\Utility_Rut::addDV($Contribuyente->rut) and !empty($info['razon_social'])) {
                    $Contribuyente->razon_social = substr($info['razon_social'], 0, 100);
                    $cambios = true;
                }
                if (!$Contribuyente->actividad_economica and !empty($info['actividades'][0]['codigo'])) {
                    $Contribuyente->actividad_economica = $info['actividades'][0]['codigo'];
                    $cambios = true;
                }
                if (!$Contribuyente->giro and !empty($info['actividades'][0]['glosa'])) {
                    $Contribuyente->giro = substr($info['actividades'][0]['glosa'], 0, 80);
                    $cambios = true;
                }
                if ($cambios) {
                    try {
                        if ($Contribuyente->save())
                            $actualizados++;
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    }
                }
            }
        }
        $this->out('Se actualizaron '.num($actualizados).' contribuyentes de un total de '.$registros);
    }

}
