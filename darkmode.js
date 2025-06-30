<script>
    // Dark-Mode umschalten und speichern
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const tables = document.querySelectorAll('table');
        tables.forEach(table => table.classList.toggle('dark-mode'));

        // Zustand speichern
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    }

    // Dark-Mode beim Laden aktivieren, falls gespeichert
    window.onload = function() {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            const tables = document.querySelectorAll('table');
            tables.forEach(table => table.classList.add('dark-mode'));
        }
    }
</script>