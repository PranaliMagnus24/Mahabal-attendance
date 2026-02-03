$(function () {

    // Date range validation
    $('#startDate').on('change', function () {
        const startDate = $(this).val();
        $('#endDate').attr('min', startDate);

        // If end date is before start date, clear it
        const endDate = $('#endDate').val();
        if (endDate && endDate < startDate) {
            $('#endDate').val('');
        }
    });

    let table = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('attendance.list') }}",
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
                d.user_id = $('#filterUser').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'id' },
            { data: 'date' },
            { data: 'user_name' },
            { data: 'check_in' },
            { data: 'check_out' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    $('#startDate, #endDate, #filterUser').change(function () {
        table.draw();
    });

    // Handle show details button click
    $(document).on('click', '.show-details', function () {
        var attendanceId = $(this).data('id');
        $.ajax({
            url: '{{ route("attendance.show", ":id") }}'.replace(':id', attendanceId),
            type: 'GET',
            success: function (data) {
                $('#userName').text(data.user.name);
                $('#userEmail').text(data.user.email);
                $('#userPhone').text(data.user.phone || 'N/A');
                $('#attendanceDate').text(data.date);
                $('#checkInTime').text(data.check_in_time ? new Date(data.check_in_time).toLocaleString() : 'N/A');
                $('#checkOutTime').text(data.check_out_time ? new Date(data.check_out_time).toLocaleString() : 'N/A');
                $('#checkInSelfie').attr('src', data.check_in_selfie ? '{{ asset("storage/") }}' + data.check_in_selfie : '');
                $('#checkOutSelfie').attr('src', data.check_out_selfie ? '{{ asset("storage/") }}' + data.check_out_selfie : '');
                $('#attendanceDetailsModal').modal('show');
            },
            error: function () {
                alert('Error fetching attendance details.');
            }
        });
    });

});
