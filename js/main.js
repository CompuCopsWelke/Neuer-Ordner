$(function() {
	$('#kategorie').on('change', function() {
         document.getElementById("suche").submit();
    });
	$('.bestand_row').on('click', function() {
         window.location='/index.php/apps/bestand/teil?id='+this.id;
    });
});
