<?php
$page_title = "Register";
require_once __DIR__ . '/../config/config.php';

// Ambil error dan data form jika ada (setelah redirect)
$errors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? ['name' => '', 'email' => ''];
unset($_SESSION['errors'], $_SESSION['form_data']);

// Kita tidak pakai header.php standar karena ini halaman full-screen
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="h-full">
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Buat akun baru</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-md">
        
        <!-- Tampilkan Error -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
                <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="space-y-6" action="../app/process_register.php" method="POST">
            <div>
                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Nama Lengkap</label>
                <div class="mt-2">
                    <input id="name" name="name" type="text" autocomplete="name" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($form_data['name']); ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Alamat Email</label>
                <div class="mt-2">
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($form_data['email']); ?>">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                </div>
                <div class="mt-2">
                    <input id="password" name="password" type="password" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           aria-describedby="password-hint">
                    <p class="mt-2 text-xs text-gray-500" id="password-hint">Minimal 8 karakter.</p>
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium leading-6 text-gray-900">Konfirmasi Password</label>
                <div class="mt-2">
                    <input id="confirm_password" name="confirm_password" type="password" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Register
                </button>
            </div>
        </form>

        <p class="mt-10 text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="login.php" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Login di sini</a>
        </p>
    </div>
</div>
</body>
</html>
