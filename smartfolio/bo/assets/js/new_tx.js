window.addEventListener("DOMContentLoaded", function () {
    // VARS
    var tx_type_blocks = document.querySelectorAll('.tx_type_block'),
    select_tx = document.querySelector('select[name="tx_type"]'),
    pair_input = document.querySelector('input[name="tx_pair_id"]'),
    fee_type_opts = document.querySelectorAll('select[name="tx_fee_type"] > option'),
    dep_curr = document.querySelector('input[name="tx_transfer_curr_id"]');

    // IF $_POST
    setAmountPrefix();

    // CHOOSE A TX TYPE
    select_tx.addEventListener('change', function () {
        var option = select_tx.options[select_tx.selectedIndex].value;

        document.querySelector('input[type="submit"]').removeAttribute('disabled');

        for (var i = 0; i < tx_type_blocks.length; i += 1) {
            if (tx_type_blocks[i].id == option) {
                tx_type_blocks[i].classList.add('selected');
            } else {
                tx_type_blocks[i].classList.remove('selected');
            }
        }
    });

    // BUY >> SET FEE TYPES ACCORDING TO PAIR
    pair_input.addEventListener('change', setAmountPrefix);
    // DEPOSIT >> SET CURRENCY SYMBOL FOR AMOUNT
    dep_curr.addEventListener('change', setAmountPrefix);

    function SetFeeTypes() {
        if (pair_input.value.length > 0) {
            var opt_vals = [
                pair_input.value.split('/')[0],
                (pair_input.value.split('/')[1] || ['Index']).split(':')[0],
                '%' + pair_input.value.split('/')[0],
                '%' + (pair_input.value.split('/')[1] || ['Index']).split(':')[0]
            ];

            for (var i = 1; i < fee_type_opts.length; i += 1) {
                fee_type_opts[i].textContent = opt_vals[i - 1];
            }
        }
    }

    function setAmountPrefix() {
        switch (select_tx.options[select_tx.selectedIndex].value) {
            case 'deposit':
            document.querySelector('#tx_amount > .prefix > span').textContent = dep_curr.value.match(/\[(.*?)\]/)[1];
            break;
            case 'buy':
            document.querySelector('#tx_amount > .prefix > span').textContent = pair_input.value.split('/')[0];
            document.querySelector('#tx_price > .prefix > span').textContent = (pair_input.value.split('/')[1] || [' ']).split(':')[0];
            SetFeeTypes();
            break;
        }
    }
});