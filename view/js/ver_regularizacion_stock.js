$(document).ready(function() {
   actualizar_detalle_regularizacion();

   $("#b_eliminar_regularizacion").click(function(event) {
      event.preventDefault();
      bootbox.confirm({
         message: '¿Realmente desea eliminar el Regularización de Stock?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = 'index.php?page=lista_regularizaciones_stock&delete='+idregularizacion;
            }
         }
      });
   });

   $("#btn_opciones").click(function(event) {
      event.preventDefault();
      $("#modal_ver_opciones").modal('show');
   });
});

function actualizar_detalle_regularizacion() {
   $.ajax({
      url: regularizacion_url+"&actdetreg",
      type: "POST",
      success: function(data){
         let datos = JSON.parse(data);
         $("#detalle_reg").empty();
         if (datos.error == 'T') {
            //alert(datos.msj);
         } else {
            $(datos.lineas).each(function(key, value){
               let eliminar = '';
               if (allow_delete == 1) {
                  eliminar = "<a onclick='eliminar_linea("+value.idlinearegularizacion+")' id='b_eliminar_linea' class='btn btn-sm btn-danger'><span class='bi bi-trash'></span></a>";
               }
               //ice
               //<td style='text-align: right;'>"+value.valorice+"</td>\n\
               $("#detalle_reg").append("<tr>\n\
                  <td><a href='"+value.url_art+"' target='_blank'>"+value.codprincipal+"</a></td>\n\
                  <td>"+value.descripcion+"</td>\n\
                  <td style='text-align: right;'>"+value.cantidad+"</td>\n\
                  <td style='text-align: right;'>"+value.nueva_cantidad+"</td>\n\
                  <td style='text-align: right;'>"+(value.nueva_cantidad - value.cantidad)+"</td>\n\
                  <td style='text-align: right;'>"+value.costo+"</td>\n\
                  <td style='text-align: right;'>"+value.costototal+"</td>\n\
                  <td>"+eliminar+"</td>\n\
               </tr>");
            });
            $("#totalmov").html(number_format(datos.regularizacion.total, 2));

         }
      },
      error: function(){
          alert("Error al intentar mostrar la regularizacion!");
      }
   });
}

function eliminar_linea(idlinea) {
   bootbox.confirm({
      message: '¿Realmente desea eliminar la linea de la Regularización de Stock?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            $.ajax({
               url: regularizacion_url,
               type: "POST",
               data: {eliminar_linea: idlinea},
               success: function(data) {
                  let datos = JSON.parse(data);
                  if (datos.error == 'T') {
                     alert(datos.msj);
                  } else {
                     actualizar_detalle_regularizacion();
                  }
               },
               error: function(){
                   alert("Error al intentar guardar la linea de la Regularización de Stock!");
               }
            });
         }
      }
   });
}