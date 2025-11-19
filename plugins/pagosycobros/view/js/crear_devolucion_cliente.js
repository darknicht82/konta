$(document).ready(function() {
   $("#f_nueva_devolucion").submit(function (event) {
      $("#btn_save_devolucion").attr('disabled', true);
      event.preventDefault();
      $.ajax({
         async: false,
         url: url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data){
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
               $("#btn_save_devolucion").attr('disabled', false);
            } else {
               alert(datos.msj);
               window.location.href = datos.url;
            }
         },
         error: function(){
             alert("Error al intentar guardar el Pago!");
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
         inputTooShort: function () {
            return "Ingrese al menos 3 caracteres para realizar la busqueda";
         },
      },
      ajax: {
         url: url,
         dataType: 'json',
         delay: 250,
         data: function (params) {
            return {
               buscar_cliente: params.term
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idcliente, text: obj.identificacion +" - "+ obj.razonsocial };
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

function activar_saldo(iddocumento, calc = true)
{
   let saldo = parseFloat($("#saldop_"+iddocumento).html());
   if ($("#marcar_"+iddocumento).is(':checked')) {
      $("#valor_"+iddocumento).attr('disabled', false);
      $("#valor_"+iddocumento).val(number_format(saldo));
      $("#valor_"+iddocumento).select();
   } else {
      $("#valor_"+iddocumento).val('0.00');
      $("#saldof_"+iddocumento).html(number_format(saldo));
      $("#valor_"+iddocumento).attr('disabled', true);
   }
   if (calc) {
      calcular_total();
   }
}

function calcular_total() {
   let total = 0;
   $("input[type=checkbox]:checked").each(function() {
      let iddocumento = $(this).val();
      if (iddocumento != 'on') {
         let valor = parseFloat($("#valor_"+iddocumento).val());
         let pendiente = parseFloat($("#saldop_"+iddocumento).html());

         if (valor > pendiente) {
            alert('El valor a devolver no puede ser superior al valor pendiente de devolucion.');
            valor = pendiente;
            $("#valor_"+iddocumento).val(pendiente);
            $("#valor_"+iddocumento).select();
         }
         total += valor;

         let saldof = parseFloat(pendiente - valor);
         $("#saldof_"+iddocumento).html(number_format(saldof));
      }
   });
   let totalpagar = parseFloat($("#total_anticipos").val());
   let pendientepagar = round(parseFloat(totalpagar - total), 2);

   $("#total_devolucion").html(number_format(total));
   $("#saldo_final").html(number_format(pendientepagar));

   if (total > 0) {
      $("#div_forma_pago").show('slow');
      $("#btn_save_devolucion").prop('disabled', false);
      $("#fpselec").val('');
      $("#num_doc").val('');
      $("#num_doc").prop('disabled', true);
      $("#observaciones").val('');
   } else {
      $("#div_forma_pago").hide('slow');
      $("#btn_save_devolucion").prop('disabled', true);
   }
}

function marcar_todos() {
   if ($("#mtodos").is(':checked')) {
      $("#mtodos2").prop('checked', true);
      $(".documentospen").each(function() {
         let iddocumento = $(this).val();
         $("#marcar_"+iddocumento).prop('checked', true);
         activar_saldo(iddocumento, false);
      });
   } else {
      $("#mtodos2").prop('checked', false);
      $(".documentospen").each(function() {
         let iddocumento = $(this).val();
         $("#marcar_"+iddocumento).prop('checked', false);
         activar_saldo(iddocumento, false);
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