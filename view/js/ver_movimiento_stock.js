$(document).ready(function() {
   actualizar_detalle_movimiento();
   $("#f_nueva_linea").submit(function (event) {
      event.preventDefault();
      $.ajax({
         url: movimiento_url,
         type: "POST",
         data: $(this).serialize(),
         success: function(data) {
            let datos = JSON.parse(data);
            if (datos.error == 'T') {
               alert(datos.msj);
            } else {
               $("#codigobarras").focus();
               limpiar_campos();
               actualizar_detalle_movimiento();
            }
         },
         error: function(){
             alert("Error al intentar guardar la linea del Movimiento!");
         }
      });
   });

   $("#b_eliminar_movimiento").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Movimiento de Stock?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_movimientos_stock&delete='+idmovimiento;
            }
         }
      });
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
                     $("#costo").val(datos.costo);
                     $("#costototal").val(0);
                     $("#stock").val(datos.art.stock_fisico);
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
            url: movimiento_url,
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
                  $("#costo").val(datos.costo);
                  $("#costototal").val(0);
                  $("#stock").val(datos.art.stock_fisico);
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

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea del Movimiento de Stock?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: movimiento_url,
               type: "POST",
               data: {eliminar_linea: idlinea},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     $("#codigobarras").focus();
                     actualizar_detalle_movimiento();
                  }
               },
               error: function(){
                   alert("Error al intentar guardar la linea del Movimiento de Stock!");
               }
            });
         }
      }
   });
}

function actualizar_detalle_movimiento() {
   $.ajax({
      url: movimiento_url+"&actdetmov",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_mov").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
         } else {
            $(datos.lineas).each(function(key, value){
               let eliminar = '';
               if (allow_delete == 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlineamovimiento+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               //ice
               //<td style='text-align: right;'>"+value.valorice+"</td>\n\
               $("#detalle_mov").append("<tr>\n\
                  <td><a href='"+value.url_art+"' target='_blank'>"+value.codprincipal+"</a></td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>\n\
                  <td style='text-align: right;'>"+value.costo+"</td>\n\
                  <td style='text-align: right;'>"+value.costototal+"</td>\n\
                  <td>"+eliminar+"</td>\n\
               </tr>");
            });
            $("#totalmov").html(number_format(datos.movimiento.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la movimiento!");
      }
   });
}
function calcular_total() {
   let cantidad = parseFloat($("#cantidad").val());
   let costo = parseFloat($("#costo").val());
   //Calculo el subtotal sin impuestos
   let costototal = (cantidad * costo);
   $("#costototal").val(costototal);
}

function limpiar_campos() {
   $("#idarticulo_linea").val('');
   $("#codprincipal").val('');
   $("#descripcion").val('');
   $("#cantidad").val('');
   $("#costo").val('');
   $("#costototal").val('');
   $("#stock").val('');
   $("#codigobarras").focus();
}