function Contribuyente() {
    'use strict';
    return;
}

Contribuyente.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.razon_social.value = "";
    f.giro.value = "";
    f.actividad_economica.value = "";
    f.direccion.value = "";
    f.comuna.value = "";
    f.telefono.value = "";
    f.email.value = "";
    if (f.config_ambiente_produccion_fecha != undefined) {
        f.config_ambiente_produccion_fecha.value = "";
        f.config_ambiente_produccion_numero.value = "";
    }
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.rut.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.rut) !== true) {
        alert('RUT contribuyente es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.rut.value.charAt(f.rut.value.length - 1),
        rut = f.rut.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (c) {
            f.razon_social.value = c.razon_social;
            f.giro.value = c.giro;
            f.actividad_economica.value = c.actividad_economica;
            f.direccion.value = c.direccion;
            f.comuna.value = c.comuna;
            f.telefono.value = c.telefono;
            f.email.value = c.email;
            if (f.config_ambiente_produccion_fecha != undefined) {
                f.config_ambiente_produccion_fecha.value = c.config_ambiente_produccion_fecha !== undefined ? c.config_ambiente_produccion_fecha : null;
                f.config_ambiente_produccion_numero.value = c.config_ambiente_produccion_numero !== undefined ? c.config_ambiente_produccion_numero : null ;
            }
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Emisor() {
    'use strict';
    return;
}

Emisor.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSoc.value = "";
    f.GiroEmis.value = "";
    f.Acteco.value = "";
    f.DirOrigen.value = "";
    f.CmnaOrigen.value = "";
    f.Telefono.value = "";
    f.CorreoEmisor.value = "";
    f.FchResol.value = "";
    f.NroResol.value = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTEmisor.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.RUTEmisor) !== true) {
        alert('RUT emisor es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTEmisor.value.charAt(f.RUTEmisor.value.length - 1),
        rut = f.RUTEmisor.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (c) {
            f.RznSoc.value = c.razon_social !== undefined ? c.razon_social : null;
            f.GiroEmis.value = c.giro !== undefined ? c.giro : null;
            f.Acteco.value = c.actividad_economica !== undefined ? c.actividad_economica : null;
            f.DirOrigen.value = c.direccion !== undefined ? c.direccion : null;
            f.CmnaOrigen.value = c.comuna !== undefined ? c.comuna : null;
            f.Telefono.value = c.telefono !== undefined ? c.telefono : null;
            f.CorreoEmisor.value = c.email !== undefined ? c.email : null;
            f.FchResol.value = c.config_ambiente_produccion_fecha !== undefined ? c.config_ambiente_produccion_fecha : null;
            f.NroResol.value = c.config_ambiente_produccion_numero !== undefined ? c.config_ambiente_produccion_numero : null;
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Receptor() {
    'use strict';
    return;
}

Receptor.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSocRecep.value = "";
    f.GiroRecep.value = "";
    f.DirRecep.value = "";
    f.CmnaRecep.value = "";
    f.Contacto.value = "";
    f.CorreoRecep.value = "";
    if (f.RUTSolicita !== undefined)
        f.RUTSolicita.value = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTRecep.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.RUTRecep) !== true) {
        alert('RUT receptor es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTRecep.value.charAt(f.RUTRecep.value.length - 1),
        rut = f.RUTRecep.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (c) {
            f.RznSocRecep.value = c.razon_social;
            f.GiroRecep.value = c.giro.substr(0, 40);
            f.DirRecep.value = (c.direccion!==undefined && c.direccion) ? c.direccion : '';
            f.CmnaRecep.value = (c.comuna!==undefined && c.comuna) ? c.comuna : '';
            f.Contacto.value = (c.telefono!==undefined && c.telefono) ? c.telefono : '';
            f.CorreoRecep.value = (c.email!==undefined && c.email) ? c.email : '';
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function DTE() {
    'use strict';
    return;
}

DTE.parseInt = function (valor) {
    var TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    if (TpoDoc!=110 && TpoDoc!=111 && TpoDoc!=112)
        return parseInt(valor);
    return parseFloat(valor);
}

DTE.round = function (valor) {
    var TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    if (TpoDoc!=110 && TpoDoc!=111 && TpoDoc!=112)
        return Math.round(valor);
    return valor;
}

DTE.setTipo = function (tipo) {
    // si es boleta indicador de servicios 3 por defecto
    if (!document.getElementById('IndServicioField').value || document.getElementById('IndServicioField').value==3) {
        if (tipo==39 || tipo==41) {
            document.getElementById('IndServicioField').value = 3;
        } else {
            document.getElementById('IndServicioField').value = '';
        }
    }
    // habilitar u ocultar datos para guía de despacho
    if (tipo==52) {
        $('#datosTransporte').show();
    } else {
        $('#datosTransporte').hide();
    }
    // habilitar u ocultar datos para exportación
    if (tipo==110 || tipo==111 || tipo==112) {
        $('#modalBuscar').hide();
        $('#RUTRecepField').removeAttr('onblur');
        $('#RUTRecepField').attr('readonly', 'readonly');
        $('#GiroRecepField').attr('disabled', 'disabled');
        $('#CmnaRecepField').attr('disabled', 'disabled');
        $('#TpoMonedaField').removeAttr('disabled');
        $('#NacionalidadField').removeAttr('disabled');
        document.getElementById('RUTRecepField').value = '55.555.555-5';
        document.getElementById('RznSocRecepField').focus();
        document.getElementById('GiroRecepField').value = '';
        document.getElementById('CmnaRecepField').value = '';
        Form.addJS('referencias');
        $('#datosExportacion').show();
        if (config_extra_indicador_servicio) {
            document.getElementById('IndServicioField').value = config_extra_indicador_servicio;
        }
    } else {
        $('#modalBuscar').show();
        if (!document.getElementById('#dte_referencia_defecto').value) {
            $('#RUTRecepField').attr('onblur', 'Receptor.setDatos(\'emitir_dte\')');
            $('#RUTRecepField').removeAttr('readonly');
        }
        $('#GiroRecepField').removeAttr('disabled');
        $('#CmnaRecepField').removeAttr('disabled');
        $('#TpoMonedaField').attr('disabled', 'disabled');
        $('#NacionalidadField').attr('disabled', 'disabled');
        $('#datosExportacion').hide();
        if (document.getElementById('IndServicioField').value==4 || document.getElementById('IndServicioField').value==5) {
            document.getElementById('IndServicioField').value = '';
        }
    }
    // agregar observación si existe
    document.getElementById("TermPagoGlosaField").value = (emision_observaciones !== null && emision_observaciones[tipo] !== undefined) ? emision_observaciones[tipo] : '';
}

DTE.setFormaPago = function (tipo) {
    // habilitar o ocultar datos para pagos programados
    if (tipo==2) {
        $('#datosPagos').show();
    } else {
        $('#datosPagos').hide();
    }
}

DTE.setItem = function (contribuyente, codigo) {
    var f = document.getElementById("emitir_dte");
    var cols = codigo.parentNode.parentNode.parentNode.parentNode.childNodes;
    var fecha = document.getElementById("FchVencField").value;
    if (codigo.value) {
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/admin/itemes/info/'+contribuyente+'/'+codigo.value+'?fecha='+fecha,
            dataType: "json",
            success: function (item) {
                // asignar valores del item
                cols[0].childNodes[0].childNodes[0].childNodes[0].value = item.VlrCodigo !== undefined ? item.VlrCodigo : '';
                cols[1].childNodes[0].childNodes[0].value = item.NmbItem !== undefined ? item.NmbItem : '';
                cols[2].childNodes[0].childNodes[0].value = (item.DscItem !== undefined && item.DscItem) ? item.DscItem : '';
                cols[3].childNodes[0].childNodes[0].value = item.IndExe !== undefined ? item.IndExe : 0;
                cols[5].childNodes[0].childNodes[0].value = (item.UnmdItem !== undefined && item.UnmdItem)? item.UnmdItem : '';
                cols[6].childNodes[0].childNodes[0].value = item.PrcItem !== undefined ? item.PrcItem : '';
                cols[7].childNodes[0].childNodes[0].value = item.ValorDR !== undefined ? item.ValorDR : 0;
                cols[8].childNodes[0].childNodes[0].value = item.TpoValor !== undefined ? item.TpoValor : '%';
                if (cols.length == 12) {
                    cols[9].childNodes[0].childNodes[0].value = (item.CodImpAdic !== undefined && item.CodImpAdic>0) ? item.CodImpAdic : '';
                }
                // foco en cantidad sólo si se logró obtener el código
                if (item.VlrCodigo !== undefined) {
                    cols[4].childNodes[0].childNodes[0].focus();
                    cols[4].childNodes[0].childNodes[0].select();
                }
                // calcular valores del dte
                DTE.calcular();
            },
            error: function (jqXHR) {
                cols[0].childNodes[0].childNodes[0].childNodes[0].value = '';
                cols[1].childNodes[0].childNodes[0].value = '';
                cols[2].childNodes[0].childNodes[0].value = '';
                cols[3].childNodes[0].childNodes[0].value = 0;
                cols[5].childNodes[0].childNodes[0].value = '';
                cols[6].childNodes[0].childNodes[0].value = '';
                cols[7].childNodes[0].childNodes[0].value = 0;
                cols[8].childNodes[0].childNodes[0].value = '%';
                if (cols.length == 12) {
                    cols[9].childNodes[0].childNodes[0].value = '';
                }
                console.log(jqXHR.responseJSON);
            }
        });
    }
}

DTE.setFechaReferencia = function (contribuyente, field) {
    var cols = field.parentNode.parentNode.parentNode.childNodes;
    var dte = cols[1].childNodes[0].childNodes[0].value;
    var folio = cols[2].childNodes[0].childNodes[0].value;
    if (!__.empty(dte) && !__.empty(folio)) {
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/dte_emitidos/info/'+dte+'/'+folio+'/'+contribuyente,
            dataType: "json",
            success: function (dte) {
                if (dte.fecha) {
                    cols[0].childNodes[0].childNodes[0].value = dte.fecha;
                }
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
            }
        });
    }
}

DTE.calcular = function () {
    var neto = 0, exento = 0, descuento = 0, CodImpAdic, CodImpAdic_tasa, adicional = 0, retencion = 0;
    // realizar cálculo de detalles
    $('input[name="QtyItem[]"]').each(function (i, e) {
        if (!__.empty($(e).val()) && !__.empty($('input[name="PrcItem[]"]').get(i).value)) {
            // calcular subtotal sin aplicar descuento
            $('input[name="subtotal[]"]').get(i).value = DTE.round(parseFloat($('input[name="QtyItem[]"]').get(i).value) * parseFloat($('input[name="PrcItem[]"]').get(i).value));
            // agregar descuento si aplica
            if (!__.empty($('input[name="ValorDR[]"]').get(i).value) && $('input[name="ValorDR[]"]').get(i).value!=0) {
                if ($('select[name="TpoValor[]"]').get(i).selectedOptions[0].value=="%")
                    descuento = DTE.round($('input[name="subtotal[]"]').get(i).value * (DTE.parseInt($('input[name="ValorDR[]"]').get(i).value)/100.0));
                else
                    descuento = DTE.parseInt($('input[name="ValorDR[]"]').get(i).value);
                $('input[name="subtotal[]"]').get(i).value -= descuento;
            }
            if (parseInt($('select[name="IndExe[]"]').get(i).selectedOptions[0].value)===1)
                exento += DTE.parseInt($('input[name="subtotal[]"]').get(i).value);
            else
                neto += DTE.parseInt($('input[name="subtotal[]"]').get(i).value);
            // si existe código de impuesto adicional se contabiliza
            if ($('select[name="CodImpAdic[]"]').get(i) !== undefined && $('select[name="CodImpAdic[]"]').get(i).value) {
                CodImpAdic = $('select[name="CodImpAdic[]"]').get(i).value;
                if (document.getElementById("impuesto_adicional_tipo_" + CodImpAdic + "Field")) {
                    CodImpAdic_tasa = parseFloat(document.getElementById("impuesto_adicional_tasa_" + CodImpAdic + "Field").value);
                    // es adicional / anticipo
                    if (document.getElementById("impuesto_adicional_tipo_" + CodImpAdic + "Field").value == "A") {
                        adicional += DTE.round($('input[name="subtotal[]"]').get(i).value * (CodImpAdic_tasa/100.0));
                    }
                    // es retención
                    else {
                        retencion += DTE.round($('input[name="subtotal[]"]').get(i).value * (CodImpAdic_tasa/100.0));
                    }
                }
            }
        }
    });
    // calcular descuento global si existe el input (contribuyentes con impuestos adicionales no tienen descuentos globales)
    if ($('select[name="TpoValor_global"]').length) {
        // calcular descuento global para neto
        if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
            descuento = DTE.round(neto * (DTE.parseInt($('input[name="ValorDR_global"]').get(0).value)/100.0));
        else
            descuento = DTE.parseInt($('input[name="ValorDR_global"]').get(0).value);
        neto -= descuento;
        if (neto<0)
            neto = 0;
        // calcular descuento global para exento
        if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
            descuento = DTE.round(exento * (DTE.parseInt($('input[name="ValorDR_global"]').get(0).value)/100.0));
        else
            descuento = DTE.parseInt($('input[name="ValorDR_global"]').get(0).value);
        exento -= descuento;
        if (exento<0)
            exento = 0;
    }
    // asignar neto y exento
    $('input[name="neto"]').val(neto);
    $('input[name="exento"]').val(exento)
    // asignar IVA y monto total
    $('input[name="iva"]').val(DTE.round(neto*(DTE.parseInt($('input[name="tasa"]').val())/100)));
    $('input[name="total"]').val(neto + exento + DTE.parseInt($('input[name="iva"]').val()) + adicional - retencion);
}

DTE.check = function () {
    var status = true, TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    var dte_check_detalle = [33, 34, 39, 41];
    var n_itemAfecto = 0, n_itemExento = 0;
    var monto_pago;
    // revisión general formulario
    if (!Form.check())
        return false;
    // revisión detalle del dte
    $('input[name="QtyItem[]"]').each(function (i, e) {
        if (__.empty($('input[name="NmbItem[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+', item no puede estar en blanco');
            $('input[name="NmbItem[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (dte_check_detalle.indexOf(TpoDoc)!=-1) {
            if (__.empty($(e).val())) {
                alert ('En la línea '+(i+1)+', cantidad no puede estar en blanco');
                $(e).focus();
                status = false;
                return false;
            }
            if (Form.check_real($('input[name="QtyItem[]"]').get(i))!==true) {
                alert ('En la línea '+(i+1)+', cantidad debe ser un número (entero o decimal)');
                $(e).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="PrcItem[]"]').get(i).value)) {
                alert ('En la línea '+(i+1)+', precio no puede estar en blanco');
                $('input[name="PrcItem[]"]').get(i).focus();
                status = false;
                return false;
            }
            if (Form.check_real($('input[name="PrcItem[]"]').get(i))!==true) {
                alert ('En la línea '+(i+1)+', cantidad debe ser un número (entero o decimal)');
                $('input[name="PrcItem[]"]').get(i).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="ValorDR[]"]').get(i).value)) {
                alert ('En la línea '+(i+1)+', descuento no puede estar en blanco');
                $('input[name="ValorDR[]"]').get(i).focus();
                status = false;
                return false;
            }
        }
        // si el documento es 34 o 41 forzar que todos los detalles sean exentos
        if (TpoDoc==34 || TpoDoc==41 || TpoDoc==110 || TpoDoc==111 || TpoDoc==112) {
            $('select[name="IndExe[]"]').get(i).value = 1;
        }
        // contabilizar items afectos
        if (!parseInt($('select[name="IndExe[]"]').get(i).value))
            n_itemAfecto++;
        else
            n_itemExento++;
    });
    if (!status)
        return false;
    // si no hay afecto pero si exento y el documento es 33 se cambia a 34 o
    // si es 39 se cambia a 41
    if (!n_itemAfecto && n_itemExento) {
        if (TpoDoc==33) {
            document.getElementById("TpoDocField").value = 34;
        }
        if (TpoDoc==39) {
            document.getElementById("TpoDocField").value = 41;
        }
    }
    // revisión referencia del dte
    $('select[name="CodRef[]"]').each(function (i, e) {
        if (__.empty($('select[name="TpoDocRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Tipo de documento referenciado no puede estar en blanco');
            $('select[name="TpoDocRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FolioRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Folio no puede estar en blanco');
            $('input[name="FolioRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FchRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Fecha no puede estar en blanco');
            $('input[name="FchRef[]"]').get(i).focus();
            status = false;
            return false;
        }
    });
    if (!status)
        return false;
    // verificar montos programados si es que existen (forma de pago crédito)
    if (document.getElementById("FmaPagoField").value==2 && $('input[name="MntPago[]"]').length) {
        monto_pago = 0;
        $('input[name="MntPago[]"]').each(function (i, m) {
            monto_pago += DTE.parseInt(m.value);
        });
        if (monto_pago != $('input[name="total"]').val()) {
            alert('Monto de pago programado $' + __.num(monto_pago) + '.- no cuadra con el total del documento');
            return false;
        }
    }
    // pedir confirmación de generación de factura
    DTE.calcular();
    return Form.checkSend('Confirmar '+document.getElementById("TpoDocField").selectedOptions[0].textContent+' por $'+__.num($('input[name="total"]').val())+' a '+$('input[name="RUTRecep"]').val());
}

function dte_recibido_check() {
    var emisor = document.getElementById("emisorField");
    var dte = document.getElementById("dteField");
    var folio = document.getElementById("folioField");
    var receptor = document.getElementById("receptorField");
    if (emisor.value && dte.value && folio.value) {
        estado = Form.check_rut(emisor);
        if (estado !== true) {
            alert(estado);
            return;
        }
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/dte_recibidos/info/'+emisor.value+'/'+dte.value+'/'+folio.value+'/'+receptor.value,
            dataType: "json",
            success: function (documento) {
                document.getElementById("fechaField").value = documento.fecha;
                document.getElementById("exentoField").value = documento.exento;
                document.getElementById("netoField").value = documento.neto;
                document.getElementById("impuesto_tipoField").value = documento.impuesto_tipo;
                document.getElementById("tasaField").value = documento.tasa;
                document.getElementById("ivaField").value = documento.iva;
                document.getElementById("periodoField").value = documento.periodo;
                document.getElementById("iva_uso_comunField").value = documento.iva_uso_comun;
                document.getElementById("iva_no_recuperableField").value = documento.iva_no_recuperable ? documento.iva_no_recuperable : '';
                document.getElementById("impuesto_adicionalField").value = documento.impuesto_adicional ? documento.impuesto_adicional  : '';
                document.getElementById("impuesto_sin_creditoField").value = documento.impuesto_sin_credito;
                document.getElementById("monto_activo_fijoField").value = documento.monto_activo_fijo;
                document.getElementById("monto_iva_activo_fijoField").value = documento.monto_iva_activo_fijo;
                document.getElementById("iva_no_retenidoField").value = documento.iva_no_retenido;
                document.getElementById("anuladoField").checked = documento.anulado == 'A' ? true : false;
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
            }
        });
    }
}
