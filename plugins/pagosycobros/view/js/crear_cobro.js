$(document).ready(function() {
    $("#f_nuevo_cobro").submit(function(event) {
        $("#btn_save_cobro").attr('disabled', true);
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
                    $("#btn_save_cobro").attr('disabled', false);
                } else {
                    alert(datos.msj);
                    window.location.href = datos.url;
                }
            },
            error: function() {
                alert("Error al intentar guardar el Cobro!");
            }
        });
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
        minimumInputLength: 3,
        theme: 'bootstrap-5',
        allowClear: true
    });
});
$(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
});

function activar_saldo(idfactura, calc = true) {
    let saldo = parseFloat($("#saldop_" + idfactura).html());
    if ($("#marcar_" + idfactura).is(':checked')) {
        $("#valor_" + idfactura).attr('disabled', false);
        $("#valor_" + idfactura).val(number_format(saldo));
        $("#valor_" + idfactura).select();
    } else {
        $("#valor_" + idfactura).val('0.00');
        $("#saldof_" + idfactura).html(number_format(saldo));
        $("#valor_" + idfactura).attr('disabled', true);
    }
    if (calc) {
        calcular_total();
    }
}

function calcular_total() {
    let total = 0;
    $("input[type=checkbox]:checked").each(function() {
        let idfactura = $(this).val();
        if (idfactura != 'on') {
            let valor = parseFloat($("#valor_" + idfactura).val());
            let pendiente = parseFloat($("#saldop_" + idfactura).html());
            if (valor > pendiente) {
                alert('El valor a cobrar no puede ser superior al valor pendiente de cobro.');
                valor = pendiente;
                $("#valor_" + idfactura).val(pendiente);
                $("#valor_" + idfactura).select();
            }
            total += valor;
            let saldof = parseFloat(pendiente - valor);
            $("#saldof_" + idfactura).html(number_format(saldof));
        }
    });
    let totalcobrar = parseFloat($("#total_cobrar").val());
    let pendientecobrar = round(parseFloat(totalcobrar - total), 2);
    $("#total_pagado").html(number_format(total));
    $("#saldo_final").html(number_format(pendientecobrar));
    if (total > 0) {
        $("#div_forma_pago").show('slow');
        $("#btn_save_cobro").prop('disabled', false);
        $("#fpselec").val('');
        $("#num_doc").val('');
        $("#num_doc").prop('disabled', true);
        $("#observaciones").val('');
    } else {
        $("#div_forma_pago").hide('slow');
        $("#btn_save_cobro").prop('disabled', true);
    }
}

function marcar_todos() {
    if ($("#mtodos").is(':checked')) {
        $("#mtodos2").prop('checked', true);
        $(".facturaspen").each(function() {
            let idfactura = $(this).val();
            $("#marcar_" + idfactura).prop('checked', true);
            activar_saldo(idfactura, false);
        });
    } else {
        $("#mtodos2").prop('checked', false);
        $(".facturaspen").each(function() {
            let idfactura = $(this).val();
            $("#marcar_" + idfactura).prop('checked', false);
            activar_saldo(idfactura, false);
        });
    }
    calcular_total();
}

function marcar_todos2() {
    if ($("#mtodos2").is(':checked')) {
        $("#mtodos").prop('checked', true);
    } else {
        $("#mtodos").prop('checked', false);
    }
    marcar_todos();
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