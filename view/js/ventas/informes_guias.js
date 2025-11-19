$(document).ready(function() {
   $("#idcliente1").select2({
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
      placeholder: 'Buscar Cliente',
      minimumInputLength: 3,
      theme: 'bootstrap-5',
      allowClear: true
   });
});