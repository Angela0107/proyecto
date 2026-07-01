$(document).ready(function() {
    // Cargar áreas al iniciar la página
    cargarAreas();

    // Evento change del select de área
    $("#area").change(function() {
        var idArea = $(this).val();

        if (idArea != "") {
            cargarTramites(idArea);
        } else {
            $("#tramite").html("");
            $("#formulario-dinamico").html("");
        }
    });

    // Evento change del select de trámite
    $("#tramite").change(function() {
        var idTramite = $(this).val();

        if (idTramite != "") {
            cargarFormulario(idTramite);
        } else {
            $("#formulario-dinamico").html("");
        }
    });
});