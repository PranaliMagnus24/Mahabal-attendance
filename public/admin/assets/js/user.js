$(document).ready(function () {

    let table = $('.employeeList').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "/dashboard",
            data: function (d) {
                d.search_value = $('#customSearchInput').val();
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'date' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'action', orderable: false, searchable: false }
        ]
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
                toastr.success(response.success);
            },
            error: function (xhr) {
                toastr.error('Error updating employee.');
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
                toastr.success(response.success);
            },
            error: function (xhr) {
                alert('Error deleting employee.');
            }
        });
    });

});
