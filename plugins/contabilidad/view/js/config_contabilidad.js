$(document).ready(function() {});

function actualizar_banco(id) {
   let valor = 0;
   if ($("#esbanco_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsub: id,
         valorp: valor
      },
      beforeSend: function() {
         $("#esb_" + id).addClass('bg-warning');
         $("#esb_" + id).html('Guardando....');
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $("#esb_" + id).removeClass('bg-warning');
            $("#esb_" + id).html('');
            alert(datos.msj);
         } else {
            $("#esb_" + id).removeClass('bg-warning');
            $("#esb_" + id).addClass('bg-success');
            $("#esb_" + id).html('Guardado');
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_cajach(id) {
   let valor = 0;
   if ($("#escajach_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubch: id,
         valorp: valor
      },
      beforeSend: function() {
         $("#escj_" + id).addClass('bg-warning');
         $("#escj_" + id).html('Guardando....');
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $("#escj_" + id).removeClass('bg-warning');
            $("#escj_" + id).html('');
            alert(datos.msj);
         } else {
            $("#escj_" + id).removeClass('bg-warning');
            $("#escj_" + id).addClass('bg-success');
            $("#escj_" + id).html('Guardado');
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctacliente(id) {
   let valor = 0;
   if ($("#ctacliente_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubccli: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentascli").each(function() {
               let ids = $(this).val();
               $("#ctacliente_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentascli").each(function() {
               let ids = $(this).val();
               $("#ctacliente_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctacliente_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctaantprov(id) {
   let valor = 0;
   if ($("#ctaantprov_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcantprov: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasantprov").each(function() {
               let ids = $(this).val();
               $("#ctaantprov_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasantprov").each(function() {
               let ids = $(this).val();
               $("#ctaantprov_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctaantprov_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctaproveedor(id) {
   let valor = 0;
   if ($("#ctaproveedor_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcprov: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasprov").each(function() {
               let ids = $(this).val();
               $("#ctaproveedor_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasprov").each(function() {
               let ids = $(this).val();
               $("#ctaproveedor_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctaproveedor_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctaantcli(id) {
   let valor = 0;
   if ($("#ctaantcli_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcantcli: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasantcli").each(function() {
               let ids = $(this).val();
               $("#ctaantcli_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasantcli").each(function() {
               let ids = $(this).val();
               $("#ctaantcli_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctaantcli_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctaresultado(id) {
   let valor = 0;
   if ($("#ctaresultado_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcresul: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasresult").each(function() {
               let ids = $(this).val();
               $("#ctaresultado_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasresult").each(function() {
               let ids = $(this).val();
               $("#ctaresultado_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctaresultado_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctaventa(id) {
   let valor = 0;
   if ($("#ctaventa_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcventa: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasventas").each(function() {
               let ids = $(this).val();
               $("#ctaventa_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasventas").each(function() {
               let ids = $(this).val();
               $("#ctaventa_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctaventa_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctadtoventa(id) {
   let valor = 0;
   if ($("#ctadtoventa_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcdtoventa: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasdesc").each(function() {
               let ids = $(this).val();
               $("#ctadtoventa_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasdesc").each(function() {
               let ids = $(this).val();
               $("#ctadtoventa_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctadtoventa_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctadevolventa(id) {
   let valor = 0;
   if ($("#ctadevolventa_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcdevol: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasdevol").each(function() {
               let ids = $(this).val();
               $("#ctadevolventa_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasdevol").each(function() {
               let ids = $(this).val();
               $("#ctadevolventa_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctadevolventa_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctacompra(id) {
   let valor = 0;
   if ($("#ctacompra_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubccompra: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentascompras").each(function() {
               let ids = $(this).val();
               $("#ctacompra_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentascompras").each(function() {
               let ids = $(this).val();
               $("#ctacompra_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctacompra_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctacostos(id) {
   let valor = 0;
   if ($("#ctacostos_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubccostos: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentascostos").each(function() {
               let ids = $(this).val();
               $("#ctacostos_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentascostos").each(function() {
               let ids = $(this).val();
               $("#ctacostos_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctacostos_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ingresos(id) {
   let valor = 0;
   if ($("#ingresos_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubingresos: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasing").each(function() {
               let ids = $(this).val();
               $("#ingresos_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasing").each(function() {
               let ids = $(this).val();
               $("#ingresos_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ingresos_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ingresosnop(id) {
   let valor = 0;
   if ($("#ingresosnop_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubingresosnop: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasingnop").each(function() {
               let ids = $(this).val();
               $("#ingresosnop_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasingnop").each(function() {
               let ids = $(this).val();
               $("#ingresosnop_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ingresosnop_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_costos(id) {
   let valor = 0;
   if ($("#costos_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubcostos: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentascostos").each(function() {
               let ids = $(this).val();
               $("#costos_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentascostos").each(function() {
               let ids = $(this).val();
               $("#costos_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#costos_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_gastos(id) {
   let valor = 0;
   if ($("#gastos_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubgastos: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasgastos").each(function() {
               let ids = $(this).val();
               $("#gastos_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasgastos").each(function() {
               let ids = $(this).val();
               $("#gastos_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#gastos_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_egresosnop(id) {
   let valor = 0;
   if ($("#egresosnop_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubegresosnop: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasegrnop").each(function() {
               let ids = $(this).val();
               $("#egresosnop_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasegrnop").each(function() {
               let ids = $(this).val();
               $("#egresosnop_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#egresosnop_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctanotaventa(id) {
   let valor = 0;
   if ($("#ctanotaventa_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubctanotaventa: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasnotaventas").each(function() {
               let ids = $(this).val();
               $("#ctanotaventa_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasnotaventas").each(function() {
               let ids = $(this).val();
               $("#ctanotaventa_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctanotaventa_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}

function actualizar_ctanotacostos(id) {
   let valor = 0;
   if ($("#ctanotacostos_" + id).is(':checked')) {
      valor = 1;
   }
   $.ajax({
      url: url,
      type: "POST",
      data: {
         idsubctanotacostos: id,
         valorp: valor,
         idejerciciop: $("#idejercicio").val()
      },
      beforeSend: function() {
      },
      success: function(data) {
         let datos = JSON.parse(data);
         if (datos.error == 'T') {
            $(".cuentasnotacostos").each(function() {
               let ids = $(this).val();
               $("#ctanotacostos_" + ids).prop('checked', false);
            });
            alert(datos.msj);
         } else {
            $(".cuentasnotacostos").each(function() {
               let ids = $(this).val();
               $("#ctanotacostos_" + ids).prop('checked', false);
            });
            if (valor == 1) {
               $("#ctanotacostos_" + id).prop('checked', true);
            }
         }
      },
      error: function() {
         alert("Error al intentar guardar la Parametrización.");
      }
   });
}