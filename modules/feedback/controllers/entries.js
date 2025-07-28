function viewDetail(data) {
    //let feedbackData = data;
    $('#name').text(data.name);
    $('#email').text(data.email);
    $('#shopName').text(data.shopName);
    $('#shopType').text(data.shopType);
    $('#package').text(data.package);
    $('#description').text(data.description || 'No additional details.');
    $('#attachFile').html(data.attachFile || 'No additional File.');
    $('#date').text(data.date);
    $('#time').text(data.time);

    $('#formModal').modal('show');
}