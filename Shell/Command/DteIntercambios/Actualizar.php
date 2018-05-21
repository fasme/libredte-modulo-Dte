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
 * Comando para actualizar la bandeja de intercambio de los contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-05-20
 */
class Shell_Command_DteIntercambios_Actualizar extends \Shell_App
{

    public function main($grupo = null, $dias = 7, $meses = 2)
    {
        $contribuyentes = $this->getContribuyentes($grupo);
        foreach ($contribuyentes as $rut) {
            if ($dias) {
                $this->actualizarIntercambio($rut, $dias);
            }
            if ($meses) {
                $this->sincronizarConRegistroComprasSII($rut, $meses);
            }
        }
        $this->showStats();
        return 0;
    }

    private function actualizarIntercambio($rut, $dias)
    {
        $Contribuyente = (new Model_Contribuyentes())->get($rut);
        if (!$Contribuyente->exists() or !$Contribuyente->getEmailImap()) {
            return false;
        }
        if ($this->verbose) {
            $this->out('Actualizando bandeja '.$Contribuyente->config_email_intercambio_user.' del contribuyente '.$Contribuyente->razon_social);
        }
        try {
            $resultado = $Contribuyente->actualizarBandejaIntercambio($dias);
            if ($resultado['n_EnvioDTE']) {
                $msg = $Contribuyente->razon_social.','."\n\n";
                $msg .= 'Tiene '.$resultado['n_EnvioDTE'].' documento(s) nuevo(s) en su bandeja de intercambio.'."\n\n";
                $msg .= 'Revisar pendientes en '.(new \sowerphp\core\Network_Request())->url.'/dte/contribuyentes/seleccionar/'.$Contribuyente->rut.'/'.base64_encode('/dte/dte_intercambios/listar');
                $Contribuyente->notificar('Nuevo(s) documento(s) recibido(s)', $msg);
            }
        } catch (\Exception $e) {
            if ($this->verbose) {
                $this->out('  '.$e->getMessage());
            }
        }
    }

    private function sincronizarConRegistroComprasSII($rut, $meses)
    {
        $Contribuyente = (new Model_Contribuyentes())->get($rut);
        if (!$Contribuyente->exists() or !$Contribuyente->config_sii_pass) {
            return false;
        }
        if ($this->verbose) {
            $this->out('Sincronizando registro compras SII del contribuyente '.$Contribuyente->razon_social);
        }
        try {
            (new Model_DteCompras())->setContribuyente($Contribuyente)->sincronizarRegistroComprasSII();
        } catch (\Exception $e) {
            if ($this->verbose) {
                $this->out(' '.$e->getMessage());
            }
        }
    }

    private function getContribuyentes($grupo = null)
    {
        if (is_numeric($grupo))
            return [$grupo];
        $db = \sowerphp\core\Model_Datasource_Database::get();
        if ($grupo) {
            return $db->getCol('
                SELECT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                WHERE
                    g.grupo = :grupo
                    AND cc.configuracion = \'email\'
                    AND cc.variable = \'intercambio_pass\'
            ', [':grupo' => $grupo]);
        } else {
            return $db->getCol('
                SELECT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                WHERE
                    c.usuario IS NOT NULL
                    AND cc.configuracion = \'email\'
                    AND cc.variable = \'intercambio_pass\'
            ');
        }
    }

}
