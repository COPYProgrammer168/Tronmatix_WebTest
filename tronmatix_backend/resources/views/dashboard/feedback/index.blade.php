<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - Tronmatix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#F97316',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #111827; color: #f1f5f9; font-family: sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-xl">
        <h1 class="text-2xl font-black text-white mb-6 text-center">Customer Feedback</h1>
        
        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-lg mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('feedback.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-1">Name</label>
                <input type="text" name="name" required class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-1">Email</label>
                <input type="email" name="email" required class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-1">Feedback</label>
                <textarea name="feedback" required rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-primary outline-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-orange-600 text-white font-black py-3 rounded-lg transition-all">
                Submit Feedback
            </button>
        </form>
    </div>
</body>
</html>
