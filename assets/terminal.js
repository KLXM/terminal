document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.terminal-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Verhindert das normale Verhalten des Links
            var href = this.getAttribute('href');
            // Öffnet das Popup; passen Sie die Parameter nach Bedarf an
            window.open(href, 'REDAXO Terminal', 'width=800,height=600,left=200,top=200');
        });
    });
});
