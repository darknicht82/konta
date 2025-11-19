$(document).ready(function() {
   actualizar_detalle_factura();
   $("#f_nueva_linea").submit(function (event) {
      event.preventDefault();
      $.ajax({
         url: factura_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#codigobarras").focus();
               limpiar_campos();
               actualizar_detalle_factura();
            }
         },
         error: function(){
             alert("Error al intentar guardar la linea de la Nota de Venta!");
         }
      });
   });

   $("#f_nuevo_cobro").submit(function (event) {
      event.preventDefault();
      $.ajax({
         url: factura_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data){
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               alert(datos.msj);
               listar_cobros();
               $("#idformapago").val('');
            }
         },
         error: function(){
             alert("Error al intentar guardar el Cobro!");
         }
      });
   });

   $("#b_eliminar_factura").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar la Nota de Venta?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_notas_de_venta&delete='+idfacturacli;
            }
         }
      });
   });

   $("#b_anular_documento").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea anular la Nota de Venta?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_notas_de_venta&anular='+idfacturacli;
            }
         }
      });
   });
   
   $("#btn_cobros").click(function(event) {
      event.preventDefault();
      listar_cobros();
      $("#modal_ver_cobros").modal('show');
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

   if (editar == 1) {
      $('#idcliente').on("change", function(e) {
         let idcliente = $("#idcliente").val();
         if (idcliente === null) {} else {
            e.preventDefault();
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    idcliente: idcliente
                },
                success: function(data) {
                    let datos = JSON.parse(data);
                    if (datos.error == 'T') {
                        alert(datos.msj);
                    } else {
                        $("#direccion").val(datos.cli.direccion);
                        $("#email").val(datos.cli.email);
                        $("#tipidencliente").val(datos.cli.tipoid);
                        $("#identcliente").val(datos.cli.identificacion);
                        $("#razonscliente").val(datos.cli.razonsocial);
                    }
                },
                error: function() {
                    alert("Error al intentar buscar el Cliente!");
                }
            });
         }
      });
   }
});
function buscar_fp()
{
   fp = $("#idformapago").val();
   if (fp != '') {
      $.ajax({
         url: factura_url,
         type: "POST",
         data: {buscar_fp: fp},
         success: function(data){
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               if (datos.fp.num_doc) {
                  $("#div_numdoc").show('slow');
                  $("#num_doc").attr('required', true);
               } else {
                  $("#div_numdoc").hide('slow');
                  $("#num_doc").attr('required', false);
               }
            }
         },
         error: function(){
            alert("Error al intentar buscar la forma de cobro!");
         }
      });
   } else {
      $("#div_numdoc").hide('slow');
      $("#num_doc").attr('required', false);
   }
}

function validar_edicion()
{
   $.ajax({
      url: factura_url,
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
               data: [{id: datos.factura.idcliente, text: datos.factura.identificacion +" - "+ datos.factura.razonsocial}],
               dropdownParent: $('#modal_editar_documento'),
               placeholder: 'Buscar Cliente',
               theme: 'bootstrap-5',
               allowClear: true
            });
            
            //Datos de la Factura
            $("#direccion").val(datos.factura.direccion);
            $("#email").val(datos.factura.email);
            //Datos del cliente
            $("#dircliente").val(datos.cliente.direccion);
            $("#emailcliente").val(datos.cliente.email);
            $("#tipidencliente").val(datos.cliente.tipoid);
            $("#identcliente").val(datos.cliente.identificacion);
            $("#razonscliente").val(datos.cliente.razonsocial);
            //Observaciones
            $("#observaciones").val(datos.factura.observaciones);

            $("#modal_editar_documento").modal('show');
         }
      },
      error: function(){
         alert("Error al intentar buscar la Factura!");
      }
   });
}

function listar_cobros()
{
   $.ajax({
      url: factura_url,
      type: "POST",
      data: {buscar_cobros: ''},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#det_cobros").empty();
            $("#sal_cobros").empty();
            let total = parseFloat(datos.total);
            let cobros = parseFloat(0); 
            $(datos.cobros).each(function(key, value) {
               let valor = parseFloat(round(value.debito - value.credito, 2));
               cobros += valor;
               eliminar = '';
               imprimir = '';
               if (allow_delete == 1 && value.tipo == 'Cobro') {
                  eliminar = "<a onclick='eliminar_cobro("+value.idtranscobro+")' id='b_eliminar_cobro' class='btn btn-sm btn-danger'><span class='bi bi-trash2'></span></a>";
               }
               if (value.tipo == 'Cobro' && impresion == 1) {
                  imprimir = "<a target='_blank' href='index.php?page=impresion_ventas&cobro&id="+value.idtranscobro+"' id='b_imprimir_cobro' class='btn btn-sm btn-outline-primary'><span class='bi bi-printer'></span></a>";
               }
               $("#det_cobros").append("<tr>\n\
                  <td style='text-align: left;'>"+value.tipo+"</td>\n\
                  <td style='text-align: right;'>"+value.fecha_trans+"</td>\n\
                  <td style='text-align: left;'>"+value.nombre_fp+"</td>\n\
                  <td style='text-align: right;'>"+valor+"</td>\n\
                  <td style='text-align: right;'><div class='btn-group'>"+eliminar+imprimir+"</div></td>\n\
               </tr>");
            });
            let saldo = parseFloat(round(cobros, 2));
            $("#sal_cobros").append("<tr>\n\
               <th style='text-align: left;'>Total NV.</th>\n\
               <td style='text-align: right;'>"+total+"</td>\n\
               <th style='text-align: right;'>Saldo:</th>\n\
               <td style='text-align: right;'>"+saldo+"</td>\n\
               <td style='text-align: right;'></td>\n\
            </tr>");

            $("#valor").val(saldo);

            if (saldo > 0) {
               $("#div_nuevo_cobro").show('slow');
            } else {
               $("#div_nuevo_cobro").hide('slow');
            }
         }
      },
      error: function(){
          alert("Error al intentar buscar los cobros de la Nota de Venta!");
      }
   });
}

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea de la Nota de Venta?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: factura_url,
               type: "POST",
               data: {eliminar_linea: idlinea},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#codigobarras").focus();
                     actualizar_detalle_factura();
                  }
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Nota de Venta!");
               }
            });
         }
      }
   });
}

function eliminar_cobro(idcobro) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar el cobro de la Nota de Venta?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: factura_url,
               type: "POST",
               data: {eliminar_cobro: idcobro},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     alert(datos.msj);
                     listar_cobros();
                  }
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Nota de Venta!");
               }
            });
         }
      }
   });
}

function actualizar_detalle_factura() {
   $.ajax({
      url: factura_url+"&actdetfact",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_fac").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
         } else {
            $(datos.lineas).each(function(key, value){
               let total = round(value.pvptotal + value.valorice + value.valoriva, 6);
               let eliminar = '';
               if (allow_delete == 1 && anulado != 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlineafaccli+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               let tdunidad = '';
               if (unidad_med == 1) {
                  tdunidad = "<td style='text-align: left;'>"+value.medida+"</td>";
               }
               //ice
               //<td style='text-align: right;'>"+value.valorice+"</td>\n\
               $("#detalle_fac").append("<tr>\n\
                  <td><a href='"+value.url_art+"' target='_blank'>"+value.codprincipal+"</a></td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>"+tdunidad+"\n\
                  <td style='text-align: right;'>"+value.pvpunitario+"</td>\n\
                  <td style='text-align: right;'>"+value.dto+"</td>\n\
                  <td style='text-align: right;'>"+value.valoriva+"</td>\n\
                  <td style='text-align: right;'>"+total+"</td>\n\
                  <td>"+eliminar+"</td>\n\
               </tr>");
            });
            let subtotal = datos.factura.base_gra + datos.factura.base_0 + datos.factura.base_noi + datos.factura.base_exc;
            $("#base_gra").html(number_format(datos.factura.base_gra, 2));
            $("#base_0").html(number_format(datos.factura.base_0, 2));
            $("#base_noi").html(number_format(datos.factura.base_noi, 2));
            $("#base_exc").html(number_format(datos.factura.base_exc, 2));
            $("#subtotal").html(number_format(subtotal, 2));
            $("#totaldescuento").html(number_format(datos.factura.totaldescuento, 2));
            //$("#totalice").html(number_format(datos.factura.totalice, 2));
            $("#totaliva").html(number_format(datos.factura.totaliva, 2));
            $("#totalfac").html(number_format(datos.factura.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la Nota de Venta!");
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