let form = $('form[name="ip"]');
let formUrl = form.attr('data-api-action');

$(document).on('submit', function (event) {
    event.preventDefault();

    let uploadFile = form.find('input[type="file"]').prop('files')[0];
    let formData = new FormData();
    formData.append('ip[ip]', uploadFile);

    $.ajax ({
        url: formUrl + '?' + form.find('input[name="initial"]').val(),
        dataType: 'text',
        type: "POST",
        data: formData,
        beforeSend: function () {
            $('#status').foundation('open');
        },
        complete: function () {
            $('#status').foundation('close');
        },
        cache: false,
        processData: false,
        contentType: false
    })
        .done( function (data, textStatus, request) {
            let a = document.createElement("a");
            document.body.appendChild(a);
            let blob = new Blob([data], {type: 'text/csv'});
            let url = window.URL.createObjectURL(blob);
            a.href = url;
            a.download = 'Cities.csv';
            a.click();
            //window.URL.revokeObjectURL(url);
            //console.log(blob, request.getAllResponseHeaders());
        })
});