<?php
include('../connection.php');
session_start();

$stmt_content = $conn->prepare("SELECT * FROM content");
$stmt_content->execute();
$result = $stmt_content->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Portal Admin Dashboard - Courses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        #coursesTable thead {
            background-color: #4f46e5;
        }

        #coursesTable thead th {
            font-weight: 600;
            color: white;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        #coursesTable tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        #coursesTable tbody tr:hover {
            background-color: #edf2f7;
        }

        #coursesTable td,
        #coursesTable th {
            padding: 1rem;
        }

        .hover\:bg-gray-50:hover {
            background-color: #f7fafc;
        }

        .text-indigo-600:hover {
            color: #3730a3;
        }

        .text-red-600:hover {
            color: #b91c1c;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-indigo-700 text-white p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Learning Portal</h1>
                <p class="text-indigo-200">Admin Dashboard</p>
            </div>
            <nav>
                <ul class="space-y-2">
                    <li><a href="test.php" class="block py-2 px-4 rounded bg-indigo-600">Courses</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Courses</h1>
                <button onclick="toggleModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Course
                </button>
            </div>

            <!-- Courses Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table id="coursesTable" class="min-w-full divide-y divide-gray-200 border-collapse">
                                <thead class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide">Course Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide">Course Video File</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr class="hover:bg-gray-50 transition-all duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 text-sm font-medium">
                                                <?php echo htmlspecialchars($row['content_title']); ?>
                                                <div class="text-gray-500 text-xs">
                                                    <?php echo substr(htmlspecialchars($row['contents']), 0, 15) . '...'; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo substr(htmlspecialchars($row['video']), 0, 30) . '...'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">
                                                <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-4" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="text-red-600 hover:text-red-900" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Course Modal -->
    <div id="addCourseModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-1/2 p-6">
            <h2 class="text-xl font-bold mb-4">Add Course Content</h2>
            <form id="contentForm">
                <div class="mb-4">
                    <label class="block text-gray-700">Course Title</label>
                    <input type="text" id="contentTitle" name="contentTitle" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Course Details</label>
                    <textarea id="contentDescription" name="contentDescription" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Courset Video File</label>
                    <input type="file" id="contentVideo" name="contentVideo" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#coursesTable').DataTable();
        });

        document.querySelectorAll('.fa-edit, .fa-trash-alt').forEach(icon => {
            icon.addEventListener('mouseenter', function() {
                this.setAttribute('data-tooltip', this.parentElement.getAttribute('title'));
            });
        });

        // Toggle modal visibility
        function toggleModal() {
            document.getElementById('addCourseModal').classList.toggle('hidden');
        }

        document.getElementById('contentForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            axios.post('action/add_content.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(function(response) {
                    if (response.data.status === 'success') {
                        Swal.fire({
                            title: "Good job!",
                            text: response.data.message,
                            icon: "success"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: "Oops...",
                            text: response.data.message,
                            icon: "error"
                        });
                    }
                })
                .catch(function(error) {
                    Swal.fire({
                        title: "Oops...",
                        text: "An error occurred while uploading the file.",
                        icon: "error"
                    });
                    console.error('Error:', error);
                });
        });
    </script>

</body>

</html>