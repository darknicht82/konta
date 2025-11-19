$(document).ready(function() {
   actualizar_detalle_doc();

   $("#b_procesar").click(function(event) {
      event.preventDefault();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: {procesar_doc: idbandejasri},
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#email").val(datos.email);
               $("#direccion").val(datos.direccion);
               $("#telefono").val(datos.telefono);
               $("#modal_procesar").modal('show');
            }
         },
         error: function(){
             alert("Error al intentar guardar la homologacion del Documento!");
         }
      });
   });

   $("#b_noprocesar").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea no procesar el Documento?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=bandeja_sri&noprocesar='+idbandejasri;
            }
         }
      });
   });

   $("#idtiporetencion").select2({
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
         url: doc_url,
         dataType: 'json',
         delay: 250,
         data: function (params) {
            return {
               buscar_retencion: params.term
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idtiporetencion, text: obj.codigo +" - "+ obj.nombre };
               })
            };
         },
         cache: true
      },
      dropdownParent: $('#modal_homologar'),
      placeholder: 'Buscar Retención',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $("#f_homologar").submit(function (event) {
      event.preventDefault();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#modal_homologar").modal('hide');
               actualizar_detalle_doc();
            }
         },
         error: function(){
             alert("Error al intentar guardar la homologacion del Documento!");
         }
      });
   });
});

function actualizar_detalle_doc() {
   $.ajax({
      url: doc_url+"&actdetret",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_ret").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
         } else {
            $(datos.lineas).each(function(key, value){
               let total = round(value.pvptotal + value.valorice + value.valoriva + value.valorirbp, 6);
               let homologar = "";
               if (value.idtiporetencion) {
                  homologar = "<a href='#' onclick='homologacion("+value.idlinearetbandejasri+")' class='btn btn-sm btn-success' title='Ver homologación'>\n\
                                 <span class='bi bi-eye-fill'></span>\n\
                              </a>";
               } else {
                  homologar = "<a href='#' onclick='homologacion("+value.idlinearetbandejasri+")' class='btn btn-sm btn-info' title='Realizar homologación'>\n\
                                 <span class='bi bi-bezier2'></span>\n\
                              </a>";
               }
               $("#detalle_ret").append("<tr>\n\
                  <td>"+value.numero_documento_mod+"</td>\n\
                  <td>"+value.fec_emision_mod+"</td>\n\
                  <td style='text-align: right;'>"+value.baseimponible+"</td>\n\
                  <td style='text-align: right;'>"+value.especie+"</td>\n\
                  <td style='text-align: right;'>"+value.codigo+"</td>\n\
                  <td style='text-align: right;'>"+value.porcentaje+"</td>\n\
                  <td style='text-align: right;'>"+value.total+"</td>\n\
                  <td><div class='btn-group'>"+homologar+"</div>\n\</td>\n\
               </tr>");
            });
            $("#totalret").html(datos.retencion.total);
         }
      },
      error: function(){
          alert("Error al intentar mostrar el detalle del Documento!");
      }
   });         
}

function homologacion(idlinearetbandejasri) {
   $("#hom_linea").val(idlinearetbandejasri);
   $("#crearret").prop('checked', false);
   mostrar_div_crearret();
   $("#idtiporetencion").empty().trigger('change');
   $("#idtiporetencion").select2({
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
         url: doc_url,
         dataType: 'json',
         delay: 250,
         data: function (params) {
            return {
               buscar_retencion: params.term
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idtiporetencion, text: obj.codigo +" - "+ obj.nombre };
               })
            };
         },
         cache: true
      },
      dropdownParent: $('#modal_homologar'),
      placeholder: 'Buscar Retención',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $.ajax({
      url: doc_url,
      type: "POST",
      data: {buscar_linea_ret: idlinearetbandejasri},
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#codigobase").val(datos.linea.codigo);
            $("#especie_ret").val(datos.linea.especie);
            $("#porcentaje_ret").val(datos.linea.porcentaje);
            if (datos.linea.idtiporetencion) {
               $('#idtiporetencion').html('').select2({
                  data: [{id: datos.ret.idtiporetencion, text: datos.ret.codigo+" - "+ datos.ret.nombre}],
                  dropdownParent: $('#modal_homologar'),
                  theme: 'bootstrap-5',
                  allowClear: true
               });
               $("#idtiporetencion").select2({
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
                     url: doc_url,
                     dataType: 'json',
                     delay: 250,
                     data: function (params) {
                        return {
                           buscar_retencion: params.term
                        };
                     },
                     processResults: function (data, params) {
                        return {
                           results: $.map(data, function(obj, index) {
                              return { id: obj.idtiporetencion, text: obj.codigo +" - "+ obj.nombre };
                           })
                        };
                     },
                     cache: true
                  },
                  dropdownParent: $('#modal_homologar'),
                  placeholder: 'Buscar Retención',
                  minimumInputLength: 3,
                  theme: 'bootstrap-5',
                  allowClear: true
               });
            }
            $("#modal_homologar").modal('show');
         }
      },
      error: function(){
          alert("Error al intentar Buscar la linea del Documento!");
      }
   });
}

function mostrar_div_crearret() {
   $("#idtiporetencion").attr('disabled', false);
   $("#idtiporetencion").empty().trigger('change');
   $("#div_new_ret").html('');

   if ($("#crearret").is(':checked')) {
      let codigo = $("#codigobase").val();
      let especie = $("#especie_ret").val();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: {codret: codigo, especie: especie},
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
               $("#crearret").prop('checked', false);
               mostrar_div_crearret();
            } else {
               $("#idtiporetencion").attr('disabled', true);
               let linea = '\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="codigo" class="col-form-label">Código:</label>\n\
                        <input type="text" class="form-control form-control-sm text-end" id="codigo" name="codigo" placeholder="Código" required>\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-8">\n\
                     <div class="form-group">\n\
                        <label for="nombre" class="col-form-label">Nombre Retención:</label>\n\
                        <input type="text" class="form-control form-control-sm text-end" id="nombre" name="nombre" placeholder="Nombre Retención" required>\n\
                     </div>\n\
                  </div>\n\
               ';
               $("#div_new_ret").append(linea);
            }
         },
         error: function(){
             alert("Error al intentar Buscar el Codigo del Producto!");
         }
      });
   } else {
      $("#idtiporetencion").select2({
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
            url: doc_url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
               return {
                  buscar_retencion: params.term
               };
            },
            processResults: function (data, params) {
               return {
                  results: $.map(data, function(obj, index) {
                     return { id: obj.idtiporetencion, text: obj.codigo +" - "+ obj.nombre };
                  })
               };
            },
            cache: true
         },
         dropdownParent: $('#modal_homologar'),
         placeholder: 'Buscar Retención',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
   }
}