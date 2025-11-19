$(document).ready(function() {
   actualizar_detalle_guia();
   $("#f_nueva_linea").submit(function (event) {
      calcular_total()
      event.preventDefault();
      $.ajax({
         url: guia_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#codigobarras").focus();
               limpiar_campos();
               actualizar_detalle_guia();
            }
         },
         error: function(){
             alert("Error al intentar guardar la linea de la Guía de Remisiòn!");
         }
      });
   });

   $("#b_eliminar_guia").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar la Guía de Remisiòn?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_guias_remision&delete='+idguiacli;
            }
         }
      });
   });

   $("#b_anular_guia").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea anular la Guía de Remisión?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_guias_remision&anular='+idguiacli;
            }
         }
      });
   });

   $("#btn_autorizacion").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea autorizar el Documento en el SRI?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = guia_url+'&autorizar';
            }
         }
      });
   });

   $("#b_editar_documento").click(function(event) {
      event.preventDefault();
      validar_edicion();
   });

   $("#btn_opciones").click(function(event) {
      event.preventDefault();
      $("#modal_ver_opciones").modal('show');
   });

   $("#codigobarras").on("keyup", function(e) {
      if(e.which == 13) {
         let buscar = $("#codigobarras").val();
         if (buscar != '') {
            e.preventDefault();
            $.ajax({
               url: url,
               type: "POST",
               data: {codigobarras: buscar, establecimiento: establecimiento},
               success: function(data){
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#idarticulo_linea").val(datos.art.idarticulo);
                     $("#codprincipal").val(datos.art.codprincipal);
                     $("#descripcion").val(datos.art.nombre);
                     $("#cantidad").val(1);
                     $("#pvpunitario").val(datos.art.precio);
                     $("#dto").val(0);
                     $("#pvptotal").val(0);
                     $("#stock").val(datos.art.stock_fisico);
                     //$("#valorice").val(0);
                     $("#idimpuesto").val(datos.art.idimpuesto);
                     $("#total").val(0);
                     $("#cantidad").focus();
                     $("#cantidad").select();
                     calcular_total();
                  }
               },
               error: function(){
                   alert("Error al intentar buscar el articulo!");
               }
            });
            $("#codigobarras").val('');
         }
      }
   });
   
   $("#idarticulo").select2({
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
               buscar_articulo: params.term,
               establecimiento: establecimiento
            };
         },
         processResults: function (data, params) {
            return {
               results: $.map(data, function(obj, index) {
                  return { id: obj.idarticulo, text: obj.codprincipal +" - "+ obj.nombre + " ("+obj.stock_fisico+")" };
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

   $('#idarticulo').on("change", function(e) {
      let idarticulo = $("#idarticulo").val();
      if (idarticulo  === null)
      {
      } else {
         e.preventDefault();
         $.ajax({
            url: url,
            type: "POST",
            data: {idarticulo: idarticulo, establecimiento: establecimiento},
            success: function(data){
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  $("#idarticulo_linea").val(datos.art.idarticulo);
                  $("#codprincipal").val(datos.art.codprincipal);
                  $("#descripcion").val(datos.art.nombre);
                  $("#cantidad").val(1);
                  $("#pvpunitario").val(datos.art.precio);
                  $("#dto").val(0);
                  $("#pvptotal").val(0);
                  $("#stock").val(datos.art.stock_fisico);
                  //$("#valorice").val(0);
                  $("#idimpuesto").val(datos.art.idimpuesto);
                  $("#total").val(0);
                  $("#cantidad").focus();
                  $("#cantidad").select();
                  calcular_total();
               }
            },
            error: function(){
                alert("Error al intentar buscar el articulo!");
            }
         });
         $("#idarticulo").empty().trigger('change');
      }
   });

   $('#trasnp').on("change", function(e) {
      let trasnp = $("#trasnp").val();
      if (trasnp  === null)
      {
      } else {
         e.preventDefault();
         $.ajax({
            url: url,
            type: "POST",
            data: {trasnp: trasnp},
            success: function(data){
               let datos = JSON.parse(data);
               if (datos.error == 'T') {
                  alert(datos.msj);
               } else {
                  $("#tipoid_trans").val(datos.trans.tipoid_trans);
                  $("#identificacion_trans").val(datos.trans.identificacion_trans);
                  $("#razonsocial_trans").val(datos.trans.razonsocial_trans);
                  $("#placa").val(datos.trans.placa);
               }
            },
            error: function(){
                alert("Error al intentar buscar el transportista!");
            }
         });
         $("#trasnp").empty().trigger('change');
      }
   });
});

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea de la Guía de Remisión?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: guia_url,
               type: "POST",
               data: {eliminar_linea: idlinea},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#codigobarras").focus();
                  }
                  actualizar_detalle_guia();
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Guía de Remisión!");
               }
            });
         }
      }
   });
}

function actualizar_detalle_guia() {
   $.ajax({
      url: guia_url+"&actdetguia",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_fac").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
            $("#base_gra").html(number_format(0, 2));
            $("#base_0").html(number_format(0, 2));
            $("#base_noi").html(number_format(0, 2));
            $("#base_exc").html(number_format(0, 2));
            $("#subtotal").html(number_format(0, 2));
            $("#totaldescuento").html(number_format(0, 2));
            //$("#totalice").html(number_format(0, 2));
            $("#totaliva").html(number_format(0, 2));
            $("#totalfac").html(number_format(0, 2));
         } else {
            $(datos.lineas).each(function(key, value){
               let total = value.pvptotal + value.valorice + value.valoriva;
               let eliminar = '';
               if (allow_delete == 1 && anulado != 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlineaguiacli+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               //ice
               //<td style='text-align: right;'>"+value.valorice+"</td>\n\
               $("#detalle_fac").append("<tr>\n\
                  <td><a href='"+value.url_art+"' target='_blank'>"+value.codprincipal+"</a></td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>\n\
                  <td style='text-align: right;'>"+value.pvpunitario+"</td>\n\
                  <td style='text-align: right;'>"+value.dto+"</td>\n\
                  <td style='text-align: right;'>"+value.valoriva+"</td>\n\
                  <td style='text-align: right;'>"+total+"</td>\n\
                  <td>"+eliminar+"</td>\n\
               </tr>");
            });
            let subtotal = datos.guia.base_gra + datos.guia.base_0 + datos.guia.base_noi + datos.guia.base_exc;
            $("#base_gra").html(number_format(datos.guia.base_gra, 2));
            $("#base_0").html(number_format(datos.guia.base_0, 2));
            $("#base_noi").html(number_format(datos.guia.base_noi, 2));
            $("#base_exc").html(number_format(datos.guia.base_exc, 2));
            $("#subtotal").html(number_format(subtotal, 2));
            $("#totaldescuento").html(number_format(datos.guia.totaldescuento, 2));
            //$("#totalice").html(number_format(datos.guia.totalice, 2));
            $("#totaliva").html(number_format(datos.guia.totaliva, 2));
            $("#totalfac").html(number_format(datos.guia.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la guia!");
      }
   });         
}

function calcular_total() {
   let cantidad = parseFloat($("#cantidad").val());
   let pvpunitario = parseFloat($("#pvpunitario").val());
   let dto = parseFloat($("#dto").val());
   //let valorice = parseFloat($("#valorice").val());
   let valorice = parseFloat(0);
   let idimpuesto = $("#idimpuesto").val();
   let porcentaje = 0;
   //Busco el porcentaje
   for (var i = 0; i < impuestos.length; i++) {
      if (impuestos[i].idimpuesto == idimpuesto) {
         porcentaje = parseInt(impuestos[i].porcentaje);
      }
   }
   //Calculo el subtotal sin impuestos
   let pvptotal = (cantidad * pvpunitario) * ((100-dto)/100);
   $("#pvptotal").val(pvptotal);
   let valoriva = (pvptotal + valorice) * (porcentaje / 100);
   $("#valoriva").val(valoriva);
   let total = pvptotal + valorice + valoriva;
   $("#total").val(total);
}

function calcular_costounit() {
   let cantidad = parseFloat($("#cantidad").val());
   let pvptotal = parseFloat($("#pvptotal").val());
   let dto = parseFloat($("#dto").val());
   if (dto > 0) {
   } else {
      let pvpunitario = pvptotal / cantidad;
      $("#pvpunitario").val(pvpunitario);
      calcular_total();
   }
}

function limpiar_campos() {
   $("#idarticulo_linea").val('');
   $("#codprincipal").val('');
   $("#descripcion").val('');
   $("#cantidad").val('');
   $("#pvpunitario").val('');
   $("#dto").val('');
   $("#pvptotal").val('');
   //$("#valorice").val('');
   $("#idimpuesto").val('');
   $("#total").val('');
   $("#stock").val('');
   $("#codigobarras").focus();
}

function reenviar_correo() {
   $("#modal_ver_opciones").modal('hide');
   $("#modal_reenviar_correo").modal('show');
}

function validar_edicion()
{
   $.ajax({
      url: guia_url,
      type: "POST",
      data: {validar_edicion: ''},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $('#idcliente').select2({
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
               data: [{id: datos.guia.idcliente, text: datos.guia.identificacion +" - "+ datos.guia.razonsocial}],
               dropdownParent: $('#modal_editar_documento'),
               placeholder: 'Buscar Cliente',
               theme: 'bootstrap-5',
               allowClear: true
            });

            //Datos de la Guia
            $("#direccion").val(datos.guia.direccion);
            $("#email").val(datos.guia.email);
            $("#motivo").val(datos.guia.motivo);
            $("#codestablecimiento").val(datos.guia.codestablecimiento);
            $("#ruta").val(datos.guia.ruta);
            $("#tipoid_trans").val(datos.guia.tipoid_trans);
            $("#identificacion_trans").val(datos.guia.identificacion_trans);
            $("#razonsocial_trans").val(datos.guia.razonsocial_trans);
            $("#placa").val(datos.guia.placa);
            $("#dirpartida").val(datos.guia.dirpartida);
            $("#idfactura_mod").val(datos.guia.idfactura_mod);
            $("#tipo_guia").val(datos.guia.tipo_guia);
            //Datos del cliente
            $("#dircliente").val(datos.cliente.direccion);
            $("#emailcliente").val(datos.cliente.email);
            $("#tipidencliente").val(datos.cliente.tipoid);
            $("#identcliente").val(datos.cliente.identificacion);
            $("#razonscliente").val(datos.cliente.razonsocial);
            //Observaciones
            $("#observaciones").val(datos.guia.observaciones);
            //Buscador del Transporte
            $("#trasnp").select2({
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
                        buscar_trans: params.term
                     };
                  },
                  processResults: function (data, params) {
                     return {
                        results: $.map(data, function(obj, index) {
                           return { id: obj.value, text: obj.label };
                        })
                     };
                  },
                  cache: true
               },
               placeholder: 'Buscar Transporte',
               dropdownParent: $('#modal_editar_documento'),
               minimumInputLength: 3,
               theme: 'bootstrap-5',
               allowClear: true
            });
            //Buscador de factura Asociada
            if (datos.guia.idfactura_mod) {
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
                           idcliente: $("#idcliente").val()
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
                  data : [{id: datos.guia.idfactura_mod, text: "N.: "+datos.guia.numero_documento_mod +" - Fec: "+ datos.guia.fec_emision_mod}],
                  placeholder: 'Buscar Factura',
                  dropdownParent: $('#modal_editar_documento'),
                  minimumInputLength: 3,
                  theme: 'bootstrap-5',
                  allowClear: true
               });
            } else {
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
                           idcliente: $("#idcliente").val()
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
                  dropdownParent: $('#modal_editar_documento'),
                  minimumInputLength: 3,
                  theme: 'bootstrap-5',
                  allowClear: true
               });
            }
            $("#modal_editar_documento").modal('show');
         }
      },
      error: function(){
         alert("Error al intentar buscar la Factura!");
      }
   });
}