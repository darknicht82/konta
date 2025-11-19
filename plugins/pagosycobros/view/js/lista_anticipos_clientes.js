$(document).ready(function() {
    $("#b_nuevo_cliente").click(function(event) {
        event.preventDefault();
        $("#modal_nuevo_cliente").modal('show');
        limpiar_campos();
        document.f_nuevo_cliente.identificacion.focus();
    });
    $("#b_nuevo_anticipo").click(function(event) {
        event.preventDefault();
        $("#modal_nuevo_anticipo").modal('show');
        limpiar_campos();
        document.f_nuevo_cliente.identificacion.focus();
    });
    $("#f_nuevo_anticipo").submit(function(event) {
        $("#btn_guardar_ant").attr('disabled', true);
        event.preventDefault();
        $.ajax({
            async: false,
            url: url,
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    alert(datos.msj);
                } else {
                    alert(datos.msj);
                    window.location.href = datos.url;
                }
            },
            error: function() {
                alert("Error al intentar guardar el Anticipo del Cliente!");
            }
        });
        $("#btn_guardar_ant").attr('disabled', false);
    });
    $("#f_nuevo_cliente").submit(function(event) {
        $("#btn_guardar_cli").attr('disabled', true);
        event.preventDefault();
        $.ajax({
            async: false,
            url: url,
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                let datos = JSON.parse(data);
                if (datos.error == 'T') {
                    alert(datos.msj);
                } else {
                    alert(datos.msj);
                    $("#modal_nuevo_cliente").modal('hide');
                    if (allow_modify == 1) {
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
                            data: [{
                                id: datos.cliente.idcliente,
                                text: datos.cliente.identificacion + " - " + datos.cliente.razonsocial
                            }],
                            placeholder: 'Buscar Cliente',
                            dropdownParent: $('#modal_nuevo_anticipo'),
                            minimumInputLength: 3,
                            theme: 'bootstrap-5',
                            allowClear: true
                        });
                        $("#modal_nuevo_anticipo").modal('show');
                        document.f_nuevo_anticipo.valor.focus();
                    }
                }
            },
            error: function() {
                alert("Error al intentar guardar el Cliente!");
            }
        });
        $("#btn_guardar_cli").attr('disabled', false);
    });
    $("#b_cliente").select2({
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
        dropdownParent: $('#modal_nuevo_anticipo'),
        minimumInputLength: 3,
        theme: 'bootstrap-5',
        allowClear: true
    });
});
$(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
});

function eliminar_anticipo(idanticipocli, numero) {
    bootbox.confirm({
        message: '¿Realmente desea eliminar el Anticipo ' + numero + '?',
        title: '<b>Atención</b>',
        callback: function(result) {
            if (result) {
                window.location.href = 'index.php?page=lista_anticipos_clientes&delete=' + idanticipocli;
            }
        }
    });
}

function limpiar_campos() {
    $("#tipoid").attr('readonly', false);
    $("#identificacion").attr('readonly', false);
    $("#tipoid").val('R');
    $("#identificacion").val('');
    $("#razonsocial").val('');
    $("#nombrecomercial").val('');
    $("#telefono").val('');
    $("#celular").val('');
    $("#email").val('');
    $("#direccion").val('');
    $("#regimen").val('GE');
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
                        $("#direccion").val(data.direccion);
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
                    if (data.fpago.num_doc) {
                        $("#num_doc").attr('disabled', false);
                    } else {
                        $("#num_doc").attr('disabled', true);
                    }
                }
            },
        });
    }
}

function mostrar_historial(idanticipocli) {
    $.ajax({
        url: url,
        async: false,
        type: 'post',
        dataType: 'json',
        data: {
            historial: idanticipocli,
        },
        beforeSend: function() {},
        success: function(data) {
            if (data.error == 'T') {
                alert(data.msj);
            } else {
                $("#det_anticipo").empty();
                $("#sal_anticipo").empty();
                let total = parseFloat(data.total);
                let anticipos = parseFloat(0);
                $(data.historial).each(function(key, value) {
                    let valor = parseFloat(round(value.credito - value.debito, 2));
                    anticipos += valor;
                    imprimir = '';
                    if (value.tipo == 'Cobro' && impresion == 1 && !value.idcobro) {
                        imprimir = "<a target='_blank' href='index.php?page=impresion_ventas&cobro&id=" + value.idtranscobro + "' id='b_imprimir_cobro' class='btn btn-sm btn-outline-primary'><span class='bi bi-printer'></span></a>";
                    }
                    $("#det_anticipo").append("<tr>\n\
                      <td style='text-align: left;'>" + value.tipo + "</td>\n\
                      <td style='text-align: right;'>" + value.fecha_trans + "</td>\n\
                      <td style='text-align: left;'>" + value.nombre_fp + "</td>\n\
                      <td style='text-align: right;'>" + valor + "</td>\n\
                      <td style='text-align: right;'><div class='btn-group'>" + imprimir + "</div></td>\n\
                   </tr>");
                });
                let saldo = parseFloat(round(anticipos, 2));
                $("#sal_anticipo").append("<tr>\n\
                   <th style='text-align: left;'>Total Ant.</th>\n\
                   <td style='text-align: right;'>" + total + "</td>\n\
                   <th style='text-align: right;'>Saldo:</th>\n\
                   <td style='text-align: right;'>" + saldo + "</td>\n\
                   <td style='text-align: right;'></td>\n\
                </tr>");
                $("#modal_historial").modal('show');
            }
        },
    });
}