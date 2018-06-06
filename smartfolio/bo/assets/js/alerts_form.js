window.addEventListener("DOMContentLoaded", function () {
    // OPEN/CLOSE
    document.querySelector('#alerts > .actions > button').addEventListener('click', function () {
        document.getElementById('form_alerts').classList.remove('hidden');
    });
    document.querySelector('#form_alerts > form > button').addEventListener('click', function () {
        document.getElementById('form_alerts').classList.add('hidden');
    });

    // VARS
    var fields = document.querySelectorAll('#alerts_fields > *'),
    select_alerts = document.querySelector('select[name="alerts_type"]');

    // IF $_POST
    alertsType();

    // CHOOSE A alerts TYPE
    select_alerts.addEventListener('change', alertsType);

    function alertsType() {
        var option = select_alerts.options[select_alerts.selectedIndex].value;

        document.querySelector('#form_alerts > form > input[type="submit"]').removeAttribute('disabled');

        for (var i = 0; i < fields.length; i += 1) {
            console.log("passage "+i);
            if (fields[i].dataset.alerts.indexOf(option) !== -1) {
                console.log(fields[i]+" remove hidden")
                fields[i].classList.remove('hidden');
            } else {
                console.log(fields[i]+" add hidden")
                fields[i].classList.add('hidden');
            }
        }
    }
});