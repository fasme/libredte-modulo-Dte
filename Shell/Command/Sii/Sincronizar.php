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
 * Comando para sincronizar datos del SII en LibreDTE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-08-10
 */
class Shell_Command_Sii_Sincronizar extends \Shell_App
{

    public function main($grupo = 'dte_plus,contadores', $meses = 2, $sincronizar = 'all', $ambiente = \sasco\LibreDTE\Sii::PRODUCCION)
    {
        // se pasó un contribuyente específico
        if (is_numeric($grupo)) {
            $contribuyentes = [$grupo];
        }
        // se pasó el nombre de los grupos
        else {
            $grupos = explode(',',$grupo);
            if (!empty($grupos[1])) {
                list($grupo_contribuyentes, $grupo_contadores) = $grupos;
            } else {
                $grupo_contribuyentes = $grupos[0];
                $grupo_contadores = null;
            }
            $contribuyentes = $this->getContribuyentes($grupo_contribuyentes);
        }
        // recorrer contribuyentes y obtener datos
        $Contribuyentes = new Model_Contribuyentes();
        foreach ($contribuyentes as $rut) {
            // crear objeto del contribuyente
            $Contribuyente = $Contribuyentes->get($rut);
            // verificar que el contribuyente exista y tenga clave de SII
            if (!$Contribuyente->exists() or !$Contribuyente->config_sii_pass) {
                continue;
            }
            // verificar que el contribuyente esté autorizado a actualizar
            // sea parte del grupo de contribuyente o bien tenga contador asociado y este sea parte del grupo de contadores
            if (!empty($grupo_contribuyentes) and !$Contribuyente->getAuthLibreDTE($grupo_contribuyentes, $grupo_contadores)) {
                continue;
            }
            // verificar que la empresa esté en el mismo ambiente que se solicitó al comando
            if ($Contribuyente->config_ambiente_en_certificacion != $ambiente) {
                continue;
            }
            // sincronizar
            if ($this->verbose) {
                $this->out('Sincronizando datos del SII de: '.$Contribuyente->razon_social);
            }
            try {
                if (in_array($sincronizar, ['all', 'rc'])) {
                    (new Model_RegistroCompras())->setContribuyente($Contribuyente)->sincronizar('PENDIENTE', $meses);
                }
                if (in_array($sincronizar, ['all', 'bhe'])) {
                    (new Model_BoletaHonorarios())->setContribuyente($Contribuyente)->sincronizar($meses);
                }
                if (in_array($sincronizar, ['all', 'bte'])) {
                    (new Model_BoletaTerceros())->setContribuyente($Contribuyente)->sincronizar($meses);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  - '.$e->getMessage());
                }
            }
        }
        $this->showStats();
        return 0;
    }

    private function getContribuyentes($grupo = null)
    {
        $db = \sowerphp\core\Model_Datasource_Database::get();
        return $db->getCol('
            SELECT DISTINCT c.rut
            FROM
                contribuyente AS c
                JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut AND cc.configuracion = \'sii\' AND cc.variable = \'pass\'
                LEFT JOIN usuario_grupo AS ug ON ug.usuario = c.usuario
                LEFT JOIN grupo AS g ON ug.grupo = g.id AND g.grupo = :grupo
                LEFT JOIN contribuyente_config AS cont ON cont.contribuyente = c.rut AND cont.configuracion = \'contabilidad\' AND cont.variable = \'contador_run\'
            WHERE
                (g.grupo IS NOT NULL OR cont.valor IS NOT NULL)
                AND cc.valor IS NOT NULL
        ', [':grupo' => $grupo]);
        exit;
    }

}
