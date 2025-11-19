$(document).ready(function() {
   actualizar_detalle_retencion();
   $("#f_nueva_linea").submit(function (event) {
      $("#btn_aniadir").attr('disabled', true);
      calcular_total();
      event.preventDefault();
      $.ajax({
         async: false,
         url: retencion_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               limpiar_campos();
               actualizar_detalle_retencion();
            }
         },
         error: function(){
             alert("Error al intentar guardar la linea de la Retención!");
         }
      });
      $("#btn_aniadir").attr('disabled', false);
   });

   $("#b_eliminar_retencion").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar la Retención?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_retenciones_cliente&delete='+idretencioncli;
            }
         }
      });
   });

   $("#idfactura").select2({
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
               buscar_factura: params.term,
               idcliente: idcliente
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idfacturacli, text: "N.: "+obj.numero_documento +" - Fec: "+ obj.fec_emision };
               })
            };
         },
         cache: true
      },
      placeholder: 'Buscar Factura',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $("#b_tiporetencion").select2({
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
               buscar_tiporetencion: params.term
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idtiporetencion, text: obj.nombre +" - "+ obj.porcentaje };
               })
            };
         },
         cache: true
      },
      placeholder: 'Buscar Tipo de Retención',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $('#b_tiporetencion').on("change", function(e) {
      let idtiporetencion = $("#b_tiporetencion").val();
      if (idtiporetencion  === null)
      {
      } else {
         let idfactura = -1;
         if ($("#nofactura").is(':checked')) {
         } else {
            idfactura = $("#idfactura").val();
         }
         if (idfactura === null)
         {
            alert("Debe seleccionar la Retención.");
         } else {
            e.preventDefault();
            $.ajax({
               url: url,
               type: "POST",
               data: {idtiporetencion: idtiporetencion, idfactura_ret: idfactura},
               success: function(data){
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#idtiporetencion").val(datos.tipret.idtiporetencion);
                     $("#baseimponible").val(datos.base);
                     $("#especie").val(datos.tipret.especie);
                     $("#codigo").val(datos.tipret.codigo);
                     $("#porcentaje").val(datos.tipret.porcentaje);
                     calcular_total()
                  }
               },
               error: function(){
                   alert("Error al intentar buscar el articulo!");
               }
            });
         }
         $("#b_tiporetencion").empty().trigger('change');
      }
   });
});

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea de la Retención?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: retencion_url,
               type: "POST",
               data: {eliminar_linea: idlinea},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                  }
                  actualizar_detalle_retencion();
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Retención!");
               }
            });
         }
      }
   });
}

function actualizar_detalle_retencion() {
   $.ajax({
      url: retencion_url+"&actdetret",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_ret").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
            $("#totalret").html(number_format(0, 2));
         } else {
            $(datos.lineas).each(function(key, value){
               let eliminar = '';
               if (allow_delete == 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlinearetencion+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               let num_doc = value.numero_documento_mod;
               if (value.iddocumento_mod) {
                   num_doc = "<a href='index.php?page=ver_factura_cliente&id="+value.iddocumento_mod+"' target='_blank'>"+value.numero_documento_mod+"</a>"
               }
               $("#detalle_ret").append("<tr>\n\
                  <td>"+num_doc+"</td>\n\
                  <td>"+value.fec_emision_mod+"</td>\n\
                  <td style='text-align: right;'>"+value.baseimponible+"</td>\n\
                  <td style='text-align: right;'>"+value.especie+"</td>\n\
                  <td style='text-align: right;'>"+value.codigo+"</td>\n\
                  <td style='text-align: right;'>"+value.porcentaje+"</td>\n\
                  <td style='text-align: right;'>"+value.total+"</td>\n\
                  <td>"+eliminar+"</td>\n\
               </tr>");
            });
            
            $("#totalret").html(number_format(datos.retencion.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la factura!");
      }
   });         
}

function calcular_total() {
   let baseimponible = parseFloat($("#baseimponible").val());
   let porcentaje = parseFloat($("#porcentaje").val());
   //Calculo el subtotal sin impuestos
   let total = round(baseimponible * (porcentaje/100), 2);
   $("#total").val(total);
}

function limpiar_campos(factura = false) {
   if (factura) {
      $("#nofactura").prop('checked', false);
      mostrar_div_factura();
   }
   $("#baseimponible").val(0);
   $("#especie").val("");
   $("#codigo").val("");
   $("#porcentaje").val(0);
   $("#total").val(0);
}

function mostrar_div_factura() {
   if ($("#nofactura").is(':checked')) {
      $("#idfactura").attr('required', false);
      $("#idfactura").attr('disabled', true);
      $("#idfactura").val('').trigger('change');
      // crea un nuevo objeto `Date`
      let today = new Date();       
      // `getDate()` devuelve el día del mes (del 1 al 31)
      let day = today.getDate();
      if (day < 10) {
         day = '0'+day;
      }
      // `getMonth()` devuelve el mes (de 0 a 11)
      let month = today.getMonth() + 1;
      if (month < 10) {
         month = '0'+month;
      }
      let year = today.getFullYear();
      let hoy = `${year}-${month}-${day}`;
      $('#fac_manual').html('<div class="col-sm-3">\n\
            <label for="doc_estab" class="col-form-label">Nro. Estab.:</label>\n\
            <input type="text" class="form-control form-control-sm solo_numeros2" name="doc_estab" id="doc_estab" placeholder="Nro. Est." required maxlength="3">\n\
         </div>\n\
         <div class="col-sm-3">\n\
            <label for="doc_pto" class="col-form-label">Pto. Emisión:</label>\n\
            <input type="text" class="form-control form-control-sm solo_numeros2" name="doc_pto" id="doc_pto" placeholder="Pto. Emi." required maxlength="3">\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <label for="doc_secuen" class="col-form-label">Secuencial:</label>\n\
            <input type="text" class="form-control form-control-sm solo_numeros2" name="doc_secuen" id="doc_secuen" placeholder="Nro. Doc." required maxlength="9">\n\
         </div>\n\
         <div class="col-sm-2">\n\
            <label for="doc_fecemi" class="col-form-label">Fecha Doc:</label>\n\
            <input type="date" class="form-control form-control-sm" name="doc_fecemi" id="doc_fecemi" value="'+hoy+'" placeholder="Fecha de Emisión." required">\n\
         </div>');
      $(".solo_numeros2").validCampo('0123456789');
   } else {
      $("#idfactura").attr('required', true);
      $("#idfactura").attr('disabled', false);
      $('#fac_manual').html('');
      $("#idfactura").val('').trigger('change');
   }
}