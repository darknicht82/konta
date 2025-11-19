$(document).ready(function() {
   $("#b_nuevo_impuesto").click(function(event) {
      event.preventDefault();
      $("#modal_nuevo_impuesto").modal('show');
      document.f_nuevo_impuesto.nombre.focus();
   });
   if (activo_cont == 1) {
      $("#idsubcivaventas").empty().trigger('change').select2({
         dropdownParent: $('#modal_config_contabilidad'),
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcivacompras").empty().trigger('change').select2({
         dropdownParent: $('#modal_config_contabilidad'),
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      if (activo_nt == 1) {
         $("#idsubcivanotasventa").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
      }

      $("#f_config_impuestos").submit(function (event) {
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
                  $("#modal_config_contabilidad").modal('hide');
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

function delete_impuesto(idimpuesto, nombre){
   event.preventDefault();
   bootbox.confirm({
      message: '¿Realmente desea eliminar el Impuesto '+nombre+'?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            window.location.href = '{$gsc->url()}&delete='+idimpuesto;
         }
      }
   });
}

if (activo_cont == 1) {
   function config_cuentas(idimpuesto, nombre) {
      $("#idsubcivacompras").empty().trigger('change').select2({
         dropdownParent: $('#modal_config_contabilidad'),
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcivacompras").prop('disabled', true);
      $("#idsubcivaventas").empty().trigger('change').select2({
         dropdownParent: $('#modal_config_contabilidad'),
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcivaventas").prop('disabled', true);
      if (activo_nt == 1) {
         $("#idsubcivanotasventa").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcivanotasventa").prop('disabled', true);
      }

      $("#idejercicio").val('');
      $("#btn_save_conf").prop('disabled', true);
      $("#nomimp").html(nombre);
      $("#idimpuesto_config").val(idimpuesto);
      $("#modal_config_contabilidad").modal('show');
   }

   function buscar_config() {
      let idejercicio = $("#idejercicio").val();
      let idimpuesto_config = $("#idimpuesto_config").val();
      if (idejercicio != '') {
         $("#idsubcivacompras").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcivaventas").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         if (activo_nt == 1) {
            $("#idsubcivanotasventa").empty().trigger('change').select2({
               dropdownParent: $('#modal_config_contabilidad'),
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
            data: {config: idimpuesto_config, idejercicio: idejercicio},
            success: function(data) {
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  if (datos.compras) {
                     $('#idsubcivacompras').html('').select2({
                        data: [{id: datos.compras.id, text: datos.compras.codigo +" - "+ datos.compras.nombre}]
                     });
                  }
                  $("#idsubcivacompras").select2({
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
                     dropdownParent: $('#modal_config_contabilidad'),
                     placeholder: 'Seleccione Subcuenta',
                     minimumInputLength: 3,
                     theme: 'bootstrap-5',
                     allowClear: true
                  });

                  if (datos.ventas) {
                     $('#idsubcivaventas').html('').select2({
                        data: [{id: datos.ventas.id, text: datos.ventas.codigo +" - "+ datos.ventas.nombre}]
                     });
                  }
                  $("#idsubcivaventas").select2({
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
                     dropdownParent: $('#modal_config_contabilidad'),
                     placeholder: 'Seleccione Subcuenta',
                     minimumInputLength: 3,
                     theme: 'bootstrap-5',
                     allowClear: true
                  });

                  if (activo_nt == 1) {

                     if (datos.notas) {
                        $('#idsubcivanotasventa').html('').select2({
                           data: [{id: datos.notas.id, text: datos.notas.codigo +" - "+ datos.notas.nombre}]
                        });
                     }
                     $("#idsubcivanotasventa").select2({
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
                        dropdownParent: $('#modal_config_contabilidad'),
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
         $("#idsubcivacompras").prop('disabled', false);
         $("#idsubcivaventas").prop('disabled', false);
         $("#idsubcivanotasventa").prop('disabled', false);

      } else {
         $("#idsubcivacompras").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcivacompras").prop('disabled', true);
         $("#idsubcivaventas").empty().trigger('change').select2({
            dropdownParent: $('#modal_config_contabilidad'),
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcivaventas").prop('disabled', true);
         if (activo_nt == 1) {
            $("#idsubcivanotasventa").empty().trigger('change').select2({
               dropdownParent: $('#modal_config_contabilidad'),
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            $("#idsubcivanotasventa").prop('disabled', true);
         }

         $("#btn_save_conf").prop('disabled', true);
      }
   }
}