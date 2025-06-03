</main>
    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> NightClub Projekt Szkolny. Stworzone z pasją (i odrobiną kodu).</p>
        </div>
    </footer>
    <script src="/nightclub/js/script.js?v=<?php echo time(); // Cache busting ?>"></script>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) { // Sprawdź, czy $conn jest obiektem mysqli
    $conn->close();
}
?>