$(document).ready(function() {
   $("#b_nuevo_cliente").click(function(event) {
      event.preventDefault();
      $("#modal_nuevo_cliente").modal('show');
      limpiar_campos();
      document.f_nuevo_cliente.identificacion.focus();
   });
   $("#f_nuevo_cliente").submit(function (event) {
      $("#btn_guardar_cli").attr('disabled', true);
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
            } else {
               alert(datos.msj);
               $('#idcliente').html('').select2({
                  data: [{id: datos.cliente.idcliente, text: datos.cliente.identificacion +" - "+ datos.cliente.razonsocial}],
                  theme: 'bootstrap-5',
                  allowClear: true
               });
               $("#modal_nuevo_cliente").modal('hide');
            }
         },
         error: function(){
             alert("Error al intentar guardar el Cliente!");
         }
      });
      $("#btn_guardar_cli").attr('disabled', false);
   });

   $("#f_nueva_factura").submit(function (event) {
      $("#btn_save_fac").attr('disabled', true);
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
               $("#btn_save_fac").attr('disabled', false);
            } else {
               window.location.href = datos.url;
            }
         },
         error: function(){
             alert("Error al intentar guardar la Factura!");
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

   $('#idcliente').on('change', function (e) {
      if (medidor == 1) {
         $("#medidorescli").find('option').not(':first').remove();
         let idcliente = $("#idcliente").val();
         if (idcliente) {
            $.ajax({
               async: false,
               url: url,
               type: "POST",
               data: {b_medidor: idcliente},
               success: function(data){
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $(datos.medidores).each(function(key, value) {
                        $("#medidorescli").append('<option value="'+value.idmedidor+'">'+value.numero+'</option>')
                     });
                  }
               },
               error: function(){
                   alert("Error al buscar los Medidores!");
               }
            });
         }
      }
   });
});

$(document).on('select2:open', () => {
   document.querySelector('.select2-search__field').focus();
});

function limpiar_campos()
{
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

function buscar_cliente()
{
   let tipoid = $("#tipoid").val();
   let identificacion = $("#identificacion").val();

   if (tipoid != '' && identificacion != '')
   {
      if (tipoid == 'C' || tipoid == 'R')
      {
         $.ajax({
            url: url,
            dataType: 'json',
            delay: 250,
            data : { 
               tipoid_b: tipoid,
               identificacion_b: identificacion,
            },
            beforeSend: function() {
               $("#tipoid").attr('readonly', true);
               $("#identificacion").attr('readonly', true);
            },
            success : function(data) {
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

function buscar_codigodoc()
{
   let iddocumento = $("#iddocumento").val();
   if (iddocumento != '') {
      $.ajax({
         url: url,
         dataType: 'json',
         delay: 250,
         data : { 
            iddocumento_b: iddocumento,
         },
         beforeSend: function() {
            //No hago nada
         },
         success : function(data) {
            //let datos = JSON.parse(data);
            if (data.error == 'T') {
               alert(data.msj);
            } else {
               validar_documento_seleccionado(data.documento);
            }
         },
      });
   } else {
      //Si no selecciono le reinicio los campos
      validar_documento_seleccionado('01');
   }
}

function validar_documento_seleccionado(coddoc)
{
   let idcliente = $("#idcliente").val();
   $("#coddocumento").val(coddoc);
   $("#observaciones").attr('required', false);
   if (coddoc == '04') {
      $("#observaciones").attr('required', true);
   }
   //Notas de Credito o Nota de Debido
   if (coddoc == '04' || coddoc == '05') {
      if (idcliente) {
         let div = '<div class="row"><br></div>\n\
            <div class="row" style="background-color: #D6EAF8; font-weight:bold;">\n\
               <div class="row"><br></div>\n\
               <input type="hidden" name="coddocumento_mod" id="coddocumento_mod" value="01">\n\
               <div class="col-sm-3">\n\
                  <label for="idfactura_mod" class="col-form-label">Documento Modificado:</label>\n\
                  <select id="idfactura_mod" name="idfactura_mod" class="form-select" required></select>\n\
                  <div class="form-check form-switch" style="margin-top: 0.5rem;">\n\
                     <input class="form-check-input" type="checkbox" id="nofactura" name="nofactura" onclick="mostrar_div_factura()">\n\
                     <label class="form-check-label" for="nofactura">El Documento no se encuentra en el sistema?</label>\n\
                  </div>\n\
               </div>\n\
               <div id="fac_manual" class="col-sm-9"></div>\n\
            </div>';
         $("#doc_modificado").html(div);
         let idcliente = $("#idcliente").val();
         $("#idfactura_mod").select2({
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
      } else {
         alert("Debe selecionar primero el cliente");
         $("#coddocumento").val('01');
         $("#iddocumento").val('').trigger('change');
      }
   } else {
      $("#doc_modificado").html('');
   }
}

function mostrar_div_factura() {
   if ($("#nofactura").is(':checked')) {
      $("#idfactura_mod").attr('required', false);
      $("#idfactura_mod").attr('disabled', true);
      $("#idfactura_mod").val('').trigger('change');
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
      $('#fac_manual').html('<div class="row"><div class="col-sm-2">\n\
            <label for="doc_estab" class="col-form-label">Nro. Estab.:</label>\n\
            <input type="text" class="form-control solo_numeros2" name="doc_estab" id="doc_estab" placeholder="Nro. Est." required maxlength="3">\n\
         </div>\n\
         <div class="col-sm-2">\n\
            <label for="doc_pto" class="col-form-label">Pto. Emisión:</label>\n\
            <input type="text" class="form-control solo_numeros2" name="doc_pto" id="doc_pto" placeholder="Pto. Emi." required maxlength="3">\n\
         </div>\n\
         <div class="col-sm-2">\n\
            <label for="doc_secuen" class="col-form-label">Secuencial:</label>\n\
            <input type="text" class="form-control solo_numeros2" name="doc_secuen" id="doc_secuen" placeholder="Nro. Doc." required maxlength="9">\n\
         </div>\n\
         <div class="col-sm-2">\n\
            <label for="doc_fecemi" class="col-form-label">Fecha Doc:</label>\n\
            <input type="date" class="form-control" name="doc_fecemi" id="doc_fecemi" value="'+hoy+'" placeholder="Fecha de Emisión." required">\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <label for="doc_nroaut" class="col-form-label">Nro. Aut.:</label>\n\
            <input type="text" class="form-control solo_numeros2" name="doc_nroaut" id="doc_nroaut" placeholder="Nro. Autorización" required maxlength="49" onblur="validar_campos2()">\n\
            <div class="row" style="margin-top: 0.5rem;">\n\
            </div>\n\
         </div>\n\
      </div>');
      $(".solo_numeros2").validCampo('0123456789');
   } else {
      $("#idfactura_mod").attr('required', true);
      $("#idfactura_mod").attr('disabled', false);
      $('#fac_manual').html('');
   }
}

function validar_campos2() {
   
   let paso = true;
   let msj = '';
   let nro_autorizacion = $("#doc_nroaut").val();
   let coddocumento = $("#coddocumento_mod").val();

   if (coddocumento != '03') {
      let largo = nro_autorizacion.length;
      if (largo == 49 || largo == 10) {
      } else {
         msj += "Numero de Autorización del Documento Modificado no Válido.";
         $("#observaciones").focus();
         paso = false;
      }
   }

   if (paso) {
      $("#btn_save_fac").attr('disabled', false);
   } else {
      alert(msj);
      $("#btn_save_fac").attr('disabled', true);
   }
}