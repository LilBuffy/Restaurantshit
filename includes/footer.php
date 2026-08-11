    </main>
    <footer>
        <div class="container footer-grid">
            <div>
                <h4>Basta Masarap</h4>
                <p><?= t('footer_tagline') ?></p>
                <p><?= t('footer_hours') ?></p>
            </div>
            <div>
                <h4><?= t('nav_menu') ?></h4>
                <a href="/basta-masarap/menu.php?category=ulam"><?= currentLang() === 'fil' ? 'Ulam' : 'Main Dishes' ?></a>
                <a href="/basta-masarap/menu.php?category=silog">Silog</a>
                <a href="/basta-masarap/menu.php?category=desserts"><?= currentLang() === 'fil' ? 'Panghimagas' : 'Desserts' ?></a>
                <a href="/basta-masarap/menu.php?category=drinks"><?= currentLang() === 'fil' ? 'Inumin' : 'Drinks' ?></a>
            </div>
            <div>
                <h4><?= t('footer_contact') ?></h4>
                <p>📍 Quezon City, Philippines</p>
                <p>📞 (02) 8123-4567</p>
                <p>✉️ hello@bastamasarap.local</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Basta Masarap Restaurant. <?= t('footer_rights') ?>
        </div>
    </footer>
    <script src="/basta-masarap/assets/js/app.js"></script>
</body>
</html>
