<?php
// File: public/layout/footer.php
?>
</main>

<footer class="bg-gray-800 text-white mt-8">
    <div class="container mx-auto px-4 py-6 text-center">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Dibuat dengan PHP dan Tailwind CSS.</p>
        <div class="mt-2 space-x-4">
            <a href="<?= BASE_URL ?>/about.php" class="text-gray-400 hover:text-white transition duration-150">Tentang Kami</a>
            <a href="<?= BASE_URL ?>/contact.php" class="text-gray-400 hover:text-white transition duration-150">Kontak Kami</a>
        </div>
    </div>
</footer>

<script>
    // Contoh JavaScript untuk menu mobile sederhana (jika diperlukan)
    const mobileMenuBtn = document.getElementById('mobile-menu-button');
    // ... Tambahkan logika menu mobile di sini
</script>

</body>
</html>
