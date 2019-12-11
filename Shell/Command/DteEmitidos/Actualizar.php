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
 * Comando para actualizar los documentos emitidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-12-10
 */
class Shell_Command_DteEmitidos_Actualizar extends \Shell_App
{

    public function main($grupo = null, $certificacion = 0, $meses = 2, $retry = 3)
    {
        $this->db = \sowerphp\core\Model_Datasource_Database::get();
        $contribuyentes = $this->getContribuyentes($grupo, $certificacion);
        foreach ($contribuyentes as $rut) {
            $this->actualizarDocumentosEmitidos($rut, $certificacion, $retry);
        }
        try {
            $this->actualizarEventosReceptor($meses, $grupo, $certificacion);
        } catch(\Exception $e) {
            if ($this->verbose) {
                $this->out('<error>'.$e->getMessage().'</error>');
            }
        }
        $this->showStats();
        return 0;
    }

    private function actualizarDocumentosEmitidos($rut, $certificacion, $retry = null)
    {
        $Contribuyente = (new Model_Contribuyentes())->get($rut);
        if ($this->verbose) {
            $this->out('Buscando documentos del contribuyente '.$Contribuyente->razon_social);
        }
        // actualizar estado de DTE enviados
        $sin_estado = $Contribuyente->getDteEmitidosSinEstado($certificacion);
        foreach ($sin_estado as $d) {
            if ($this->verbose) {
                $this->out('  Actualizando estado T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], (int)$Contribuyente->config_ambiente_en_certificacion);
            try {
                $estado_original = $DteEmitido->revision_estado;
                $DteEmitido->actualizarEstado();
                if ($DteEmitido->getEstado()=='R') {
                    $msg = $Contribuyente->razon_social.','."\n\n";
                    $msg .= 'El documento '.$DteEmitido->getTipo()->tipo.' folio '.$DteEmitido->folio.' se encuentra '.$DteEmitido->revision_estado."\n\n";
                    if ($DteEmitido->revision_detalle) {
                        $msg .= $DteEmitido->revision_detalle."\n\n";
                    }
                    $msg .= 'Revisar el documento y su estado en '.(new \sowerphp\core\Network_Request())->url.'/dte/contribuyentes/seleccionar/'.$Contribuyente->rut.'/'.base64_encode('/dte/dte_emitidos/actualizar_estado/'.$DteEmitido->dte.'/'.$DteEmitido->folio).' Si el estado del DTE no está disponible se solicitará una nueva revisión, espere un momento y vuelva a hacer click en "Actualizar estado"'."\n\n";
                    $msg .= 'Adicionalmente puede hacer click en "ver estado envío en SII" bajo el botón "Actualizar estado", en la página del documento, para ver el motivo del rechazo en el sitio del SII.'."\n\n";
                    $msg .= 'Si tiene alguna duda, recuerde revisar el siguiente enlace https://wiki.libredte.cl/doku.php/faq/libredte/sowerphp/dte_rechazado'."\n\n";
                    $msg .= 'También puede encontrar más información sobre los motivos de los rechazos en https://wiki.libredte.cl/doku.php/faq/sii/estado_envio#estados_rechazados';
                    $Contribuyente->notificar('T'.$DteEmitido->dte.'F'.$DteEmitido->folio.' RECHAZADO!', $msg);
                }
                if ($estado_original=='-11' and $DteEmitido->revision_estado!=$estado_original) {
                    $msg = $DteEmitido->revision_estado."\n\n".$DteEmitido->revision_detalle;
                    $Contribuyente->notificar('T'.$DteEmitido->dte.'F'.$DteEmitido->folio.' ESTADO -11 ACTUALIZADO!', $msg);
                }
                if ($this->verbose) {
                    $this->out($DteEmitido->revision_estado);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
        // enviar lo generado sin track id
        $sin_enviar = $Contribuyente->getDteEmitidosSinEnviar($certificacion);
        foreach ($sin_enviar as $d) {
            if ($this->verbose) {
                $this->out('  Enviando al SII T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], (int)$Contribuyente->config_ambiente_en_certificacion);
            try {
                $DteEmitido->enviar(null, $retry);
                if ($this->verbose) {
                    $this->out($DteEmitido->track_id);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
    }

    private function getContribuyentes($grupo, $certificacion)
    {
        if (is_numeric($grupo)) {
            return [$grupo];
        }
        if ($grupo) {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    g.grupo = :grupo
                    AND e.dte NOT IN (39, 41)
                    AND e.certificacion = :certificacion
                    AND (
                        -- no enviados al SII (sin track id)
                        e.track_id IS NULL
                        -- enviados al SII (con track ID válido != -1)
                        OR (
                            (
                                e.revision_estado IS NULL
                                OR e.revision_estado LIKE \'-%\'
                                OR SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']).'\')
                            )
                            AND e.track_id > 0
                        )
                    )
            ', [':certificacion'=>(int)$certificacion, ':grupo' => $grupo]);
        } else {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    c.usuario IS NOT NULL
                    AND e.dte NOT IN (39, 41)
                    AND e.certificacion = :certificacion
                    AND (
                        -- no enviados al SII (sin track id)
                        e.track_id IS NULL
                        -- enviados al SII (con track ID válido != -1)
                        OR (
                            (
                                e.revision_estado IS NULL
                                OR e.revision_estado LIKE \'-%\'
                                OR SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']).'\')
                            )
                            AND e.track_id > 0
                        )
                    )
            ', [':certificacion'=>(int)$certificacion]);
        }
    }

    private function actualizarEventosReceptor($meses, $grupo, $certificacion)
    {
        if (is_numeric($grupo)) {
            $contribuyentes = [$grupo];
        } else {
            $contribuyentes = $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    c.usuario IS NOT NULL
                    AND cc.configuracion = \'sii\' AND cc.variable = \'pass\' AND cc.valor IS NOT NULL
                    AND e.dte IN ('.implode(', ', array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes)).')
                    AND e.certificacion = :certificacion
                    AND e.receptor_evento IS NULL
                    AND e.fecha  >=  (CURRENT_DATE - INTERVAL \''.(int)$meses.' MONTHS\')
            ', [':certificacion'=>(int)$certificacion]);
        }
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        foreach ($contribuyentes as $rut) {
            $Contribuyente = (new Model_Contribuyentes())->get($rut);
            if ((int)$Contribuyente->config_ambiente_en_certificacion!=(int)$certificacion) {
                continue;
            }
            if ($this->verbose) {
                $this->out('Buscando eventos receptor de '.$Contribuyente->razon_social);
            }
            $DteEmitidos = (new Model_DteEmitidos())->setContribuyente($Contribuyente);
            try {
                foreach ($periodos as $periodo) {
                    $DteEmitidos->actualizarEstadoReceptor($periodo);
                    if ($this->verbose) {
                        $this->out('  Procesado período '.$periodo);
                    }
                }

            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  '.$e->getMessage());
                }
            }
        }
    }

}
