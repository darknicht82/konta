$(document).ready(function() {
   $("#b_eliminar_articulo").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Servicio?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_servicios&delete={$gsc->servicio->idarticulo}';
            }
         }
      });
   });
   if (activo_cont == 1) {
      $("#idsubccompras").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $("#idsubcventas").empty().trigger('change').select2({
         placeholder: 'Seleccione Subcuenta',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      if (activo_nt == 1) {
         $("#idsubcntventas").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
      }
      $("#f_config_servicio").submit(function (event) {
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

function calcular_precio()
{
   let pvpiva = parseFloat($("#pvpiva").val());
   let idimpuesto = $("#impuesto").val();
   let porcentaje = 0;
   //Busco el porcentaje
   for (var i = 0; i < impuestos.length; i++) {
      if (impuestos[i].idimpuesto == idimpuesto) {
         porcentaje = parseInt(impuestos[i].porcentaje);
      }
   }

   let precio = round(pvpiva / (1 + (porcentaje / 100)), 6);
   $("#precio_art").val(precio);
}

if (activo_cont == 1) {
   function buscar_config() {
      let idejercicio = $("#idejercicio").val();
      let idservicio_config = $("#idservicio_config").val();
      if (idejercicio != '') {
         $("#idsubccompras").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcventas").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         if (activo_nt == 1) {
            $("#idsubcntventas").empty().trigger('change').select2({
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
            data: {config: idservicio_config, idejercicio: idejercicio},
            success: function(data) {
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  if (datos.compras) {
                     $('#idsubccompras').html('').select2({
                        data: [{id: datos.compras.id, text: datos.compras.codigo +" - "+ datos.compras.nombre}]
                     });
                  }
                  $("#idsubccompras").select2({
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
                  if (datos.ventas) {
                     $('#idsubcventas').html('').select2({
                        data: [{id: datos.ventas.id, text: datos.ventas.codigo +" - "+ datos.ventas.nombre}]
                     });
                  }
                  $("#idsubcventas").select2({
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
                        $('#idsubcntventas').html('').select2({
                           data: [{id: datos.notas.id, text: datos.notas.codigo +" - "+ datos.notas.nombre}]
                        });
                     }
                     $("#idsubcntventas").select2({
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
         $("#idsubccompras").prop('disabled', false);
         $("#idsubcventas").prop('disabled', false);
         if (activo_nt == 1) {
            $("#idsubcntventas").prop('disabled', false);
         }
      } else {
         $("#idsubccompras").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubccompras").prop('disabled', true);
         $("#idsubcventas").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubcventas").prop('disabled', true);
         if (activo_nt) {
            $("#idsubcntventas").empty().trigger('change').select2({
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            $("#idsubcntventas").prop('disabled', true);
         }
         $("#btn_save_conf").prop('disabled', true);
      }
   }
}