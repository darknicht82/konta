$(document).ready(function() {
   $("#b_proveedor").select2({
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
      placeholder: 'Buscar Proveedor',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });
});

$(document).on('select2:open', () => {
   document.querySelector('.select2-search__field').focus();
});

function eliminar_pago(idpago, numero)
{
   bootbox.confirm({
      message: '¿Realmente desea eliminar el Pago '+numero+'?',
      title: '<b>Atención</b>',
      callback: function(result) {
         if (result) {
            window.location.href = 'index.php?page=lista_pagos_proveedores&delete='+idpago;
         }
      }
   });
}