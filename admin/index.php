<?php
include('../connection.php');
session_start();

$stmt_content = $conn->prepare("SELECT * FROM content WHERE deleted = 0");
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
    <link href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css" rel="stylesheet" />
    <style>
        /* Custom Styles */
        .dataTables_wrapper select,
        .dataTables_wrapper .dataTables_filter input {
            color: #4a5568;
            padding-left: 1rem;
            padding-right: 1rem;
            padding-top: .5rem;
            padding-bottom: .5rem;
            line-height: 1.25;
            border-width: 2px;
            border-radius: .25rem;
            border-color: #edf2f7;
            background-color: white;
        }

        table.dataTable.hover tbody tr:hover,
        table.dataTable.display tbody tr:hover {
            background-color: #ebf4ff;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-weight: 700;
            border-radius: .25rem;
            border: 1px solid transparent;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            color: #fff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
            font-weight: 700;
            border-radius: .25rem;
            background: #667eea !important;
            border: 1px solid transparent;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #fff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
            font-weight: 700;
            border-radius: .25rem;
            background: #667eea !important;
            border: 1px solid transparent;
        }

        table.dataTable.no-footer {
            border-bottom: 1px solid #e2e8f0;
            margin-top: 0.75em;
            margin-bottom: 0.75em;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            background-color: #667eea !important;
        }

        #coursesTable_wrapper {
            box-sizing: border-box !important;
            border-width: 20px !important;
            border-color: white !important;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Sidebar Toggle for Mobile -->
    <button onclick="toggleSidebar()" class="md:hidden fixed top-4 left-4 z-50 bg-indigo-700 p-2 rounded text-white">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <div class="min-h-screen flex">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6">
                <h1 class="text-2xl font-bold mb-4 md:mb-0">Courses</h1>
                <button onclick="toggleModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Course
                </button>
            </div>

            <!-- Courses Table -->
            <div class="bg-white rounded-sm shadow-lg overflow-hidden">
                <div>
                    <table id="coursesTable" class="stripe hover" style="width:100%; padding-top: 1em; padding-bottom: 1em;">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Video</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($row['content_title']); ?>
                                        <div class="text-gray-500 text-xs">
                                            <?php echo substr(htmlspecialchars($row['contents']), 0, 15) . '...'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo substr(htmlspecialchars($row['video']), 0, 30) . '...'; ?>
                                    </td>
                                    <td>
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4 edit-button"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($row['content_title']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['contents']); ?>"
                                            data-lesson="<?php echo $row['lesson_number']; ?>"
                                            data-term="<?php echo $row['term']; ?>"
                                            data-semester="<?php echo $row['semester']; ?>"
                                            data-video="<?php echo $row['video']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="deleteContent(<?php echo $row['id']; ?>)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Course Modal -->
    <div id="addCourseModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full md:w-1/2 p-6">
            <h2 class="text-xl font-bold mb-4">Add Course Content</h2>
            <form id="contentForm">
                <div class="mb-4">
                    <label class="block text-gray-700">Course Title</label>
                    <input type="text" id="contentTitle" name="contentTitle" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Lesson Number</label>
                    <input type="number" id="lesson_number" name="lesson_number" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Term</label>
                    <input type="text" id="term" name="term" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Semester</label>
                    <input type="text" id="semester" name="semester" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Course Details</label>
                    <textarea id="contentDescription" name="contentDescription" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Course Video File</label>
                    <input type="file" id="contentVideo" name="contentVideo" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div id="editCourseModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full md:w-1/2 p-6">
            <h2 class="text-xl font-bold mb-4">Edit Course Content</h2>
            <form id="editContentForm">
                <input type="hidden" id="editContentId" name="contentId"> <!-- Hidden input to store course ID -->
                <div class="mb-4">
                    <label class="block text-gray-700">Course Title</label>
                    <input type="text" id="editContentTitle" name="contentTitle" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Lesson Number</label>
                    <input type="number" id="editLessonNumber" name="lessonNumber" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Term</label>
                    <input type="text" id="editTerm" name="term" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Semester</label>
                    <input type="text" id="editSemester" name="semester" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Course Details</label>
                    <textarea id="editContentDescription" name="contentDescription" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Course Video File</label>
                    <input type="file" id="editContentVideo" name="contentVideo" class="block w-full mt-1 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:outline-none">
                    <p class="text-gray-600">Current Video: <span id="currentVideo"></span></p>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="toggleEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#coursesTable').DataTable({
                    responsive: true
                })
                .columns.adjust()
                .responsive.recalc();
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }

        function toggleModal() {
            document.getElementById('addCourseModal').classList.toggle('hidden');
        }

        document.getElementById('contentForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait while the content is being uploaded.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Start the loading animation
                }
            });
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
    <script>
        // Toggle Edit Modal
        function toggleEditModal() {
            document.getElementById('editCourseModal').classList.toggle('hidden');
            console.log('Edit Modal Toggled');
        }

        // Fill and Show Edit Modal
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                const description = this.dataset.description;
                const lesson = this.dataset.lesson;
                const term = this.dataset.term;
                const semester = this.dataset.semester;
                let video = this.dataset.video;

                if (video === 'null') {
                    video = '';
                }

                // Populate form fields
                document.getElementById('editContentId').value = id;
                document.getElementById('editContentTitle').value = title;
                document.getElementById('editContentDescription').value = description;
                document.getElementById('editLessonNumber').value = lesson;
                document.getElementById('editTerm').value = term;
                document.getElementById('editSemester').value = semester;
                document.getElementById('currentVideo').textContent = video;


                // Show the modal
                toggleEditModal();
            });
        });

        // Handle Edit Form Submission
        document.getElementById('editContentForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while the content is being updated.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Start the loading animation
                }
            });

            let formData = new FormData(this);

            const response = await axios.post('action/edit_content.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            console.log(response.data);

            if (response.data.status === 'success') {
                await Swal.fire({
                    title: "Good job!",
                    text: response.data.message,
                    icon: "success"
                }).then(() => {
                    window.location.reload();
                });
            } else {
                await Swal.fire({
                    title: "Oops...",
                    text: response.data.message,
                    icon: "error"
                });
            }
        });
    </script>
    <script>
        async function deleteContent(id) {
            const result = await Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                reverseButtons: true
            });

            if (result.isConfirmed) {
                const response = await axios.post('action/delete_content.php', {
                    id: id
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        title: "Good job!",
                        text: response.data.message,
                        icon: "success"
                    }); 
                    location.reload();
                } else {
                    await Swal.fire({
                        title: "Oops...",
                        text: response.data.message,
                        icon: "error"
                    });
                }
            }
        }
    </script>
</body>

</html>