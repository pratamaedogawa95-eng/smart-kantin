<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tenant - Smart Kantin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-xl font-bold text-center mb-6 text-gray-800">Login Dashboard Tenant</h2>
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm font-medium">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="/tenant/proses-login" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Username Stand</label>
                <input type="text" name="username" required placeholder="Contoh: bakso" class="w-full border p-2.5 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required placeholder="******" class="w-full border p-2.5 rounded-lg text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm transition shadow">
                Masuk Dashboard
            </button>
        </form>
    </div>
</body>
</html>