$(document).ready(function() {
   actualizar_detalle_factura();
   $("#f_nueva_linea").submit(function (event) {
      $("#btn_aniadir").attr('disabled', true);
      calcular_total();
      event.preventDefault();
      $.ajax({
         async: false,
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
             alert("Error al intentar guardar la linea de la Factura!");
         }
      });
      $("#btn_aniadir").attr('disabled', false);
   });

   $("#f_editar_retencion").submit(function (event) {
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
               alert(datos.msj);
               $("#modal_editar_retencion").modal('hide');
               $("#codigobarras").focus();
               actualizar_detalle_factura();
            }
         },
         error: function(){
             alert("Error al intentar guardar la linea de la Factura!");
         }
      });
   });

   $("#f_nuevo_pago").submit(function (event) {
      $("#btn_add_pago").attr('disabled', true);
      event.preventDefault();
      $.ajax({
         async: false,
         url: factura_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data){
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               alert(datos.msj);
               listar_pagos();
            }
         },
         error: function(){
             alert("Error al intentar guardar el Pago!");
         }
      });
      $("#btn_add_pago").attr('disabled', false);
   });

   $("#b_eliminar_factura").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar la Factura?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_facturas_proveedor&delete='+idfacturaprov;
            }
         }
      });
   });

   $("#b_anular_documento").click(function(event) {
      event.preventDefault();
      validar_anulacion();
   });

   $("#btn_autorizacion").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea autorizar el Documento en el SRI?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = factura_url+'&autorizar';
            }
         }
      });
   });

   $("#btn_autorizacion_ret").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea autorizar la Retención en el SRI?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = factura_url+'&autorizar_ret';
            }
         }
      });
   });

   $("#btn_pagos").click(function(event) {
      event.preventDefault();
      listar_pagos();
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
               data: {codigobarras: buscar},
               success: function(data){
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#idarticulo_linea").val(datos.art.idarticulo);
                     $("#codprincipal").val(datos.art.codprincipal);
                     $("#descripcion").val(datos.art.nombre);
                     $("#cantidad").val(1);
                     $("#pvpunitario").val(0);
                     $("#dto").val(0);
                     $("#pvptotal").val(0);
                     $("#valorice").val(0);
                     $("#valorirbp").val(0);
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
   $('#idarticulo').on("change", function(e) {
      let idarticulo = $("#idarticulo").val();
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
                  $("#idarticulo_linea").val(datos.art.idarticulo);
                  $("#codprincipal").val(datos.art.codprincipal);
                  $("#descripcion").val(datos.art.nombre);
                  $("#cantidad").val(1);
                  $("#pvpunitario").val(0);
                  $("#dto").val(0);
                  $("#pvptotal").val(0);
                  $("#valorice").val(0);
                  $("#valorirbp").val(0);
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
            alert("Error al intentar buscar la forma de pago!");
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
            $('#idproveedor').select2({
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
                        buscar_proveedor: params.term
                     };
                  },
                  processResults: function (data, params) {
                     return {
                        results: $.map(data, function(obj, index) {
                           return { id: obj.idproveedor, text: obj.identificacion +" - "+ obj.razonsocial };
                        })
                     };
                  },
                  cache: true
               },
               data: [{id: datos.factura.idproveedor, text: datos.factura.identificacion +" - "+ datos.factura.razonsocial}],
               dropdownParent: $('#modal_editar_documento'),
               placeholder: 'Buscar Proveedor',
               theme: 'bootstrap-5',
               allowClear: true
            });
            if (datos.factura.coddocumento == '04' && datos.factura.idfactura_mod) {
               $("#idproveedor").attr('disabled', true);
            } else if (datos.factura.coddocumento == '05' && datos.factura.idfactura_mod) {
               $("#idproveedor").attr('disabled', true);
            }

            if (datos.factura.coddocumento == '04') {
               $("#observaciones").attr('required', true);
            }
            //Datos de la Factura
            $("#direccion").val(datos.factura.direccion);
            $("#email").val(datos.factura.email);
            $("#idsustento").val(datos.factura.idsustento).trigger('change');
            $("#fec_emision").val(datos.factura.fec_emision2);
            $("#fec_registro").val(datos.factura.fec_registro2);
            $("#diascredito").val(datos.factura.diascredito);
            if (datos.factura.coddocumento != '03') {
               $("#nro_autorizacion").val(datos.factura.nro_autorizacion);
            }
            //Datos del proveedor
            $("#dirproveedor").val(datos.proveedor.direccion);
            $("#emailproveedor").val(datos.proveedor.email);
            $("#tipidenproveedor").val(datos.proveedor.tipoid);
            $("#identproveedor").val(datos.proveedor.identificacion);
            $("#razonsproveedor").val(datos.proveedor.razonsocial);
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

function validar_anulacion()
{
   $.ajax({
      url: factura_url,
      type: "POST",
      data: {validar_anulacion: ''},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            if (datos.factura.numero_retencion && datos.factura.estado_sri_ret == 'AUTORIZADO') {
               $("#anul_ret").show();
            } else {
               $("#anul_ret").hide();
            }
            $("#modal_anular_documento").modal('show');
         }
      },
      error: function(){
         alert("Error al intentar buscar la Factura!");
      }
   });
}

function listar_pagos()
{
   $.ajax({
      url: factura_url,
      type: "POST",
      data: {buscar_pagos: ''},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#det_pagos").empty();
            $("#sal_pagos").empty();
            let total = parseFloat(datos.total);
            let pagos = parseFloat(0); 
            $(datos.pagos).each(function(key, value) {
               let valor = parseFloat(round(value.credito - value.debito, 2));
               pagos += valor;
               eliminar = '';
               imprimir = '';
               if (allow_delete == 1 && value.tipo == 'Pago') {
                  eliminar = "<a onclick='eliminar_pago("+value.idtranspago+")' id='b_eliminar_pago' class='btn btn-sm btn-danger'><span class='bi bi-trash2'></span></a>";
               }
               if (value.tipo == 'Pago' && impresion == 1) {
                  imprimir = "<a target='_blank' href='index.php?page=impresion_compras&pago&id="+value.idtranspago+"' id='b_imprimir_pago' class='btn btn-sm btn-outline-primary'><span class='bi bi-printer'></span></a>";
               }
               $("#det_pagos").append("<tr>\n\
                  <td style='text-align: left;'>"+value.tipo+"</td>\n\
                  <td style='text-align: right;'>"+value.fecha_trans+"</td>\n\
                  <td style='text-align: left;'>"+value.nombre_fp+"</td>\n\
                  <td style='text-align: right;'>"+valor+"</td>\n\
                  <td style='text-align: right;'><div class='btn-group'>"+eliminar+imprimir+"</div></td>\n\
               </tr>");
            });
            let saldo = parseFloat(round(pagos, 2));
            $("#sal_pagos").append("<tr>\n\
               <th style='text-align: left;'>Total Fac.</th>\n\
               <td style='text-align: right;'>"+total+"</td>\n\
               <th style='text-align: right;'>Saldo:</th>\n\
               <td style='text-align: right;'>"+saldo+"</td>\n\
               <td style='text-align: right;'></td>\n\
            </tr>");

            $("#valor").val(saldo);

            if (saldo > 0) {
               $("#div_nuevo_pago").show('slow');
            } else {
               $("#div_nuevo_pago").hide('slow');
            }
            $("#modal_ver_pagos").modal('show');
         }
      },
      error: function(){
          alert("Error al intentar buscar los pagos de la factura!");
      }
   });
}

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea de la Factura?',
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
                  }
                  actualizar_detalle_factura();
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Factura!");
               }
            });
         }
      }
   });
}

function eliminar_pago(idpago) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar el pago de la Factura?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: factura_url,
               type: "POST",
               data: {eliminar_pago: idpago},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     alert(datos.msj);
                     listar_pagos();
                  }
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Factura!");
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
            $("#base_gra").html(number_format(0, 2));
            $("#base_0").html(number_format(0, 2));
            $("#base_noi").html(number_format(0, 2));
            $("#base_exc").html(number_format(0, 2));
            $("#subtotal").html(number_format(0, 2));
            $("#totaldescuento").html(number_format(0, 2));
            $("#totalice").html(number_format(0, 2));
            $("#totaliva").html(number_format(0, 2));
            $("#totalirpb").html(number_format(0, 2));
            $("#totalfac").html(number_format(0, 2));
         } else {
            $(datos.lineas).each(function(key, value){
               let total = value.pvptotal + value.valorice + value.valoriva + value.valorirbp;
               let eliminar = '';
               if (allow_delete == 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlineafacprov+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               let editar_ret = '';
               if (editar == 1 && genera_ret == 1) {
                  editar_ret = "<a onclick='editar_linea_ret("+value.idlineafacprov+")' id='b_editar_linea' class='btn btn-sm btn-info'><span class='bi bi-pencil-fill'></span></a>";
               }

               $("#detalle_fac").append("<tr>\n\
                  <td><a href='"+value.url_art+"' target='_blank'>"+value.codprincipal+"</a></td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>\n\
                  <td style='text-align: right;'>"+value.pvpunitario+"</td>\n\
                  <td style='text-align: right;'>"+value.dto+"</td>\n\
                  <td style='text-align: right;'>"+value.valorice+"</td>\n\
                  <td style='text-align: right;'>"+value.valoriva+"</td>\n\
                  <td style='text-align: right;'>"+value.valorirbp+"</td>\n\
                  <td style='text-align: right;'>"+total+"</td>\n\
                  <td><div class='btn-group'>"+eliminar+editar_ret+"</div></td>\n\
               </tr>");
            });
            let subtotal = datos.factura.base_gra + datos.factura.base_0 + datos.factura.base_noi + datos.factura.base_exc;
            $("#base_gra").html(number_format(datos.factura.base_gra, 2));
            $("#base_0").html(number_format(datos.factura.base_0, 2));
            $("#base_noi").html(number_format(datos.factura.base_noi, 2));
            $("#base_exc").html(number_format(datos.factura.base_exc, 2));
            $("#subtotal").html(number_format(subtotal, 2));
            $("#totaldescuento").html(number_format(datos.factura.totaldescuento, 2));
            $("#totalice").html(number_format(datos.factura.totalice, 2));
            $("#totaliva").html(number_format(datos.factura.totaliva, 2));
            $("#totalirpb").html(number_format(datos.factura.totalirbp, 2));
            $("#totalfac").html(number_format(datos.factura.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la factura!");
      }
   });         
}
function calcular_total() {
   let cantidad = parseFloat($("#cantidad").val());
   let pvpunitario = parseFloat($("#pvpunitario").val());
   let dto = parseFloat($("#dto").val());
   let valorice = parseFloat($("#valorice").val());
   let valorirbp = parseFloat($("#valorirbp").val());
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
   let total = pvptotal + valorice + valorirbp + valoriva;
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
   $("#valorice").val('');
   $("#valorirbp").val('');
   $("#idimpuesto").val('');
   $("#total").val('');
   if (agretencion_empresa) {
      $("#idretencion_renta").val('');
      $("#idretencion_iva").val('');
   }
   $("#codigobarras").focus();
}

function actualizar_ret_masivo() {
   $.ajax({
      url: factura_url,
      type: "POST",
      data: {validar_edicion_retencion_masivo: ''},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#modal_editar_retencion_masiva").modal('show');
         }
      },
      error: function(){
         alert("Error al intentar buscar la Factura!");
      }
   });
}

function editar_linea_ret(idlinea) {
   $.ajax({
      url: factura_url,
      type: "POST",
      data: {validar_edicion_retencion: idlinea},
      success: function(data){
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#editar_ret").val(idlinea);
            $("#idretencion_renta_ind").val(datos.linea.idretencion_renta);
            $("#idretencion_iva_ind").val(datos.linea.idretencion_iva);
            $("#modal_editar_retencion").modal('show');
         }
      },
      error: function(){
         alert("Error al intentar buscar la Factura!");
      }
   });
}

function reenviar_correo(tipo) {
   $("#modal_ver_opciones").modal('hide');
   $("#reenviar_correo").val(tipo);
   if (tipo == 'retencion') {
      $("#tipoReenvio").html('Retención');
   } else {
      $("#tipoReenvio").html('');
   }
   $("#modal_reenviar_correo").modal('show');
}

function enviar_form(btn) {
   $(btn).prop('disabled', true)
   btn.form.submit();
}