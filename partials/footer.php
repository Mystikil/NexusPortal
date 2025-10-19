<!-- layout:footer:start -->
</main>
<footer class="site-footer">
    <div class="grid">
        <div>
            <h3>About</h3>
            <p>Nexus One is a modern control center for your online world. Forge heroes, conquer dungeons, and stay in sync with guildmates.</p>
        </div>
        <div>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="<?= e(siteUrl('register.php')) ?>">Create Account</a></li>
                <li><a href="<?= e(siteUrl('login.php')) ?>">Account Login</a></li>
                <li><a href="<?= e(siteUrl('news.php')) ?>">Community News</a></li>
                <li><a href="<?= e(siteUrl('highscores.php')) ?>">Highscores</a></li>
                <li><a href="<?= e(siteUrl('character.php')) ?>">Character Lookup</a></li>
                <li><a href="<?= e(siteUrl('guilds.php')) ?>">Guild Directory</a></li>
                <li><a href="<?= e(siteUrl('deaths.php')) ?>">Recent Deaths</a></li>
            </ul>
        </div>
        <div>
            <h3>Need Help?</h3>
            <p>Reach out to our support team via <a href="mailto:support@example.com">support@example.com</a>.</p>
        </div>
    </div>
    <p class="legal">&copy; <?= date('Y') ?> Nexus One. Crafted for Adventurers.</p>
</footer>
<script src="<?= e(assetUrl('js/app.js')) ?>"></script>
</body>
</html>
<!-- layout:footer:end -->
