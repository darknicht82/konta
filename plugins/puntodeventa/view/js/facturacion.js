$(document).ready(function() {
    $("#b_nuevo_cliente").click(function(event) {
        event.preventDefault();
        nuevo_cliente();
    });
    $("#b_historial").click(function(event) {
        event.preventDefault();
        mostrar_historial();
    });
    $("#b_mov_caja").click(function(event) {
        event.preventDefault();
        mostrar_movimientos();
    });
    $("#b_cerrar_caja").click(function(event) {
        event.preventDefault();
        cerrar_caja();
    });
    $("#b_cobrar").click(function(event) {
        event.preventDefault();
        validar_cobrar();
    });
    $("#b_atajos").click(function(event) {
        event.preventDefault();
        mostrar_atajos();
    });
    if (mosmenu == 1) {
        $("#b_mosmenu").click(function(event) {
            event.preventDefault();
            mostrar_menu();
        });
        $("#btn_regresar").click(function(event) {
            event.preventDefault();
            tratar_grupos();
        });
    }
    $('#idcliente').html('').select2({
        data: [{
            id: consfinal.idcliente,
            text: consfinal.identificacion + " - " + consfinal.razonsocial
        }],
        theme: 'bootstrap-5',
        allowClear: true
    });
    $("#telefono").val(consfinal.telefono);
    $("#email").val(consfinal.email);
    $("#direccion").val(consfinal.direccion);
    $("#idcliente").select2({
        language: {
            noResults: function() {
                return "No hay resultado";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function() {
                return "Ingrese al menos 3 caracteres para realizar la busqueda";
            },
        },
        ajax: {
            url: url,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    buscar_cliente: params.term
                };
            },
            processResults: function(data, params) {
                return {
                    results: $.map(data, function(obj, index) {
                        return {
                            id: obj.idcliente,
                            text: obj.identificacion + " - " + obj.razonsocial
                        };
                    })
                };
            },
            cache: true
        },
        placeholder: 'Buscar Cliente',
        minimumInputLength: 3,
        theme: 'bootstrap-5',
        allowClear: true
    });
    $("#f_nuevo_cliente").submit(function(event) {
        event.preventDefault();
        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    alert(datos.msj);
                } else {
                    alert(datos.msj);
                    $('#idcliente').html('').select2({
                        language: {
                            noResults: function() {
                                return "No hay resultado";
                            },
                            searching: function() {
                                return "Buscando...";
                            },
                            inputTooShort: function() {
                                return "Ingrese al menos 3 caracteres para realizar la busqueda";
                            },
                        },
                        ajax: {
                            url: url,
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    buscar_cliente: params.term
                                };
                            },
                            processResults: function(data, params) {
                                return {
                                    results: $.map(data, function(obj, index) {
                                        return {
                                            id: obj.idcliente,
                                            text: obj.identificacion + " - " + obj.razonsocial
                                        };
                                    })
                                };
                            },
                            cache: true
                        },
                        data: [{
                            id: datos.cliente.idcliente,
                            text: datos.cliente.identificacion + " - " + datos.cliente.razonsocial
                        }],
                        placeholder: 'Buscar Cliente',
                        minimumInputLength: 3,
                        theme: 'bootstrap-5',
                        allowClear: true
                    });
                    $("#idcliente").trigger('change');
                    $("#modal_nuevo_cliente").modal('hide');
                }
            },
            error: function() {
                alert("Error al intentar guardar el Cliente!");
            }
        });
    });
    $('#idcliente').on("change", function(e) {
        let idcliente = $("#idcliente").val();
        if (idcliente === null) {} else {
            e.preventDefault();
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    idcliente: idcliente
                },
                success: function(data) {
                    let datos = JSON.parse(data);
                    if (datos.error == 'T') {
                        alert(datos.msj);
                    } else {
                        $("#direccion").val(datos.cli.direccion);
                        $("#telefono").val(datos.cli.telefono);
                        $("#email").val(datos.cli.email);
                    }
                },
                error: function() {
                    alert("Error al intentar buscar el Cliente!");
                }
            });
        }
    });
    $("#codigobarras").on("keyup", function(e) {
        if (e.which == 13) {
            let buscar = $("#codigobarras").val();
            if (buscar != '') {
                e.preventDefault();
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        codigobarras: buscar,
                        establecimiento: establecimiento
                    },
                    success: function(data) {
                        let datos = JSON.parse(data);
                        if (datos.error == 'T') {
                            alert(datos.msj);
                        } else {
                            agregar_linea(datos.art);
                        }
                    },
                    error: function() {
                        alert("Error al intentar buscar el articulo!");
                    }
                });
                $("#codigobarras").val('');
            }
        }
    });
    $("#idarticulo").select2({
        language: {
            noResults: function() {
                return "No hay resultado";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function() {
                return "Ingrese al menos 3 caracteres para realizar la busqueda";
            },
        },
        ajax: {
            url: url,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    buscar_articulo: params.term,
                    establecimiento: establecimiento
                };
            },
            processResults: function(data, params) {
                return {
                    results: $.map(data, function(obj, index) {
                        return {
                            id: obj.idarticulo,
                            text: obj.codprincipal + " - " + obj.nombre + " (" + obj.stock_fisico + ")"
                        };
                    })
                };
            },
            cache: true
        },
        placeholder: 'Buscar Artículo',
        minimumInputLength: 3,
        theme: 'bootstrap-5',
        allowClear: true
    });
    $('#idarticulo').on("change", function(e) {
        let idarticulo = $("#idarticulo").val();
        if (idarticulo === null) {} else {
            e.preventDefault();
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    idarticulo: idarticulo,
                    establecimiento: establecimiento
                },
                success: function(data) {
                    let datos = JSON.parse(data);
                    if (datos.error == 'T') {
                        alert(datos.msj);
                    } else {
                        agregar_linea(datos.art);
                    }
                },
                error: function() {
                    alert("Error al intentar buscar el articulo!");
                }
            });
            $("#idarticulo").empty().trigger('change');
        }
    });
});

function nuevo_cliente() {
    $("#modal_nuevo_cliente").modal('show');
    limpiar_campos();
    document.f_nuevo_cliente.identificacion.focus();
}

function mostrar_historial() {
    $("#detalle_his").empty();
    $.ajax({
        url: url,
        type: "POST",
        data: {
            idcierre: idcierre
        },
        success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
                alert(datos.msj);
            } else {
                $(datos.facs).each(function(key, value) {
                    let reimpre = "";
                    if (reimpresion == 1) {
                        reimpre = "<a class='btn btn-sm btn-success' href='"+url+"&reimprimir="+value.idfacturacli+"' title='Reimprimir Factura'>\n\
                                        <span class='bi bi-printer-fill'></span>\n\
                                    </a>";
                    }
                    let printPdf = "";
                    if (impresion == 1 && value.coddocumento != '02') {
                        printPdf = "<a class='btn btn-sm btn-info' target='_blank' href='index.php?page=impresion_ventas&tipo=a4&id="+value.idfacturacli+"' title='Imprimir RIDE'>\n\
                                    <span class='bi bi-filetype-pdf'></span>\n\
                                </a>";
                    }
                    let tipodoc = "";
                    let numdoc = "";
                    if (mtipodoc == 1) {
                        tipodoc = "<td>"+value.namedoc+"</td>";
                        numdoc = "<a href='"+value.urldoc+"' target='_blank'>"+value.numero_documento+"</a>";
                    } else {
                        numdoc = value.numero_documento;
                    }
                    $("#detalle_his").append("<tr>\n\
                        <td>"+value.fec_emision+"</td>\n\
                        "+tipodoc+"\n\
                        <td>"+numdoc+"</td>\n\
                        <td>"+value.razonsocial+"</td>\n\
                        <td class='text-end'>"+number_format(value.total, 2)+" $ </td>\n\
                        <td>\n\
                            <div class='btn-group'>\n\
                                " + reimpre + printPdf + "\n\
                            </div>\n\
                        </td>\n\
                    </tr>");
                });
                $("#modal_historial").modal('show');
            }
        },
        error: function() {
            alert("Error al intentar buscar el articulo!");
        }
    });
}

function mostrar_movimientos() {
    buscar_movimientos();
    $("#modal_movimientos").modal('show');
    $('#n_mov').focus();
}

function buscar_movimientos() {
    $("#detalle_movs").empty();
    $.ajax({
        url: url,
        type: "POST",
        data: {
            b_movs: idcierre
        },
        success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
                //alert(datos.msj);
            } else {
                let totmovs = 0;
                $(datos.movs).each(function(key, value) {
                    let clase = 'table-secondary';
                    let signo = '- ';
                    if (value.tipo == 'ingreso') {
                        clase = 'table-success';
                        signo = '+ ';
                        totmovs += parseFloat(round(value.valor, 2));
                    } else {
                        totmovs -= parseFloat(round(value.valor, 2));
                    }
                    let reimpre = "";
                    if (reimpresion == 1) {
                        reimpre = "<a class='btn btn-sm btn-success' href='"+url+"&reimprimir_mov="+value.idmovcaja+"' title='Reimprimir Movimiento'>\n\
                                        <span class='bi bi-printer-fill'></span>\n\
                                    </a>";
                    }
                    $("#detalle_movs").append("<tr class='"+clase+"'>\n\
                        <td>"+value.tipo+"</td>\n\
                        <td>"+value.nombre+"</td>\n\
                        <td class='text-end'>"+signo+number_format(value.valor, 2)+" $ </td>\n\
                        <td>\n\
                            " + reimpre + "\n\
                        </td>\n\
                    </tr>");
                });
                $("#tot_mov").html(number_format(totmovs, 2)+" $");
            }
        },
        error: function() {
            alert("Error al intentar buscar el articulo!");
        }
    });
}

function cerrar_caja() {
    $("#modal_cierrecaja").modal('show');
    $("#m001").select();
}

function calcular_cierre() {

    let totalcaja = 0;

    totalcaja += round(parseFloat($("#m001").val()) * 0.01, 2);
    totalcaja += round(parseFloat($("#m005").val()) * 0.05, 2);
    totalcaja += round(parseFloat($("#m010").val()) * 0.10, 2);
    totalcaja += round(parseFloat($("#m025").val()) * 0.25, 2);
    totalcaja += round(parseFloat($("#m050").val()) * 0.50, 2);
    totalcaja += round(parseFloat($("#m1").val()) * 1, 2);
    totalcaja += round(parseFloat($("#b1").val()) * 1, 2);
    totalcaja += round(parseFloat($("#b5").val()) * 5, 2);
    totalcaja += round(parseFloat($("#b10").val()) * 10, 2);
    totalcaja += round(parseFloat($("#b20").val()) * 20, 2);
    totalcaja += round(parseFloat($("#b50").val()) * 50, 2);
    totalcaja += round(parseFloat($("#b100").val()) * 100, 2);

    $("#total_caja").html(number_format(totalcaja, 2));
    $("#totalc").val(round(totalcaja, 2));
}

function mostrar_atajos() {
    $("#modal_atajos").modal('show');
}

function limpiar_campos() {
    $("#tipoid").attr('readonly', false);
    $("#identificacion").attr('readonly', false);
    $("#tipoid").val('R');
    $("#identificacion").val('');
    $("#razonsocial").val('');
    $("#nombrecomercial").val('');
    $("#telefono2").val('');
    $("#celular").val('');
    $("#email2").val('');
    $("#direccion2").val('');
    $("#regimen").val('GE');
}

function validar_cobrar() {
    saldo = $("#total_fac").val();
    totalcobro = 0;
    cambio = 0;
    $("#b_addfp").attr('disabled', false);
    $("#btn_guardar").attr('disabled', true);
    $("#detfp").empty();
    $("#total_venta").html(number_format(saldo, 2)+" $");
    $("#total_rec").val(number_format(0, 2));
    $("#cambio_fac").val(number_format(0, 2));
    $("#valor").val(round(saldo, 2));
    if (numlineas == 0) {
        alert("Sin detalle para Facturar.");
        return;
    }
    if ($("#idcliente").val() == null) {
        alert("El cliente no se encuentra seleccionado.");
        return;
    }

    if ($("#direccion").val() == '') {
        alert('Debe ingresar la direccion.');
        $("#direccion").focus();
        return
    }

    if ($("#telefono").val() == '') {
        alert('Debe ingresar el telefono.');
        $("#telefono").focus();
        return
    }

    if ($("#email").val() == '') {
        alert('Debe ingresar el email.');
        $("#email").focus();
        return
    }

    for (var i = 0; i < numlineas; i++) {
        if ($("#linea_" + i).length > 0) {
            if ($("#cantidad_" + i).val() <= 0) {
                $("#modal_cobrar").modal('hide');
                $("#cantidad_" + i).select();
                alert("Cantidad nó Válida.");
                return;
            }
            if ($("#pvpunitario_" + i).val() <= 0) {
                $("#modal_cobrar").modal('hide');
                $("#pvpunitario_" + i).select();
                alert("Precio nó Válido.");
                return;
            }
            if ($("#dto_" + i).val() < 0 || $("#dto_" + i).val() > 100) {
                $("#modal_cobrar").modal('hide');
                $("#dto_" + i).select();
                alert("Descuento nó Válido.");
                return;
            }
        }
    }
    $("#modal_cobrar").modal('show');
    $("#valor").select();
}

function buscar_cliente() {
    let tipoid = $("#tipoid").val();
    let identificacion = $("#identificacion").val();
    if (tipoid != '' && identificacion != '') {
        if (tipoid == 'C' || tipoid == 'R') {
            $.ajax({
                url: url,
                dataType: 'json',
                delay: 250,
                data: {
                    tipoid_b: tipoid,
                    identificacion_b: identificacion,
                },
                beforeSend: function() {
                    $("#tipoid").attr('readonly', true);
                    $("#identificacion").attr('readonly', true);
                },
                success: function(data) {
                    if (data.error == 'F') {
                        $("#razonsocial").val(data.razonsocial);
                        $("#nombrecomercial").val(data.nombre);
                        $("#direccion2").val(data.direccion);
                        $("#regimen").val(data.regimen);
                        if (data.obligado == 'No') {
                            $("#obligado").attr('checked', false);
                        } else {
                            $("#obligado").attr('checked', true);
                        }
                        if (data.agenteR == 'No') {
                            $("#agretencion").attr('checked', false);
                        } else {
                            $("#agretencion").attr('checked', true);
                        }
                    } else if (data.error == 'T') {
                        $("#tipoid").attr('readonly', false);
                        $("#identificacion").attr('readonly', false);
                        alert(data.msj);
                    } else if (data.error == 'R') {
                        $("#modal_nuevo_cliente").modal('hide');
                        alert(data.msj);
                    }
                },
            });
        } else {
            alert("Tipo de Identificación no Válido para realizar la busqueda.");
        }
    } else {
        alert("Debe tener la Identificación y el tipo de Identificación para realizar la busqueda.");
    }
}

function agregar_linea(articulo) {
    let btn_pres = '';
    if (numlineas > 0) {
        //Busco si el articulo ya se encuentra para aumentar la cantidad
        for (var i = 0; i < numlineas; i++) {
            if ($("#linea_" + i).length > 0) {
                if ($("#idarticulo_" + i).val() == articulo.idarticulo) {
                    let cantidad = parseFloat($("#cantidad_" + i).val());
                    cantidad += 1;
                    $("#cantidad_" + i).val(cantidad);
                    $("#cantidad_" + i).select();
                    calcular();
                    return;
                }
            }
        }
    }
    let porcentaje = 0;
    //Busco el porcentaje
    for (var i = 0; i < impuestos.length; i++) {
        if (impuestos[i].idimpuesto == articulo.idimpuesto) {
            porcentaje = parseInt(impuestos[i].porcentaje);
        }
    }
    let modificartot = '';
    if (modtotal == 1) {
        modificartot = '<button class="btn btn-sm btn-info" type="button" onclick="editar_total('+numlineas+')"><span class="bi bi-pen"></span></button>';
    }
    eliminar_linea = "$('#linea_" + numlineas + "').remove()";
    buscar_pre = "buscar_presentacion("+articulo.idarticulo+", '"+articulo.nombre+"', "+numlineas+")";
    let linea = '<tr id="linea_' + numlineas + '">\n\
        <input type="hidden" id="idarticulo_' + numlineas + '" name="idarticulo_' + numlineas + '" value="' + articulo.idarticulo + '">\n\
        <input type="hidden" id="idimpuesto_' + numlineas + '" name="idimpuesto_' + numlineas + '" value="' + articulo.idimpuesto + '">\n\
        <input type="hidden" id="factor_' + numlineas + '" name="factor_' + numlineas + '" value="1">\n\
        <input type="hidden" id="idunidad_' + numlineas + '" name="idunidad_' + numlineas + '" value="'+articulo.idunidad+'">\n\
        <input type="hidden" id="valoriva_' + numlineas + '" name="valoriva_' + numlineas + '" value="0">\n\
        <input type="hidden" id="pvptotal_' + numlineas + '" name="pvptotal_' + numlineas + '" value="0">\n\
        <input type="hidden" id="stockfis_' + numlineas + '" name="stockfis_' + numlineas + '" value="' + articulo.stock_fisico + '">\n\
        <input type="hidden" id="tipo_' + numlineas + '" name="tipo_' + numlineas + '" value="' + articulo.tipo + '">\n\
        <input type="hidden" id="controlar_stock_' + numlineas + '" name="controlar_stock_' + numlineas + '" value="' + articulo.controlar_stock + '">\n\
        <td>' + articulo.codprincipal + '</td>\n\
        <td><input class="form-control form-control-sm" type="text" id="descripcion_' + numlineas + '" name="descripcion_' + numlineas + '" value="' + articulo.nombre + '"></td>';
    if (paso_um == 1) {
        if (articulo.tipo == 1) {
            linea += '<td class="text-center" id="txt_medida_'+numlineas+'">'+articulo.medida+'</td>';
            btn_pres += '<button class="btn btn-sm btn-info" type="button" onclick="'+buscar_pre+'"><span class="bi bi-speedometer2"></span></button>';
        } else {
            linea += '<td class="text-center">-</td>';
        }
    }
    linea += '<td><input class="form-control form-control-sm solo_precios2 text-end" type="text" onblur="validar_stock(' + numlineas + ')" id="cantidad_' + numlineas + '" name="cantidad_' + numlineas + '" value="1"></td>\n\
        <td><input class="form-control form-control-sm solo_precios2 text-end" type="text" onblur="calcular()" id="pvpunitario_' + numlineas + '" name="pvpunitario_' + numlineas + '" value="' + articulo.precio + '"></td>\n\
        <td><input class="form-control form-control-sm solo_precios2 text-end" type="text" onblur="calcular()" id="dto_' + numlineas + '" name="dto_' + numlineas + '" value="0"></td>\n\
        <td><input class="form-control form-control-sm solo_precios2 text-end" readonly type="text" id="iva_' + numlineas + '" name="iva_' + numlineas + '" value="' + porcentaje + '"></td>\n\
        <td><input class="form-control form-control-sm solo_precios2 text-end" readonly type="text" id="total_' + numlineas + '" name="total_' + numlineas + '" value="0"></td>\n\
        <td><div class="btn-group">'+modificartot+'<button class="btn btn-sm btn-danger" type="button" onclick="' + eliminar_linea + ';calcular();"><span class="bi bi-trash"></span></button>'+btn_pres+'</div></td>\n\
    </tr>';
    $("#detalle_fac").prepend(linea);
    $(".solo_precios2").validCampo('0123456789.');
    $("#cantidad_" + numlineas).select();
    numlineas++;
    $("#numlineas").val(numlineas);
    calcular();
}

function validar_stock(linea) {
    let stockfis = parseFloat($("#stockfis_" + linea).val());
    let cantidad = parseFloat($("#cantidad_" + linea).val()) * parseFloat($("#factor_" + linea).val());
    let tipo = $("#tipo_" + linea).val();
    let controlar_stock = $("#controlar_stock_" + linea).val();
    if (controlar_stock == 1 && tipo == 1) {
        if (cantidad > stockfis) {
            alert("No dispone de la cantidad solicitada, Stock Actual: " + (stockfis / parseFloat($("#factor_" + linea).val())));
            $("#cantidad_" + linea).val((stockfis / parseFloat($("#factor_" + linea).val())));
        }
    }
    calcular();
}

function calcular() {
    let subtotal = 0;
    let descuento = 0;
    let iva = 0;
    let totalf = 0;
    for (var i = 0; i < numlineas; i++) {
        if ($("#linea_" + i).length > 0) {
            let cantidad = parseFloat($("#cantidad_" + i).val());
            let pvpunitario = parseFloat($("#pvpunitario_" + i).val());
            let dto = parseFloat($("#dto_" + i).val());
            //let valorice = parseFloat($("#valorice").val());
            //let valorice = parseFloat($("#valorice").val());
            let valorice = parseFloat(0);
            let idimpuesto = $("#idimpuesto_" + i).val();
            let porcentaje = 0;
            //Busco el porcentaje
            for (var j = 0; j < impuestos.length; j++) {
                if (impuestos[j].idimpuesto == idimpuesto) {
                    porcentaje = parseInt(impuestos[j].porcentaje);
                }
            }
            let valordto = (cantidad * pvpunitario) * (dto / 100);
            descuento += valordto;
            //Calculo el subtotal sin impuestos
            let pvptotal = (cantidad * pvpunitario) * ((100 - dto) / 100);
            subtotal += pvptotal
            $("#pvptotal_" + i).val(pvptotal);
            let valoriva = (pvptotal + valorice) * (porcentaje / 100);
            iva += valoriva;
            $("#valoriva_" + i).val(valoriva);
            let total = pvptotal + valorice + valoriva;
            totalf += total;
            $("#total_" + i).val(total);
        }
    }
    $("#subtotal").html(number_format(subtotal, 2));
    $("#totaldescuento").html(number_format(descuento, 2));
    $("#totaliva").html(number_format(iva, 2));
    $("#totalfac").html(number_format(totalf, 2));
    $("#total_venta").html(number_format(totalf, 2) + " $");
    $("#total_fac").val(number_format(totalf, 2));
    saldo = round(totalf, 2);
    totalfac = round(totalf, 2);
}

function validar_guardar(btn) {
    $("#btn_guardar").attr('disabled', true);
    if (numlineas == 0) {
        alert("Sin detalle para Facturar.");
        $("#btn_guardar").attr('disabled', false);
        return;
    }
    if ($("#isnota").length > 0) {
        if (!$("#isnota").is(':checked')) {
            if ($("#idcliente").val() == $("#identcons").val()) {
                totf = $("#total_fac").val();
                if (parseFloat(totf) > parseFloat(val_max_cf)) {
                    alert("La Factura de Consumidor Final no puede ser superior a: "+val_max_cf);
                    $("#btn_guardar").attr('disabled', false);
                    return;
                }
            }
        }    
    } else {
        if ($("#idcliente").val() == $("#identcons").val()) {
            totf = $("#total_fac").val();
            if (parseFloat(totf) > parseFloat(val_max_cf)) {
                alert("La Factura de Consumidor Final no puede ser superior a: "+val_max_cf);
                $("#btn_guardar").attr('disabled', false);
                return;
            }
        }
    }

    let numfp = $("#lineasfp").val();
    let arts = [];
    for (let i = 0; i < numlineas; i++) {
        if ($("#linea_" + i).length > 0) {
            arts.push([$("#idarticulo_" + i).val(), parseFloat($("#cantidad_" + i).val() * $("#factor_").val())]);
        }
    }
    if (arts.length > 0) {
        $.ajax({
            url: url,
            async: false,
            dataType: 'json',
            data: {
                articulos_v: JSON.stringify(arts),
                establecimiento: establecimiento,
            },
            beforeSend: function() {},
            success: function(data) {
                if (data.error == 'T') {
                    alert(data.msj);
                    $("#modal_cobrar").modal('hide');
                    $("#btn_guardar").attr('disabled', false);
                    return;
                }
            },
        });
    } else {
        alert("Sin items para Facturar.");
        $("#btn_guardar").attr('disabled', false);
        return;
    }
    btn.form.submit();
}

function add_fp() {
    let valor = round(parseFloat($("#valor").val()), 2);
    let esefec = $("#esefec").val();
    let fec_trans = $("#fec_trans").val();
    let num_doc = $("#num_doc").val();
    let idfp = $("#fpselec").val();
    let valorreal = valor;
    let cambio1 = 0;
    if (idfp != '') {
        if (valor > 0) {
            //No hago nada
        } else {
            alert('Valor a Cobrar no Válido.');
            return;
        }
        if (esefec == 'NO') {
            if (valor > saldo) {
                alert('El valor a cancelar no puede ser mayor al saldo pendiente de cancelar. Saldo Pendiente: '+ number_format(saldo, 2));
                $("#valor").val(round(saldo, 2));
                $("#valor").select();
                return;
            }
        } else {
            if (valor > saldo) {
                valorreal = saldo;
            }
            cambio1 = round(valor - saldo, 2);
            if (cambio1 > 0) {
                cambio += cambio1;
            } else {
                cambio1 = 0;
            }
        }
        //Valido los campos si estan completos.
        if ($("#num_doc").is(':disabled')) {
            //no valido
        } else {
            if ($("#num_doc").val() == '') {
                alert('Debe ingresar el Número de Documento.');
                return;
            }
        }
    } else {
        alert('Debe seleccionar una Forma de Pago.');
        return;
    }

    //Busco las lineas de las formas de pago.
    let lineasfp = parseFloat($("#lineasfp").val());
    let nombrefp = $("#fpselec").find(':selected').data('nombre');

    //si pasa las validaciones ingreso en la tabla de las formas de pago.
    let linea = '<tr id="idlinfp_'+lineasfp+'">\n\
        <input type="hidden" id="esefec_'+lineasfp+'" value="'+esefec+'">\n\
        <input type="hidden" id="cambio_'+lineasfp+'" value="'+cambio1+'">\n\
        <input type="hidden" name="valorreal_'+lineasfp+'" id="valorreal_'+lineasfp+'" value="'+valorreal+'">\n\
        <input type="hidden" name="idformapago_'+lineasfp+'" id="idformapago_'+lineasfp+'" value="'+idfp+'">\n\
        <input type="hidden" name="numdoc_'+lineasfp+'" id="numdoc_'+lineasfp+'" value="'+num_doc+'">\n\
        <input type="hidden" name="valor_'+lineasfp+'" id="valor_'+lineasfp+'" value="'+valor+'">\n\
        <input type="hidden" name="fec_trans_'+lineasfp+'" id="fec_trans_'+lineasfp+'" value="'+fec_trans+'">\n\
        <td>'+nombrefp+'</td>\n\
        <td>'+fec_trans+'</td>\n\
        <td>'+num_doc+'</td>\n\
        <td>'+valor+'</td>\n\
        <td><button class="btn btn-sm btn-danger" type="button" onclick="eliminar_cobro('+lineasfp+')"><span class="bi bi-trash2"></span></button></td>\n\
    </tr>';
    totalcobro += round(valor, 2);
    $("#total_rec").val(number_format(totalcobro, 2));
    if (cambio >= 0) {
        $("#cambio_fac").val(number_format(cambio, 2));
    }
    saldo -= valor;
    saldo = round(saldo, 2);
    $("#detfp").prepend(linea);
    lineasfp++;
    $("#lineasfp").val(lineasfp);
    reiniciar_fp();
    if (saldo > 0) {
        //No hago nada
    } else {
        $("#b_addfp").attr('disabled', true);
        $("#btn_guardar").attr('disabled', false);
    }
}

function reiniciar_fp() {
    $("#num_doc").val('');
    if (saldo > 0) {
        $("#valor").val(round(saldo, 2));
        $("#b_addfp").attr('disabled', false);
        $("#btn_guardar").attr('disabled', true);
    } else {
        $("#valor").val(round(0, 2));
        $("#b_addfp").attr('disabled', true);
        $("#btn_guardar").attr('disabled', false);
    }
    $("#num_doc").attr('disabled', true);
    $("#valor").select();
}

function eliminar_cobro( lfp ) {
    let cambio1 = round(parseFloat($('#cambio_'+lfp).val()), 2);
    let valor = round(parseFloat($('#valor_'+lfp).val()), 2);
    $('#idlinfp_'+lfp).remove();

    saldo += round(valor, 2);
    totalcobro -= round(valor, 2);
    cambio -= round(cambio1, 2);
    $("#total_rec").val(number_format(totalcobro, 2));
    if (cambio >= 0) {
        $("#cambio_fac").val(number_format(cambio, 2));
    } else {
        $("#cambio_fac").val(number_format(0, 2));
    }

    reiniciar_fp();
}

function buscar_fp() {
    let idfp = $("#fpselec").val();
    if (idfp != '') {
        $.ajax({
            url: url,
            async: false,
            type: 'post',
            dataType: 'json',
            data: {
                idfp: idfp,
            },
            beforeSend: function() {},
            success: function(data) {
                if (data.error == 'T') {
                    alert(data.msj);
                    return;
                } else {
                    $("#num_doc").val('');
                    $("#valor").val(round(saldo, 2));
                    if (data.fpago.num_doc) {
                        $("#num_doc").attr('disabled', false);
                    } else {
                        $("#num_doc").attr('disabled', true);
                    }
                    if (data.fpago.esefec) {
                        $("#esefec").val('SI');
                    } else {
                        $("#esefec").val('NO');
                    }
                    $("#valor").select();
                }
            },
        });
    } else {
        alert('Seleccione la Forma de Pago.');
        $("#esefec").val('NO');
        return;
    }
}

function buscar_presentacion(idarticulo, nombre, numlin)
{
    $("#detalle_pre").empty();
    $.ajax({
        url: url,
        type: "POST",
        data: {
            presentacion: idarticulo
        },
        success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
                alert(datos.msj);
            } else {
                $(datos.presentaciones).each(function(key, value) {
                    let funcion = 'aplicar_um('+value.idunidad+', "'+value.descripcion+'", '+value.precio+', '+value.factor+', '+numlin+');';
                    $("#detalle_pre").append("<tr>\n\
                        <td>"+value.descripcion+"</td>\n\
                        <td class='text-end'>"+value.factor+"</td>\n\
                        <td class='text-end'>"+number_format(value.precio, 6)+"</td>\n\
                        <td class='text-end'>"+number_format(value.total, 2)+" $ </td>\n\
                        <td>\n\
                            <a class='btn btn-sm btn-info' onclick='"+funcion+"' title='Aplicar Unidad de Medida'>\n\
                                <span class='bi bi-check-circle'></span>\n\
                            </a>\n\
                        </td>\n\
                    </tr>");
                });
                $("#articuloname").html(nombre);
                $("#modal_presentaciones").modal('show');
            }
        },
        error: function() {
            alert("Error al intentar buscar las presentaciones!");
        }
    });
}

function editar_total(numlin) {
    $('#numlineasmod').val(numlin);
    $('#edit_total').val($("#total_"+numlin).val());
    $('#modal_editar_total').modal('show');
    $('#edit_total').select();
}

function recalcular_total()
{
    let lin = $('#numlineasmod').val();
    //obtengo el valor digitado
    let total = parseFloat($("#edit_total").val());

    if (total != '') {
        //Recupero el id del impuesto
        let idimpuesto = $("#idimpuesto_"+lin).val();
        //busco el porcentaje
        let porcentaje = 0;
        //Busco el porcentaje
        for (var i = 0; i < impuestos.length; i++) {
            if (impuestos[i].idimpuesto == idimpuesto) {
                porcentaje = parseInt(impuestos[i].porcentaje);
            }
        }
        //Calculo el Neto
        let neto = total / (1 + (porcentaje/100));
        //Busco la cantidad
        let cantidad = $("#cantidad_"+lin).val();
        //calculo el precio Unitario
        let pvpunitario = round(neto / cantidad, 5);
        $("#pvpunitario_"+lin).val(pvpunitario);
        //encero el descuento
        $("#dto_"+lin).val(0);

        calcular();
    }

    $('#modal_editar_total').modal('hide'); 
}

function aplicar_um(idunidad, nombre, precio, factor, numlin)
{
    $("#txt_medida_"+numlin).html(nombre);
    $("#factor_"+numlin).val(factor);
    $("#idunidad_"+numlin).val(idunidad);
    $("#pvptotal_"+numlin).val(precio);
    $("#pvpunitario_"+numlin).val(precio);
    calcular();
    $("#modal_presentaciones").modal('hide');
}
if (mosmenu == 1) {
    function mostrar_menu(modal = true)
    {
        $("#gruponame").html('');
        $("#b_detalle").empty();
        $.ajax({
            url: url,
            type: "POST",
            data: {
                b_grupos: ''
            },
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    let div = '\n\
                        <div class="col-sm-12 text-center">\n\
                            <h4>'+datos.msj+'</h4>\n\
                        </div>';
                    $("#b_detalle").append(div);
                } else {
                    $(datos.grupos).each(function(key, value) {
                        let funcion = "buscar_arts('"+value.idgrupo+"')";
                        if (value.espadre == '1') {
                            funcion = "buscar_grups('"+value.idgrupo+"', '"+value.nombre+"')";
                        }
                        let div = '\n\
                            <div class="col-sm-6 p-1">\n\
                                <div class="card" onclick="'+funcion+'">\n\
                                    <div class="row g-0">\n\
                                        <div class="col-md-5">\n\
                                            <img src="'+value.url_imagen+'" class="img-fluid">\n\
                                        </div>\n\
                                        <div class="col-md-7">\n\
                                            <div class="card-body text-center">\n\
                                                <h5 class="card-title">'+value.nombre+'</h5>\n\
                                            </div>\n\
                                        </div>\n\
                                    </div>\n\
                                </div>\n\
                            </div>';
                        $("#b_detalle").append(div);
                    });
                }
            },
            error: function() {
                alert("Error al intentar buscar las grupos!");
            }
        });
        if (modal) {
            $('#modal_menu').modal('show');
        }
    }

    function buscar_arts(idgrupo)
    {
        $("#b_detalle").empty();
        $.ajax({
            url: url,
            type: "POST",
            data: {
                b_artics: idgrupo,
                establecimiento: establecimiento
            },
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    let div = '\n\
                        <div class="col-sm-12 text-center">\n\
                            <h4>'+datos.msj+'</h4>\n\
                        </div>';
                    $("#b_detalle").append(div);
                    $("#idpadre").val('');
                } else {
                    $(datos.articulos).each(function(key, value) {
                        let funcion = "agregar_articulo("+value.idarticulo+")";
                        let div = '\n\
                            <div class="col-sm-6 p-1">\n\
                                <div class="card" onclick="'+funcion+'">\n\
                                    <div class="row g-0">\n\
                                        <div class="card-header text-center"><b>\n\
                                            '+value.nombre+'</b>\n\
                                        </div>\n\
                                        <div class="col-md-4">\n\
                                            <img src="'+value.url_imagen+'" class="img-fluid">\n\
                                        </div>\n\
                                        <div class="col-md-7">\n\
                                            <div class="card-body">\n\
                                                <p class="card-text"><h5>Stock: '+value.stock_fisico+'<h5></p>\n\
                                                <p class="card-text"><h4 class="text-end"><b>'+value.pvppublico+' $</b><h4></p>\n\
                                            </div>\n\
                                        </div>\n\
                                    </div>\n\
                                </div>\n\
                            </div>';
                        $("#b_detalle").append(div);
                    });
                    $("#idpadre").val(datos.idpadre);
                }
            },
            error: function() {
                alert("Error al intentar buscar las Articulos!");
            }
        });
    }

    function buscar_grups(idgrupo, nombre = '')
    {
        if (nombre != '') {
            $("#gruponame").html(nombre);
        }
        $("#b_detalle").empty();
        $.ajax({
            url: url,
            type: "POST",
            data: {
                b_subgrupos: idgrupo
            },
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    let div = '\n\
                        <div class="col-sm-12 text-center">\n\
                            <h4>'+datos.msj+'</h4>\n\
                        </div>';
                    $("#b_detalle").append(div);
                    $("#idpadre").val('');
                } else {
                    $(datos.grupos).each(function(key, value) {
                        let funcion = "buscar_arts('"+value.idgrupo+"')";
                        if (value.espadre == '1') {
                            funcion = "buscar_grups('"+value.idgrupo+"')";
                        }
                        let div = '\n\
                            <div class="col-sm-6 p-1">\n\
                                <div class="card" onclick="'+funcion+'">\n\
                                    <div class="row g-0">\n\
                                        <div class="col-md-5">\n\
                                            <img src="'+value.url_imagen+'" class="img-fluid">\n\
                                        </div>\n\
                                        <div class="col-md-7">\n\
                                            <div class="card-body text-center">\n\
                                                <h5 class="card-title">'+value.nombre+'</h5>\n\
                                            </div>\n\
                                        </div>\n\
                                    </div>\n\
                                </div>\n\
                            </div>';
                        $("#b_detalle").append(div);
                    });
                    $("#idpadre").val(datos.idpadre);
                }
            },
            error: function() {
                alert("Error al intentar buscar las Grupos!");
            }
        });
    }

    function agregar_articulo(idarticulo) {
        $.ajax({
            url: url,
            type: "POST",
            data: {
                idarticulo: idarticulo,
                establecimiento: establecimiento
            },
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    alert(datos.msj);
                } else {
                    agregar_linea(datos.art);
                }
            },
            error: function() {
                alert("Error al intentar buscar el articulo!");
            }
        });
    }

    function tratar_grupos()
    {
        let idpadre = $("#idpadre").val();
        console.log(idpadre);
        if (idpadre == '') {
            mostrar_menu(false);
        } else {
            buscar_grups(idpadre);
        }
    }
}

$(document).keydown(function(e) {
    if (e.which == 27) {
        $('#codigobarras').focus();
    } else if (e.which == 120) {
        //Atajo para guardar la factura F9
        validar_cobrar();
    } else if (e.which == 113) {
        //Atajo para Nuevo Cliente F2
        nuevo_cliente();
    } else if (e.which == 115) {
        //Atajo para Mostrar historial F4
        mostrar_historial();
    } else if (e.which == 117) {
        //Atajo para movimientos de Caja F6
        mostrar_movimientos();
    } else if (e.which == 119) {
        //Atajo para Cierre de Caja F8
        cerrar_caja();
    } else if (e.altKey == true && e.keyCode == 78) {
        //Atajo para Abrir una Nueva Pestaña ALT + N
        let win = window.open(url, '_blank');
        win.focus();
    } else if (e.altKey == true && e.keyCode == 73) {
        //Atajo para Ver Atajos ALT + I
        mostrar_atajos();
    } else if (e.altKey == true && e.keyCode == 77) {
        //Atajo para Ver Atajos ALT + I
        if (mosmenu == 1) {
            mostrar_menu();
        }
    }
})