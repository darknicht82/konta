$(document).ready(function() {
   $("#b_eliminar_cliente").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Cliente?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_clientes&delete='+idcliente;
            }
         }
      });
   });
   $("#b_nuevo_medidor").click(function(event) {
      event.preventDefault();
      $('#modal_nuevo_medidor').modal('show');
      $('#numero_n').focus();
   });

   $("#f_nuevo_medidor").submit(function (event) {
      $("#btn_nuevo_med").attr('disabled', true);
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
         error: function(){
             alert("Error al intentar guardar la linea de la Factura!");
         }
      });
      $("#btn_nuevo_med").attr('disabled', false);
   });

   if (activo_cont == 1) {
      $("#idsubccliente").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcantcliente").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      if (activo_nt == 1) {
         $("#idsubcntcliente").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
      }
      $("#f_config_cliente").submit(function (event) {
         $("#btn_save_conf").attr('disabled', true);
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
               }
            },
            error: function(){
                alert("Error al intentar guardar el Cliente!");
            }
         });
         $("#btn_save_conf").attr('disabled', false);
      });
   }
});

if (activo_cont == 1) {
   function buscar_config() {
      let idejercicio = $("#idejercicio").val();
      let idcliente_config = $("#idcliente_config").val();
      if (idejercicio != '') {
         $("#idsubccliente").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcantcliente").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         if (activo_nt == 1) {
            $("#idsubcntcliente").empty().trigger('change').select2({
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
         }
         $.ajax({
            async: false,
            url: url,
            type: "POST",
            data: {config: idcliente_config, idejercicio: idejercicio},
            success: function(data) {
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  if (datos.cliente) {
                     $('#idsubccliente').html('').select2({
                        data: [{id: datos.cliente.id, text: datos.cliente.codigo +" - "+ datos.cliente.nombre}]
                     });
                  }
                  $("#idsubccliente").select2({
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
                              buscar_subcuenta: params.term,
                              ejer: idejercicio
                           };
                        },
                        processResults: function (data, params) {
                           return {
                              results: $.map(data, function(obj, index) {
                                 return { id: obj.id, text: obj.codigo +" - "+ obj.nombre };
                              })
                           };
                        },
                        cache: true
                     },
                     placeholder: 'Seleccione Subcuenta',
                     minimumInputLength: 3,
                     theme: 'bootstrap-5',
                     allowClear: true
                  });
                  if (datos.anticipo) {
                     $('#idsubcantcliente').html('').select2({
                        data: [{id: datos.anticipo.id, text: datos.anticipo.codigo +" - "+ datos.anticipo.nombre}]
                     });
                  }
                  $("#idsubcantcliente").select2({
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
                              buscar_subcuenta: params.term,
                              ejer: idejercicio
                           };
                        },
                        processResults: function (data, params) {
                           return {
                              results: $.map(data, function(obj, index) {
                                 return { id: obj.id, text: obj.codigo +" - "+ obj.nombre };
                              })
                           };
                        },
                        cache: true
                     },
                     placeholder: 'Seleccione Subcuenta',
                     minimumInputLength: 3,
                     theme: 'bootstrap-5',
                     allowClear: true
                  });
                  if (activo_nt == 1) {
                     if (datos.notas) {
                        $('#idsubcntcliente').html('').select2({
                           data: [{id: datos.notas.id, text: datos.notas.codigo +" - "+ datos.notas.nombre}]
                        });
                     }
                     $("#idsubcntcliente").select2({
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
                                 buscar_subcuenta: params.term,
                                 ejer: idejercicio
                              };
                           },
                           processResults: function (data, params) {
                              return {
                                 results: $.map(data, function(obj, index) {
                                    return { id: obj.id, text: obj.codigo +" - "+ obj.nombre };
                                 })
                              };
                           },
                           cache: true
                        },
                        placeholder: 'Seleccione Subcuenta',
                        minimumInputLength: 3,
                        theme: 'bootstrap-5',
                        allowClear: true
                     });
                  }
                  $("#btn_save_conf").prop('disabled', false);
               }
            },
            error: function(){
               alert("Error al buscar las Configuraciones Contables!");
            }
         });
         $("#idsubccliente").prop('disabled', false);
         $("#idsubcantcliente").prop('disabled', false);
         if (activo_nt == 1) {
            $("#idsubcntcliente").prop('disabled', false);
         }
      } else {
         $("#idsubccliente").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubccliente").prop('disabled', true);
         $("#idsubcantcliente").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcantcliente").prop('disabled', true);
         if (activo_nt) {
            $("#idsubcntcliente").empty().trigger('change').select2({
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            $("#idsubcntcliente").prop('disabled', true);
         }
         $("#btn_save_conf").prop('disabled', true);
      }
   }
}