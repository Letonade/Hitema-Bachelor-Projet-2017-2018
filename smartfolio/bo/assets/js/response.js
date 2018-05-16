document.addEventListener('DOMContentLoaded', function () {
    var responses = document.querySelectorAll('#response > p');

    for (var i = 0; i < responses.length; i += 1) {
        responses[i].innerHTML = responses[i].innerHTML + '<br><i class="fas fa-times-circle"></i>';

        responses[i].addEventListener('click', function () {
            this.remove();
        });
    }
});