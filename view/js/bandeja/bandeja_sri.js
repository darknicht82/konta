$(document).ready(function() {
   $("#b_cargardatos").click(function(event) {
      event.preventDefault();
      $("#modal_cargardatos").modal('show');
      document.f_cargardatos.nro_aut.focus();
   });
});


function cargar_datos(btn, coddocumento)
{
   //Borro los datos de la bandeja
   $("#detalle_fac").html('');
   $("#detalle_ncp").html('');
   $("#detalle_ndc").html('');
   $("#detalle_ret").html('');
   //genero el paso para realizar la solicitud
   let abierto = true;
   if (coddocumento == '01') {
      if ($("#facpro").hasClass('collapsed')) {
         abierto = false;
      }
   } else if (coddocumento == '04') {
      if ($("#ncppro").hasClass('collapsed')) {
         abierto = false;
      }
   } else if (coddocumento == '05') {
      if ($("#ncdpro").hasClass('collapsed')) {
         abierto = false;
      }
   } else if (coddocumento == '07') {
      if ($("#retcli").hasClass('collapsed')) {
         abierto = false;
      }
   }

   if (abierto) {
      $.ajax({
         url: url+"&coddoc="+coddocumento,
         type: "POST",
         data: $("#f_buscador").serialize(),
         success: function(data){
            let datos = JSON.parse(data);
            let linea = '';
            if (datos.error == 'F') {
               $(datos.lineas).each(function(key, value) {
                  let estado = '';
                  let procesar = '';
                  if (value.estado == 0) {
                     estado = "<a href='index.php?page=bandeja_sri&noprocesar="+value.idbandejasri+"' class='btn btn-sm btn-outline-warning' title='No Procesar Documento.'>\n\
                                 <span class='bi bi-clipboard-x'></span>\n\
                              </a>";
                     procesar = "<a href='index.php?page=procesar_bandejasri&id="+value.idbandejasri+"' class='btn btn-sm btn-outline-primary' title='Procesar Documento.'>\n\
                                 <span class='bi bi-play-btn'></span>\n\
                              </a>";
                  } else if (value.estado == 1) {
                     estado = "<a href='index.php?page=bandeja_sri&pendiente="+value.idbandejasri+"' class='btn btn-sm btn-outline-success' title='Poner Documento como pendiente.'>\n\
                                 <span class='bi bi-clipboard-plus'></span>\n\
                              </a>";
                  }

                  linea += "<tr>\n\
                     <td>"+value.identificacion+"</td>\n\
                     <td>"+value.razonsocial+"</td>\n\
                     <td>"+value.nro_autorizacion+"</td>\n\
                     <td>"+value.fec_emision+"</td>\n\
                     <td style='text-align: right;'>"+number_format(value.total)+" $</td>\n\
                     <td style='text-align: right;'>\n\
                        <div class='btn-group'>"+procesar+estado+"</div>\n\
                     </td>\n\
                  </tr>";
               });
            }
            if (linea == '') {
               linea = '<tr class="table-warning"><td colspan="6">Sin Resultados.</td></tr>'
            }
            if (coddocumento == '01') {
               $("#detalle_fac").append(linea);
            } else if (coddocumento == '04') {
               $("#detalle_ncp").append(linea);
            } else if (coddocumento == '05') {
               $("#detalle_ndc").append(linea);
            } else if (coddocumento == '07') {
               $("#detalle_ret").append(linea);
            }
         },
         error: function(){
             alert("Error al intentar mostrar el detalle del Documento!");
         }
      });
   }
}