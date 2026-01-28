$(document).ready(function () {

    let table = $('.employeeList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/dashboard",
            data: function (d) {
                d.search_value = $('#customSearchInput').val();
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'date', name: 'date' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // 🔍 Search
    $('#customSearchInput').keyup(function () {
        table.draw();
    });

    // ❌ Clear Search
    $('#customSearchClear').click(function () {
        $('#customSearchInput').val('');
        table.draw();
    });

    // 🎯 Status Filter
    $('#statusFilter').change(function () {
        table.draw();
    });

});
