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
 * Comando para enviar el reporte de consumo de folios de las boletas
 * electrónicas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-11-16
 */
class Shell_Command_Boletas_EnviarRCOF extends \Shell_App
{

    public function main($grupo = 'dte_plus', $certificacion = 0)
    {
        \sasco\LibreDTE\Sii::setAmbiente((int)$certificacion);
        $from_unix_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $day_before = strtotime('yesterday', $from_unix_time);
        $dia = date('Y-m-d', $day_before);
        $contribuyentes = $this->getContribuyentes($grupo, $certificacion);
        foreach ($contribuyentes as $rut) {
            $this->enviar($rut, $dia, $certificacion);
        }
        $this->showStats();
        return 0;
    }

    private function enviar($rut, $dia, $certificacion, $retry = 10)
    {
        $Contribuyente = new Model_Contribuyente($rut);
        if (!$Contribuyente->exists()) {
            return false;
        }
        if ($this->verbose) {
            $this->out('Enviando RCOF del contribuyente '.$Contribuyente->razon_social);
        }
        if ($Contribuyente->config_ambiente_en_certificacion != $certificacion) {
            if ($this->verbose) {
                $this->out('  Contribuyente no está en el ambiente del envío');
            }
            return;
        }
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Contribuyente->rut, $dia, (int)$Contribuyente->config_ambiente_en_certificacion);
        for ($i=0; $i<$retry; $i++) {
            $track_id = false;
            try {
                $track_id = $DteBoletaConsumo->enviar();
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  '.$e->getMessage());
                }
            }
            if ($track_id) {
                break;
            }
        }
        if (!$track_id) {
            if ($this->verbose) {
                $this->out('  No fue posible enviar el reporte');
            }
            $Contribuyente->notificar('RCOF '.$dia.' falló', 'No fue posible enviar el reporte del día '.$dia);
        }
    }

    private function getContribuyentes($grupo, $certificacion)
    {
        if (is_numeric($grupo))
            return [$grupo];
        $db = \sowerphp\core\Model_Datasource_Database::get();
        return $db->getCol('
            SELECT DISTINCT c.rut
            FROM
                contribuyente AS c
                JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                JOIN contribuyente_dte AS cd ON cd.contribuyente = c.rut
                JOIN usuario AS u ON c.usuario = u.id
                JOIN usuario_grupo AS ug ON ug.usuario = u.id
                JOIN grupo AS g ON ug.grupo = g.id
                JOIN dte_folio AS f ON f.emisor = c.rut AND f.dte = cd.dte
            WHERE
                g.grupo = :grupo
                AND cc.configuracion = \'ambiente\'
                AND cc.variable = \'en_certificacion\'
                AND cc.valor = :certificacion_t
                AND cd.dte IN (39, 41)
                AND f.dte IN (39, 41)
                AND f.certificacion = :certificacion
        ', [':grupo' => $grupo, ':certificacion'=>(int)$certificacion, ':certificacion_t'=>(int)$certificacion]);
    }

}
