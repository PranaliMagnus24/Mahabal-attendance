$(document).ready(function () {
    const currentRole = $('.employeeList').data('role');
    const isAdmin = currentRole === 'admin';

    if ($.fn.DataTable.isDataTable('.employeeList')) {
        $('.employeeList').DataTable().destroy();
    }

    let columns = [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'name' },
        { data: 'phone' },
        {
            data: 'status',
            visible: authRole === 'admin'
        },
    ];
    if (isAdmin) {
        columns.push({ data: 'role' });
    }

    columns.push({ data: 'action', orderable: false, searchable: false });

    let table = $('.employeeList').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: "/dashboard",
            data: function (d) {
                d.search_value = $('#customSearchInput').val();
                d.status = $('#statusFilter').val();
            }
        },
        columns: columns
    });
    // Search
    $('#customSearchInput').keyup(function () {
        table.draw();
    });

    // Clear Search
    $('#customSearchClear').click(function () {
        $('#customSearchInput').val('');
        table.draw();
    });

    // Status Filter
    $('#statusFilter').change(function () {
        table.draw();
    });

    // Edit Button Click
    $(document).on('click', '.edit-btn', function () {
        let employeeId = $(this).data('id');
        $.get('/dashboard/show/' + employeeId)
            .done(function (data) {
                $('#editName').val(data.name);
                $('#editPhone').val(data.phone);
                $('#editStatus').val(data.status);
                $('#editWeeklyOff').val(data.weekly_off);
                $('#editPrice').val(data.price);
                $('#editDuration').val(data.duration);
                $('#editEmployeeForm').attr('action', '/dashboard/update/' + employeeId);
                $('#editEmployeeModal').modal('show');
            })
            .fail(function () {
                toastr.error('Failed to load employee data.');
            });
    });

    // Edit Form Submit
    $('#editEmployeeForm').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        let actionUrl = $(this).attr('action');
        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function (response) {
                $('#editEmployeeModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.success,
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error updating employee.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // Delete Button Click
    $(document).on('click', '.delete-btn', function () {
        let employeeId = $(this).data('id');
        $('#confirmDeleteBtn').data('id', employeeId);
        $('#deleteConfirmationModal').modal('show');
    });

    // Confirm Delete
    $('#confirmDeleteBtn').on('click', function () {
        let employeeId = $(this).data('id');
        $.ajax({
            url: '/dashboard/destroy/' + employeeId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#deleteConfirmationModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.success,
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting employee.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

});
$(document).on('click', '.change-password-btn', function () {
    let userId = $(this).data('id');
    $('#password_user_id').val(userId);
    $('#new_password').val('');
    $('#confirm_password').val('');
    $('#changePasswordModal').modal('show');
});

$('#savePasswordBtn').click(function () {
    let userId = $('#password_user_id').val();
    let password = $('#new_password').val();
    let confirm = $('#confirm_password').val();

    if (password !== confirm) {
        alert('Passwords do not match');
        return;
    }

    $.ajax({
        url: `/users/change-password/${userId}`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            password: password
        },
        success: function (res) {
            $('#changePasswordModal').modal('hide');
            alert(res.success);
        }
    });
});
