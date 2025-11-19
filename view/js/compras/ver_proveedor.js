$(document).ready(function() {
   $("#b_eliminar_proveedor").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Proveedor?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_proveedores&delete='+idproveedor;
            }
         }
      });
   });

   if (activo_cont == 1) {
      $("#idsubcproveedor").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcantproveedor").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#f_config_proveedor").submit(function (event) {
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
      let idproveedor_config = $("#idproveedor_config").val();
      if (idejercicio != '') {
         $("#idsubcproveedor").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcantproveedor").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $.ajax({
            async: false,
            url: url,
            type: "POST",
            data: {config: idproveedor_config, idejercicio: idejercicio},
            success: function(data) {
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  if (datos.proveedor) {
                     $('#idsubcproveedor').html('').select2({
                        data: [{id: datos.proveedor.id, text: datos.proveedor.codigo +" - "+ datos.proveedor.nombre}]
                     });
                  }
                  $("#idsubcproveedor").select2({
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
                     $('#idsubcantproveedor').html('').select2({
                        data: [{id: datos.anticipo.id, text: datos.anticipo.codigo +" - "+ datos.anticipo.nombre}]
                     });
                  }
                  $("#idsubcantproveedor").select2({
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
                  $("#btn_save_conf").prop('disabled', false);
               }
            },
            error: function(){
               alert("Error al buscar las Configuraciones Contables!");
            }
         });
         $("#idsubcproveedor").prop('disabled', false);
         $("#idsubcantproveedor").prop('disabled', false);
      } else {
         $("#idsubcproveedor").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcproveedor").prop('disabled', true);
         $("#idsubcantproveedor").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcantproveedor").prop('disabled', true);
         $("#btn_save_conf").prop('disabled', true);
      }
   }
}