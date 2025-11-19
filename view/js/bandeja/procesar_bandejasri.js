$(document).ready(function() {
   actualizar_detalle_doc();

   $("#idsustento").select2({
      dropdownParent: $('#modal_procesar'),
      placeholder: 'Buscar Sustento',
      theme: 'bootstrap-5',
      allowClear: true
   });

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

   $("#b_homologacion_masiva").click(function(event) {
      event.preventDefault();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: {val_hom_masiva: idbandejasri},
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#modal_homologar_masivo").modal('show');
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
         url: doc_url,
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
      dropdownParent: $('#modal_homologar'),
      placeholder: 'Buscar Artículo',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $("#idarticulo_mas").select2({
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
      dropdownParent: $('#modal_homologar_masivo'),
      placeholder: 'Buscar Artículo',
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

   $("#f_homologar_masiva").submit(function (event) {
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
               $("#modal_homologar_masivo").modal('hide');
               actualizar_detalle_doc();
            }
         },
         error: function(){
             alert("Error al intentar guardar la homologacion masiva del Documento!");
         }
      });
   });

   $("#f_editar_cantidad").submit(function (event) {
      event.preventDefault();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
               $("#cant_new").select();
            } else {
               $("#modal_editar_cantidad").modal('hide');
               actualizar_detalle_doc();
            }
         },
         error: function(){
             alert("Error al intentar guardar la edicion de la cantidad del Documento!");
         }
      });
   });
});

function actualizar_detalle_doc() {
   $.ajax({
      url: doc_url+"&actdetdoc",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_fac").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
         } else {
            $(datos.lineas).each(function(key, value){
               let total = round(value.pvptotal + value.valorice + value.valoriva + value.valorirbp, 6);
               let homologar = "";
               if (value.idarticulo) {
                  homologar = "<a href='#' onclick='homologacion("+value.idlineabandejasri+")' class='btn btn-sm btn-success' title='Ver homologación'>\n\
                                 <span class='bi bi-eye-fill'></span>\n\
                              </a>";
               } else {
                  homologar = "<a href='#' onclick='homologacion("+value.idlineabandejasri+")' class='btn btn-sm btn-info' title='Realizar homologación'>\n\
                                 <span class='bi bi-bezier2'></span>\n\
                              </a>";
               }
               let editar = "<a href='#' onclick='editar_cantidad(\""+value.idlineabandejasri+"\", \""+value.cantidad+"\", \""+value.codprincipal+"\", \""+value.descripcion+"\")' class='btn btn-sm btn-warning' title='Modificar Cantidad'>\n\
                                 <span class='bi bi-pencil-square'></span>\n\
                              </a>";
               $("#detalle_fac").append("<tr>\n\
                  <td>"+value.codprincipal+"</td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>\n\
                  <td style='text-align: right;'>"+total+"</td>\n\
                  <td><div class='btn-group'>"+homologar+editar+"</div>\n\</td>\n\
               </tr>");
            });
            let subtotal = datos.factura.base_gra + datos.factura.base_0 + datos.factura.base_noi + datos.factura.base_exc;
            $("#base_gra").html(datos.factura.base_gra);
            $("#base_0").html(datos.factura.base_0);
            $("#base_noi").html(datos.factura.base_noi);
            $("#base_exc").html(datos.factura.base_exc);
            $("#subtotal").html(subtotal);
            $("#totaldescuento").html(datos.factura.totaldescuento);
            $("#totalice").html(datos.factura.totalice);
            $("#totaliva").html(datos.factura.totaliva);
            $("#totalirpb").html(datos.factura.totalirbp);
            $("#totalfac").html(datos.factura.total);

         }
      },
      error: function(){
          alert("Error al intentar mostrar el detalle del Documento!");
      }
   });         
}

function editar_cantidad(idlineabandejasri, cantidad, codigo, descripcion)
{
   $("#ref_proveedor2").val(codigo);
   $("#desc_proveedor2").val(descripcion);
   $("#cant_ant").val(cantidad);
   $("#cant_new").val(cantidad);
   $("#editar_linea").val(idlineabandejasri);
   $("#modal_editar_cantidad").modal('show');
   $("#cant_new").select();
}

function homologacion(idlineabandejasri) {
   $("#hom_linea").val(idlineabandejasri);
   $("#crearart").prop('checked', false);
   mostrar_div_crearart();
   $("#idretencion_renta").val('');
   $("#idretencion_iva").val('');
   $("#idarticulo").empty().trigger('change');
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
         url: doc_url,
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
      dropdownParent: $('#modal_homologar'),
      placeholder: 'Buscar Artículo',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });

   $.ajax({
      url: doc_url,
      type: "POST",
      data: {buscar_linea: idlineabandejasri},
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            alert(datos.msj);
         } else {
            $("#ref_proveedor").val(datos.linea.codprincipal);
            $("#desc_proveedor").val(datos.linea.descripcion);
            if (datos.linea.idarticulo) {
               $('#idarticulo').html('').select2({
                  data: [{id: datos.art.idarticulo, text: datos.art.codprincipal +" - "+ datos.art.nombre}],
                  dropdownParent: $('#modal_homologar'),
                  theme: 'bootstrap-5',
                  allowClear: true
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
                     url: doc_url,
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
                  dropdownParent: $('#modal_homologar'),
                  placeholder: 'Buscar Artículo',
                  minimumInputLength: 3,
                  theme: 'bootstrap-5',
                  allowClear: true
               });
               if (agretencion_empresa && coddocumento == '01') {
                  $("#idretencion_renta").val(datos.linea.idretencion_renta);
                  $("#idretencion_iva").val(datos.linea.idretencion_iva);
               }
            }
            $("#modal_homologar").modal('show');
         }
      },
      error: function(){
          alert("Error al intentar Buscar la linea del Documento!");
      }
   });
}

function mostrar_div_crearart() {
   $("#desc_proveedor").attr('readonly', true);
   $("#idarticulo").attr('disabled', false);
   $("#idarticulo").empty().trigger('change');
   $("#div_new_art").html('');

   if ($("#crearart").is(':checked')) {
      let codigo = $("#ref_proveedor").val();
      $.ajax({
         url: doc_url,
         type: "POST",
         data: {codart: codigo},
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
               $("#crearart").prop('checked', false);
               mostrar_div_crearart();
            } else {
               $("#desc_proveedor").attr('readonly', false);
               $("#idarticulo").attr('disabled', true);
               let linea = '\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="tipo" class="col-form-label">Tipo:</label>\n\
                        <select id="tipo" name="tipo" class="form-select form-select-sm" required>\n\
                           <option value="1" selected>Artículo</option>\n\
                           <option value="2">Servicio</option>\n\
                        </select>\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="idgrupo" class="col-form-label">Grupo:</label>\n\
                        <select class="form-control form-control-sm" id="idgrupo" name="idgrupo">\n\
                           <option value="">Seleccione Grupo</option>';
                           for (var i = 0; i < grupos.length; i++) {
                              linea += '<option value="'+grupos[i].idgrupo+'">'+grupos[i].nombre+'</option>'
                           }
               linea += '</select>\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="idmarca" class="col-form-label">Marca:</label>\n\
                        <select class="form-control form-control-sm" id="idmarca" name="idmarca">\n\
                           <option value="">Seleccione Marca</option>';
                           for (var i = 0; i < marcas.length; i++) {
                             linea += '<option value="'+marcas[i].idmarca+'">'+marcas[i].nombre+'</option>'
                           }
               linea += '</select>\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="codbarras" class="col-form-label">Código de Barras:</label>\n\
                        <input type="text" class="form-control form-control-sm" id="codbarras" name="codbarras" placeholder="Código de Barras" maxlength="100">\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="idimpuesto" class="col-form-label">Impuesto:</label>\n\
                        <select class="form-control form-control-sm" id="idimpuesto" name="idimpuesto" required>\n\
                           <option value="">Seleccione Impuesto</option>';
                           for (var i = 0; i < impuestos.length; i++) {
                              linea += '<option value="'+impuestos[i].idimpuesto+'">'+impuestos[i].nombre+'</option>'
                           }
               linea += '</select>\n\
                     </div>\n\
                  </div>\n\
                  <div class="col-sm-4">\n\
                     <div class="form-group">\n\
                        <label for="precio" class="col-form-label">Precio + IVA:</label>\n\
                        <input type="text" class="form-control form-control-sm solo_numeros2" id="precio" name="precio" value="0" placeholder="Precio">\n\
                     </div>\n\
                  </div>\n\
               ';
               $(".solo_numeros2").validCampo('0123456789.');
               $("#div_new_art").append(linea);
            }
         },
         error: function(){
             alert("Error al intentar Buscar el Codigo del Producto!");
         }
      });
   } else {
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
            url: doc_url,
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
         dropdownParent: $('#modal_homologar'),
         placeholder: 'Buscar Artículo',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
   }
}

function mostrar_div_crearart_masivo() {
   $("#desc_proveedor_mas").attr('readonly', true);
   $("#ref_proveedor_mas").attr('readonly', true);
   $("#idarticulo_mas").attr('disabled', false);
   $("#idarticulo_mas").empty().trigger('change');
   $("#div_new_art_mas").html('');

   if ($("#crearartmas").is(':checked')) {
      $("#desc_proveedor_mas").attr('readonly', false);
      $("#ref_proveedor_mas").attr('readonly', false);
      $("#idarticulo_mas").attr('disabled', true);
      let linea = '\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="tipo" class="col-form-label">Tipo:</label>\n\
               <select id="tipo_mas" name="tipo" class="form-select form-select-sm" required>\n\
                  <option value="1" selected>Artículo</option>\n\
                  <option value="2">Servicio</option>\n\
               </select>\n\
            </div>\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="idgrupo_mas" class="col-form-label">Grupo:</label>\n\
               <select class="form-control form-control-sm" id="idgrupo" name="idgrupo">\n\
                  <option value="">Seleccione Grupo</option>';
                  for (var i = 0; i < grupos.length; i++) {
                     linea += '<option value="'+grupos[i].idgrupo+'">'+grupos[i].nombre+'</option>'
                  }
      linea += '</select>\n\
            </div>\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="idmarca_mas" class="col-form-label">Marca:</label>\n\
               <select class="form-control form-control-sm" id="idmarca" name="idmarca">\n\
                  <option value="">Seleccione Marca</option>';
                  for (var i = 0; i < marcas.length; i++) {
                    linea += '<option value="'+marcas[i].idmarca+'">'+marcas[i].nombre+'</option>'
                  }
      linea += '</select>\n\
            </div>\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="codbarras_mas" class="col-form-label">Código de Barras:</label>\n\
               <input type="text" class="form-control form-control-sm" id="codbarras" name="codbarras" placeholder="Código de Barras" maxlength="100">\n\
            </div>\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="idimpuesto_mas" class="col-form-label">Impuesto:</label>\n\
               <select class="form-control form-control-sm" id="idimpuesto" name="idimpuesto" required>\n\
                  <option value="">Seleccione Impuesto</option>';
                  for (var i = 0; i < impuestos.length; i++) {
                     linea += '<option value="'+impuestos[i].idimpuesto+'">'+impuestos[i].nombre+'</option>'
                  }
      linea += '</select>\n\
            </div>\n\
         </div>\n\
         <div class="col-sm-4">\n\
            <div class="form-group">\n\
               <label for="precio_mas" class="col-form-label">Precio + IVA:</label>\n\
               <input type="text" class="form-control form-control-sm solo_numeros2" id="precio" name="precio" value="0" placeholder="Precio">\n\
            </div>\n\
         </div>\n\
      ';
      $(".solo_numeros2").validCampo('0123456789.');
      $("#div_new_art_mas").append(linea);
   } else {
      $("#idarticulo_mas").select2({
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
         dropdownParent: $('#modal_homologar_masivo'),
         placeholder: 'Buscar Artículo',
         minimumInputLength: 3,
         theme: 'bootstrap-5',
         allowClear: true
      });
   }
}