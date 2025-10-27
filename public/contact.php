<?php
$page_title = "Hubungi Kami";
require_once __DIR__ . '/includes/header.php';

// Ambil pesan sukses/error dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? ['name' => '', 'email' => ''];
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['form_data']);

?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    
    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!$success_message): ?>
        <form action="../app/process_contact.php" method="POST" class="space-y-6">
            
            <div>
                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Nama Anda</label>
                <div class="mt-2">
                    <input id="name" name="name" type="text" autocomplete="name" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($form_data['name']); ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email Anda</label>
                <div class="mt-2">
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($form_data['email']); ?>">
                </div>
            </div>

            <div>
                <label for="message" class="block text-sm font-medium leading-6 text-gray-900">Pesan Anda</label>
                <div class="mt-2">
                    <textarea id="message" name="message" rows="5" required
                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    ><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div>
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Kirim Pesan
                </button>
            </div>
        </form>
    <?php endif; ?>

</div>

<?php
// Panggil footer
require_once __DIR__ . '/includes/footer.php';
?>