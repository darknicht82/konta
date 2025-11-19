$(document).ready(function() {
   $("#b_eliminar_articulo").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Artículo?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_articulos&delete='+idarticulo;
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
      $("#idsubccostos").empty().trigger('change').select2({
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
         $("#idsubcntcostos").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
      }
      $("#f_config_articulo").submit(function (event) {
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
   if (compuesto == 1) {
      $("#idarticulo_ins").select2({
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
                  buscar_articulo: params.term
               };
            },
            processResults: function (data, params) {
               return {
                  results: $.map(data, function(obj, index) {
                     return { id: obj.idarticulo, text: obj.codprincipal +" - "+ obj.nombre };
                  })
               };
            },
            cache: true
         },
         placeholder: 'Buscar Artículo',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
      $('#idarticulo_ins').on("change", function(e) {
         let idarticulo = $("#idarticulo_ins").val();
         if (idarticulo  === null)
         {
         } else {
            e.preventDefault();
            $.ajax({
               url: url,
               type: "POST",
               data: {idarticulo: idarticulo},
               success: function(data){
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#costuni_ins").val(datos.costo);
                     $("#cantidad_ins").focus();
                     $("#cantidad_ins").select();
                     calcular_costo_compuesto();
                  }
               },
               error: function(){
                   alert("Error al intentar buscar el articulo!");
               }
            });
         }
      });
   }
});

function eliminar_imagen() {
   if ( confirm('¿Está seguro que desea eliminar la Imagen del Artículo?') ) {
      window.location.href = url + "&delete_imagen";
   }
}

function delete_presentacion(idartunidad, unidadnombre)
{
   bootbox.confirm({
      message: '¿Realmente desea eliminar la Unidad de Medida '+unidadnombre+' del Artículo?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            window.location.href = url+'&delum='+idartunidad;
         }
      }
   });  
}

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

function buscar_kardex(idestablecimiento)
{
   $.ajax({
      url: url,
      type: "POST",
      data: { buscar_kardex: idestablecimiento },
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#kardex").empty();
            $("#saldo_kardex").empty();
            let saldo = 0;
            let ingresos = 0;
            let egresos = 0;
            $(datos.kardex).each(function(key, value) {
               saldo += parseFloat(value.movimiento);
               $("#kardex").append("<tr>\n\
                  <td style='text-align: left;'>"+transformar_fecha(value.fecha)+" "+value.hora+"</td>\n\
                  <td style='text-align: left;'><a href='"+value.url+"' target='_blank'>"+value.origen+"</a></td>\n\
                  <td style='text-align: right;'>"+number_format(value.ingresos)+"</td>\n\
                  <td style='text-align: right;'>"+number_format(value.egresos)+"</td>\n\
                  <td style='text-align: right;'>"+number_format(saldo)+"</td>\n\
               </tr>");
               ingresos += parseFloat(value.ingresos);
               egresos += parseFloat(value.egresos);
            });
            $("#kardex").append("<tr>\n\
               <th style='text-align: left;'></th>\n\
               <th style='text-align: left;'>Totales</th>\n\
               <th style='text-align: right;'>"+number_format(ingresos)+"</th>\n\
               <th style='text-align: right;'>"+number_format(egresos)+"</th>\n\
               <th style='text-align: right;'>"+number_format(saldo)+"</th>\n\
            </tr>");
         }
      },
      error: function(){
          alert("Error al intentar recuperar Kardex del Articulo.");
      }
   });
}

function recalcular_stock(idestablecimiento)
{
   event.preventDefault();
   bootbox.confirm({
      message: '¿Realmente desea recalcular el Stock del Artículo?',
      title: '<b>Información</b>',
      callback: function(result) {
         if (result) {
            window.location.href = url+'&recalcular='+idestablecimiento;
         }
      }
   });
}

if (compuesto == 1) {
   function calcular_costo_compuesto(idinsumo = '')
   {
      let cantidad = 1;
      let costuni = 0;
      if (idinsumo != '') {
         cantidad = parseFloat($("#cantidad_ins_"+idinsumo).val());
         costuni = parseFloat($("#costuni_ins_"+idinsumo).val());
      } else {
         cantidad = parseFloat($("#cantidad_ins").val());
         costuni = parseFloat($("#costuni_ins").val());
      }

      let total = cantidad * costuni;
      if (idinsumo != '') {
         $("#tot_ins_"+idinsumo).val(number_format(total));
      } else {
         $("#tot_ins").val(number_format(total));
      }
   }

   function delete_insumo(idinsumo, nombre)
   {
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Insumo: '+nombre+'?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = url+'&delins='+idinsumo;
            }
         }
      });  
   }
}

function calcular_precio_presentacion(idartunidad = '')
{
   let cantidad = 1;
   let precio = 0;
   let iva = 0;
   let total = 0;
   if (idartunidad != '') {
      cantidad = parseFloat($("#cantidad_"+idartunidad).val());
      iva = parseFloat($("#poriva_"+idartunidad).val());
      total = parseFloat($("#tot_"+idartunidad).val());
   } else {
      cantidad = parseFloat($("#cantidad").val());
      iva = parseFloat($("#poriva").val());
      total = parseFloat($("#tot").val());
   }

   let subtotal = total / (1+(iva/100));
   precio = round(subtotal / cantidad, 6)
   if (idartunidad != '') {
      $("#precio_"+idartunidad).val(number_format(precio, 6));
      $("#tot_"+idartunidad).val(number_format(total));
   } else {
      $("#precio").val(number_format(precio, 6));
      $("#tot").val(number_format(total));
   }
}

function calcular_total_presentacion(idartunidad = '')
{
   let cantidad = 1;
   let precio = 0;
   let iva = 0;
   let total = 0;
   if (idartunidad != '') {
      cantidad = parseFloat($("#cantidad_"+idartunidad).val());
      iva = parseFloat($("#poriva_"+idartunidad).val());
      precio = parseFloat($("#precio_"+idartunidad).val());
   } else {
      cantidad = parseFloat($("#cantidad").val());
      iva = parseFloat($("#poriva").val());
      precio = parseFloat($("#precio").val());
   }

   let subtotal = cantidad * precio;
   total = round(subtotal * (1+(iva/100)), 2)
   if (idartunidad != '') {
      $("#precio_"+idartunidad).val(number_format(precio, 6));
      $("#tot_"+idartunidad).val(number_format(total));
   } else {
      $("#precio").val(number_format(precio, 6));
      $("#tot").val(number_format(total));
   }
}

if (activo_cont == 1) {
   function buscar_config() {
      let idejercicio = $("#idejercicio").val();
      let idarticulo_config = $("#idarticulo_config").val();
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
         $("#idsubccostos").empty().trigger('change').select2({
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
            $("#idsubcntcostos").empty().trigger('change').select2({
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
            data: {config: idarticulo_config, idejercicio: idejercicio},
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
                  if (datos.costos) {
                     $('#idsubccostos').html('').select2({
                        data: [{id: datos.costos.id, text: datos.costos.codigo +" - "+ datos.costos.nombre}]
                     });
                  }
                  $("#idsubccostos").select2({
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
                     if (datos.notasct) {
                        $('#idsubcntcostos').html('').select2({
                           data: [{id: datos.notasct.id, text: datos.notasct.codigo +" - "+ datos.notasct.nombre}]
                        });
                     }
                     $("#idsubcntcostos").select2({
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
         $("#idsubccostos").prop('disabled', false);
         if (activo_nt == 1) {
            $("#idsubcntventas").prop('disabled', false);
            $("#idsubcntcostos").prop('disabled', false);
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
         $("#idsubccostos").empty().trigger('change').select2({
            placeholder: 'Seleccione Subcuenta',
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            allowClear: true
         });
         $("#idsubccostos").prop('disabled', true);
         if (activo_nt == 1) {
            $("#idsubcntventas").empty().trigger('change').select2({
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            $("#idsubcntventas").prop('disabled', true);
            $("#idsubcntcostos").empty().trigger('change').select2({
               placeholder: 'Seleccione Subcuenta',
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            $("#idsubcntcostos").prop('disabled', true);
         }
         $("#btn_save_conf").prop('disabled', true);
      }
   }
}