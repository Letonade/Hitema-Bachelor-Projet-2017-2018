window.addEventListener("DOMContentLoaded", function () {
    // OPEN/CLOSE
    document.querySelector('#history > .actions > button').addEventListener('click', function () {
        document.getElementById('form_tx').classList.remove('hidden');
    });
    document.querySelector('#form_tx > form > button').addEventListener('click', function () {
        document.getElementById('form_tx').classList.add('hidden');
    });

    // VARS
    var fields = document.querySelectorAll('#tx_fields > *'),
    select_tx = document.querySelector('select[name="tx_type"]'),
    pair_input = document.querySelector('input[name="tx_pair_id"]'),
    fee_type_opts = document.querySelectorAll('select[name="tx_fee_type"] > option');

    // IF $_POST
    setAmountPrefix();
    TxType();

    // CHOOSE A TX TYPE
    select_tx.addEventListener('change', TxType);

    // BUY >> SET FEE TYPES ACCORDING TO PAIR
    pair_input.addEventListener('change', setAmountPrefix);
    pair_input.addEventListener('input', SetFeeTypes);

    function SetFeeTypes() {
        if (pair_input.value.length > 0) {
            var opt_vals = [
                (pair_input.value.split('/')[1] || ['Index']).split(':')[0],
                '%' + (pair_input.value.split('/')[1] || ['Index']).split(':')[0]
            ];

            for (var i = 2, j = 0; i < fee_type_opts.length; i += 2, j += 1) {
                if (['buy', 'sell'].indexOf(select_tx.options[select_tx.selectedIndex].value) !== -1) {
                    fee_type_opts[i].removeAttribute('disabled');
                    fee_type_opts[i].textContent = opt_vals[j];
                } else {
                    fee_type_opts[i].setAttribute('disabled', '');
                }
            }
        }
    }

    function setAmountPrefix() {
        switch (select_tx.options[select_tx.selectedIndex].value) {
            case 'buy' || 'sell':
            if (pair_input.value.length > 0) {
                document.querySelector('#tx_price > .prefix > span').textContent = (pair_input.value.split('/')[1] || [' ']).split(':')[0];
                SetFeeTypes();
            }
            break;
        }
    }

    function TxType() {
        var option = select_tx.options[select_tx.selectedIndex].value;

        document.querySelector('input[type="submit"]').removeAttribute('disabled');

        for (var i = 0; i < fields.length; i += 1) {
            if (fields[i].dataset.tx.indexOf(option) !== -1) {
                fields[i].classList.remove('hidden');
            } else {
                fields[i].classList.add('hidden');
            }
        }
        SetFeeTypes();
    }
});