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
 * Comando que permite emitir masivamente DTE a partir de un archivo CSV
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-04-19
 */
class Shell_Command_DteEmitidos_EmitirMasivo extends \Shell_App
{

    private $time_start;

    public function main($emisor, $archivo, $usuario, $dte_real = false, $email = false)
    {
        $this->time_start = microtime(true);
        // crear emisor/usuario y verificar permisos
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$usuario) {
            $usuario = $Emisor->usuario;
        }
        $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($usuario);
        if (!$Emisor->usuarioAutorizado($Usuario, '/dte/documentos/emitir')) {
            $this->notificarResultado($Emisor, $Usuario, 'Usuario '.$Usuario->usuario.' no está autorizado a operar con la empresa '.$Emisor->getNombre(), $dte_real, $email);
            return 1;
        }
        // verificar sea leible
        if (!is_readable($archivo)) {
            $this->notificarResultado($Emisor, $Usuario, 'Archivo '.$archivo.' no puede ser leído', $dte_real, $email);
            return 1;
        }
        // verificar archivo sea UTF-8
        exec('file -i '.$archivo, $output);
        $aux = explode('charset=', $output[0]);
        if (!isset($aux[1]) or $aux[1]!='utf-8') {
            $this->notificarResultado($Emisor, $Usuario, 'Codificación del archivo es '.$aux[1].' y debe ser utf-8', $dte_real, $email);
            return 1;
        }
        // cargar archivo y crear documentos
        $datos = \sowerphp\general\Utility_Spreadsheet_CSV::read($archivo);
        if (strpos($archivo, '/tmp')===0) {
            unlink($archivo);
        }
        $datos[0][] = 'resultado_codigo';
        $datos[0][] = 'resultado_glosa';
        $n_datos = count($datos);
        $documentos = [];
        $documento = null;
        $error_formato = false;
        for ($i=1; $i<$n_datos; $i++) {
            // fila es un documento nuevo
            if (!empty($datos[$i][0])) {
                // si existe un documento asignado previamente se agrega al listado
                if ($documento) {
                    $documentos[] = $documento;
                }
                // se crea documento de la fila que se está viendo
                try {
                    $documento = $this->crearDocumento($datos[$i]);
                    // verificar que el usuario esté autorizado a emitir el tipo de documento
                    if (!$Emisor->documentoAutorizado($documento['Encabezado']['IdDoc']['TipoDTE'], $Usuario)) {
                        $error_formato = true;
                        $datos[$i][] = 2;
                        $datos[$i][] = 'No está autorizado a emitir el tipo de documento '.$documento['Encabezado']['IdDoc']['TipoDTE'];
                    }
                    // si se quiere enviar por correo, verificar que exista correo
                    else if ($email and empty($documento['Encabezado']['Receptor']['CorreoRecep'])) {
                        $error_formato = true;
                        $datos[$i][] = 3;
                        $datos[$i][] = 'Debe indicar correo del receptor';
                    }
                } catch (\Exception $e) {
                    $error_formato = true;
                    $datos[$i][] = 1;
                    $datos[$i][] = $e->getMessage();
                }
            }
            // si la fila no es documento nuevo, se agrega el detalle al documento que ya existe
            else {
                try {
                    $this->agregarItem($documento, array_slice($datos[$i], 11, 9));
                } catch (\Exception $e) {
                    $error_formato = true;
                    $datos[$i][] = 1;
                    $datos[$i][] = $e->getMessage();
                }
            }
        }
        $documentos[] = $documento;
        // si hay errores de formato se notifica al usuario y se detiene la ejecución
        if ($error_formato) {
            $this->notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email);
            return 1;
        }
        // ir generando cada documento
        $Request = new \sowerphp\core\Network_Request();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($Usuario->hash);
        foreach($documentos as $dte) {
            // agregar RUT emisor
            $dte['Encabezado']['Emisor']['RUTEmisor'] = $Emisor->rut.'-'.$Emisor->dv;
            // emitir DTE temporal
            $response = $rest->post($Request->url.'/api/dte/documentos/emitir', $dte);
            if ($response['status']['code']!=200) {
                $this->documentoAgregarResultado(
                    $datos,
                    $dte['Encabezado']['IdDoc']['TipoDTE'],
                    $dte['Encabezado']['IdDoc']['Folio'],
                    4,
                    $response['body']
                );
                continue;
            }
            // procesar DTE temporal (ya que no se genera el real)
            if (!$dte_real) {
                // enviar DTE temporal por correo al receptor
                if ($email) {
                    $DteTmp = new \website\Dte\Model_DteTmp(
                        $response['body']['emisor'],
                        $response['body']['receptor'],
                        $response['body']['dte'],
                        $response['body']['codigo']
                    );
                    try {
                        $DteTmp->email();
                    } catch (\Exception $e) {
                        $this->documentoAgregarResultado(
                            $datos,
                            $dte['Encabezado']['IdDoc']['TipoDTE'],
                            $dte['Encabezado']['IdDoc']['Folio'],
                            6,
                            $e->getMessage()
                        );
                        continue;
                    }
                }
            }
            // emitir DTE real
            else {
                // consumir servicio web
                $response = $rest->post($Request->url.'/api/dte/documentos/generar', $response['body']);
                if ($response['status']['code']!=200) {
                    $this->documentoAgregarResultado(
                        $datos,
                        $dte['Encabezado']['IdDoc']['TipoDTE'],
                        $dte['Encabezado']['IdDoc']['Folio'],
                        5,
                        $response['body']
                    );
                    continue;
                }
                // enviar DTE real por correo al receptor
                if ($email) {
                    $DteEmitido = new \website\Dte\Model_DteEmitido($response['body']['emisor'], $response['body']['dte'], $response['body']['folio'], $Emisor->config_ambiente_en_certificacion);
                    $DteEmitido->email($DteEmitido->getEmails(), null, null, true);
                }
            }
        }
        // notificar al usuario que solicitó la emisión masiva
        $this->notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email);
        // estadisticas y terminar
        $this->showStats();
        return 0;
    }

    private function crearDocumento($datos)
    {
        // verificar datos mínimos
        if (empty($datos[0])) {
            throw new \Exception('Falta tipo de documento');
        }
        if (empty($datos[1])) {
            throw new \Exception('Falta folio del documento');
        }
        if (empty($datos[4])) {
            throw new \Exception('Falta RUT del receptor');
        }
        // verificar datos si no es boleta
        if (!in_array($datos[0], [39, 41])) {
            if (empty($datos[5])) {
                throw new \Exception('Falta razón social del receptor');
            }
            if (empty($datos[6])) {
                throw new \Exception('Falta giro del receptor');
            }
            if (empty($datos[9])) {
                throw new \Exception('Falta dirección del receptor');
            }
            if (empty($datos[10])) {
                throw new \Exception('Falta comuna del receptor');
            }
        }
        // armar dte
        $documento = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => (int)$datos[0],
                    'Folio' => (int)$datos[1],
                ],
                'Receptor' => [
                    'RUTRecep' => str_replace('.', '', $datos[4]),
                ],
            ],
            'Detalle' => [],
        ];
        if (!empty($datos[2])) {
            if (!\sowerphp\general\Utility_Date::check($datos[2])) {
                throw new \Exception('Fecha emisión '.$datos[2].' es incorrecta, debe ser formato AAAA-MM-DD');
            }
            $documento['Encabezado']['IdDoc']['FchEmis'] = $datos[2];
        }
        if (!empty($datos[3])) {
            if (!\sowerphp\general\Utility_Date::check($datos[3])) {
                throw new \Exception('Fecha vencimiento '.$datos[3].' es incorrecta, debe ser formato AAAA-MM-DD');
            }
            $documento['Encabezado']['IdDoc']['FchVenc'] = $datos[3];
        }
        if (!empty($datos[5])) {
            $documento['Encabezado']['Receptor']['RznSocRecep'] = mb_substr(trim($datos[5]), 0, 100);
        }
        if (!empty($datos[6])) {
            $documento['Encabezado']['Receptor']['GiroRecep'] = mb_substr(trim($datos[6]), 0, 40);
        }
        if (!empty($datos[7])) {
            $documento['Encabezado']['Receptor']['Contacto'] = mb_substr(trim($datos[7]), 0, 80);
        }
        if (!empty($datos[8])) {
            if (!filter_var($datos[8], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Correo electrónico '.$datos[8].' no es válido');
            }
            $documento['Encabezado']['Receptor']['CorreoRecep'] = mb_substr(trim($datos[8]), 0, 80);
        }
        if (!empty($datos[9])) {
            $documento['Encabezado']['Receptor']['DirRecep'] = mb_substr(trim($datos[9]), 0, 70);
        }
        if (!empty($datos[10])) {
            $documento['Encabezado']['Receptor']['CmnaRecep'] = mb_substr(trim($datos[10]), 0, 20);
        }
        if (!empty($datos[19])) {
            $documento['Encabezado']['IdDoc']['TermPagoGlosa'] = mb_substr(trim($datos[19]), 0, 100);
        }
        $this->agregarItem($documento, array_slice($datos, 11, 9));
        return $documento;
    }

    private function agregarItem(&$documento, $item)
    {
        // verificar datos mínimos
        if (empty($item[2])) {
            throw new \Exception('Falta nombre del item');
        }
        if (empty($item[4])) {
            throw new \Exception('Falta cantidad del item');
        }
        if (empty($item[6])) {
            throw new \Exception('Falta precio del item');
        }
        // crear detalle
        $detalle = [
            'NmbItem' => mb_substr(trim($item[2]), 0, 80),
            'QtyItem' => (float)str_replace(',', '.', $item[4]),
            'PrcItem' => (float)str_replace(',', '.', $item[6]),
        ];
        if (!empty($item[0])) {
            $detalle['CdgItem'] = [
                'TpoCodigo' => 'INT1',
                'VlrCodigo' => mb_substr(trim($item[0]), 0, 35),
            ];
        }
        if (!empty($item[1])) {
            $detalle['IndExe'] = (int)$item[1];
        }
        if (!empty($item[3])) {
            $detalle['DscItem'] = mb_substr(trim($item[3]), 0, 1000);
        }
        if (!empty($item[5])) {
            $detalle['UnmdItem'] = mb_substr(trim($item[5]), 0, 4);
        }
        if (!empty($item[7])) {
            if (strpos($item[7], ',') or strpos($item[7], '.')) {
                $detalle['DescuentoPct'] = (int)((float)(str_replace(',','.', $item[7]))*100);
            } else {
                $detalle['DescuentoMonto'] = (float)$item[7];
            }
        }
        // agregar detalle al documento
        $documento['Detalle'][] = $detalle;
    }

    private function documentoAgregarResultado(&$datos, $tipo_dte, $folio, $resultado_codigo, $resultado_glosa)
    {
        foreach ($datos as &$d) {
            if ($d[0]==$tipo_dte and $d[1]==$folio) {
                $d[] = $resultado_codigo;
                $d[] = $resultado_glosa;
                break;
            }
        }
    }

    private function notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email)
    {
        // datos del envío
        $id = date('YmdHis');
        $tiempo = round(microtime(true) - $this->time_start, 2);
        if (is_array($datos)) {
            $file = [
                'tmp_name' => tempnam('/tmp', $Emisor->rut.'_dte_masivo_'),
                'name' => $Emisor->rut.'_dte_masivo_'.$id.'.csv',
                'type' => 'text/csv',
            ];
            \sowerphp\general\Utility_Spreadsheet_CSV::save($datos, $file['tmp_name']);
        } else $file = null;
        // enviar correo
        $titulo = 'Resultado emisión masiva de DTE #'.$id;
        $msg = $Usuario->nombre.','."\n\n";
        if ($file) {
            $msg .= 'Se adjunta archivo CSV con el detalle de la emisión para cada DTE solicitado.'."\n\n";
        } else if ($datos) {
            $msg .= 'Ha ocurrido un error y el archivo no ha podido ser procesado: '.$datos."\n\n";
        }
        $msg .= '- Generar DTE real: '.($dte_real?'Si':'No')."\n";
        $msg .= '- Enviar DTE por correo: '.($email?'Si':'No')."\n";
        $msg .= '- Tiempo ejecución: '.num($tiempo).' segundos'."\n";
        $Emisor->notificar($titulo, $msg, $Usuario->email, null, $file);
        if ($file) {
            unlink($file['tmp_name']);
        }
    }

}
