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
 * Clase para mapear la tabla dte_boleta_consumo de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_boleta_consumo
 * @author SowerPHP Code Generator
 * @version 2016-02-14 05:05:56
 */
class Model_DteBoletaConsumos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_boleta_consumo'; ///< Tabla del modelo

    /**
     * Método que entrega los días pendientes de enviar RCOF
     * Se busca entre el primer día enviado y el día de ayer
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-11
     */
    public function getPendientes()
    {
        // determinar desde y hasta
        $desde = $this->db->getValue(
            'SELECT MIN(dia) FROM dte_boleta_consumo WHERE emisor = :emisor AND certificacion = :certificacion',
            [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion]
        );
        if (!$desde) {
            return false;
        }
        $hasta = \sowerphp\general\Utility_Date::getPrevious(date('Y-m-d'), 'D');
        // crear listado de días que se buscarán
        $dias = [];
        $dia = $desde;
        while ($dia<=$hasta) {
            $dias[] = $dia;
            $dia = \sowerphp\general\Utility_Date::getNext($dia, 'D');
        }
        // consultar los dias que si están en el RCOF
        $dias_enviados = $this->db->getCol(
            'SELECT dia FROM dte_boleta_consumo WHERE emisor = :emisor AND certificacion = :certificacion',
            [':emisor'=>$this->getContribuyente()->rut, ':certificacion'=>(int)$this->getContribuyente()->config_ambiente_en_certificacion]
        );
        // calcular la diferencia entre los enviados y los que se solicitaron
        return array_diff($dias, $dias_enviados);
    }

}
