<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</head>

<body>
    <nav class="w-64 bg-blue-900 text-white p-5 border-r border-gray-200 hidden lg:block">
        <div class="flex items-center gap-3 text-blue-500 font-semibold text-lg mb-8">
            <svg width="40" height="40" viewBox="0 0 40 40">
                <circle cx="20" cy="20" r="18" fill="#2196F3" />
                <path d="M20 10 L28 15 L28 25 L20 30 L12 25 L12 15 Z" fill="white" />
            </svg>
            <span>Learning Portal</span>
        </div>
        <ul>
            <li onclick="location.href='index.php'" class="py-3 px-4 rounded-lg bg-blue-50 text-blue-500 mb-2 cursor-pointer">
                <i class="fas fa-play-circle mr-2"></i> Lectures
            </li>
        </ul>
    </nav>

</body>

</html>